<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Laravel\Scout\Searchable;

class Event extends Model
{
    use Searchable;

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'status',
        'venue',
        'organiser_id'
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'auto_convert_timezone' => 'boolean',
        'tags' => 'array'
    ];

    public function searchableAs(): string
    {
        return 'users_index';
    }

    // The organiser of the event
    public function organiser()
    {
        return $this->belongsTo(User::class, 'organiser_id');
    }

    // Bookings for the event
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'event_id');
    }

    // Attendees that booked the event (many-to-many through bookings)
    public function attendees()
    {
        return $this->belongsToMany(User::class, 'bookings');
    }

    public function location()
    {
        return $this->hasOne(EventLocation::class, 'event_id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'event_id');
    }

    // Event settings
    public function settings()
    {
        return $this->hasOne(EventSetting::class, 'event_id');
    }

    protected static function booted()
    {
        static::saved(function ($event) {
            Cache::tags(['events_' . $event->organiser_id])->flush();
        });

        static::deleted(function ($event) {
            Cache::tags(['events_' . $event->organiser_id])->flush();
        });
    }

}
