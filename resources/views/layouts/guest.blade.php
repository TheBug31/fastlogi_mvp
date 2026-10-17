@extends('layouts.base')

@section('body_class', 'bg-white text-slate-900 antialiased')

@section('body')
<div class="flex min-h-screen flex-col md:flex-row">
    <section class="flex flex-col justify-between gap-10 bg-sidebar p-14 text-white md:w-1/2">
        <img src="{{ asset('images/brand/logo-horizontal-dark.png') }}" alt="FastLogi" class="h-8 w-auto"/>

        <div>
            <h1 class="text-4xl font-bold tracking-tight">Tu operación de domicilios, bajo control</h1>

            <ul class="mt-10 space-y-5">
                <li class="flex items-center gap-4">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-white/10 bg-white/5 text-teal-400" aria-hidden="true">
                        <x-icon name="truck" />
                    </span>
                    <span class="text-sm text-slate-300">Pedidos y repartidores en una sola pantalla</span>
                </li>
                <li class="flex items-center gap-4">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-white/10 bg-white/5 text-teal-400" aria-hidden="true">
                        <x-icon name="clock" />
                    </span>
                    <span class="text-sm text-slate-300">Trazabilidad de cada entrega</span>
                </li>
                <li class="flex items-center gap-4">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-white/10 bg-white/5 text-teal-400" aria-hidden="true">
                        <x-icon name="chart-bar" />
                    </span>
                    <span class="text-sm text-slate-300">Reportes de tu operación</span>
                </li>
            </ul>
        </div>

        <span class="text-xs text-slate-400">Colombia</span>
    </section>

    <section class="flex flex-col bg-white p-8 md:w-1/2">
        <div class="flex flex-1 items-center justify-center">
            <div class="w-full max-w-[400px]">
                @yield('content')
            </div>
        </div>

        <p class="pt-6 text-center text-xs text-slate-400">Nexlab Studio</p>
    </section>
</div>
@endsection
