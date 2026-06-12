<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Create organization') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                    Each organization gets its own isolated database. Pick a name — we'll generate a
                    URL-safe slug and provision the database automatically.
                </p>

                <form method="POST" action="{{ route('organizations.store') }}" class="space-y-6">
                    @csrf
                    <div>
                        <x-input-label for="name" :value="__('Organization name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                      :value="old('name')" required autofocus placeholder="Acme Inc." />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end">
                        <x-primary-button>{{ __('Create organization') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
