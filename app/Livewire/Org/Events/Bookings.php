<?php

namespace App\Livewire\Org\Events;

use App\Events\UpdateChart;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\Attendee;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Bookings extends Component
{
    use WithPagination;

    public int $eventId;
    public $event;

    // For modal control
    public $isModalOpen = false;
    public $selectedBooking = null;

    // For simulation
    public $isSimulating = false;
    public $simulationCount = 1;
    public $selectedTicketId;
    public $ticketQuantity = 1;
    public $selectedCustomerId = null;
    public $customers = [];
    public $joinWaitingList = true;

    public function mount($id)
    {
        $this->eventId = $id;
        $this->event = Event::findOrFail($id);
        $this->loadCustomers();

        // Check if user is authorized to manage this event
        // if ($this->event->organiser->organiser_id !== Auth::id()) {
        //     abort(403, 'Unauthorized action.');
        // }
    }

    public function loadCustomers()
    {
        $this->customers = \App\Models\User::role('user')
            ->with('userDetail')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->userDetail?->phone_number,
                ];
            });
    }

    public function viewBooking($bookingId)
    {
        $this->selectedBooking = Booking::with(['tickets', 'attendees'])->findOrFail($bookingId);
        $this->isModalOpen = true;
    }

    public function printBooking($bookingId)
    {
        // Implement print functionality
        $this->dispatch('print-booking', bookingId: $bookingId);
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->selectedBooking = null;
    }

    public function simulatePurchase()
    {
        // First validate basic requirements
        $this->validate([
            'selectedCustomerId' => [
                'required',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    $customer = \App\Models\User::find($value);
                    if (!$customer || !$customer->hasRole('user')) {
                        $fail('Selected customer is not valid.');
                    }
                }
            ],
            'selectedTicketId' => [
                'required',
                'exists:tickets,id',
                function ($attribute, $value, $fail) {
                    $ticket = Ticket::find($value);

                    if (!$ticket || $ticket->event_id !== $this->eventId) {
                        $fail('Selected ticket is not valid for this event.');
                    }
                    if ($ticket->status !== 'active') {
                        $fail('Selected ticket is not active.');
                    }
                }
            ],
            'ticketQuantity' => [
                'required',
                'integer',
                'min:1'
            ]
        ]);

        // Get the ticket after basic validation passes
        $ticket = Ticket::find($this->selectedTicketId);
        if (!$ticket) {
            $this->addError('selectedTicketId', 'Selected ticket is not valid.');
            return;
        }

        $customer = \App\Models\User::findOrFail($this->selectedCustomerId);

        // Check if ticket is available for sale
        if (!now()->between($ticket->sale_start_date, $ticket->sale_end_date)) {
            if ($this->joinWaitingList) {
                // Check if user is already on the waiting list
                $existingEntry = $ticket->waitingList()
                    ->where('user_id', $customer->id)
                    ->where('status', 'pending')
                    ->first();

                if ($existingEntry) {
                    $this->dispatch('toast', "You are already on the waiting list for {$ticket->name}. Your requested quantity has been updated.", 'info', 'top-right');
                } else {
                    $this->dispatch('toast', "You have been added to the waiting list for {$ticket->name}.", 'info', 'top-right');
                }
                // Add to waiting list if not available
                $ticket->addToWaitingList($customer, $this->ticketQuantity);
            } else {
                $this->dispatch('toast', "Ticket is not available for sale at this time.", 'error', 'top-right');
            }
            $this->reset(['selectedCustomerId', 'selectedTicketId', 'ticketQuantity', 'isSimulating']);
            return;
        }

        // Validate ticket-specific rules
        $this->validate([
            'ticketQuantity' => [
                function ($attribute, $value, $fail) use ($ticket, $customer) {
                    if ($ticket->max_tickets_per_booking && $value > $ticket->max_tickets_per_booking) {
                        $fail("Cannot book more than {$ticket->max_tickets_per_booking} tickets per booking.");
                    }
                    if ($ticket->quantity_available < $value) {
                        if ($this->joinWaitingList) {
                            // Check if user is already on the waiting list
                            $existingEntry = $ticket->waitingList()
                                ->where('user_id', $customer->id)
                                ->where('status', 'pending')
                                ->first();

                            if ($existingEntry) {
                                $fail("You are already on the waiting list for this ticket. Your requested quantity has been updated.");
                            } else {
                                $fail("Not enough tickets available. You have been added to the waiting list.");
                            }
                            // Add to waiting list if not enough tickets
                            $ticket->addToWaitingList($customer, $value);
                        } else {
                            $fail("Not enough tickets available. Only {$ticket->quantity_available} tickets remaining.");
                        }
                    }
                }
            ]
        ]);

        // Create booking
        $booking = Booking::create([
            'event_id' => $this->eventId,
            'user_id' => $customer->id,
            'booking_reference' => 'TEST-' . Str::random(8),
            'status' => 'confirmed',
            'total_amount' => $ticket->price * $this->ticketQuantity,
            'payment_status' => 'paid'
        ]);

        // Create booking item
        $booking->tickets()->attach($ticket->id, [
            'quantity' => $this->ticketQuantity,
            'unit_price' => $ticket->price,
            'subtotal' => $ticket->price * $this->ticketQuantity
        ]);

        // Create attendee for each ticket
        for ($i = 0; $i < $this->ticketQuantity; $i++) {
            Attendee::create([
                'booking_id' => $booking->id,
                'ticket_id' => $ticket->id,
                'first_name' => $customer->name,
                'last_name' => '',
                'email' => $customer->email,
                'phone' => $customer->userDetail?->phone_number
            ]);
        }

        // Update ticket quantities
        $ticket->increment('quantity_sold', $this->ticketQuantity);

        // Reset form
        $this->reset(['selectedCustomerId', 'selectedTicketId', 'ticketQuantity', 'isSimulating']);

        $this->dispatch('toast', "Successfully created booking for {$customer->name}.", 'success', 'top-right');
        $this->dispatch('booking-changed');
    }

    public function deleteBooking($bookingId)
    {
        $booking = Booking::findOrFail($bookingId);

        // Check if booking is already cancelled
        if ($booking->status === 'cancelled') {
            $this->dispatch('toast', 'Booking is already cancelled.', 'error', 'top-right');
            return;
        }

        // Update ticket quantities before deleting
        foreach ($booking->tickets as $ticket) {
            $ticket->decrement('quantity_sold', $ticket->pivot->quantity);
        }

        // Delete the booking (this will cascade delete attendees and booking items)
        $booking->delete();

        $this->dispatch('booking-changed');
        $this->dispatch('toast', 'Booking deleted successfully.', 'success', 'top-right');
    }

    public function render()
    {
        $bookings = $this->event->bookings()
            ->with([
                'tickets' => function ($query) {
                    $query->withPivot('quantity', 'unit_price', 'subtotal');
                },
                'attendees' => function ($query) {
                    $query->with('ticket');
                }
            ])
            ->latest()
            ->paginate(10);

        return view('livewire.org.events.bookings', [
            'bookings' => $bookings,
            'tickets' => $this->event->tickets()->where('status', 'active')->get()
        ])->layout('components.layouts.event-detail', ['eventId' => $this->eventId]);
    }
}
