<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name') }} &mdash; Multi-tenant SaaS Starter Kit</title>
        <script>
            if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        </script>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased h-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
        <div class="min-h-full">
            <header class="max-w-5xl mx-auto px-6 py-6 flex items-center justify-between">
                <span class="font-semibold text-lg">{{ config('app.name') }}</span>
                <nav class="flex items-center gap-4 text-sm">
                    @auth
                        <a href="{{ route('dashboard') }}" class="font-medium hover:underline">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="hover:underline">Log in</a>
                        <a href="{{ route('register') }}" class="px-4 py-2 rounded-md bg-indigo-600 text-white font-medium hover:bg-indigo-500">Get started</a>
                    @endauth
                </nav>
            </header>

            <main class="max-w-5xl mx-auto px-6">
                <section class="py-20 text-center">
                    <h1 class="text-4xl sm:text-5xl font-bold tracking-tight">
                        A multi-tenant SaaS starter kit with <span class="text-indigo-600 dark:text-indigo-400">a database per tenant</span>.
                    </h1>
                    <p class="mt-6 text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                        Laravel, teams &amp; roles, Stripe billing, and true multi-database tenant isolation —
                        ready to fork. Most starter kits scope by a column; this one gives every organization its own database.
                    </p>
                    <div class="mt-8 flex items-center justify-center gap-4">
                        <a href="{{ route('register') }}" class="px-6 py-3 rounded-md bg-indigo-600 text-white font-medium hover:bg-indigo-500">Create an organization</a>
                        <a href="https://github.com" class="px-6 py-3 rounded-md border border-gray-300 dark:border-gray-700 font-medium hover:bg-gray-100 dark:hover:bg-gray-800">View on GitHub</a>
                    </div>
                </section>

                <section class="grid grid-cols-1 sm:grid-cols-3 gap-6 pb-20">
                    <div class="p-6 rounded-lg bg-white dark:bg-gray-800 shadow">
                        <h3 class="font-semibold">Database-per-tenant</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Physical isolation you can't forget to apply. No global scopes, no leaked rows.</p>
                    </div>
                    <div class="p-6 rounded-lg bg-white dark:bg-gray-800 shadow">
                        <h3 class="font-semibold">Teams &amp; roles</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Owner / Admin / Member, invitations, and org switching out of the box.</p>
                    </div>
                    <div class="p-6 rounded-lg bg-white dark:bg-gray-800 shadow">
                        <h3 class="font-semibold">Stripe billing</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Laravel Cashier subscriptions, trials, and the hosted billing portal.</p>
                    </div>
                </section>
            </main>
        </div>
    </body>
</html>
