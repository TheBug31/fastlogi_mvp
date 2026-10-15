@extends('layouts.owner')

@section('title', 'Repartidores')

@section('content')
<div class="flex flex-col w-full">
<!-- Top Header / Page Title Bar -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
<div>
<h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight mb-1">Gestión de Repartidores</h1>
<p class="font-body-md text-body-md text-on-surface-variant">Supervisión de flota de domiciliarios, disponibilidad y límites de carga</p>
</div>
<div class="flex items-center gap-3">
<button class="px-4 py-2.5 bg-primary text-on-primary font-label-lg rounded-lg shadow-sm hover:opacity-95 transition-all flex items-center gap-2 cursor-pointer" onclick="openModal()">
<span class="material-symbols-outlined text-[20px]">add</span>
<span>+ Nuevo repartidor</span>
</button>
</div>
</div>
<!-- Quick Metrics Overview -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
<div class="p-4 bg-surface-container-lowest rounded-xl border border-outline-variant/50 shadow-sm flex items-center justify-between">
<div>
<p class="font-label-md text-label-md text-on-surface-variant uppercase">Total Flota</p>
<h3 class="font-headline-lg text-headline-lg text-on-surface mt-1">24</h3>
<span class="text-body-sm text-primary font-medium flex items-center gap-1 mt-1">
<span class="material-symbols-outlined text-[14px]">trending_up</span> 18 activos hoy
        </span>
</div>
<div class="w-12 h-12 bg-surface-container-low rounded-xl flex items-center justify-center text-primary">
<span class="material-symbols-outlined text-[24px]">two_wheeler</span>
</div>
</div>
<div class="p-4 bg-surface-container-lowest rounded-xl border border-outline-variant/50 shadow-sm flex items-center justify-between">
<div>
<p class="font-label-md text-label-md text-on-surface-variant uppercase">Disponibles</p>
<h3 class="font-headline-lg text-headline-lg text-on-surface mt-1">11</h3>
<span class="text-body-sm text-primary font-medium flex items-center gap-1 mt-1">
<span class="material-symbols-outlined text-[14px]">check_circle</span> Listos para asignar
        </span>
</div>
<div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600">
<span class="material-symbols-outlined text-[24px]">person_check</span>
</div>
</div>
<div class="p-4 bg-surface-container-lowest rounded-xl border border-outline-variant/50 shadow-sm flex items-center justify-between">
<div>
<p class="font-label-md text-label-md text-on-surface-variant uppercase">En Ruta</p>
<h3 class="font-headline-lg text-headline-lg text-on-surface mt-1">7</h3>
<span class="text-body-sm text-label-lg text-amber-600 font-medium flex items-center gap-1 mt-1">
<span class="material-symbols-outlined text-[14px]">local_shipping</span> Ocupados
        </span>
</div>
<div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-amber-600">
<span class="material-symbols-outlined text-[24px]">navigation</span>
</div>
</div>
<div class="p-4 bg-surface-container-lowest rounded-xl border border-outline-variant/50 shadow-sm flex items-center justify-between">
<div>
<p class="font-label-md text-label-md text-on-surface-variant uppercase">Novedades / Alertas</p>
<h3 class="font-headline-lg text-headline-lg text-error mt-1">1</h3>
<span class="text-body-sm text-error font-medium flex items-center gap-1 mt-1">
<span class="material-symbols-outlined text-[14px]">warning</span> Requiere atención
        </span>
