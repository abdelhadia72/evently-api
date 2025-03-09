<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        // Get all orders
        $orders = Order::all();

        foreach ($orders as $order) {
            // Skip if order doesn't have ticket data (shouldn't happen but just in case)
            if (! isset($order->ticketsData)) {
                continue;
            }

            // Create tickets for each ticket type
            foreach ($order->ticketsData as $ticketData) {
                for ($i = 0; $i < $ticketData['quantity']; $i++) {
                    $status = $order->status === 'completed' ? 'active' : 'cancelled';

                    // Generate a unique ticket number
                    $ticketNumber = 'TCK-'.strtoupper(Str::random(8));

                    // Create the ticket
                    Ticket::create([
                        'ticket_number' => $ticketNumber,
                        'event_id' => $order->event_id,
                        'user_id' => $order->user_id,
                        'order_id' => $order->id,
                        'ticket_type_id' => $ticketData['ticket_type_id'],
                        'price_paid' => $ticketData['price'],
                        'status' => $status,
                        'qr_code' => 'qr_'.Str::random(20), // Simplified QR code generation
                        'created_at' => $order->created_at,
                    ]);
                }
            }
        }
    }
}
