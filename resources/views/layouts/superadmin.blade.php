@extends('layouts.base')

@section('body')
@php
    $items = [
        [['superadmin.dashboard'], 'superadmin.dashboard', 'home', 'Dashboard'],
        [['superadmin.businesses.*'], 'superadmin.businesses.index', 'building-storefront', 'Negocios'],
        [['superadmin.metrics.*'], 'superadmin.metrics.index', 'chart-bar', 'Métricas'],
    ];
@endphp

<x-ui.toast />

<aside class="fixed inset-y-0 left-0 z-30 hidden w-64 flex-col justify-between bg-sidebar lg:flex">
    <div>
        <div class="flex h-16 items-center gap-2 border-b border-white/10 px-6">
            <img src="{{ asset('images/brand/logo-horizontal-dark.png') }}" alt="FastLogi" class="h-8 w-auto"/>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-xs font-medium text-teal-300">SuperAdmin</span>
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
                        <x-icon :name="$icon" class="h-5 w-5" />
                        <span>{{ $label }}</span>
                    </a>
                @else
                    <span aria-disabled="true"
                          class="pointer-events-none flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-sm font-medium text-slate-400 opacity-50">
                        <x-icon :name="$icon" class="h-5 w-5" />
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
                <x-icon name="arrow-right-on-rectangle" class="h-5 w-5" />
            </button>
        </form>
    </div>
</aside>

<div class="min-h-screen lg:pl-64" x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false">
    <div x-cloak x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 lg:hidden" role="dialog" aria-modal="true" aria-label="Menú principal">
        <button type="button" class="absolute inset-0 h-full w-full bg-slate-950/60" aria-label="Cerrar menú" @click="sidebarOpen = false"></button>
        <aside id="superadmin-mobile-menu" class="relative flex h-full w-72 max-w-[85vw] flex-col justify-between bg-sidebar shadow-2xl" @click.stop>
            <div>
                <div class="flex h-16 items-center justify-between border-b border-white/10 px-5">
                    <div class="flex items-center gap-2">
                        <img src="{{ asset('images/brand/logo-horizontal-dark.png') }}" alt="FastLogi" class="h-8 w-auto"/>
                        <span class="rounded-full bg-white/10 px-2 py-0.5 text-xs font-medium text-teal-300">SuperAdmin</span>
                    </div>
                    <button type="button" class="rounded-lg p-2 text-slate-300 hover:bg-sidebar-active hover:text-white" aria-label="Cerrar menú" @click="sidebarOpen = false">
                        <x-icon name="x-mark" class="h-5 w-5" />
                    </button>
                </div>
                <nav class="space-y-1 p-3" aria-label="Navegación principal">
                    @foreach ($items as [$patterns, $route, $icon, $label])
                        @php $active = request()->routeIs(...$patterns); $enabled = Route::has($route); @endphp
                        @if ($enabled)
                            <a href="{{ route($route) }}" @if ($active) aria-current="page" @endif @click="sidebarOpen = false"
                               class="flex min-h-11 items-center gap-3 rounded-lg px-3.5 py-2.5 text-sm font-medium {{ $active ? 'border-l-[3px] border-teal-500 bg-sidebar-active text-white' : 'text-slate-300 hover:bg-sidebar-active hover:text-white' }}">
                                <x-icon :name="$icon" class="h-5 w-5" /><span>{{ $label }}</span>
                            </a>
                        @endif
                    @endforeach
                </nav>
            </div>
            <div class="flex items-center justify-between border-t border-white/10 px-5 py-4">
                <span class="flex items-center gap-2 text-xs font-medium text-slate-400"><span class="h-2 w-2 rounded-full bg-teal-500" aria-hidden="true"></span>Servicio en línea</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex min-h-10 items-center gap-2 rounded-lg px-3 text-sm font-medium text-slate-300 hover:bg-sidebar-active hover:text-white">
                        <x-icon name="arrow-right-on-rectangle" class="h-4 w-4" /><span>Salir</span>
                    </button>
                </form>
            </div>
        </aside>
    </div>

    <header class="sticky top-0 z-20 flex min-h-16 items-center justify-between gap-3 border-b border-slate-200 bg-white px-4 py-2 sm:px-6 lg:px-8">
        <div class="flex items-center gap-2.5">
            <button type="button" class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 lg:hidden"
                    aria-label="Abrir menú" aria-controls="superadmin-mobile-menu" :aria-expanded="sidebarOpen.toString()" @click="sidebarOpen = true">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
            </button>
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-100 text-sm font-semibold text-teal-800" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()?->name ?? 'S', 0, 1)) }}</span>
            <span class="text-base font-semibold text-slate-900">Plataforma</span>
        </div>

        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2.5">
                <div class="hidden text-right sm:block">
                    <div class="text-sm font-semibold leading-tight text-slate-900">{{ auth()->user()?->name }}</div>
                    <div class="text-xs text-slate-500">{{ auth()->user()?->role?->label() }}</div>
                </div>
                <x-ui.avatar :name="auth()->user()?->name ?? ''" />
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl space-y-5 p-4 sm:space-y-6 sm:p-6 lg:p-8">
        @yield('content')
    </main>
</div>
@endsection
