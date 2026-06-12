<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Projects') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- New project --}}
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-4">New project</h3>
                <form method="POST" action="{{ route('tenant.projects.store') }}" class="flex flex-col sm:flex-row gap-3">
                    @csrf
                    <x-text-input name="name" type="text" class="flex-1" placeholder="Project name" required />
                    <x-text-input name="description" type="text" class="flex-1" placeholder="Description (optional)" />
                    <x-primary-button>Add</x-primary-button>
                </form>
            </div>

            {{-- List --}}
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden">
                @forelse ($projects as $project)
                    <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between gap-4">
                        <div>
                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ $project->name }}</div>
                            @if ($project->description)
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $project->description }}</div>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('tenant.projects.update', ['project' => $project->id]) }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="name" value="{{ $project->name }}">
                                <input type="hidden" name="description" value="{{ $project->description }}">
                                <input type="hidden" name="status" value="{{ $project->status === 'active' ? 'archived' : 'active' }}">
                                <button class="text-xs px-2 py-1 rounded-full {{ $project->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">
                                    {{ $project->status }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('tenant.projects.destroy', ['project' => $project->id]) }}"
                                  onsubmit="return confirm('Delete this project?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-sm text-red-600 dark:text-red-400 hover:underline">Delete</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="p-6 text-sm text-gray-500 dark:text-gray-400">No projects yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
