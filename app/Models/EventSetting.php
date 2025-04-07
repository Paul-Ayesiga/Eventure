<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Event;

class EventSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'registration_deadline',
        'max_attendees',
        'enable_waitlist',
        'waitlist_capacity',
        'is_private',
        'access_code',
        'enable_refunds',
        'refund_deadline',
        'enable_discounts',
        'max_discounts_per_booking',
        'enable_live_streaming',
        'streaming_platform',
        'streaming_url',
        'enable_analytics',
        'track_attendance',
        'track_engagement',
        'track_revenue',
    ];

    protected $casts = [
        'registration_deadline' => 'datetime',
        'refund_deadline' => 'datetime',
        'enable_waitlist' => 'boolean',
        'is_private' => 'boolean',
        'enable_refunds' => 'boolean',
        'enable_discounts' => 'boolean',
        'enable_live_streaming' => 'boolean',
        'enable_analytics' => 'boolean',
        'track_attendance' => 'boolean',
        'track_engagement' => 'boolean',
        'track_revenue' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
