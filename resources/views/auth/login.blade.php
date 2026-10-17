@extends('layouts.guest')

@section('title', 'Iniciar sesión')

{{-- Controlador: validar 'identifier' (correo o teléfono) + 'password'.
     Credenciales inválidas -> withErrors(['identifier' => ...]).
     Cuenta/negocio suspendido -> ->with('suspended', true)->withErrors(['identifier' => 'suspended']). --}}
@section('content')
@php
    $suspended = (bool) session('suspended');
    $credentialError = $errors->has('identifier') && ! $suspended;
@endphp

<h1 class="text-2xl font-bold tracking-tight text-slate-900">Iniciar sesión</h1>

@if ($suspended)
    <x-ui.alert type="warning" class="mt-5">
        Tu cuenta o tu negocio está suspendido. Contacta al administrador.
    </x-ui.alert>
@elseif ($credentialError)
    <x-ui.alert type="error" class="mt-5">
        Correo, teléfono o contraseña incorrectos
    </x-ui.alert>
@endif

<form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-5">
    @csrf

    <x-ui.input
        label="Correo o teléfono"
        name="identifier"
        :value="old('identifier')"
        autocomplete="username"
        :invalid="$credentialError"
        :show-error="false"
        required
    />

    <div x-data="{ show: false }">
        <x-ui.input
            label="Contraseña"
            name="password"
            type="password"
            ::type="show ? 'text' : 'password'"
            autocomplete="current-password"
            :invalid="$credentialError"
            required
        >
            <x-slot:suffix>
                <button
                    type="button"
                    @click="show = !show"
                    :aria-label="show ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                    class="flex items-center rounded text-slate-400 transition-colors hover:text-slate-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600"
                >
                    <x-icon name="eye" x-show="!show" />
                    <x-icon name="eye-slash" x-show="show" x-cloak />
                </button>
            </x-slot:suffix>
        </x-ui.input>
    </div>

    <x-ui.checkbox name="remember" label="Recordar sesión" />

    <x-ui.button type="submit" class="w-full">Iniciar sesión</x-ui.button>
</form>

<p class="mt-6 text-center text-xs text-slate-500">
    Las cuentas son creadas por el administrador de la plataforma.
</p>
@endsection
