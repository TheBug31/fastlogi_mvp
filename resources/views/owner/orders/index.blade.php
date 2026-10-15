@extends('layouts.owner')

@section('title', 'Pedidos')

@section('content')
<div class="flex flex-col w-full">
<!-- Blurred background showing orders list -->
<div class="filter blur-sm pointer-events-none select-none opacity-40">
<div class="flex justify-between items-center mb-space-lg">
<div>
<h1 class="font-headline-xl text-headline-xl text-on-surface">Gestión de Pedidos</h1>
<p class="font-body-md text-body-md text-on-surface-variant">Monitoreo en tiempo real y asignación de flotas urbanas</p>
</div>
<div class="flex gap-space-sm">
<button class="px-space-md py-space-sm bg-surface-container-low text-on-surface font-label-lg rounded">Filtrar</button>
<button class="px-space-md py-space-sm bg-primary-container text-on-primary-container font-label-lg rounded">Nuevo Pedido</button>
</div>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-space-md mb-space-lg">
<div class="p-space-lg bg-surface-container-lowest rounded-lg border border-outline-variant">
<div class="text-label-md text-on-surface-variant uppercase">Pedidos Activos</div>
<div class="text-headline-xl text-on-surface font-headline-xl">24</div>
</div>
<div class="p-space-lg bg-surface-container-lowest rounded-lg border border-outline-variant">
<div class="text-label-md text-on-surface-variant uppercase">Repartidores en Ruta</div>
<div class="text-headline-xl text-on-surface font-headline-xl">12</div>
</div>
<div class="p-space-lg bg-surface-container-lowest rounded-lg border border-outline-variant">
<div class="text-label-md text-on-surface-variant uppercase">Tiempo Promedio</div>
<div class="text-headline-xl text-on-surface font-headline-xl">28 min</div>
</div>
</div>
<div class="bg-surface-container-lowest rounded-lg border border-outline-variant overflow-hidden">
<div class="p-space-md bg-surface-container-low border-b border-outline-variant font-headline-sm text-headline-sm">Listado General</div>
<div class="p-space-lg space-y-space-md">
<div class="flex items-center justify-between p-space-md bg-surface-container-low rounded">
<div>
<span class="font-data-mono text-data-mono text-primary font-bold">#FL-1042</span>
<span class="ml-space-md text-body-md">Mariana Gómez - Calle 94 # 11-30</span>
</div>
<span class="px-space-sm py-space-xs bg-primary-container text-on-primary-container rounded label-sm">PENDIENTE</span>
</div>
<div class="flex items-center justify-between p-space-md bg-surface-container-low rounded">
<div>
<span class="font-data-mono text-data-mono text-primary font-bold">#FL-1041</span>
<span class="ml-space-md text-body-md">Andrés Parra - Cra 15 # 88-12</span>
</div>
<span class="px-space-sm py-space-xs bg-surface-container text-on-surface rounded label-sm">EN CAMINO</span>
</div>
</div>
</div>
</div>
<!-- Toast Notification Overlay in Top-Right -->
<div class="fixed top-20 right-6 z-50 bg-inverse-surface text-inverse-on-surface px-space-lg py-space-md rounded-lg shadow-xl flex items-center gap-space-md border border-primary-container animate-bounce">
<span class="material-symbols-outlined text-primary-fixed">check_circle</span>
<div>
<div class="font-label-lg text-label-lg">Oferta enviada con éxito</div>
<div class="font-body-sm text-body-sm text-surface-dim">Carlos Mendoza (Quedan 0:90)</div>
</div>
</div>
<!-- Modal Overlay -->
<div class="fixed inset-0 z-40 bg-inverse-surface/60 backdrop-blur-sm flex items-center justify-center p-space-md">
<div class="bg-surface-container-lowest w-full max-w-2xl rounded-xl shadow-2xl border border-outline-variant flex flex-col overflow-hidden">
<!-- Modal Header -->
<div class="px-space-lg py-space-md bg-surface-container-low border-b border-outline-variant flex items-center justify-between">
<div class="flex items-center gap-space-sm">
<span class="material-symbols-outlined text-primary">local_shipping</span>
<h2 class="font-headline-lg text-headline-lg text-on-surface">Ofrecer Pedido #FL-1042</h2>
</div>
<button class="p-space-xs text-on-surface-variant hover:text-on-surface rounded-lg transition-colors">
<span class="material-symbols-outlined">close</span>
</button>
</div>
<!-- Modal Body -->
<div class="p-space-lg space-y-space-lg overflow-y-auto max-h-[70vh]">
<!-- Order Summary Card -->
<div class="p-space-md bg-surface-container-low rounded-lg border border-outline-variant">
<div class="flex justify-between items-start mb-space-sm">
<div>
<span class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Cliente</span>
<div class="font-headline-sm text-headline-sm text-on-surface">Mariana Gómez</div>
</div>
<span class="px-space-sm py-space-xs bg-primary-container/20 text-primary font-label-sm rounded uppercase border border-primary-container">
              Trazabilidad estricta: Sí (Código QR)
            </span>
