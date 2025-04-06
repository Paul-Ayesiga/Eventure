<?php

namespace App\Livewire\Org\Events;

use App\Models\Events;
use App\Models\EventSetting;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class EventSettings extends Component
{
    public $eventId;
    public $event;
    public $settings;
    public $isEditing = false;

    public $registration_deadline;
    public $max_attendees;
    public bool $enable_waitlist = false;
    public $waitlist_capacity;

    protected $rules = [
        'settings.registration_deadline' => 'nullable|date',
        'settings.max_attendees' => 'nullable|integer|min:1',
        'settings.enable_waitlist' => 'boolean',
        'settings.waitlist_capacity' => 'nullable|integer|min:1',
    ];

    public function mount($id)
    {
        $this->eventId = $id;
        $this->event = Events::findOrFail($id);
        $this->settings = $this->event->settings;

    }

    public function toggleEdit()
    {
        $this->isEditing = !$this->isEditing;
    }

    public function save()
    {
        $this->validate();

        $this->settings->update([
            'registration_deadline' => $this->registration_deadline,
            'max_attendees' => $this->max_attendees,
            'enable_waitlist' => $this->enable_waitlist,
            'waitlist_capacity' => $this->waitlist_capacity,
        ]);

        $this->settings->save();

        $this->isEditing = false;
        $this->dispatch('settings-updated');
        session()->flash('message', 'Settings updated successfully.');
    }

    public function render()
    {
        return view('livewire.org.events.event-settings')
            ->layout('components.layouts.event-detail', ['eventId' => $this->eventId]);
    }
}
