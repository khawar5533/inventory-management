<!-- resources/views/orders/pdf.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order {{ $order->order_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background: #f4f4f4; }
        h2 { margin-bottom: 0; }
    </style>
</head>
<body>
    <h2>Order: {{ $order->order_number }}</h2>
    <p>Date: {{ $order->created_at->format('Y-m-d') }}</p>

    <table>
        <thead>
            <tr>
                <!-- <th>Lot Number</th> -->
                 <th>Batch Number</th>
                <th>Product</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $index => $item)
         <tr>
            <!-- <td>{{ $item->lot->id ?? 'N/A' }}</td> lot ID -->
            <td>{{ $item->lot->lot_number ?? 'N/A' }}</td>
            <td>{{ $item->lot->product->name ?? 'N/A' }}</td>
            <!-- <td>{{ $item->lot->box->rack->room->name ?? 'N/A' }}</td> -->
            <td>{{ $item->quantity }}</td>
            <td>{{ number_format($item->unit_price, 2) }}</td>
            <td>{{ number_format($item->subtotal, 2) }}</td>
        </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Total: {{ number_format($order->items->sum(fn($i) => $i->unit_price * $i->quantity), 2) }}</h3>
</body>
</html>
