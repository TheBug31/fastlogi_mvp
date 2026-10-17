@extends('layouts.base')

@section('body')
@php
    $business = auth()->user()?->business;

    $items = [
        [['owner.dashboard'], 'owner.dashboard', 'home', 'Dashboard'],
        [['owner.orders.index', 'owner.orders.show'], 'owner.orders.index', 'truck', 'Pedidos'],
        [['owner.orders.create'], 'owner.orders.create', 'plus-circle', 'Nuevo pedido'],
        [['owner.inventory.*'], 'owner.inventory.index', 'archive-box', 'Inventario'],
        [['owner.drivers.*'], 'owner.drivers.index', 'users', 'Repartidores'],
        [['owner.reports.*'], 'owner.reports.index', 'chart-bar', 'Reportes'],
        [['owner.settings.*'], 'owner.settings.index', 'cog', 'Configuración'],
    ];
@endphp

<aside class="fixed inset-y-0 left-0 z-30 flex w-64 flex-col justify-between bg-sidebar">
    <div>
        <div class="flex h-16 items-center border-b border-white/10 px-6">
            <img src="{{ asset('images/brand/logo-horizontal-dark.png') }}" alt="FastLogi" class="h-8 w-auto"/>
        </div>

        <nav class="space-y-1 p-3">
            @foreach ($items as [$patterns, $route, $icon, $label])
                @php
                    $active = request()->routeIs(...$patterns);
                    $enabled = Route::has($route);
                @endphp

                @if ($enabled)
                    <a href="{{ route($route) }}"
                       @if ($active) aria-current="page" @endif
                       class="flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-sm font-medium transition-colors {{ $active ? 'border-l-[3px] border-teal-500 bg-sidebar-active text-white' : 'text-slate-400 hover:bg-sidebar-active hover:text-white' }}">
                        <x-icon :name="$icon" />
                        <span>{{ $label }}</span>
                    </a>
                @else
                    <span aria-disabled="true"
                          class="pointer-events-none flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-sm font-medium text-slate-400 opacity-50">
                        <x-icon :name="$icon" />
                        <span>{{ $label }}</span>
                    </span>
                @endif
            @endforeach
        </nav>
    </div>

    <div class="flex items-center justify-between gap-2 border-t border-white/10 px-6 py-4">
        <span class="flex items-center gap-2 text-xs font-medium text-slate-400">
            <span class="h-2 w-2 rounded-full bg-teal-500" aria-hidden="true"></span>
            Servicio en línea
        </span>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" title="Cerrar sesión" aria-label="Cerrar sesión"
                    class="flex items-center rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-sidebar-active hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">
                <x-icon name="arrow-right-on-rectangle" />
            </button>
        </form>
    </div>
</aside>

<div class="min-h-screen pl-64">
    <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-slate-200 bg-white px-8">
        <div class="flex items-center gap-2.5">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-100 text-sm font-semibold text-teal-800" aria-hidden="true">{{ mb_strtoupper(mb_substr($business?->name ?? 'F', 0, 1)) }}</span>
            <span class="text-base font-semibold text-slate-900">{{ $business?->name ?? 'FastLogi' }}</span>
        </div>

        <div class="flex items-center gap-4">
            <livewire:owner.notification-bell />

            <span class="h-6 w-px bg-slate-200" aria-hidden="true"></span>

            <div class="flex items-center gap-2.5">
                <div class="text-right">
                    <div class="text-sm font-semibold leading-tight text-slate-900">{{ auth()->user()?->name }}</div>
                    <div class="text-xs text-slate-500">{{ auth()->user()?->role?->label() }}</div>
                </div>
                <x-ui.avatar :name="auth()->user()?->name ?? ''" />
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl space-y-6 p-8">
        @yield('content')
    </main>
</div>
@endsection
