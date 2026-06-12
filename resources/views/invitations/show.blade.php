<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Invitation') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6 text-center">
                @if ($invitation->isPending())
                    <p class="text-gray-700 dark:text-gray-300">
                        You've been invited to join <strong>{{ $tenant->name }}</strong>
                        as a <strong>{{ $invitation->role->label() }}</strong>.
                    </p>

                    @if (strcasecmp($invitation->email, auth()->user()->email) !== 0)
                        <p class="mt-4 text-sm text-red-600 dark:text-red-400">
                            This invitation was sent to {{ $invitation->email }}, but you're signed in as
                            {{ auth()->user()->email }}. Sign in with the invited address to accept.
                        </p>
                    @else
                        <form method="POST" action="{{ route('invitations.accept', ['token' => $invitation->token]) }}" class="mt-6">
                            @csrf
                            <x-primary-button>Accept invitation</x-primary-button>
                        </form>
                    @endif
                @else
                    <p class="text-gray-700 dark:text-gray-300">This invitation is no longer valid.</p>
                    <a href="{{ route('dashboard') }}" class="mt-4 inline-block text-sm text-indigo-600 dark:text-indigo-400 hover:underline">Go to dashboard</a>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
