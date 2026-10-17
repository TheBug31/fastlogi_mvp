<?php

use Illuminate\Support\Carbon;

if (! function_exists('format_cop')) {
    /**
     * Formatea una cifra en pesos colombianos: $ 84.500 (sin decimales).
     */
    function format_cop(int|float|string $amount): string
    {
        return '$ '.number_format((float) $amount, 0, ',', '.');
    }
}

if (! function_exists('format_datetime')) {
    /**
     * Formatea una fecha/hora en español de Colombia:
     * 15 de octubre de 2026, 3:20 p. m.
     */
    function format_datetime(Carbon|string|int|null $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        $date = $value instanceof Carbon ? $value : Carbon::parse($value);

        return $date->locale('es')->isoFormat('D [de] MMMM [de] YYYY, h:mm A');
    }
}

if (! function_exists('format_date')) {
    /**
     * Formatea solo la fecha en español de Colombia: 15 de octubre de 2026.
     */
    function format_date(Carbon|string|int|null $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        $date = $value instanceof Carbon ? $value : Carbon::parse($value);

        return $date->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
    }
}

if (! function_exists('format_time')) {
    /**
     * Formatea solo la hora en español de Colombia: 3:20 p. m.
     */
    function format_time(Carbon|string|int|null $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        $date = $value instanceof Carbon ? $value : Carbon::parse($value);

        return $date->locale('es')->isoFormat('h:mm A');
    }
}
