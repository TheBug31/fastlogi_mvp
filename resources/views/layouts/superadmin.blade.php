@extends('layouts.base')

@section('body_class', 'bg-background font-body-md text-on-surface antialiased')

@section('body')
<aside class="fixed left-0 top-0 h-full w-64 bg-surface-container-lowest shadow-[0_1px_8px_rgba(0,0,0,0.04)] z-50 flex flex-col"><div class="h-16 px-space-lg flex items-center gap-space-sm bg-surface-container-lowest"><span class="material-symbols-outlined text-primary text-[28px]">local_shipping</span><div class="flex flex-col"><span class="font-headline-sm text-headline-sm text-on-surface tracking-tight leading-tight">FastLogi</span><span class="font-label-sm text-label-sm text-primary uppercase tracking-widest">SuperAdmin</span></div></div><div class="px-space-md py-space-sm"><div class="px-space-sm py-space-xs font-label-sm text-label-sm uppercase text-on-surface-variant">Navegación Principal</div></div><nav class="flex-1 px-space-md flex flex-col gap-space-xs">@php
    $items = [
        [['superadmin.dashboard'], 'superadmin.dashboard', 'grid_view', 'Dashboard'],
        [['superadmin.businesses.*'], 'superadmin.businesses.index', 'store', 'Negocios'],
        [['superadmin.metrics.*'], 'superadmin.metrics.index', 'monitoring', 'Métricas'],
    ];
@endphp
@foreach ($items as [$patterns, $route, $icon, $label])
    @php $active = request()->routeIs(...$patterns); @endphp
    <a href="{{ Route::has($route) ? route($route) : '#' }}"
       @if($active) aria-current="page" @endif
       class="flex items-center gap-space-sm px-space-md py-space-sm rounded-lg transition-colors {{ $active ? 'bg-primary-container text-on-primary-container font-semibold' : 'text-on-surface-variant font-body-md text-body-md hover:bg-surface-container hover:text-on-surface' }}">
        <span class="material-symbols-outlined text-[20px]">{{ $icon }}</span>
        <span>{{ $label }}</span>
    </a>
@endforeach</nav><div class="p-space-md bg-surface-container-lowest"><div class="p-space-md rounded-lg bg-surface-container-low flex flex-col gap-space-xs"><div class="flex items-center gap-space-xs text-primary"><span class="material-symbols-outlined text-[16px]">hub</span><span class="font-label-md text-label-md uppercase tracking-wider">Red Logística</span></div><p class="font-body-sm text-body-sm text-on-surface-variant leading-tight">Conectado a troncales nacionales de Colombia.</p></div></div></aside>
<div class="pl-64">
<header class="fixed top-0 left-64 right-0 h-16 bg-surface-container-lowest shadow-[0_1px_8px_rgba(0,0,0,0.04)] z-40 flex items-center justify-between px-space-lg"><div class="flex items-center gap-space-md"><div class="inline-flex items-center gap-space-xs px-space-sm py-space-xs rounded-full bg-secondary-container text-on-secondary-fixed"><span class="material-symbols-outlined text-[16px] text-primary">pin_drop</span><span class="font-label-md text-label-md uppercase">Colombia Hub</span></div></div><div class="flex items-center gap-space-md"><div class="relative flex items-center justify-center"><button class="w-9 h-9 flex items-center justify-center rounded-lg text-on-surface-variant hover:bg-surface-container hover:text-on-surface transition-colors" type="button"><span class="material-symbols-outlined text-[20px]">notifications</span></button><span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-error"></span></div><div class="h-6 w-px bg-outline-variant"></div><div class="flex items-center gap-space-sm cursor-pointer select-none"><div class="flex flex-col text-right"><span class="font-label-lg text-label-lg text-on-surface leading-tight">{{ auth()->user()?->name ?? 'SuperAdministrador' }}</span><span class="font-body-sm text-body-sm text-on-surface-variant">Nexlab Studio</span></div><div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center"><span class="material-symbols-outlined text-on-primary text-[18px]">person</span></div><form method="POST" action="{{ route('logout') }}" class="inline ml-1">@csrf<button type="submit" title="Cerrar sesión" class="p-1.5 text-on-surface-variant hover:text-error transition-colors rounded-lg flex items-center"><span class="material-symbols-outlined text-[20px]">logout</span></button></form></div></div></header>
<main class="relative w-full pt-16 bg-background px-space-lg py-space-lg">
@yield('content')
</main>
</div>
@endsection
