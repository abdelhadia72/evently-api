<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Database\Seeder;

class TicketTypeSeeder extends Seeder
{
    public function run(): void
    {
        // Get all events
        $events = Event::all();

        foreach ($events as $event) {
            // Standard ticket
            TicketType::create([
                'event_id' => $event->id,
                'name' => 'Standard',
                'price' => rand(1000, 5000) / 100, // Random price between $10-$50
                'quantity' => 100,
                'description' => 'Standard admission ticket',
            ]);

            // VIP ticket
            TicketType::create([
                'event_id' => $event->id,
                'name' => 'VIP',
                'price' => rand(7500, 15000) / 100, // Random price between $75-$150
                'quantity' => 30,
                'description' => 'VIP experience with premium benefits and exclusive access',
            ]);

            // Early Bird (limited quantity)
            TicketType::create([
                'event_id' => $event->id,
                'name' => 'Early Bird',
                'price' => rand(500, 1500) / 100, // Random price between $5-$15
                'quantity' => 20,
                'description' => 'Limited early bird tickets at a special price',
            ]);
        }
    }
}
