@extends('layouts.driver')

@section('title', 'Detalle de entrega')

{{-- Variable esperada: $cancelled (bool). Sin ella se muestra el estado en tránsito. --}}
@section('content')
<div class="flex flex-col w-full pb-8">
<!-- Top Status Banner / Variant Switcher for Demo -->

<!-- ACTIVE STATE CONTAINER -->
@unless($cancelled ?? false)
<div class="flex flex-col gap-4 w-full" id="state-active">
<!-- Waybill Tracker Bar -->
<div class="flex flex-col gap-1.5 bg-surface-container-lowest p-3 rounded-lg border border-surface-dim">
<div class="flex justify-between items-center text-label-sm uppercase text-on-surface-variant">
<span>Progreso de Ruta</span>
<span class="text-primary font-bold">Paso 3 de 5</span>
</div>
<div class="grid grid-cols-5 gap-1 h-2 w-full">
<div class="bg-primary-container h-full rounded-sm"></div>
<div class="bg-primary-container h-full rounded-sm"></div>
<div class="bg-primary h-full rounded-sm"></div>
<div class="bg-surface-dim h-full rounded-sm"></div>
<div class="bg-surface-dim h-full rounded-sm"></div>
</div>
<div class="flex justify-between text-[10px] text-outline font-mono mt-0.5">
<span>Recogida</span>
<span class="text-primary font-bold">Entrega</span>
</div>
</div>
<!-- Header Details Card -->
<div class="bg-surface-container-lowest p-4 rounded-lg border border-surface-dim flex flex-col gap-3 relative overflow-hidden">
<div class="absolute top-0 right-0 w-24 h-24 bg-primary/5 rounded-full blur-xl pointer-events-none"></div>
<div class="flex items-center justify-between">
<div class="flex flex-col">
<span class="text-label-md uppercase text-outline">Guía de Rastreo</span>
<h2 class="text-headline-lg-mobile font-headline-lg-mobile text-on-surface">#FL-9821</h2>
</div>
<div class="px-3 py-1 bg-primary-container/20 text-on-primary-container border border-primary-container text-label-sm uppercase rounded font-bold flex items-center gap-1.5 shadow-sm">
<span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
          En Tránsito
        </div>
</div>
<div class="grid grid-cols-2 gap-2 pt-2 border-t border-surface-dim">
<div class="flex flex-col">
<span class="text-label-sm uppercase text-outline">Ventana Horaria</span>
<span class="text-body-md font-mono font-medium text-on-surface">14:00 - 15:30</span>
</div>
<div class="flex flex-col">
<span class="text-label-sm uppercase text-outline">Distancia Estimada</span>
<span class="text-body-md font-mono font-medium text-on-surface">1.2 km (4 min)</span>
</div>
</div>
</div>
<!-- Customer Card -->
<div class="bg-surface-container-lowest p-4 rounded-lg border border-surface-dim flex flex-col gap-3">
<div class="flex items-center justify-between border-b border-surface-dim pb-2">
<div class="flex items-center gap-2">
<div class="w-8 h-8 rounded-full bg-surface-container-highest flex items-center justify-center text-primary font-bold">
            MG
          </div>
<div class="flex flex-col">
<span class="text-label-sm uppercase text-outline">Cliente</span>
<span class="text-body-lg font-medium text-on-surface">Mariana Gómez</span>
</div>
</div>
<a class="w-10 h-10 rounded-full bg-primary-container/10 border border-primary-container text-primary flex items-center justify-center hover:bg-primary hover:text-on-primary transition-all shadow-sm" href="tel:+573104567890">
<span class="material-symbols-outlined text-[20px]">call</span>
</a>
</div>
<div class="flex items-start gap-2.5 pt-1">
<span class="material-symbols-outlined text-primary text-[20px] mt-0.5">location_on</span>
<div class="flex flex-col">
<span class="text-body-md text-on-surface font-medium">Cra. 15 # 85-12, Chicó, Bogotá</span>
<span class="text-body-sm text-outline">Torre 2, Apto 402 • Tocar timbre 402</span>
</div>
</div>
<!-- Mini Map Preview -->
<div class="w-full h-24 rounded bg-surface-container-high relative overflow-hidden flex items-center justify-center border border-surface-dim" data-location="Cra. 15 # 85-12, Bogotá" style="">
<div class="absolute inset-0 bg-cover bg-center opacity-80" data-alt="Detailed map view of Chicó neighborhood in Bogotá showing street grids and delivery pin at Carrera 15 and Calle 85, styled with teal and high-contrast technical lines." style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuBB-YbRVaEfm4rqRP6-14-A0UHGYtVv_0u-Ozt1GS76WtVXUJcnGcqiueabghJyHHKZYi8nFIIEr0nAAF0kMX78qx_b-jYJC0mOi8G0omfYKhYmz8KV4sttUJZFOXueyBUEywY3by36pNevEt9n2JxosBtHbiYR4v7BXUmlrTt9uimXlmiuXs8Ysp6cJFHIFt52qdtJ_uyBm6FLO8fa4uwgqO26Y5BQ0M8ZKjloqnxxuN-KOWyy0m3gzw')"></div>
<div class="absolute bg-primary text-on-primary px-2 py-1 rounded text-label-sm uppercase font-mono shadow flex items-center gap-1">
<span class="material-symbols-outlined text-[14px]">navigation</span> Navegar
        </div>
