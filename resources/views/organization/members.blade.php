<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $tenant->name }} &mdash; {{ __('Members') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if ($canManage)
                {{-- Invite --}}
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-4">Invite a member</h3>
                    <form method="POST" action="{{ route('invitations.store') }}" class="flex flex-col sm:flex-row gap-3">
                        @csrf
                        <x-text-input name="email" type="email" class="flex-1" placeholder="person@example.com" required />
                        <select name="role" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            @foreach ($assignableRoles as $role)
                                <option value="{{ $role->value }}">{{ $role->label() }}</option>
                            @endforeach
                        </select>
                        <x-primary-button>Send invite</x-primary-button>
                    </form>
                </div>
            @endif

            {{-- Members --}}
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 font-semibold text-gray-800 dark:text-gray-200">Members</div>
                @foreach ($members as $membership)
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between gap-4">
                        <div>
                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ $membership->user->name }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $membership->user->email }}</div>
                        </div>
                        <div class="flex items-center gap-3">
                            @if ($canManage && $membership->role->value !== 'owner')
                                <form method="POST" action="{{ route('members.update', ['membership' => $membership->id]) }}">
                                    @csrf
                                    @method('PUT')
                                    <select name="role" onchange="this.form.submit()" class="text-sm rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                        @foreach ($assignableRoles as $role)
                                            <option value="{{ $role->value }}" @selected($membership->role === $role)>{{ $role->label() }}</option>
                                        @endforeach
                                    </select>
                                </form>
                                <form method="POST" action="{{ route('members.destroy', ['membership' => $membership->id]) }}"
                                      onsubmit="return confirm('Remove this member?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-sm text-red-600 dark:text-red-400 hover:underline">Remove</button>
                                </form>
                            @else
                                <span class="text-sm px-2 py-1 rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">{{ $membership->role->label() }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pending invitations --}}
            @if ($invitations->isNotEmpty())
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 font-semibold text-gray-800 dark:text-gray-200">Pending invitations</div>
                    @foreach ($invitations as $invitation)
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between gap-4">
                            <div>
                                <div class="text-gray-900 dark:text-gray-100">{{ $invitation->email }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $invitation->role->label() }} &middot; expires {{ $invitation->expires_at?->diffForHumans() }}</div>
                            </div>
                            @if ($canManage)
                                <form method="POST" action="{{ route('invitations.destroy', ['invitation' => $invitation->id]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-sm text-red-600 dark:text-red-400 hover:underline">Revoke</button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
