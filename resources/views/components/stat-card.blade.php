@props(['label', 'value'])

<div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $label }}</div>
    <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $value }}</div>
</div>
