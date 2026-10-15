<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de repartidores</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 10px; color: #111827; }
        h1 { font-size: 16px; margin: 0 0 2px; }
        .header { border-bottom: 2px solid #0F172A; padding-bottom: 6px; margin-bottom: 10px; }
        .muted { color: #4B5563; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #9CA3AF; padding: 4px 6px; text-align: left; }
        th { background-color: #E5E7EB; font-size: 9px; text-transform: uppercase; }
        .right { text-align: right; }
        tfoot td { font-weight: bold; background-color: #F3F4F6; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de repartidores</h1>
        <div class="muted">
            <strong>{{ $business?->name ?? 'FastLogi' }}</strong>
            · Periodo: {{ $filters['desde'] }} a {{ $filters['hasta'] }}
            · Generado: {{ $generatedAt->format('d/m/Y H:i') }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Repartidor</th>
                <th>Contacto</th>
                <th>Disponibilidad</th>
                <th class="right">Pedidos activos</th>
                <th class="right">Entregados en el periodo</th>
                <th class="right">Total entregado</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($drivers as $driver)
                <tr>
                    <td>{{ $driver->id }}</td>
                    <td>{{ $driver->user?->name }}</td>
                    <td>{{ $driver->user?->email }}<br>{{ $driver->user?->phone }}</td>
                    <td>{{ $driver->availability->label() }}</td>
                    <td class="right">{{ $driver->active_count }}</td>
                    <td class="right">{{ $driver->delivered_count }}</td>
                    <td class="right">$ {{ number_format((float) ($driver->delivered_total ?? 0), 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="right">No hay repartidores registrados.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="right">Total</td>
                <td class="right">{{ $drivers->sum('delivered_count') }}</td>
                <td class="right">$ {{ number_format((float) $drivers->sum('delivered_total'), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
