<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Invoice {{ $booking->order_number }}</title>
    <style>
        @page {
            margin: 3mm;
            padding: 0;
            size: 80mm auto;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9px;
            line-height: 1.3;
            color: #000000;
            background: #ffffff;
            width: 74mm;
        }

        .invoice {
            width: 100%;
            padding: 2mm;
        }

        /* Header */
        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 3mm;
            margin-bottom: 3mm;
        }

        .logo-text {
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .logo-sub {
            font-size: 7px;
            color: #555;
            margin-top: 1mm;
        }

        .doc-type {
            font-size: 10px;
            font-weight: bold;
            margin-top: 2mm;
            text-transform: uppercase;
        }

        /* Order Info */
        .order-info {
            border-bottom: 1px dashed #000;
            padding-bottom: 3mm;
            margin-bottom: 3mm;
        }

        .order-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1mm;
        }

        .order-label {
            color: #555;
        }

        .order-value {
            font-weight: bold;
            text-align: right;
        }

        /* Destination */
        .destination {
            text-align: center;
            padding: 2mm 0;
            background: #f5f5f5;
            border-radius: 2mm;
            margin-bottom: 3mm;
        }

        .dest-name {
            font-size: 10px;
            font-weight: bold;
        }

        .dest-date {
            font-size: 8px;
            color: #555;
            margin-top: 1mm;
        }

        /* Contact Info */
        .contact {
            border-bottom: 1px dashed #000;
            padding-bottom: 3mm;
            margin-bottom: 3mm;
        }

        .section-title {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 2mm;
            color: #333;
        }

        .contact-row {
            margin-bottom: 1mm;
        }

        /* Items */
        .items {
            border-bottom: 1px dashed #000;
            padding-bottom: 3mm;
            margin-bottom: 3mm;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1mm;
        }

        .item-name {
            flex: 1;
        }

        .item-qty {
            width: 15mm;
            text-align: center;
        }

        .item-price {
            width: 20mm;
            text-align: right;
        }

        .item-header {
            font-weight: bold;
            border-bottom: 1px solid #ccc;
            padding-bottom: 1mm;
            margin-bottom: 2mm;
        }

        /* Totals */
        .totals {
            margin-bottom: 3mm;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1mm;
        }

        .total-final {
            font-size: 11px;
            font-weight: bold;
            border-top: 1px solid #000;
            padding-top: 2mm;
            margin-top: 2mm;
        }

        /* Status */
        .status {
            text-align: center;
            padding: 2mm;
            background: #059669;
            color: #fff;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            border-radius: 2mm;
            margin-bottom: 3mm;
        }

        .status.pending {
            background: #f59e0b;
        }

        .status.cancelled {
            background: #dc2626;
        }

        /* Footer */
        .footer {
            text-align: center;
            border-top: 1px dashed #000;
            padding-top: 3mm;
            font-size: 7px;
            color: #555;
        }

        .footer p {
            margin-bottom: 1mm;
        }

        .barcode {
            text-align: center;
            font-family: monospace;
            font-size: 10px;
            letter-spacing: 2px;
            margin: 2mm 0;
        }

        .thank-you {
            font-size: 9px;
            font-weight: bold;
            margin-top: 2mm;
        }
    </style>
</head>

<body>
    <div class="invoice">
        <!-- Header -->
        <div class="header">
            <div class="logo-text">TNLL EXPLORE</div>
            <div class="logo-sub">Taman Nasional Lore Lindu</div>
            <div class="doc-type">Invoice Pembayaran</div>
        </div>

        <!-- Order Info -->
        <div class="order-info">
            <table style="width: 100%;">
                <tr>
                    <td style="color: #555;">No. Order</td>
                    <td style="text-align: right; font-weight: bold;">{{ $booking->order_number }}</td>
                </tr>
                <tr>
                    <td style="color: #555;">Tanggal</td>
                    <td style="text-align: right;">{{ $booking->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            </table>
        </div>

        <!-- Destination -->
        <div class="destination">
            <div class="dest-name">{{ $booking->destination->name ?? 'Destinasi' }}</div>
            <div class="dest-date">Kunjungan: {{ $booking->visit_date?->translatedFormat('d M Y') ?? '-' }}</div>
        </div>

        <!-- Contact -->
        <div class="contact">
            <div class="section-title">Data Pemesan</div>
            <table style="width: 100%;">
                <tr>
                    <td style="width: 25mm;">Nama</td>
                    <td>: {{ $booking->leader_name }}</td>
                </tr>
                <tr>
                    <td>Telepon</td>
                    <td>: {{ $booking->leader_phone }}</td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td>: {{ Str::limit($booking->leader_email, 25) }}</td>
                </tr>
            </table>
        </div>

        <!-- Items -->
        <div class="items">
            <div class="section-title">Rincian Pesanan</div>
            <table style="width: 100%;">
                <tr style="font-weight: bold; border-bottom: 1px solid #ccc;">
                    <td>Item</td>
                    <td style="text-align: center; width: 10mm;">Qty</td>
                    <td style="text-align: right; width: 22mm;">Harga</td>
                </tr>
                @if($booking->total_adults > 0)
                    <tr>
                        <td>Dewasa</td>
                        <td style="text-align: center;">{{ $booking->total_adults }}</td>
                        <td style="text-align: right;">-</td>
                    </tr>
                @endif
                @if($booking->total_children > 0)
                    <tr>
                        <td>Anak-anak</td>
                        <td style="text-align: center;">{{ $booking->total_children }}</td>
                        <td style="text-align: right;">-</td>
                    </tr>
                @endif
                @if($booking->total_seniors > 0)
                    <tr>
                        <td>Pelajar</td>
                        <td style="text-align: center;">{{ $booking->total_seniors }}</td>
                        <td style="text-align: right;">-</td>
                    </tr>
                @endif
                @if($booking->items && $booking->items->count() > 0)
                    @foreach($booking->items as $item)
                        <tr>
                            <td>{{ $item->label }}</td>
                            <td style="text-align: center;">{{ $item->quantity }}</td>
                            <td style="text-align: right;">Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @endif
            </table>
        </div>

        <!-- Totals -->
        <div class="totals">
            <table style="width: 100%;">
                <tr>
                    <td>Subtotal</td>
                    <td style="text-align: right;">Rp {{ number_format($booking->subtotal ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Biaya Layanan</td>
                    <td style="text-align: right;">Rp {{ number_format($booking->service_fee ?? 0, 0, ',', '.') }}</td>
                </tr>
                @if($booking->discount > 0)
                    <tr style="color: #059669;">
                        <td>Diskon</td>
                        <td style="text-align: right;">- Rp {{ number_format($booking->discount, 0, ',', '.') }}</td>
                    </tr>
                @endif
                <tr style="font-size: 11px; font-weight: bold; border-top: 1px solid #000;">
                    <td style="padding-top: 2mm;">TOTAL</td>
                    <td style="text-align: right; padding-top: 2mm;">Rp
                        {{ number_format($booking->total_amount ?? 0, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <!-- Status -->
        <div
            class="status {{ $booking->status === 'pending' ? 'pending' : ($booking->status === 'cancelled' ? 'cancelled' : '') }}">
            {{ $booking->status_label ?? strtoupper($booking->status) }}
        </div>

        <!-- Barcode -->
        <div class="barcode">
            {{ $booking->order_number }}
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Dicetak: {{ now()->format('d/m/Y H:i') }}</p>
            <p>Taman Nasional Lore Lindu</p>
            <p>www.tnll.id | info@tnll.id</p>
            <p class="thank-you">Terima kasih telah berkunjung!</p>
        </div>
    </div>
</body>

</html>