</div>
<div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center text-error">
<span class="material-symbols-outlined text-[24px]">error</span>
</div>
</div>
</div>
<!-- Filters and Search Toolbar -->
<div class="p-4 bg-surface-container-lowest rounded-xl border border-outline-variant/50 shadow-sm mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
<div class="relative w-full md:w-96">
<span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-on-surface-variant">
<span class="material-symbols-outlined text-[20px]">search</span>
</span>
<input class="w-full pl-10 pr-4 py-2 bg-surface-container-low border border-outline-variant rounded-lg text-body-md text-on-surface focus:outline-none focus:border-primary transition-colors" placeholder="Buscar por nombre o teléfono..." type="text"/>
</div>
<div class="flex items-center gap-3 w-full md:w-auto justify-end">
<select class="px-3 py-2 bg-surface-container-low border border-outline-variant rounded-lg text-body-md text-on-surface focus:outline-none focus:border-primary">
<option>Estado: Todos</option>
<option>Disponible</option>
<option>Ocupado</option>
<option>Desconectado</option>
</select>
<select class="px-3 py-2 bg-surface-container-low border border-outline-variant rounded-lg text-body-md text-on-surface focus:outline-none focus:border-primary">
<option>Vehículo: Todos</option>
<option>Moto</option>
<option>Bicicleta</option>
</select>
</div>
</div>
<!-- Drivers Table Card -->
<div class="bg-surface-container-lowest rounded-xl border border-outline-variant/50 shadow-sm overflow-hidden mb-8">
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-surface-container-low border-b border-outline-variant/50 font-label-md text-label-md text-on-surface-variant uppercase">
<th class="py-3 px-4">Nombre</th>
<th class="py-3 px-4">Teléfono</th>
<th class="py-3 px-4">Disponibilidad</th>
<th class="py-3 px-4">Pedidos activos</th>
<th class="py-3 px-4">Última conexión</th>
<th class="py-3 px-4">Límite de pedidos</th>
<th class="py-3 px-4 text-right">Acciones</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant/30 text-body-md text-on-surface">
<!-- Row 1: Normal Active Driver -->
<tr class="hover:bg-surface-container-low/50 transition-colors">
<td class="py-3 px-4">
<div class="flex items-center gap-3">
<div class="w-10 h-10 rounded-full bg-primary/10 text-primary font-semibold flex items-center justify-center text-label-lg overflow-hidden">
<img class="w-full h-full object-cover" data-alt="Portrait of a young male Colombian delivery driver smiling wearing a teal FastLogi jacket and helmet, clean lighting, professional headshot style." src="https://lh3.googleusercontent.com/aida-public/AB6AXuDH55dalEEDv2hW69LfuN6KIUF2KPpqXEPO2SsZdSuReqYYyEax1MLL-OckOpG5mxYESvS5-qACDWD0R73m20zwFzoxf4-cQyUxJx7NSQ7yoF6bdpQybdkIusGJzy04fXSaRY9Huh8YXPhiWwAiCHaWYF5KmBMRTejilt99cS3k8PorHhKbI1OinnL4APrQ_qzHaDPrRRuZmcCvO3z3swPPANj6GuwAgFKYuBtaXvi30MvnhgJJNJBn_Q"/>
</div>
<div>
<div class="font-medium text-on-surface">Carlos Andrés Pérez</div>
<div class="text-body-sm text-on-surface-variant flex items-center gap-1">
<span class="material-symbols-outlined text-[14px]">two_wheeler</span> Moto • Placa HXT-452
                  </div>
</div>
</div>
</td>
<td class="py-3 px-4 font-data-mono">+57 310 492 8173</td>
<td class="py-3 px-4">
<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-label-sm font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
<span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Disponible
              </span>
</td>
<td class="py-3 px-4 font-data-mono">1 / 3</td>
<td class="py-3 px-4 text-on-surface-variant text-body-sm">hace 3 min</td>
<td class="py-3 px-4">
<div class="flex items-center gap-2">
<div class="w-24 bg-surface-container-high h-2 rounded-full overflow-hidden">
<div class="bg-primary h-full w-1/3"></div>
</div>
<span class="font-data-mono text-body-sm">Max 3</span>
</div>
</td>
<td class="py-3 px-4 text-right">
<div class="flex items-center justify-end gap-2">
<button class="p-1.5 text-on-surface-variant hover:text-primary hover:bg-surface-container rounded-lg transition-colors" title="Editar">
<span class="material-symbols-outlined text-[18px]">edit</span>
</button>
<button class="p-1.5 text-on-surface-variant hover:text-error hover:bg-surface-container rounded-lg transition-colors" title="Desactivar">
<span class="material-symbols-outlined text-[18px]">block</span>
</button>
</div>
</td>
</tr>
<!-- Row 2: Ocupado with Orange Warning -->
<tr class="hover:bg-surface-container-low/50 transition-colors">
<td class="py-3 px-4">
<div class="flex items-center gap-3">
<div class="w-10 h-10 rounded-full bg-amber-100 text-amber-800 font-semibold flex items-center justify-center text-label-lg overflow-hidden">
<img class="w-full h-full object-cover" data-alt="Portrait of an experienced Colombian courier with a bicycle delivery backpack, friendly professional look against a urban background." src="https://lh3.googleusercontent.com/aida-public/AB6AXuCE6TAVGC5t_SzFUIlBr4WhUx1_nUslrbVuWFwfMLOQuZoK4ileUfZGkP02NzXCzDzm29q52309rlYtlWv0y1kCIxLGOQFa2mshCPOS8FoSrz5hxUSP6MUO3cBfKgeBZuKkgjWtIEZAosH0DNMMNO1c0ZyLz2nTG4qvloWYykKu5FMUrABL_QF8OqtIS-nfiG5-Fgon0sXueCulcNMs9vJwufhdzKG41-RYJsKiHBnKB0W4KrCeGEtw7A"/>
</div>
<div>
<div class="font-medium text-on-surface">Mateo Rodríguez</div>
<div class="text-body-sm text-on-surface-variant flex items-center gap-1">
<span class="material-symbols-outlined text-[14px]">pedal_bike</span> Bicicleta urbana
                  </div>
