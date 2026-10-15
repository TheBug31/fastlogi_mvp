@extends('layouts.base')

@section('body')
<aside class="fixed left-0 top-0 h-full w-64 bg-surface-container-lowest z-50 flex flex-col border-r border-outline-variant"><div class="h-16 px-space-md flex items-center gap-space-sm border-b border-outline-variant bg-surface-container-low"><div class="w-8 h-8 bg-primary-container text-on-primary-container flex items-center justify-center font-headline-lg text-headline-sm font-bold">FL</div><div class="flex flex-col"><span class="font-headline-sm text-headline-sm text-on-surface tracking-tight leading-none">FastLogi</span><span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Operaciones CO</span></div></div><div class="p-space-md flex-1 overflow-y-auto"><div class="px-space-xs mb-space-sm font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Menú Principal</div><nav class="space-y-space-xs">@php
    $items = [
        [['owner.dashboard'], 'owner.dashboard', 'grid_view', 'Dashboard'],
        [['owner.orders.index', 'owner.orders.show'], 'owner.orders.index', 'local_shipping', 'Pedidos'],
        [['owner.orders.create'], 'owner.orders.create', 'add_circle', 'Nuevo pedido'],
        [['owner.inventory.*'], 'owner.inventory.index', 'inventory_2', 'Inventario'],
        [['owner.drivers.*'], 'owner.drivers.index', 'two_wheeler', 'Repartidores'],
        [['owner.reports.*'], 'owner.reports.index', 'analytics', 'Reportes'],
        [['owner.settings.*'], 'owner.settings.index', 'settings', 'Configuración'],
    ];
@endphp
@foreach ($items as [$patterns, $route, $icon, $label])
    @php $active = request()->routeIs(...$patterns); @endphp
    <a href="{{ Route::has($route) ? route($route) : '#' }}"
       @if($active) aria-current="page" @endif
       class="flex items-center gap-space-sm px-space-md py-space-sm rounded-lg transition-colors {{ $active ? 'bg-primary-container text-on-primary-container font-semibold' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface font-body-md text-body-md' }}">
        <span class="material-symbols-outlined text-[20px]">{{ $icon }}</span>
        <span>{{ $label }}</span>
    </a>
@endforeach</nav></div><div class="p-space-md border-t border-outline-variant bg-surface-container-low"><div class="flex items-center justify-between text-on-surface-variant font-body-sm text-body-sm"><span>Estado Telemetría</span><span class="flex items-center gap-1 font-label-sm text-label-sm text-primary uppercase"><span class="w-2 h-2 rounded-full bg-primary-container animate-pulse"></span> En línea</span></div></div></aside>
<div class="pl-64">
<header class="fixed top-0 left-64 right-0 h-16 bg-surface-container-lowest/90 backdrop-blur-md border-b border-outline-variant z-40 px-space-lg flex items-center justify-between"><div class="flex items-center gap-space-md"><div class="flex items-center gap-space-sm px-space-sm py-space-xs rounded-lg border border-outline-variant bg-surface-container-low"><span class="material-symbols-outlined text-primary text-[20px]">storefront</span><span class="font-body-md text-body-md font-medium text-on-surface">{{-- TODO: nombre del negocio del usuario autenticado --}}Restaurante El Leñador - Sede Bogotá 93</span><span class="material-symbols-outlined text-on-surface-variant text-[18px]">expand_more</span></div></div><div class="flex items-center gap-space-md"><button class="relative p-space-sm text-on-surface-variant hover:text-on-surface hover:bg-surface-container transition-colors rounded-lg" type="button"><span class="material-symbols-outlined text-[22px]">notifications</span><span class="absolute top-1 right-1 w-4 h-4 bg-error text-on-error font-label-sm text-label-sm rounded-full flex items-center justify-center leading-none">3</span></button><div class="h-6 w-px bg-outline-variant"></div><div class="flex items-center gap-space-sm"><div class="text-right"><div class="font-headline-sm text-label-lg font-semibold text-on-surface leading-tight">Dueño de negocio</div><div class="font-body-sm text-body-sm text-on-surface-variant">Admin General</div></div><div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center"><span class="material-symbols-outlined text-on-primary text-[18px]">person</span></div></div></div></header>
<main class="relative pt-16 w-full min-h-screen px-space-lg py-space-md bg-surface">
@yield('content')
</main>
</div>
@endsection
