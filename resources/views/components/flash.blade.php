@if (session('status'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
         class="max-w-7xl mx-auto mt-4 px-4 sm:px-6 lg:px-8">
        <div class="rounded-md bg-green-50 dark:bg-green-900/40 border border-green-200 dark:border-green-800 p-4 flex items-center justify-between">
            <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('status') }}</p>
            <button @click="show = false" class="text-green-700 dark:text-green-300 hover:opacity-70">&times;</button>
        </div>
    </div>
@endif

@if ($errors->any())
    <div class="max-w-7xl mx-auto mt-4 px-4 sm:px-6 lg:px-8">
        <div class="rounded-md bg-red-50 dark:bg-red-900/40 border border-red-200 dark:border-red-800 p-4">
            <ul class="list-disc list-inside text-sm text-red-800 dark:text-red-200 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