</div>
</div>
</td>
<td class="py-3 px-4 font-data-mono">+57 321 889 2210</td>
<td class="py-3 px-4">
<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-label-sm font-bold bg-amber-100 text-amber-800 border border-amber-300">
<span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span> Ocupado
              </span>
</td>
<td class="py-3 px-4 font-data-mono">2 / 2</td>
<td class="py-3 px-4">
<span class="text-amber-700 font-medium text-body-sm flex items-center gap-1">
<span class="material-symbols-outlined text-[14px]">schedule</span> hace 25 min
              </span>
</td>
<td class="py-3 px-4">
<div class="flex items-center gap-2">
<div class="w-24 bg-surface-container-high h-2 rounded-full overflow-hidden">
<div class="bg-amber-500 h-full w-full"></div>
</div>
<span class="font-data-mono text-body-sm">Max 2</span>
</div>
</td>
<td class="py-3 px-4 text-right">
<div class="flex items-center justify-end gap-2">
<button class="p-1.5 text-on-surface-variant hover:text-primary hover:bg-surface-container rounded-lg transition-colors" title="Editar">
<span class="material-symbols-outlined text-[18px]">edit</span>
</button>
<button class="p-1.5 text-on-surface-variant hover:text-error hover:bg-surface-container rounded-lg transition-colors" title="Desactivar">
<span class="material-symbols-outlined text-[18px]">block</span>
</button>
</div>
</td>
</tr>
<!-- Row 3: Special Alert Row (Julián Sierra) -->
<tr class="bg-red-50/40 hover:bg-red-50/70 transition-colors">
<td class="py-3 px-4">
<div class="flex items-center gap-3">
<div class="w-10 h-10 rounded-full bg-red-100 text-error font-semibold flex items-center justify-center text-label-lg overflow-hidden">
<img class="w-full h-full object-cover" data-alt="Portrait of a Colombian motorcycle delivery courier looking concerned with a telemetry disconnected warning indicator." src="https://lh3.googleusercontent.com/aida-public/AB6AXuCifhcpNwwom820ag47RMxCiK9kjpdLSueOi22ksIXrZ18NfQa550C7zFYNj0yi33PgJEsAlzNEh6dAPiHiZ85mLyFVr0V9XXDvp8i-SIAiGz5X8Ga_PJNMUCjBlsP6VNm5SXYwflkw--0WGMwhEwh1ympUKdClM07xJhyqAvNQTxhhJTyy3lM8zdZQe6pJe0FaXCDUVtGW1MLx6lBo36MzwkPZNve9zZU73ujX6VSzI-BJmkbGwBIYQQ"/>
</div>
<div>
<div class="font-medium text-on-surface flex items-center gap-2">
                    Julián Sierra
                    <span class="px-2 py-0.5 bg-red-100 text-error text-label-sm rounded border border-error/30 font-bold uppercase">Sin conexión con pedidos asignados</span>
</div>
<div class="text-body-sm text-on-surface-variant flex items-center gap-1">
<span class="material-symbols-outlined text-[14px]">two_wheeler</span> Moto • Placa WQZ-88A
                  </div>
