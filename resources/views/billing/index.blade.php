<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $tenant->name }} &mdash; {{ __('Billing') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Current status --}}
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Current plan</div>
                        <div class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ \Illuminate\Support\Str::title($tenant->plan) }}</div>
                        @if ($subscription)
                            <div class="mt-1 text-sm {{ $subscription->active() ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                Status: {{ $subscription->stripe_status }}
                                @if ($onTrial) &middot; on trial @endif
                            </div>
                        @endif
                    </div>
                    @if ($subscription)
                        <a href="{{ route('billing.portal') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-900 text-sm font-medium rounded-md">
                            Manage in billing portal
                        </a>
                    @endif
                </div>
            </div>

            {{-- Plans --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach ($plans as $key => $plan)
                    <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6 flex flex-col">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $plan['name'] }}</h3>
                        <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">
                            ${{ $plan['price'] }}<span class="text-sm font-normal text-gray-500">/mo</span>
                        </div>
                        <ul class="mt-4 space-y-2 text-sm text-gray-600 dark:text-gray-400 flex-1">
                            @foreach ($plan['features'] as $feature)
                                <li class="flex items-start gap-2">
                                    <span class="text-green-500">&check;</span> {{ $feature }}
                                </li>
                            @endforeach
                        </ul>
                        <div class="mt-6">
                            @if ($tenant->plan === $key)
                                <span class="block text-center text-sm text-gray-500 dark:text-gray-400">Current plan</span>
                            @elseif (empty($plan['price_id']))
                                <span class="block text-center text-sm text-gray-400">&mdash;</span>
                            @else
                                <form method="POST" action="{{ route('billing.checkout', ['plan' => $key]) }}">
                                    @csrf
                                    <x-primary-button class="w-full justify-center">Choose {{ $plan['name'] }}</x-primary-button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <p class="text-xs text-gray-400 text-center">
                Payments are processed by Stripe. Set your Stripe keys and price IDs in <code>.env</code> to enable checkout.
            </p>
        </div>
    </div>
</x-app-layout>
