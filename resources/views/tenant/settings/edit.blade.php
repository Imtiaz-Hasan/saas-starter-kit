<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ tenant('name') }} &mdash; {{ __('Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                    These settings live in this organization's own database and never mix with other tenants.
                </p>

                <form method="POST" action="{{ route('tenant.settings.update') }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="tagline" :value="__('Tagline')" />
                        <x-text-input id="tagline" name="tagline" type="text" class="mt-1 block w-full"
                                      :value="old('tagline', $tagline)" placeholder="A short description of your organization" />
                        <x-input-error :messages="$errors->get('tagline')" class="mt-2" />
                    </div>

                    <label class="flex items-center gap-3">
                        <input type="checkbox" name="weekly_digest" value="1" @checked($weeklyDigest)
                               class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Email me a weekly digest</span>
                    </label>

                    <div class="flex items-center justify-end">
                        <x-primary-button>{{ __('Save settings') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