</div>
</div>
</td>
<td class="py-3 px-4 font-data-mono">+57 314 555 9012</td>
<td class="py-3 px-4">
<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-label-sm font-bold bg-gray-100 text-gray-700 border border-gray-300">
<span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span> Desconectado
              </span>
</td>
<td class="py-3 px-4 font-data-mono text-error font-bold">2 / 4</td>
<td class="py-3 px-4 text-error font-medium text-body-sm">Sin señal (&gt;45 min)</td>
<td class="py-3 px-4">
<div class="flex items-center gap-2">
<div class="w-24 bg-surface-container-high h-2 rounded-full overflow-hidden">
<div class="bg-error h-full w-1/2"></div>
</div>
<span class="font-data-mono text-body-sm">Max 4</span>
</div>
</td>
<td class="py-3 px-4 text-right">
<div class="flex items-center justify-end gap-2">
<button class="px-2.5 py-1 bg-error text-on-error rounded text-body-sm font-medium hover:opacity-90 flex items-center gap-1" onclick="alert('Abriendo pedidos asignados a Julián Sierra para reasignación manual...')">
<span class="material-symbols-outlined text-[14px]">visibility</span> Ver pedidos
                </button>
<button class="p-1.5 text-on-surface-variant hover:text-primary hover:bg-surface-container rounded-lg transition-colors" title="Editar">
<span class="material-symbols-outlined text-[18px]">edit</span>
</button>
</div>
</td>
</tr>
<!-- Row 4: Desconectado Normal -->
<tr class="hover:bg-surface-container-low/50 transition-colors">
<td class="py-3 px-4">
<div class="flex items-center gap-3">
<div class="w-10 h-10 rounded-full bg-gray-100 text-gray-600 font-semibold flex items-center justify-center text-label-lg overflow-hidden">
<img class="w-full h-full object-cover" data-alt="Portrait of a female Colombian courier off-duty smiling." src="https://lh3.googleusercontent.com/aida-public/AB6AXuDOQFeHQ0AZXNv8HL70neuDH0UEJQGjfiWbqaDTIx4nepnVHPRwMq5R_gBBGJndO5e3bHPzT26IlIT7v7QSjBMzN-mcIE8fLMHzmi7PLL8OzIHsg91UUylFrVM6evMXyH3CqNTjt-xtEuJkSIhJ_wU6nws4y8VyW4iR8Gf-tDNe9vqwpPI1aoB3NFVoMju20N5x1f_YKRqyaVooVKykYC8oRMFFouZxKAmFWp3rV8PLqWFd0POH1e0Rqg"/>
</div>
<div>
<div class="font-medium text-on-surface">Valentina Gómez</div>
<div class="text-body-sm text-on-surface-variant flex items-center gap-1">
<span class="material-symbols-outlined text-[14px]">two_wheeler</span> Moto • Placa JKL-129
                  </div>
</div>
</div>
</td>
<td class="py-3 px-4 font-data-mono">+57 300 123 4567</td>
<td class="py-3 px-4">
<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-label-sm font-bold bg-gray-100 text-gray-700 border border-gray-300">
<span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span> Desconectado
              </span>
