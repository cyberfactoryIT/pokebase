<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('messages.receipt_title') }} {{ $invoice->number }}</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; }
        .header { font-size: 18px; font-weight: bold; margin-bottom: 20px; }
        .section { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 6px; }
    </style>
</head>
<body>
    <div class="header">{{ __('messages.receipt_title') }} #{{ $invoice->number }}</div>
    <div class="section">
        <strong>{{ __('messages.billed_to') }}</strong><br>
        {{ $org->company }}<br>
        {{ $org->billing_email }}<br>
        {{ $org->address_line1 }}<br>
        {{ $org->city }}, {{ $org->country }}
    </div>
    <div class="section">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.description') }}</th>
                    <th>{{ __('messages.qty') }}</th>
                    <th>{{ __('messages.unit_price') }}</th>
                    <th>{{ __('messages.total') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->unit_price_cents / 100, 2) }} {{ $invoice->currency }}</td>
                    <td>{{ number_format($item->total_cents / 100, 2) }} {{ $invoice->currency }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <strong>Total: {{ number_format($invoice->total_cents / 100, 2) }} {{ $invoice->currency }}</strong>
    </div>
</body>
</html>
