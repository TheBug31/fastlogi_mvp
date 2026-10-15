@extends('layouts.guest')

@section('title', 'Iniciar sesión')

{{-- Controlador: validar 'identifier' (correo o teléfono) + 'password'.
     Credenciales inválidas -> withErrors(['identifier' => ...]).
     Cuenta/negocio suspendido -> ->with('suspended', true)->withErrors(['identifier' => 'suspended']). --}}
@section('content')
<div class="flex flex-col w-full items-center justify-center py-space-md">

<!-- Main Login Card -->
<div class="w-full max-w-[480px] bg-surface-container-lowest rounded-lg shadow-xl overflow-hidden transition-all duration-200">
<!-- Subtle Top Accent Indicator -->
<div class="h-1.5 w-full bg-gradient-to-r from-primary-container via-primary to-inverse-surface"></div>
<div class="p-space-lg md:p-space-xl flex flex-col gap-space-lg">
<!-- Card Header -->
<div class="flex flex-col items-center text-center gap-space-sm">
<div class="flex items-center justify-center gap-2">
<img alt="FastLogi Brand Logo" class="h-9 w-auto object-contain" src="https://lh3.googleusercontent.com/aida/AEtjO1Wvdd5_rBH0xSqpbopsGvcLkdZvn1LynW-D5SKLpKi9M9_gYx6GOgGxpa2exaZsR3i0er3SJisxTK05nkRxJ3ffQfaz6cYfzx5HS0_GvlM5bj5_sRVt7WJfCS8Rao1QncGiiGpqsznvXbZEFR_aMB3FpnrYyHIfzAjuF0LrYSky7jp2Hf9H2BAewUehrBjkZpcj9ASD1qy031NXwrxiFoowv434IWE1e5hS1jzMxxEKOZqI57DMW80RrQeT"/>
<span class="font-headline-lg text-headline-lg text-on-surface uppercase tracking-tight">Fast<span class="text-primary-container font-bold">Logi</span></span>
</div>
<span class="inline-flex items-center gap-1.5 px-space-sm py-0.5 rounded-full bg-surface-container text-on-surface-variant font-label-sm text-label-sm tracking-wide">
<span class="w-1.5 h-1.5 rounded-full bg-primary-container animate-pulse"></span>
          Colombia 🇨🇴 • Multi-Tenant Platform
        </span>
<p class="font-body-md text-body-md text-on-surface-variant max-w-[340px]">
          Gestiona tus pedidos y domicilios sin intermediarios
        </p>
</div>
<!-- Interactive Alerts Section -->
@if($errors->has('identifier') && !session('suspended'))<div class="flex items-start gap-space-sm p-space-sm bg-error-container text-on-error-container rounded-lg transition-all animate-fadeIn">
<span class="material-symbols-outlined text-[20px] text-error shrink-0">error</span>
<div class="flex-1">
<p class="font-label-lg text-label-lg font-bold text-on-error-container">Credenciales incorrectas</p>
<p class="font-body-sm text-body-sm text-on-error-container opacity-90">El correo o la contraseña ingresada no coinciden con nuestros registros del sistema.</p>
</div>
</div>@endif
@if(session('suspended'))<div class="flex items-start gap-space-sm p-space-sm bg-tertiary-fixed text-on-tertiary-fixed rounded-lg transition-all animate-fadeIn">
<span class="material-symbols-outlined text-[20px] text-tertiary shrink-0">lock_clock</span>
<div class="flex-1">
<p class="font-label-lg text-label-lg font-bold text-on-tertiary-fixed">Cuenta suspendida o inactiva</p>
<p class="font-body-sm text-body-sm text-on-tertiary-fixed opacity-90">Tu empresa o usuario se encuentra temporalmente inactivo. Contacta al administrador de tu tenant o soporte.</p>
</div>
</div>@endif
<!-- Login Form -->
<form action="{{ Route::has('login.store') ? route('login.store') : '#' }}" class="flex flex-col gap-space-md" id="loginForm" method="POST">@csrf
<!-- Field 1: Identifier -->
<div class="flex flex-col gap-1">
<label class="font-label-md text-label-md text-on-surface font-semibold flex items-center justify-between" for="identifier">
<span>Correo o teléfono</span>
<span class="text-on-surface-variant text-[11px] font-normal">Identificador único</span>
</label>
<div class="relative flex items-center">
<span class="material-symbols-outlined absolute left-3 text-secondary text-[20px] pointer-events-none">alternate_email</span>
<input class="w-full pl-10 pr-4 py-2.5 bg-surface rounded-lg font-body-md text-body-md text-on-surface placeholder:text-outline focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary-container transition-all" id="identifier" name="identifier" placeholder="ejemplo@@negocio.com o 300 123 4567" required="" type="text" value="{{ old('identifier') }}"/>
</div>
</div>
<!-- Field 2: Password -->
<div class="flex flex-col gap-1">
<div class="flex items-center justify-between">
<label class="font-label-md text-label-md text-on-surface font-semibold" for="password">
              Contraseña
            </label>
<a class="font-label-md text-label-md text-primary hover:text-primary-container transition-colors" href="#">
              ¿Olvidaste tu contraseña?
            </a>