</td>
<td class="py-3 px-4 font-data-mono">0 / 3</td>
<td class="py-3 px-4 text-on-surface-variant text-body-sm">hace 3 horas</td>
<td class="py-3 px-4">
<div class="flex items-center gap-2">
<div class="w-24 bg-surface-container-high h-2 rounded-full overflow-hidden">
<div class="bg-gray-400 h-full w-0"></div>
</div>
<span class="font-data-mono text-body-sm">Max 3</span>
</div>
</td>
<td class="py-3 px-4 text-right">
<div class="flex items-center justify-end gap-2">
<button class="p-1.5 text-on-surface-variant hover:text-primary hover:bg-surface-container rounded-lg transition-colors" title="Editar">
<span class="material-symbols-outlined text-[18px]">edit</span>
</button>
<button class="p-1.5 text-on-surface-variant hover:text-emerald-600 hover:bg-surface-container rounded-lg transition-colors" title="Activar">
<span class="material-symbols-outlined text-[18px]">check_circle</span>
</button>
</div>
</td>
</tr>
</tbody>
</table>
</div>
<!-- Pagination Footer -->
<div class="p-4 bg-surface-container-low border-t border-outline-variant/50 flex items-center justify-between text-body-sm text-on-surface-variant">
<span>Mostrando 4 de 24 repartidores registrados</span>
<div class="flex items-center gap-2">
<button class="px-3 py-1 bg-surface-container-lowest border border-outline-variant rounded hover:bg-surface-container transition-colors disabled:opacity-50" disabled="">Anterior</button>
<button class="px-3 py-1 bg-primary text-on-primary rounded font-medium">1</button>
<button class="px-3 py-1 bg-surface-container-lowest border border-outline-variant rounded hover:bg-surface-container transition-colors">2</button>
<button class="px-3 py-1 bg-surface-container-lowest border border-outline-variant rounded hover:bg-surface-container transition-colors">3</button>
<button class="px-3 py-1 bg-surface-container-lowest border border-outline-variant rounded hover:bg-surface-container transition-colors">Siguiente</button>
</div>
</div>
</div>
<!-- Modal Overlay ("Nuevo repartidor") -->
<div class="fixed inset-0 bg-on-surface/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden" id="driverModal">
<div class="bg-surface-container-lowest w-full max-w-lg rounded-xl border border-outline-variant shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-150">
<div class="px-6 py-4 bg-surface-container-low border-b border-outline-variant flex items-center justify-between">
<h3 class="font-headline-sm text-headline-sm text-on-surface">Registrar Nuevo Repartidor</h3>
<button class="text-on-surface-variant hover:text-on-surface p-1 rounded-lg" onclick="closeModal()">
<span class="material-symbols-outlined text-[20px]">close</span>
</button>
</div>
<form class="p-6 space-y-4" onsubmit="event.preventDefault(); closeModal();">
<div>
<label class="block font-label-md text-label-md text-on-surface-variant uppercase mb-1">Nombre completo *</label>
<input class="w-full px-3 py-2 bg-surface-container-low border border-outline-variant rounded-lg text-body-md text-on-surface focus:outline-none focus:border-primary" placeholder="Ej. Sebastián Morales" required="" type="text"/>
</div>
<div>
<label class="block font-label-md text-label-md text-on-surface-variant uppercase mb-1">Teléfono de contacto *</label>
<input class="w-full px-3 py-2 bg-surface-container-low border border-outline-variant rounded-lg text-body-md text-on-surface focus:outline-none focus:border-primary" placeholder="Ej. +57 312 345 6789" required="" type="tel"/>
</div>
<div>
<label class="block font-label-md text-label-md text-on-surface-variant uppercase mb-1">Contraseña temporal *</label>
<input class="w-full px-3 py-2 bg-surface-container-low border border-outline-variant rounded-lg text-body-md text-on-surface focus:outline-none focus:border-primary" placeholder="••••••••" required="" type="password"/>
</div>
<div>
<div class="flex justify-between items-center mb-1">
<label class="font-label-md text-label-md text-on-surface-variant uppercase">Máximo de pedidos activos</label>
<span class="font-headline-sm text-primary font-bold" id="sliderValue">3</span>
</div>
<input class="w-full accent-primary cursor-pointer" max="8" min="1" oninput="document.getElementById('sliderValue').innerText = this.value" type="range" value="3"/>
<div class="flex justify-between text-body-sm text-on-surface-variant mt-1">
<span>1 pedido</span>
<span>8 pedidos</span>
</div>
</div>
<div class="pt-4 border-t border-outline-variant flex items-center justify-end gap-3">
<button class="px-4 py-2 border border-outline-variant bg-surface-container-lowest text-on-surface font-label-lg rounded-lg hover:bg-surface-container transition-colors" onclick="closeModal()" type="button">
            Cancelar
          </button>
<button class="px-4 py-2 bg-primary text-on-primary font-label-lg rounded-lg hover:opacity-95 transition-colors shadow-sm" type="submit">
            Guardar repartidor
          </button>
</div>
</form>
</div>
</div>

</div>
@endsection

@push('scripts')
<script>
function openModal() {
      document.getElementById('driverModal').classList.remove('hidden');
    }
    function closeModal() {
      document.getElementById('driverModal').classList.add('hidden');
    }
</script>
@endpush
