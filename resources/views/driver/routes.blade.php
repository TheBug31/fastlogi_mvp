@extends('layouts.driver')

@section('title', 'Rutas de hoy')

@section('content')
<div class="flex flex-col w-full pb-10">
<!-- Status & Warning Banner -->
<div class="flex flex-col gap-space-sm mb-space-md">
<div class="flex items-center justify-between bg-surface-container-high p-space-md rounded-lg shadow-sm">
<div class="flex items-center gap-space-sm">
<span class="w-3 h-3 bg-primary-container rounded-full animate-pulse"></span>
<span class="text-label-lg uppercase font-headline-sm text-on-surface">Estado de Flota</span>
</div>
<div class="bg-primary-container text-on-primary-container px-space-md py-space-xs rounded-full text-label-sm uppercase font-label-sm">
        Disponible
      </div>
</div>
<!-- Alert Banner -->
<div class="bg-secondary-container text-on-secondary-container p-space-md rounded-lg flex items-start gap-space-md shadow-sm">
<span class="material-symbols-outlined text-tertiary-container text-[22px]">warning</span>
<div class="flex flex-col gap-space-xs">
<span class="text-label-md uppercase font-headline-sm text-on-secondary-container">Aviso de Telemetría</span>
<span class="text-body-sm text-on-secondary-container">Ubicación no enviada, mantén la pantalla encendida para asegurar la asignación de rutas óptimas.</span>
</div>
</div>
</div>
<!-- Section 1: Nueva oferta -->
<section class="flex flex-col gap-space-md mb-space-xl">
<div class="flex items-center justify-between">
<h2 class="text-headline-sm font-headline-sm text-on-surface">Nueva oferta disponible</h2>
<span class="text-data-mono bg-surface-container px-space-sm py-space-xs rounded text-primary">ID: #1042</span>
</div>
<!-- Prominent Order Card -->
<div class="bg-surface-container-low rounded-xl p-space-lg shadow-md relative overflow-hidden flex flex-col gap-space-md border border-primary/20">
<div class="absolute top-0 right-0 bg-primary-container text-on-primary-container px-space-md py-space-xs rounded-bl-xl text-label-sm uppercase font-label-sm">
        Express
      </div>
<div class="flex items-center justify-between">
<div class="flex flex-col">
<span class="text-label-md text-on-surface-variant uppercase">Pedido #1042</span>
<span class="text-headline-lg font-headline-lg text-on-surface">Calle 94 # 11-30</span>
<span class="text-body-sm text-on-surface-variant">Chicó, Bogotá D.C.</span>
</div>
<!-- Timer Ring Mock -->
<div class="flex flex-col items-center justify-center w-16 h-16 rounded-full bg-surface-container-highest border-2 border-primary text-primary">
<span class="text-data-mono font-headline-sm">1:12</span>
<span class="text-[9px] uppercase tracking-tighter">Restante</span>
</div>
</div>
<div class="grid grid-cols-2 gap-space-md bg-surface p-space-sm rounded-lg">
<div class="flex flex-col">
<span class="text-label-sm text-on-surface-variant uppercase">Volumen</span>
<span class="text-data-mono font-bold text-on-surface">2 Ítems</span>
</div>
<div class="flex flex-col">
<span class="text-label-sm text-on-surface-variant uppercase">Cobro</span>
<span class="text-data-mono font-bold text-primary">Contra Entrega (Nequi)</span>
</div>
</div>
<!-- Action Buttons -->
<div class="flex gap-space-md mt-space-sm">
<button class="flex-1 bg-surface text-error border border-error py-space-md rounded-lg font-label-md uppercase transition-all hover:bg-error-container hover:text-on-error-container" onclick="alert('Pedido rechazado')">
          Rechazar
        </button>
<button class="flex-1 bg-primary-container text-on-primary-container py-space-md rounded-lg font-label-md uppercase shadow-sm transition-all hover:opacity-90" onclick="alert('¡Pedido aceptado! Redirigiendo a navegación')">
          Aceptar
        </button>
</div>
</div>
</section>
<!-- Section 2: Mis pedidos -->
<section class="flex flex-col gap-space-md">
<div class="flex items-center justify-between">
<h2 class="text-headline-sm font-headline-sm text-on-surface">Mis pedidos en curso</h2>
<span class="text-body-sm text-on-surface-variant">2 activos</span>
</div>
<!-- Order 1: Asignado -->
<div class="bg-surface-container-low rounded-xl p-space-md shadow-sm flex flex-col gap-space-md">
<div class="flex items-center justify-between">
<span class="text-label-md bg-secondary-container text-on-secondary-container px-space-sm py-space-xs rounded uppercase font-label-sm">
          Asignado
        </span>
<span class="text-data-mono text-on-surface-variant">#9821</span>
</div>
<div class="flex flex-col">
<span class="text-headline-sm text-on-surface">Cra. 15 # 85-12</span>
<span class="text-body-sm text-on-surface-variant">Cliente: Mariana Gómez • Tel: 310 456 7890</span>
</div>
<div class="flex gap-space-sm">
<button class="w-full bg-surface text-on-surface border border-outline-variant py-space-sm rounded-lg font-label-md uppercase flex items-center justify-center gap-space-xs hover:bg-surface-container" onclick="alert('Escaneando paquete de recogida...')">
<span class="material-symbols-outlined text-[18px]">qr_code_scanner</span>
          Escanear recogida
        </button>
</div>
</div>
<!-- Order 2: En Tránsito -->
<div class="bg-surface-container-low rounded-xl p-space-md shadow-sm flex flex-col gap-space-md">
<div class="flex items-center justify-between">
<span class="text-label-md bg-primary-fixed text-on-primary-fixed px-space-sm py-space-xs rounded uppercase font-label-sm">
          En Tránsito
        </span>
<span class="text-data-mono text-on-surface-variant">#9815</span>
</div>
<div class="flex flex-col">
<span class="text-headline-sm text-on-surface">Cl. 72 # 10-34</span>
<span class="text-body-sm text-on-surface-variant">Cliente: Carlos Pérez • Pago verificado</span>
</div>
<div class="flex gap-space-sm">
<button class="w-full bg-primary text-on-primary py-space-sm rounded-lg font-label-md uppercase flex items-center justify-center gap-space-xs shadow-sm hover:opacity-90" onclick="alert('Marcando pedido como entregado...')">
<span class="material-symbols-outlined text-[18px]">check_circle</span>
          Marcar entregado
        </button>
</div>
</div>
</section>
</div>
@endsection
