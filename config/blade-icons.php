<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Componentes
    |--------------------------------------------------------------------------
    |
    | FastLogi define su propio componente anónimo <x-icon name="...">
    | (resources/views/components/icon.blade.php) con el estilo de la guía de
    | UI: iconos Heroicons outline a 20 px y stroke-width 1.75.
    |
    | Se deja en null el componente <x-icon> por defecto de blade-icons para
    | que no registre su propio alias "icon" y el anónimo sea el que gane.
    | El resto de la configuración (sets, class, attributes, fallback) se
    | conserva desde el paquete mediante mergeConfigFrom().
    |
    */

    'components' => [

        // FastLogi no usa <x-heroicon-o-*>; todos los iconos pasan por <x-icon>.
        'disabled' => true,

        'default' => null,

    ],

];