</div>
<div class="flex items-center gap-space-xs text-on-surface-variant font-body-md">
<span class="material-symbols-outlined text-[18px]">location_on</span>
<span>Calle 94 # 11-30, Chicó, Bogotá</span>
</div>
</div>
<!-- Candidate Drivers List -->
<div>
<h3 class="font-headline-sm text-headline-sm text-on-surface mb-space-md">Repartidores Candidatos</h3>
<div class="space-y-space-sm">
<!-- Driver 1: Carlos Mendoza -->
<div class="flex items-center justify-between p-space-md bg-surface-container-lowest border border-outline-variant rounded-lg hover:border-primary transition-all">
<div class="flex items-center gap-space-md">
<div class="w-10 h-10 rounded-full bg-surface-container-high flex items-center justify-center font-headline-sm text-on-surface overflow-hidden">
<img class="w-full h-full object-cover" data-alt="Portrait of a smiling delivery driver named Carlos Mendoza wearing a black motorcycle jacket and helmet in Bogota." src="https://lh3.googleusercontent.com/aida-public/AB6AXuAU7iBPvpWsIAsfmoWch0RG2LtDBuXZ6DaUNIoC8MI5Y93Z4RGABF4onN1MBxf3i6wbdNiMO-Kq0Boy0QY61D1pIq8PKdyoYPt-18c8KETU45K_5jtW310Oal9NdyOztsJ1ewTHr_JxI66TJfHhI-vcye1EirjWYjMZ7V8wk2fLyJyRm-TruFwG3Fzvg1MUx7S57SkQQ7TFFPSl2TGe7MVJcL-8SFOoBngpnikjS61IOBkonpr5SXOMkg"/>
</div>
<div>
<div class="font-headline-sm text-headline-sm text-on-surface">Carlos Mendoza</div>
<div class="font-body-sm text-body-sm text-on-surface-variant">Moto Yamaha YBR • <span class="text-primary font-medium">1/3 pedidos activos</span></div>
</div>
</div>
<div class="flex items-center gap-space-md">
<span class="px-space-sm py-space-xs bg-[#CCFBF1] text-[#0F766E] border border-[#14B8A6] rounded label-sm uppercase font-bold">Disponible</span>
<button class="px-space-md py-space-sm bg-primary text-on-primary font-label-lg rounded hover:bg-primary/90 transition-colors" onclick="alert('Oferta enviada a Carlos Mendoza')">
                  Ofrecer
                </button>
</div>
</div>
<!-- Driver 2: Mateo Ríos -->
<div class="flex items-center justify-between p-space-md bg-surface-container-lowest border border-outline-variant rounded-lg opacity-80">
<div class="flex items-center gap-space-md">
<div class="w-10 h-10 rounded-full bg-surface-container-high flex items-center justify-center font-headline-sm text-on-surface overflow-hidden">
<img class="w-full h-full object-cover" data-alt="Portrait of a young delivery driver named Mateo Rios with short hair looking focused." src="https://lh3.googleusercontent.com/aida-public/AB6AXuBeaBj1ZUNazpKYpXgBiu6IJSpmyxeEJkpO1aYnJwSWVLKgg4bPbZlXOUT68xdAyfoVmrq8xsyIOdtkJJGXV_IJNyFsvq6r3EoauNXcumrc405y0gFenQN6MxSBlPI6aSv8rFolP0O4eee-EpJKfMQ767Xc5HTr2nrQDPVbT2-zOW25rlYOPfKUoSBYSWRNq7-ljElHDvgtavnQ5hYvqjtOT50JJxsd6qviXASeJjmFbTc5t_35nKYNwg"/>
</div>
<div>
<div class="font-headline-sm text-headline-sm text-on-surface">Mateo Ríos</div>
<div class="font-body-sm text-body-sm text-on-surface-variant">Moto AKT 125 • <span class="text-tertiary font-medium">2/3 pedidos activos</span></div>
</div>
</div>
<div class="flex items-center gap-space-md">
<span class="px-space-sm py-space-xs bg-[#FEF3C7] text-[#B45309] border border-[#F59E0B] rounded label-sm uppercase font-bold">Ocupado</span>
<button class="px-space-md py-space-sm bg-surface-container text-on-surface-variant font-label-lg rounded cursor-not-allowed" disabled="">
                  Ofrecer
                </button>
