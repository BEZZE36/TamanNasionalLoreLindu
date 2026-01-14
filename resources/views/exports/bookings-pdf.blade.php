<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Laporan Booking</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #333;
        }

        .header {
            background: linear-gradient(135deg, #10b981, #0d9488);
            color: white;
            padding: 20px;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 10px;
            opacity: 0.9;
        }

        .stats {
            display: flex;
            margin-bottom: 20px;
        }

        .stat-box {
            background: #f3f4f6;
            padding: 10px 15px;
            margin-right: 15px;
            border-radius: 8px;
        }

        .stat-box .label {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
        }

        .stat-box .value {
            font-size: 16px;
            font-weight: bold;
            color: #111827;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        th {
            background: #10b981;
            color: white;
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
        }

        td {
            padding: 6px;
            border-bottom: 1px solid #e5e7eb;
        }

        tr:nth-child(even) {
            background: #f9fafb;
        }

        .status-pending {
            color: #d97706;
            font-weight: bold;
        }

        .status-paid {
            color: #059669;
            font-weight: bold;
        }

        .status-confirmed {
            color: #10b981;
            font-weight: bold;
        }

        .status-cancelled {
            color: #dc2626;
            font-weight: bold;
        }

        .status-used {
            color: #6b7280;
            font-weight: bold;
        }

        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            font-size: 8px;
            color: #9ca3af;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>📋 Laporan Data Booking</h1>
        <p>Taman Nasional Lore Lindu | Dibuat: {{ $generated_at }}</p>
    </div>

    <div style="display: flex; gap: 15px; margin-bottom: 20px;">
        <div class="stat-box">
            <div class="label">Total Booking</div>
            <div class="value">{{ $stats['total'] }}</div>
        </div>
        <div class="stat-box">
            <div class="label">Total Pendapatan</div>
            <div class="value">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Order Number</th>
                <th>Tanggal Booking</th>
                <th>Tanggal Kunjungan</th>
                <th>Destinasi</th>
                <th>Nama Ketua</th>
                <th>Email</th>
                <th>Telepon</th>
                <th>Pengunjung</th>
                <th>Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bookings as $index => $booking)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $booking['Order Number'] }}</td>
                    <td>{{ $booking['Tanggal Booking'] }}</td>
                    <td>{{ $booking['Tanggal Kunjungan'] }}</td>
                    <td>{{ $booking['Destinasi'] }}</td>
                    <td>{{ $booking['Nama Ketua'] }}</td>
                    <td>{{ $booking['Email'] }}</td>
                    <td>{{ $booking['Telepon'] }}</td>
                    <td>{{ $booking['Total Pengunjung'] }}</td>
                    <td>{{ $booking['Total Amount'] }}</td>
                    <td class="status-{{ strtolower($booking['Status']) }}">{{ $booking['Status'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Dokumen ini dibuat secara otomatis oleh sistem. Total {{ count($bookings) }} data.</p>
    </div>
</body>

</html>