</div>
</div>
<!-- Package Checklist -->
<div class="bg-surface-container-lowest p-4 rounded-lg border border-surface-dim flex flex-col gap-3">
<div class="flex items-center justify-between border-b border-surface-dim pb-2">
<span class="text-label-md uppercase text-outline">Manifiesto de Paquetes</span>
<span class="text-label-sm uppercase px-2 py-0.5 bg-surface-container-high text-on-surface rounded font-mono">2 Items</span>
</div>
<div class="flex flex-col gap-2">
<!-- Item 1 -->
<label class="flex items-center justify-between p-2 rounded bg-surface-container-low border border-surface-dim cursor-pointer hover:border-primary transition-all">
<div class="flex items-center gap-3">
<input checked="" class="w-5 h-5 rounded accent-primary cursor-pointer" type="checkbox"/>
<div class="flex flex-col">
<span class="text-body-md font-medium text-on-surface">Arroz Premium 1kg</span>
<span class="text-body-sm text-outline font-mono">SKU: ARR-1KG-992</span>
</div>
</div>
<span class="text-label-sm uppercase text-primary font-bold">Verificado</span>
</label>
<!-- Item 2 -->
<label class="flex items-center justify-between p-2 rounded bg-surface-container-low border border-surface-dim cursor-pointer hover:border-primary transition-all">
<div class="flex items-center gap-3">
<input class="w-5 h-5 rounded accent-primary cursor-pointer" type="checkbox"/>
<div class="flex flex-col">
<span class="text-body-md font-medium text-on-surface">Aceite de Girasol 3L</span>
<span class="text-body-sm text-outline font-mono">SKU: OIL-3L-104</span>
</div>
</div>
<span class="text-label-sm uppercase text-outline">Pendiente</span>
</label>
</div>
</div>
<!-- Action Buttons Block -->
<div class="flex flex-col gap-2 pt-2">
<button class="w-full py-3.5 px-4 bg-primary text-on-primary font-headline-sm uppercase rounded flex items-center justify-center gap-2 shadow-md hover:bg-primary-container transition-all" onclick="triggerScan()">
<span class="material-symbols-outlined text-[20px]">qr_code_scanner</span>
        Escanear recogida
      </button>
<button class="w-full py-3 px-4 bg-surface-container-lowest text-error border border-error/50 font-headline-sm uppercase rounded flex items-center justify-center gap-2 hover:bg-error-container/20 transition-all" onclick="toggleBottomSheet(true)">
<span class="material-symbols-outlined text-[20px]">report_problem</span>
        Reportar incidencia
      </button>
</div>
</div>
@endunless
<!-- CANCELLED ERROR STATE CONTAINER (Hidden by default) -->
@if($cancelled ?? false)
<div class="flex flex-col gap-4 w-full" id="state-cancelled">
<div class="bg-error-container/30 border border-error p-5 rounded-lg flex flex-col items-center text-center gap-3 mt-4">
<div class="w-12 h-12 rounded-full bg-error text-on-error flex items-center justify-center shadow-md">
<span class="material-symbols-outlined text-[24px]">cancel</span>
</div>
<div class="flex flex-col gap-1">
<h3 class="text-headline-sm text-error font-headline-sm">Pedido Cancelado</h3>
<p class="text-body-md text-on-surface-variant">Este pedido fue cancelado por el negocio emisor debido a falta de stock en inventario.</p>
</div>
<div class="w-full p-3 bg-surface-container-lowest rounded border border-surface-dim text-left text-body-sm font-mono flex flex-col gap-1">
<span class="text-outline uppercase text-[10px]">Detalle del Evento</span>
<span class="text-on-surface">Cancelado a las 13:42 por Soporte Central</span>
<span class="text-on-surface">Motivo: Producto agotado en punto de origen.</span>
</div>
<button class="w-full mt-2 py-3 px-4 bg-on-surface text-surface font-headline-sm uppercase rounded flex items-center justify-center gap-2 shadow hover:bg-on-surface-variant transition-all" onclick="switchState('active')">
<span class="material-symbols-outlined text-[20px]">arrow_back</span>
        Volver a Rutas
      </button>
