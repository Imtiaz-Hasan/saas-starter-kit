<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ tenant('name') }} &mdash; {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <x-stat-card label="Total projects" :value="$projectCount" />
                <x-stat-card label="Active projects" :value="$activeCount" />
                <x-stat-card label="Your plan" :value="\Illuminate\Support\Str::title(tenant('plan'))" />
            </div>

            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-200">Recent projects</h3>
                    <a href="{{ route('tenant.projects.index') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">View all</a>
                </div>

                @forelse ($recentProjects as $project)
                    <div class="py-2 border-t border-gray-100 dark:border-gray-700 flex justify-between">
                        <span class="text-gray-800 dark:text-gray-200">{{ $project->name }}</span>
                        <span class="text-xs px-2 py-1 rounded-full {{ $project->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">{{ $project->status }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">No projects yet. Create one from the Projects page.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
