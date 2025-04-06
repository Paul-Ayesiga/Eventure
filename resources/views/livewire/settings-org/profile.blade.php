<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public $profile_image;
    public $phone_number = '';
    public $social_media_links = '';
    public $address = '';
    public $city = '';
    public $country = '';
    public $postal_code = '';
    public $temp_image;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;

        if ($user->userDetail) {
            $this->phone_number = $user->userDetail->phone_number ?? '';
            $this->social_media_links = $user->userDetail->social_media_links ?? '';
            $this->profile_image = $user->userDetail->profile_image ?? '';
            $this->address = $user->userDetail->address ?? '';
            $this->city = $user->userDetail->city ?? '';
            $this->country = $user->userDetail->country ?? '';
            $this->postal_code = $user->userDetail->postal_code ?? '';
        }
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id)
            ],
            'temp_image' => ['nullable', 'image', 'max:1024'], // 1MB max
            'phone_number' => ['nullable', 'string', 'max:20'],
            'social_media_links' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
        ]);

        if ($this->temp_image) {
            $imagePath = $this->temp_image->store('profile-images', 'public');
            $this->profile_image = $imagePath;
        }

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Update or create organiser details
        $user->userDetail()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'phone_number' => $this->phone_number,
                'social_media_links' => $this->social_media_links,
                'profile_image' => $this->profile_image,
                'address' => $this->address,
                'city' => $this->city,
                'country' => $this->country,
                'postal_code' => $this->postal_code,
            ]
        );

        $this->dispatch('profile-updated', name: $user->name);
        $this->dispatch('toast', 'Profile updated!', 'success', 'top-right');
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Delete the user's profile image.
     */
    public function deleteProfileImage(): void
    {
        $user = Auth::user();

        if ($user->userDetail && $user->userDetail->profile_image) {
            // Delete the file from storage
            Storage::disk('public')->delete($user->userDetail->profile_image);

            // Update the database
            $user->userDetail->update(['profile_image' => null]);

            // Update the component state
            $this->profile_image = null;

            $this->dispatch('toast', 'Profile image deleted!', 'success', 'top-right');
        }
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.org-layout :heading="__('Profile')" :subheading="__('Update your profile information')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <!-- Basic Information -->
            <div class="space-y-6">
                <h3 class="text-lg font-medium">Basic Information</h3>


                <div class="flex items-center justify-center w-full">
                    <label for="profile-image-upload" class="relative cursor-pointer">
                        <div class="w-32 h-32 rounded-full overflow-hidden bg-gray-100 dark:bg-gray-700 border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500 transition-all">
                            @if($temp_image)
                                <div wire:loading wire:target="temp_image" class=" flex items-center justify-center bg-gray-100 dark:bg-gray-700 bg-opacity-75">
                                    <svg w-7 h-7 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><circle fill="none" stroke-opacity="1" stroke="#FF156D" stroke-width=".5" cx="100" cy="100" r="0"><animate attributeName="r" calcMode="spline" dur="2" values="1;80" keyTimes="0;1" keySplines="0 .2 .5 1" repeatCount="indefinite"></animate><animate attributeName="stroke-width" calcMode="spline" dur="2" values="0;25" keyTimes="0;1" keySplines="0 .2 .5 1" repeatCount="indefinite"></animate><animate attributeName="stroke-opacity" calcMode="spline" dur="2" values="1;0" keyTimes="0;1" keySplines="0 .2 .5 1" repeatCount="indefinite"></animate></circle></svg>
                                </div>
                                <img src="{{ $temp_image->temporaryUrl() }}" alt="Profile Preview" class="w-full h-full object-cover">
                            @elseif($profile_image)
                                <img src="{{ Storage::url($profile_image) }}" alt="Profile Image" class="w-full h-full object-cover">
                            @else
                                <div class="flex items-center justify-center w-full h-full">
                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                        @if($profile_image)
                        <flux:tooltip :content="__('delete')" position="bottom">
                            <button
                                type="button"
                                wire:click="deleteProfileImage"
                                class="cursor-pointer absolute top-0 left-0 p-1.5 rounded-full bg-red-600 text-white hover:bg-red-700 transition-colors"

                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </flux:tooltip>
                        @endif
                        <div class="absolute bottom-0 right-0 p-1.5 rounded-full bg-primary-600 text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </div>
                        <input
                            id="profile-image-upload"
                            type="file"
                            wire:model="temp_image"
                            accept="image/*"
                            class="hidden"
                        >
                    </label>
                </div>

                <div class="text-sm text-center text-gray-600 dark:text-gray-400 mt-2">
                    Click to upload or drag and drop<br>
                    SVG, PNG, JPG (max. 1MB)
                </div>

                @error('temp_image')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror

                <div class="space-y-4">
                    <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />
                    <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />
                     @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
                </div>
            </div>


            <!-- Organization Details -->
            <div class="space-y-6">
                <h3 class="text-lg font-medium">More Details</h3>

                <div class="space-y-4">
                    <flux:input wire:model="phone_number" :label="__('Phone Number')" type="tel" />
                    <flux:input wire:model="social_media_links" :label="__('Social Media Links')" type="text" />
                </div>
            </div>

            <!-- Address Information -->
            <div class="space-y-6">
                <h3 class="text-lg font-medium">Address Information</h3>

                <div class="space-y-4">
                    <flux:input wire:model="address" :label="__('Address')" type="text" />
                    <flux:input wire:model="city" :label="__('City')" type="text" />
                    <flux:input wire:model="country" :label="__('Country')" type="text" />
                    <flux:input wire:model="postal_code" :label="__('Postal Code')" type="text" />
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        <livewire:settings-org.delete-user-form />
    </x-settings.org-layout>
</section>
