<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>E-Ticket {{ $booking->order_number }}</title>
    <style>
        @page {
            margin: 0;
            padding: 0;
            size: 105mm 131.2mm;
        }

        html,
        body {
            margin: 0;
            padding: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #1e293b;
            background: #ffffff;
        }

        /* ===== TICKET WRAPPER ===== */
        .ticket-wrapper {
            padding: 0;
            margin: 0;
            width: 100%;
        }

        .ticket-wrapper-inner {}

        .ticket {
            border: none;
            border-radius: 0;
            background: #ffffff;
            width: 100%;
        }

        /* ===== HEADER ===== */
        .header {
            background-color: #059669;
            padding: 14px 15px;
            color: #ffffff;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-left {
            vertical-align: middle;
        }

        .header-right {
            vertical-align: middle;
            text-align: right;
        }

        .logo-text {
            font-size: 16px;
            font-weight: 800;
            letter-spacing: 1px;
            color: #ffffff;
        }

        .logo-sub {
            font-size: 6px;
            letter-spacing: 1px;
            color: rgba(255, 255, 255, 0.85);
            text-transform: uppercase;
            margin-top: 1px;
        }

        .eticket-badge {
            display: inline-block;
            background-color: rgba(255, 255, 255, 0.2);
            padding: 4px 10px;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.4);
        }

        .eticket-label {
            font-size: 6px;
            letter-spacing: 1px;
            color: rgba(255, 255, 255, 0.8);
            text-transform: uppercase;
        }

        .eticket-order {
            font-size: 7px;
            font-weight: 700;
            color: #ffffff;
            font-family: monospace;
            letter-spacing: 0.5px;
            margin-top: 1px;
        }

        /* ===== DESTINATION BANNER ===== */
        .dest-banner {
            background-color: #ecfdf5;
            border-bottom: 1px solid #059669;
            padding: 12px 15px;
        }

        .dest-table {
            width: 100%;
            border-collapse: collapse;
        }

        .dest-name {
            font-size: 11px;
            font-weight: 800;
            color: #047857;
            margin-bottom: 2px;
        }

        .dest-location {
            font-size: 7px;
            color: #059669;
        }

        .dest-date-box {
            text-align: center;
            background-color: #ffffff;
            border: 1px solid #059669;
            border-radius: 6px;
            padding: 5px 8px;
        }

        .dest-date-label {
            font-size: 5px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .dest-date-value {
            font-size: 8px;
            font-weight: 800;
            color: #047857;
            margin-top: 1px;
        }

        .status-badge {
            display: inline-block;
            background-color: #059669;
            color: #ffffff;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 6px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 4px;
        }

        /* ===== MAIN CONTENT ===== */
        .content {
            padding: 12px 15px;
        }

        .content-table {
            width: 100%;
            border-collapse: collapse;
        }

        .content-left {
            width: 55%;
            vertical-align: top;
            padding-right: 8px;
        }

        .content-right {
            width: 45%;
            vertical-align: top;
            padding-left: 8px;
            border-left: 1px dashed #e2e8f0;
        }

        /* Info Box */
        .info-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 10px;
            margin-bottom: 8px;
        }

        .info-title {
            font-size: 6px;
            font-weight: 700;
            color: #059669;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding-bottom: 3px;
            margin-bottom: 4px;
            border-bottom: 1px solid #e2e8f0;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-row td {
            padding: 2px 0;
            vertical-align: top;
        }

        .info-label {
            color: #64748b;
            font-size: 6px;
            width: 40%;
        }

        .info-value {
            font-weight: 600;
            color: #1e293b;
            text-align: right;
            font-size: 7px;
        }

        /* QR Code Section */
        .qr-box {
            background-color: #f0fdf4;
            border: 1px solid #059669;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
        }

        .qr-title {
            font-size: 6px;
            font-weight: 700;
            color: #059669;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .qr-frame {
            display: inline-block;
            background-color: #ffffff;
            padding: 4px;
            border-radius: 6px;
            border: 1px solid #d1fae5;
            margin-bottom: 5px;
        }

        .qr-frame img {
            width: 70px;
            height: 70px;
        }

        .qr-placeholder {
            width: 70px;
            height: 70px;
            background-color: #e2e8f0;
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            color: #94a3b8;
            font-size: 6px;
        }

        .ticket-code {
            background-color: #059669;
            color: #ffffff;
            padding: 4px 8px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 7px;
            font-weight: 800;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            display: inline-block;
        }

        .valid-text {
            font-size: 6px;
            color: #047857;
        }

        .valid-date {
            font-weight: 800;
            color: #059669;
        }

        .visitor-tags {
            margin-top: 5px;
            padding-top: 4px;
            border-top: 1px dashed #059669;
        }

        .tag {
            display: inline-block;
            background-color: #ffffff;
            border: 1px solid #059669;
            color: #059669;
            padding: 2px 5px;
            border-radius: 8px;
            font-size: 5px;
            font-weight: 600;
            margin: 1px;
        }

        /* ===== PAYMENT BAR ===== */
        .payment-bar {
            background-color: #f1f5f9;
            padding: 12px 15px;
            border-top: 1px dashed #cbd5e1;
        }

        .payment-table {
            width: 100%;
            border-collapse: collapse;
        }

        .payment-items {
            vertical-align: middle;
        }

        .payment-item {
            display: inline-block;
            margin-right: 12px;
        }

        .payment-item-label {
            font-size: 5px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .payment-item-value {
            font-size: 7px;
            font-weight: 700;
            color: #374151;
        }

        .payment-total-cell {
            text-align: right;
            vertical-align: middle;
        }

        .payment-total-box {
            display: inline-block;
            background-color: #059669;
            padding: 6px 12px;
            border-radius: 6px;
        }

        .payment-total-label {
            font-size: 5px;
            color: rgba(255, 255, 255, 0.8);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .payment-total-value {
            font-size: 10px;
            font-weight: 800;
            color: #ffffff;
        }

        /* ===== FOOTER ===== */
        .footer {
            background-color: #1e293b;
            padding: 12px 15px;
            color: #ffffff;
        }

        .notice-box {
            background-color: rgba(251, 191, 36, 0.15);
            border: 1px solid rgba(251, 191, 36, 0.4);
            border-radius: 4px;
            padding: 8px 10px;
            margin-bottom: 8px;
        }

        .notice-text {
            font-size: 6px;
            color: #fcd34d;
            line-height: 1.4;
        }

        .footer-info {
            text-align: center;
            font-size: 5px;
            color: #94a3b8;
        }

        .footer-info p {
            margin: 1px 0;
        }

        .footer-brand {
            color: #cbd5e1;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="ticket-wrapper">
        <div class="ticket">
            <!-- HEADER -->
            <div class="header">
                <table class="header-table">
                    <tr>
                        <td class="header-left">
                            <div class="logo-text">TNLL</div>
                            <div class="logo-sub">Taman Nasional Lore Lindu</div>
                        </td>
                        <td class="header-right">
                            <div class="eticket-badge">
                                <div class="eticket-label">E-Ticket</div>
                                <div class="eticket-order">{{ $booking->order_number }}</div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- DESTINATION BANNER -->
            <div class="dest-banner">
                <table class="dest-table">
                    <tr>
                        <td style="vertical-align: middle;">
                            <div class="dest-name">{{ $booking->destination->name ?? 'Destinasi Wisata' }}</div>
                            <div class="dest-location">Lore Lindu, Sulawesi Tengah, Indonesia</div>
                            <span class="status-badge">{{ $booking->status_label }}</span>
                        </td>
                        <td style="vertical-align: middle; text-align: right; width: 200px;">
                            <div class="dest-date-box">
                                <div class="dest-date-label">Tanggal Kunjungan</div>
                                <div class="dest-date-value">
                                    {{ $booking->visit_date?->translatedFormat('d M Y') ?? '-' }}
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- MAIN CONTENT -->
            <div class="content">
                <table class="content-table">
                    <tr>
                        <td class="content-left">
                            <!-- Pemesan Info -->
                            <div class="info-box">
                                <div class="info-title">Detail Pemesan</div>
                                <table class="info-table">
                                    <tr class="info-row">
                                        <td class="info-label">Nama Lengkap</td>
                                        <td class="info-value">{{ $booking->leader_name }}</td>
                                    </tr>
                                    <tr class="info-row">
                                        <td class="info-label">Email</td>
                                        <td class="info-value">{{ $booking->leader_email }}</td>
                                    </tr>
                                    <tr class="info-row">
                                        <td class="info-label">No. Telepon</td>
                                        <td class="info-value">{{ $booking->leader_phone }}</td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Kunjungan Info -->
                            <div class="info-box">
                                <div class="info-title">Detail Pengunjung</div>
                                <table class="info-table">
                                    <tr class="info-row">
                                        <td class="info-label">Total Pengunjung</td>
                                        <td class="info-value">{{ $booking->total_visitors }} Orang</td>
                                    </tr>
                                    @if($booking->total_adults > 0)
                                        <tr class="info-row">
                                            <td class="info-label">Dewasa</td>
                                            <td class="info-value">{{ $booking->total_adults }} Orang</td>
                                        </tr>
                                    @endif
                                    @if($booking->total_children > 0)
                                        <tr class="info-row">
                                            <td class="info-label">Anak-anak</td>
                                            <td class="info-value">{{ $booking->total_children }} Orang</td>
                                        </tr>
                                    @endif
                                    @if($booking->total_seniors > 0)
                                        <tr class="info-row">
                                            <td class="info-label">Lansia</td>
                                            <td class="info-value">{{ $booking->total_seniors }} Orang</td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        </td>

                        <td class="content-right">
                            <!-- QR Code -->
                            <div class="qr-box">
                                <div class="qr-title">Scan Untuk Masuk</div>

                                @if($booking->tickets && $booking->tickets->count() > 0)
                                    @php $ticket = $booking->tickets->first(); @endphp
                                    <div class="qr-frame">
                                        @if($ticket->qr_code_path && file_exists(public_path('storage/' . $ticket->qr_code_path)))
                                            <img src="{{ public_path('storage/' . $ticket->qr_code_path) }}" alt="QR Code">
                                        @else
                                            <table>
                                                <tr>
                                                    <td class="qr-placeholder">QR Code</td>
                                                </tr>
                                            </table>
                                        @endif
                                    </div>
                                    <div class="ticket-code">{{ $ticket->ticket_code }}</div>
                                    <div class="valid-text">
                                        Berlaku: <span
                                            class="valid-date">{{ $ticket->valid_date?->translatedFormat('d F Y') ?? '-' }}</span>
                                    </div>
                                @else
                                    <div class="qr-frame">
                                        <table>
                                            <tr>
                                                <td class="qr-placeholder">Menunggu<br>Pembayaran</td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="valid-text">QR tersedia setelah pembayaran</div>
                                @endif

                                @if($booking->total_visitors > 0)
                                    <div class="visitor-tags">
                                        @if($booking->total_adults > 0)
                                            <span class="tag">{{ $booking->total_adults }} Dewasa</span>
                                        @endif
                                        @if($booking->total_children > 0)
                                            <span class="tag">{{ $booking->total_children }} Anak</span>
                                        @endif
                                        @if($booking->total_seniors > 0)
                                            <span class="tag">{{ $booking->total_seniors }} Lansia</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- PAYMENT BAR -->
            <div class="payment-bar">
                <table class="payment-table">
                    <tr>
                        <td class="payment-items">
                            <span class="payment-item">
                                <div class="payment-item-label">Subtotal</div>
                                <div class="payment-item-value">Rp
                                    {{ number_format($booking->subtotal ?? 0, 0, ',', '.') }}
                                </div>
                            </span>
                            <span class="payment-item">
                                <div class="payment-item-label">Biaya Layanan</div>
                                <div class="payment-item-value">Rp
                                    {{ number_format($booking->service_fee ?? 0, 0, ',', '.') }}
                                </div>
                            </span>
                            @if($booking->discount > 0)
                                <span class="payment-item">
                                    <div class="payment-item-label">Diskon</div>
                                    <div class="payment-item-value" style="color: #059669;">- Rp
                                        {{ number_format($booking->discount, 0, ',', '.') }}
                                    </div>
                                </span>
                            @endif
                        </td>
                        <td class="payment-total-cell">
                            <div class="payment-total-box">
                                <div class="payment-total-label">Total Pembayaran</div>
                                <div class="payment-total-value">Rp
                                    {{ number_format($booking->total_amount ?? 0, 0, ',', '.') }}
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- FOOTER -->
            <div class="footer">
                <div class="notice-box">
                    <div class="notice-text">
                        <strong>PENTING:</strong> Tunjukkan QR Code ini kepada petugas di gerbang masuk. Tiket hanya
                        berlaku sesuai tanggal kunjungan yang tertera. Harap datang 15 menit sebelum waktu kunjungan.
                    </div>
                </div>
                <div class="footer-info">
                    <p>Dicetak pada: {{ now()->translatedFormat('l, d F Y - H:i') }} WIB</p>
                    <p class="footer-brand">Taman Nasional Lore Lindu | www.tnll.id | info@tnll.id</p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>