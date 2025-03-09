<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // Get users and events
        $users = User::where('email', '!=', 'admin@example.com')->get();
        $events = Event::all();

        // Check if we have users and events
        if ($users->isEmpty()) {
            echo "No users found. Skipping OrderSeeder.\n";

            return;
        }

        if ($events->isEmpty()) {
            echo "No events found. Skipping OrderSeeder.\n";

            return;
        }

        // Payment methods
        $paymentMethods = ['credit_card', 'paypal', 'bank_transfer'];

        // Create 30 orders
        for ($i = 0; $i < 30; $i++) {
            // Randomly select a user and event
            $user = $users->random();
            $event = $events->random();

            // Get ticket types for this event
            $ticketTypes = $event->ticketTypes;

            if ($ticketTypes->isEmpty()) {
                // Skip this iteration and try another event
                continue;
            }

            // Randomly select 1-2 ticket types
            $selectedTicketTypes = $ticketTypes->random(rand(1, min(2, $ticketTypes->count())));

            // Calculate total amount
            $totalAmount = 0;
            $ticketsData = [];

            foreach ($selectedTicketTypes as $ticketType) {
                $quantity = rand(1, 3);
                $totalAmount += $ticketType->price * $quantity;

                $ticketsData[] = [
                    'ticket_type_id' => $ticketType->id,
                    'quantity' => $quantity,
                    'price' => $ticketType->price,
                ];
            }

            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'event_id' => $event->id,
                'order_number' => 'ORD-'.strtoupper(uniqid()),
                'total_amount' => $totalAmount,
                'status' => rand(0, 10) > 1 ? 'completed' : 'cancelled',
                'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                'created_at' => now()->subDays(rand(1, 30)),
            ]);

            // Store ticket data in order object for TicketSeeder to use
            $order->ticketsData = $ticketsData;
        }
    }
}
