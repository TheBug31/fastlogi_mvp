<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de pedidos</title>
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
        <h1>Reporte de pedidos</h1>
        <div class="muted">
            <strong>{{ $business?->name ?? 'FastLogi' }}</strong>
            · Periodo: {{ $filters['desde'] }} a {{ $filters['hasta'] }}
            · Estado: {{ $filters['estado'] === 'todos' ? 'Todos' : (\App\Enums\OrderStatus::tryFrom($filters['estado'])?->label() ?? $filters['estado']) }}
            · Método de pago: {{ $filters['payment_method'] === 'todos' ? 'Todos' : $filters['payment_method'] }}
            · Generado: {{ $generatedAt->format('d/m/Y H:i') }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Estado</th>
                <th>Repartidor</th>
                <th>Método de pago</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $order)
                <tr>
                    <td>{{ str_pad((string) $order->id, 4, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $order->customer_name }}</td>
                    <td>{{ $order->status->label() }}</td>
                    <td>{{ $order->deliveryDriver?->user?->name ?? '—' }}</td>
                    <td>{{ $order->payment_method }}</td>
                    <td class="right">$ {{ number_format($order->total, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="right">No hay pedidos para los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="right">Total ({{ $orders->count() }} pedido(s))</td>
                <td class="right">$ {{ number_format($total, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
