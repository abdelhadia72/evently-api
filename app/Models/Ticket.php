<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'event_id',
        'user_id',
        'status',
        'check_in_time',
        'qr_code',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            $ticket->ticket_number = 'TKT-'.str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
            $ticket->qr_code = self::generateQRHash($ticket->user_id, $ticket->event_id);
        });
    }

    public static function generateQRHash($userId, $eventId): string
    {
        $secret = config('app.key');

        return hash('sha256', $userId.$eventId.$secret);
    }

    public function verifyQRCode(string $qrCode): bool
    {
        return hash_equals($this->qr_code, $qrCode);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
