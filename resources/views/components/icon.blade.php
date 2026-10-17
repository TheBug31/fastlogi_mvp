@props([
    'name',
    'label' => null,
    'size' => 5,
])

@php
    $classes = $attributes->get('class');

    // h-5/w-5 = 20 px por defecto (guía §2); el llamador puede sobreescribir con su propia class.
    if ($classes === null || trim($classes) === '') {
        $classes = 'h-'.$size.' w-'.$size;
    }

    // Cualquier otro atributo del llamador (x-show, wire:loading, data-*, title…) viaja al <svg>.
    $svgAttributes = $attributes->getAttributes();
    $svgAttributes['class'] = $classes;

    $svg = svg('heroicon-o-'.$name, $svgAttributes)->toHtml();

    // El SVG fuente trae stroke-width="1.5"; la guía pide 1.75 (outline, 20 px).
    $svg = str_replace('stroke-width="1.5"', 'stroke-width="1.75"', $svg);

    if ($label !== null && $label !== '') {
        $svg = str_replace('aria-hidden="true"', 'role="img" aria-label="'.e($label).'"', $svg);
    }
@endphp

{!! $svg !!}
