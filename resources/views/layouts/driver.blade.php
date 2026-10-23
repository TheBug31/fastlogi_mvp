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

<x-ui.toast />

<header class="sticky top-0 z-30 border-b border-slate-200 bg-white">
    <div class="mx-auto flex min-h-16 max-w-[1100px] flex-wrap items-center justify-between gap-x-4 px-3 py-2 sm:px-6">
        <div class="flex min-w-0 flex-1 items-center justify-between gap-3 md:flex-none md:justify-start md:gap-8">
            <img src="{{ asset('images/brand/logo-horizontal.png') }}" alt="FastLogi" class="h-8 w-auto shrink-0"/>

            <nav class="flex min-w-0 items-center gap-4 overflow-x-auto md:h-16 md:gap-6" aria-label="Navegación principal">
                @foreach ($items as [$patterns, $route, $label])
                    @php
                        $active = request()->routeIs(...$patterns);
                        $enabled = Route::has($route);
                    @endphp

                    @if ($enabled)
                        <a href="{{ route($route) }}"
                           @if ($active) aria-current="page" @endif
                           class="inline-flex min-h-11 shrink-0 items-center border-b-2 text-xs font-medium transition-colors sm:text-sm md:h-16 {{ $active ? 'border-teal-600 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-900' }}">
                            {{ $label }}
                        </a>
                    @else
                        <span aria-disabled="true" title="Módulo no disponible en este MVP"
                              class="pointer-events-none inline-flex h-16 items-center border-b-2 border-transparent text-sm font-medium text-slate-400 opacity-50">
                            {{ $label }}
                        </span>
                    @endif
                @endforeach
            </nav>
        </div>

        <div class="flex w-full items-center justify-end gap-2 sm:gap-3 md:w-auto">
            @if ($locationActive)
                <span class="hidden items-center gap-1.5 whitespace-nowrap rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-800 sm:inline-flex">
                    <x-icon name="map-pin" class="h-4 w-4" />
                    Ubicación activa
                </span>
            @endif

            @if ($driver)
                <livewire:driver.availability-toggle />
            @endif

            <span class="h-6 w-px bg-slate-200" aria-hidden="true"></span>

            <div class="flex min-w-0 items-center gap-2 sm:gap-2.5">
                <div class="hidden text-right sm:block">
                    <div class="text-sm font-semibold leading-tight text-slate-900">{{ auth()->user()?->name }}</div>
                    <div class="text-xs text-slate-500">{{ auth()->user()?->role?->label() }}</div>
                </div>
                <x-ui.avatar :name="auth()->user()?->name ?? ''" />
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Cerrar sesión" aria-label="Cerrar sesión"
                        class="flex items-center rounded-lg p-2 text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600">
                    <x-icon name="arrow-right-on-rectangle" class="h-5 w-5" />
                </button>
            </form>
        </div>
    </div>
</header>

<main>
    <div class="mx-auto max-w-[1100px] px-4 py-5 sm:px-6 sm:py-8">
        @yield('content')
    </div>
</main>
@endsection
