@props([
    'name',
    'label' => null,
    'size' => 5,
])

@php
    $classes = (string) $attributes->get('class', '');

    // Si el llamador no especificó altura/ancho o size explícito, aplicar por defecto h-{$size} w-{$size}
    $hasHeight = preg_match('/\bh-\S+/', $classes);
    $hasWidth = preg_match('/\bw-\S+/', $classes);
    $hasSize = preg_match('/\bsize-\S+/', $classes);

    $defaultSize = '';
    if (! $hasSize) {
        if (! $hasHeight) {
            $defaultSize .= 'h-'.$size.' ';
        }
        if (! $hasWidth) {
            $defaultSize .= 'w-'.$size.' ';
        }
    }

    $classes = trim($defaultSize . $classes);

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
