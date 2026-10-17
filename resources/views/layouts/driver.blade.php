@extends('layouts.base')

@section('body')
@php
    $driver = auth()->user()?->deliveryDriver;

    // Ubicación activa: la telemetría se envía cada 60 s mientras hay pedidos en tránsito (RF-011).
    $locationActive = $driver?->last_seen_at?->gt(now()->subMinutes(3)) ?? false;

    $items = [
        [['driver.routes', 'driver.orders.show'], 'driver.routes', 'Rutas de hoy'],
        [['driver.history'], 'driver.history', 'Historial'],
    ];
@endphp

<header class="fixed inset-x-0 top-0 z-30 h-16 border-b border-slate-200 bg-white">
    <div class="mx-auto flex h-full max-w-[1100px] items-center justify-between px-6">
        <div class="flex h-full items-center gap-8">
            <img src="{{ asset('images/brand/logo-horizontal.png') }}" alt="FastLogi" class="h-8 w-auto"/>

            <nav class="flex h-full items-center gap-6">
                @foreach ($items as [$patterns, $route, $label])
                    @php
                        $active = request()->routeIs(...$patterns);
                        $enabled = Route::has($route);
                    @endphp

                    @if ($enabled)
                        <a href="{{ route($route) }}"
                           @if ($active) aria-current="page" @endif
                           class="inline-flex h-16 items-center border-b-2 text-sm font-medium transition-colors {{ $active ? 'border-teal-600 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-900' }}">
                            {{ $label }}
                        </a>
                    @else
                        <span aria-disabled="true"
                              class="pointer-events-none inline-flex h-16 items-center border-b-2 border-transparent text-sm font-medium text-slate-400 opacity-50">
                            {{ $label }}
                        </span>
                    @endif
                @endforeach
            </nav>
        </div>

        <div class="flex items-center gap-3">
            @if ($locationActive)
                <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-800">
                    <x-icon name="map-pin" class="h-4 w-4" />
                    Ubicación activa
                </span>
            @endif

            @if ($driver)
                <x-ui.availability-badge :availability="$driver->availability->value" />
            @endif

            <div class="flex items-center gap-2.5">
                <div class="text-right">
                    <div class="text-sm font-semibold leading-tight text-slate-900">{{ auth()->user()?->name }}</div>
                    <div class="text-xs text-slate-500">{{ auth()->user()?->role?->label() }}</div>
                </div>
                <x-ui.avatar :name="auth()->user()?->name ?? ''" />
            </div>
        </div>
    </div>
</header>

<main class="pt-16">
    <div class="mx-auto max-w-[1100px] px-6 py-8">
        @yield('content')
    </div>
</main>
@endsection