</div>
</div>
@endif
<!-- INTERACTIVE BOTTOM SHEET (Incident Report) -->
<div class="fixed inset-0 z-50 bg-on-surface/40 backdrop-blur-sm flex items-end justify-center transition-opacity duration-300 opacity-0 pointer-events-none" id="incident-sheet">
<div class="w-full max-w-[390px] bg-surface-container-lowest rounded-t-xl p-5 border-t border-surface-dim flex flex-col gap-4 transform translate-y-full transition-transform duration-300 shadow-xl" id="sheet-content">
<div class="flex items-center justify-between border-b border-surface-dim pb-3">
<div class="flex items-center gap-2 text-error">
<span class="material-symbols-outlined">warning</span>
<h3 class="text-headline-sm font-headline-sm">Reportar Incidencia</h3>
</div>
<button class="w-8 h-8 rounded-full bg-surface-container-high flex items-center justify-center text-on-surface hover:bg-surface-dim" onclick="toggleBottomSheet(false)">
<span class="material-symbols-outlined text-[18px]">close</span>
</button>
</div>
<div class="flex flex-col gap-2">
<label class="text-label-md uppercase text-outline">Motivo del Reporte</label>
<select class="w-full p-2.5 rounded bg-surface border border-surface-dim text-on-surface font-body-md focus:outline-none focus:border-primary">
<option>Cliente ausente / No contesta</option>
<option>Dirección incorrecta o incompleta</option>
<option>Paquete dañado en tránsito</option>
<option>Negocio cerrado</option>
</select>
</div>
<div class="flex flex-col gap-2">
<label class="text-label-md uppercase text-outline">Observaciones adicionales</label>
<textarea class="w-full p-2.5 rounded bg-surface border border-surface-dim text-on-surface font-body-md focus:outline-none focus:border-primary resize-none" placeholder="Describa la novedad detalladamente..." rows="3"></textarea>
</div>
<div class="flex gap-2 pt-2">
<button class="flex-1 py-3 bg-surface-container-high text-on-surface font-headline-sm uppercase rounded hover:bg-surface-dim transition-all" onclick="toggleBottomSheet(false)">Cancelar</button>
<button class="flex-1 py-3 bg-error text-on-error font-headline-sm uppercase rounded shadow hover:opacity-90 transition-all" onclick="submitIncident()">Enviar Reporte</button>
</div>
</div>
</div>
<!-- TOAST NOTIFICATION -->
<div class="fixed bottom-24 left-1/2 transform -translate-x-1/2 bg-on-surface text-surface px-4 py-2.5 rounded-full text-body-sm font-medium shadow-xl opacity-0 pointer-events-none transition-all duration-300 z-50 flex items-center gap-2" id="toast">
<span class="material-symbols-outlined text-[18px] text-primary-fixed-dim" id="toast-icon">check_circle</span>
<span id="toast-msg">Acción ejecutada con éxito</span>
</div>

</div>
@endsection

@push('scripts')
<script>
function toggleBottomSheet(show) {
      const sheet = document.getElementById('incident-sheet');
      const content = document.getElementById('sheet-content');
      if (show) {
        sheet.classList.remove('opacity-0', 'pointer-events-none');
        content.classList.remove('translate-y-full');
      } else {
        sheet.classList.add('opacity-0', 'pointer-events-none');
        content.classList.add('translate-y-full');
      }
    }

    function triggerScan() {
      showToast('Cámara activada: Escanee el código QR del paquete', 'qr_code_scanner');
    }

    function submitIncident() {
      toggleBottomSheet(false);
      showToast('Incidencia reportada a central exitosamente', 'warning');
      setTimeout(() => {
        switchState('cancelled');
      }, 1500);
    }

    function showToast(msg, iconName) {
      const toast = document.getElementById('toast');
      const msgEl = document.getElementById('toast-msg');
      const iconEl = document.getElementById('toast-icon');
      
      msgEl.innerText = msg;
      iconEl.innerText = iconName;
      
      toast.classList.remove('opacity-0', 'pointer-events-none');
      toast.classList.add('translate-y-[-10px]');
      
      setTimeout(() => {
        toast.classList.add('opacity-0', 'pointer-events-none');
        toast.classList.remove('translate-y-[-10px]');
      }, 2500);
    }
</script>
@endpush