</div>
</div>
<!-- Driver 3: Diego Morales -->
<div class="flex items-center justify-between p-space-md bg-surface-container-lowest border border-outline-variant rounded-lg hover:border-primary transition-all">
<div class="flex items-center gap-space-md">
<div class="w-10 h-10 rounded-full bg-surface-container-high flex items-center justify-center font-headline-sm text-on-surface overflow-hidden">
<img class="w-full h-full object-cover" data-alt="Portrait of a courier named Diego Morales wearing casual urban cycling gear." src="https://lh3.googleusercontent.com/aida-public/AB6AXuA3ssetqE8lSOCxEOlppzsTdLw5lBegpKwIK50wKvH-LzV-mw3hwFHy59v67mzDiLNS-KqwEhT71ovPkwNXJhoovQLumsp005zDy4ZRuN6QwV4SFrbKtmIZNB_cw0pJpS2qDc9hoCoeXghdaazIQHwR7mXC1ISssFcjEtx3uNO9-TahEGRX1B28-VpO3HQYTHJLwsjn0mo2Fi1nxrTnVKxrDLG_46JFNFZo0hGDkKFC6E4uTv6PhoYSoQ"/>
</div>
<div>
<div class="font-headline-sm text-headline-sm text-on-surface">Diego Morales</div>
<div class="font-body-sm text-body-sm text-on-surface-variant">Bici de carga • <span class="text-primary font-medium">0/3 pedidos activos</span></div>
</div>
</div>
<div class="flex items-center gap-space-md">
<span class="px-space-sm py-space-xs bg-[#CCFBF1] text-[#0F766E] border border-[#14B8A6] rounded label-sm uppercase font-bold">Disponible</span>
<button class="px-space-md py-space-sm bg-primary text-on-primary font-label-lg rounded hover:bg-primary/90 transition-colors">
                  Ofrecer
                </button>
</div>
</div>
<!-- Driver 4: Julián Sierra -->
<div class="flex items-center justify-between p-space-md bg-surface-container-lowest border border-outline-variant rounded-lg opacity-60">
<div class="flex items-center gap-space-md">
<div class="w-10 h-10 rounded-full bg-surface-container-high flex items-center justify-center font-headline-sm text-on-surface overflow-hidden">
<img class="w-full h-full object-cover" data-alt="Portrait of a delivery rider named Julian Sierra wearing a high visibility vest." src="https://lh3.googleusercontent.com/aida-public/AB6AXuBsHQPISRyq0ugIPj1q6CnykqoTdZ0q-tdz8-cNIVKKFJETRq7lHmosORLBsId2-Zk9LKDsMBJKA1J9YRoNdWMqdMY5AKeczwddZ6H5Q2oDdROm89pqh6y-weoSQruXZ9c8hYzGiVdxKq_fXpsI-zkqlZpW9bTlDMoqz1FWqmU5eGVHphyK4L64AeftYfEgTo3W-JChd9N1GQ9WgCAu2KOcnQIeIfQZelnELRCkI-SlTHY_qNQyawpjnA"/>
</div>
<div>
<div class="font-headline-sm text-headline-sm text-on-surface">Julián Sierra</div>
<div class="font-body-sm text-body-sm text-on-surface-variant">Moto Boxer • <span class="text-error font-medium">Desconectado</span></div>
</div>
</div>
<div class="flex items-center gap-space-md">
<span class="px-space-sm py-space-xs bg-surface-container text-on-surface-variant border border-outline rounded label-sm uppercase font-bold">Desconectado</span>
<div class="relative group">
<button class="px-space-md py-space-sm bg-surface-container text-on-surface-variant font-label-lg rounded cursor-not-allowed" disabled="">
                    Ofrecer
                  </button>
<div class="absolute bottom-full mb-2 right-0 hidden group-hover:block bg-inverse-surface text-inverse-on-surface text-body-sm px-space-sm py-space-xs rounded shadow-lg whitespace-nowrap z-50">
                    Sin señal GPS / Fuera de turno
                  </div>
</div>
</div>
</div>
</div>
</div>
<!-- Footer Note -->
<div class="flex items-center gap-space-sm p-space-sm bg-surface-container-low rounded border border-outline-variant text-on-surface-variant font-body-sm">
<span class="material-symbols-outlined text-[18px]">info</span>
<span>El repartidor tiene 90 segundos para aceptar la oferta antes de pasar al siguiente candidato disponible.</span>
</div>
</div>
<!-- Modal Footer -->
<div class="px-space-lg py-space-md bg-surface-container-low border-t border-outline-variant flex justify-end gap-space-sm">
<button class="px-space-lg py-space-sm bg-surface-container-lowest text-on-surface border border-outline font-label-lg rounded hover:bg-surface-container transition-colors">
          Cerrar
        </button>
</div>
</div>
</div>
</div>
@endsection
