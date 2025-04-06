<div>
    <div class="max-w-7xl mx-auto">
        <!-- Hero Section -->
        <div class="left-0 right-0 bg-gradient-to-r from-lime-200 to-blue-700 dark:from-teal-800 dark:to-teal-900 rounded-2xl">
            <div class="px-4 py-8 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                        Event Registration Settings
                    </h1>
                    <p class="mt-3 text-lg text-teal-100">
                        Configure your event registration settings, including deadlines, capacity, and waitlist options.
                    </p>
                </div>
            </div>
        </div>

        <div class="p-4 sm:p-6 lg:p-8">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Registration Settings</h2>
                    <flux:button icon="pencil" wire:click="toggleEdit" variant="primary" class="bg-teal-500">
                        {{ $isEditing ? 'Cancel' : 'Edit Settings' }}
                    </flux:button>
                </div>

                @if($isEditing)
                    <form wire:submit.prevent="save" class="space-y-8">
                        <!-- Registration Settings -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-3">Registration Settings</h3>
                            <div class="space-y-6">
                                <div>
                                    <flux:label for="registration_deadline" class="block mb-2">Registration Deadline</flux:label>
                                    <flux:input id="registration_deadline" type="date" wire:model="settings.registration_deadline" class="w-full" />
                                    @error('settings.registration_deadline') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <flux:label for="max_attendees" class="block mb-2">Maximum Attendees</flux:label>
                                    <flux:input id="max_attendees" type="number" wire:model="settings.max_attendees" class="w-full" min="1" />
                                    @error('settings.max_attendees') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div class="flex items-center justify-between">
                                    <flux:label for="enable_waitlist" class="block">Enable Waitlist</flux:label>
                                    <flux:switch id="enable_waitlist" wire:model="settings.enable_waitlist" />
                                    @error('settings.enable_waitlist') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                                </div>
                                @if($settings->enable_waitlist)
                                    <div>
                                        <flux:label for="waitlist_capacity" class="block mb-2">Waitlist Capacity</flux:label>
                                        <flux:input id="waitlist_capacity" type="number" wire:model="settings.waitlist_capacity" class="w-full" min="1" />
                                        @error('settings.waitlist_capacity') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <flux:button type="button" wire:click="toggleEdit" variant="outline">
                                Cancel
                            </flux:button>
                            <flux:button type="submit" variant="primary" class="bg-teal-500">
                                Save Settings
                            </flux:button>
                        </div>
                    </form>
                @else
                    <div class="space-y-8">
                        <!-- Registration Settings -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-3">Registration Settings</h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Registration Deadline</span>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                        {{ $settings->registration_deadline ? $settings->registration_deadline->format('M d, Y') : 'Not set' }}
                                    </p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Maximum Attendees</span>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                        {{ $settings->max_attendees ?? 'No limit' }}
                                    </p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Waitlist</span>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                        {{ $settings->enable_waitlist ? 'Enabled' : 'Disabled' }}
                                        @if($settings->enable_waitlist && $settings->waitlist_capacity)
                                            (Capacity: {{ $settings->waitlist_capacity }})
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
