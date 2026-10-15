@extends('layouts.base')

@section('viewport', 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover')
@section('body_class', 'bg-surface font-body-md text-on-surface flex flex-col min-h-screen')

@section('body')
<header class="fixed top-0 inset-x-0 z-50 bg-surface/80 backdrop-blur-xl pt-safe"><div class="h-16 px-gutter flex items-center justify-between"><span class="text-headline-lg font-headline-lg uppercase text-on-surface tracking-tight">Rutas</span><div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center"><span class="material-symbols-outlined text-on-primary text-[18px]">person</span></div></div></header>
<main class="flex flex-col relative w-full px-gutter pt-16 pb-24 bg-surface flex-grow">
@yield('content')
</main>
<nav class="fixed bottom-0 inset-x-0 z-50 pb-safe bg-surface/90 backdrop-blur-xl"><div class="flex justify-around items-center h-20 px-space-sm">
@php
    $items = [
        ['driver.routes', 'driver.routes', 'route', 'Rutas'],
        ['driver.history', 'driver.history', 'history', 'Historial'],
        ['driver.profile', 'driver.profile', 'account_circle', 'Perfil'],
    ];
@endphp
@foreach ($items as [$pattern, $route, $icon, $label])
    @php $active = request()->routeIs($pattern); @endphp
    <a href="{{ Route::has($route) ? route($route) : '#' }}"
       @if($active) aria-current="page" @endif
       class="flex flex-col items-center justify-center gap-space-xs w-20 h-16 transition-all {{ $active ? 'bg-surface-container-high text-primary font-bold' : 'text-on-surface-variant hover:text-on-surface' }}">
        <span class="material-symbols-outlined text-[24px]">{{ $icon }}</span>
        <span class="text-label-md uppercase">{{ $label }}</span>
    </a>
@endforeach
</div></nav>
@endsection