</div>
<div class="relative flex items-center">
<span class="material-symbols-outlined absolute left-3 text-secondary text-[20px] pointer-events-none">lock</span>
<input class="w-full pl-10 pr-12 py-2.5 bg-surface rounded-lg font-body-md text-body-md text-on-surface placeholder:text-outline focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary-container transition-all" id="password" name="password" placeholder="••••••••••" required="" type="password"/>
<button aria-label="Alternar visibilidad de contraseña" class="absolute right-2.5 p-1 text-secondary hover:text-on-surface transition-colors flex items-center justify-center rounded" onclick="togglePasswordVisibility()" type="button">
<span class="material-symbols-outlined text-[20px]" id="password-toggle-icon">visibility</span>
</button>
</div>
</div>
<!-- Options: Remember Device -->
<div class="flex items-center justify-between pt-1">
<label class="flex items-center gap-2 cursor-pointer select-none">
<input class="w-4 h-4 rounded text-primary-container bg-surface focus:ring-primary-container accent-[#14b8a6]" name="remember" type="checkbox"/>
<span class="font-body-sm text-body-sm text-on-surface-variant">Recordar sesión en este equipo</span>
</label>
</div>
<!-- Submit Button -->
<button class="w-full mt-2 py-3 px-space-md rounded-lg bg-primary hover:bg-primary-container text-on-primary font-headline-sm text-headline-sm text-center shadow-md hover:shadow-lg transition-all duration-150 flex items-center justify-center gap-2 font-bold group" type="submit">
<span>Iniciar sesión</span>
<span class="material-symbols-outlined text-[20px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
</button>
</form>
<!-- Institutional Tenant Access Notice -->
<div class="p-space-sm bg-surface-container rounded-lg flex items-start gap-space-sm">
<span class="material-symbols-outlined text-primary text-[20px] shrink-0 mt-0.5">verified_user</span>
<div class="flex flex-col">
<span class="font-label-sm text-label-sm text-on-surface uppercase font-bold tracking-wider">Acceso Restringido</span>
<p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
            Acceso exclusivo para comercios afiliados. Las cuentas son creadas y gestionadas únicamente por el SuperAdministrador.
          </p>
</div>
</div>
</div>
<!-- Card Bottom System Telemetry Bar -->
<div class="px-space-lg py-3 bg-surface-container-low flex flex-wrap items-center justify-between text-on-surface-variant font-label-sm text-label-sm">
<span class="flex items-center gap-1">
<span class="w-2 h-2 rounded-full bg-primary-container"></span>
        Servidores Operacionales (Bogotá DC)
      </span>
<span class="font-data-mono text-data-mono">v2.4.0-CO</span>
</div>
</div>
<!-- Operational Credentials Guidance -->
<div class="mt-space-md text-center max-w-sm flex flex-col items-center gap-1">
<p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">
      Plataforma desarrollada por Nexlab Studio • Todos los derechos reservados
    </p>
<div class="flex items-center justify-center gap-space-md text-on-surface-variant font-label-sm text-label-sm mt-1">
<a class="hover:text-primary transition-colors" href="#">Términos de servicio</a>
<span>•</span>
<a class="hover:text-primary transition-colors" href="#">Políticas de privacidad</a>
<span>•</span>
<a class="hover:text-primary transition-colors" href="#">Centro de ayuda pyme</a>
</div>
</div>
</div>
<script>
  function togglePasswordVisibility() {
    const pwdInput = document.getElementById('password');
    const toggleIcon = document.getElementById('password-toggle-icon');
    if (!pwdInput || !toggleIcon) return;

    if (pwdInput.type === 'password') {
      pwdInput.type = 'text';
      toggleIcon.textContent = 'visibility_off';
    } else {
      pwdInput.type = 'password';
      toggleIcon.textContent = 'visibility';
    }
  }

  function setDemoState(state) {
    const alertCreds = document.getElementById('alert-credentials');
    const alertSusp = document.getElementById('alert-suspended');
    const btnNormal = document.getElementById('btn-demo-normal');
    const btnCreds = document.getElementById('btn-demo-credentials');
    const btnSusp = document.getElementById('btn-demo-suspended');

    const resetButtons = () => {
      [btnNormal, btnCreds, btnSusp].forEach(btn => {
        btn.classList.remove('bg-primary', 'text-on-primary');
        btn.classList.add('bg-surface-container-lowest', 'text-on-surface');
      });
    };

    resetButtons();

    if (state === 'normal') {
      alertCreds.classList.add('hidden');
      alertSusp.classList.add('hidden');
      btnNormal.classList.add('bg-primary', 'text-on-primary');
      btnNormal.classList.remove('bg-surface-container-lowest', 'text-on-surface');
    } else if (state === 'credentials') {
      alertCreds.classList.remove('hidden');
      alertSusp.classList.add('hidden');
      btnCreds.classList.add('bg-primary', 'text-on-primary');
      btnCreds.classList.remove('bg-surface-container-lowest', 'text-on-surface');
    } else if (state === 'suspended') {
      alertCreds.classList.add('hidden');
      alertSusp.classList.remove('hidden');
      btnSusp.classList.add('bg-primary', 'text-on-primary');
      btnSusp.classList.remove('bg-surface-container-lowest', 'text-on-surface');
    }
  }
</script>
@endsection

@push('scripts')
<script>
function togglePasswordVisibility() {
    const pwdInput = document.getElementById('password');
    const toggleIcon = document.getElementById('password-toggle-icon');
    if (!pwdInput || !toggleIcon) return;

    if (pwdInput.type === 'password') {
      pwdInput.type = 'text';
      toggleIcon.textContent = 'visibility_off';
    } else {
      pwdInput.type = 'password';
      toggleIcon.textContent = 'visibility';
    }
  }
</script>
@endpush
