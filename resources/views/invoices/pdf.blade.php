<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11pt;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
        }
        
        /* Header */
        .header {
            background: #10b981;
            padding: 30px;
            margin: -30px -30px 30px -30px;
            border-bottom: 3px solid #059669;
        }
        
        .header-content {
            display: table;
            width: 100%;
        }
        
        .header-left {
            display: table-cell;
            width: 60%;
            vertical-align: top;
        }
        
        .header-right {
            display: table-cell;
            width: 40%;
            vertical-align: top;
            text-align: right;
        }
        
        .company-name {
            font-size: 24pt;
            font-weight: bold;
            color: #ffffff;
            margin-bottom: 5px;
        }
        
        .company-tagline {
            font-size: 10pt;
            color: #ffffff;
            margin-bottom: 10px;
        }
        
        .company-contact {
            font-size: 9pt;
            color: #ffffff;
            line-height: 1.8;
        }
        
        .invoice-box {
            background: #ffffff;
            border: 2px solid #ffffff;
            border-radius: 8px;
            padding: 15px;
        }
        
        .invoice-title {
            font-size: 14pt;
            font-weight: bold;
            color: #059669;
            margin-bottom: 5px;
        }
        
        .invoice-number {
            font-size: 12pt;
            color: #111827;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .invoice-date {
            font-size: 9pt;
            color: #6b7280;
        }
        
        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 5px;
            font-size: 10pt;
            font-weight: bold;
            margin-top: 10px;
        }
        
        .status-paid {
            background-color: #10b981;
            color: white;
        }
        
        .status-pending {
            background-color: #f59e0b;
            color: white;
        }
        
        .status-cancelled {
            background-color: #ef4444;
            color: white;
        }
        
        /* Customer & Company Info */
        .info-section {
            margin: 30px 0;
            display: table;
            width: 100%;
        }
        
        .info-column {
            display: table-cell;
            width: 48%;
            vertical-align: top;
            padding: 15px;
            background: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 6px;
        }
        
        .info-column + .info-column {
            margin-left: 4%;
        }
        
        .info-title {
            font-size: 10pt;
            font-weight: bold;
            color: #10b981;
            margin-bottom: 10px;
            text-transform: uppercase;
            border-bottom: 2px solid #10b981;
            padding-bottom: 5px;
        }
        
        .info-item {
            margin-bottom: 6px;
            font-size: 10pt;
            line-height: 1.6;
        }
        
        .info-label {
            font-weight: bold;
            color: #4b5563;
        }
        
        /* Booking Details Table */
        .details-section {
            margin: 30px 0;
        }
        
        .section-title {
            font-size: 12pt;
            font-weight: bold;
            color: #059669;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid #10b981;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        th {
            background: #10b981;
            color: white;
            padding: 12px 10px;
            text-align: left;
            font-size: 10pt;
            font-weight: bold;
        }
        
        td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10pt;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        tr:nth-child(even) {
            background: #f9fafb;
        }
        
        /* Summary Table */
        .summary-table {
            float: right;
            width: 50%;
            margin-top: 20px;
        }
        
        .summary-table td {
            padding: 8px 15px;
            border: none;
        }
        
        .summary-table .label {
            text-align: right;
            font-weight: bold;
            color: #4b5563;
        }
        
        .summary-table .value {
            text-align: right;
            font-weight: bold;
        }
        
        .summary-table .total-row {
            background: #10b981;
            color: white;
            font-size: 12pt;
        }
        
        .summary-table .total-row td {
            padding: 12px 15px;
        }
        
        /* QR Code Section */
        .qr-section {
            clear: both;
            margin: 30px 0;
            padding: 20px;
            background: #ffffff;
            border-radius: 8px;
            text-align: center;
            border: 2px solid #10b981;
        }
        
        .qr-title {
            font-size: 11pt;
            font-weight: bold;
            color: #059669;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        
        .booking-code {
            font-size: 18pt;
            font-weight: bold;
            color: #10b981;
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
            margin: 10px 0;
            padding: 12px;
            background: #f0fdf4;
            border-radius: 6px;
            border: 2px dashed #10b981;
        }
        
        .qr-note {
            font-size: 9pt;
            color: #6b7280;
        }
        
        /* Footer */
        .footer {
            margin-top: 40px;
            padding: 20px;
            background: #10b981;
            border-radius: 6px;
            text-align: center;
        }
        
        .footer-note {
            font-size: 10pt;
            color: #ffffff;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .footer-contact {
            font-size: 9pt;
            color: #ffffff;
        }
        
        /* Notes Section */
        .notes-section {
            margin: 30px 0;
            padding: 15px;
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            border-radius: 6px;
        }
        
        .notes-title {
            font-size: 10pt;
            font-weight: bold;
            color: #92400e;
            margin-bottom: 8px;
        }
        
        .notes-content {
            font-size: 9pt;
            color: #78350f;
            line-height: 1.5;
        }
        
        /* Terms & Conditions */
        .terms-section {
            margin: 30px 0;
            padding: 20px;
            background: #f9fafb;
            border: 1px solid #d1d5db;
            border-radius: 6px;
        }
        
        .terms-title {
            font-size: 10pt;
            font-weight: bold;
            color: #10b981;
            margin-bottom: 12px;
            text-transform: uppercase;
        }
        
        .terms-list {
            list-style: none;
            padding-left: 0;
        }
        
        .terms-list li {
            font-size: 9pt;
            color: #1f2937;
            margin-bottom: 8px;
            padding-left: 20px;
            position: relative;
            line-height: 1.6;
        }
        
        .terms-list li:before {
            content: "•";
            position: absolute;
            left: 0;
            color: #3b82f6;
            font-weight: bold;
        }
        
        .clearfix {
            clear: both;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-content">
                <div class="header-left">
                    <div class="company-name">GoField</div>
                    <div class="company-tagline">Platform Booking Lapangan Olahraga</div>
                    <div style="margin-top: 10px; font-size: 9pt; color: #666;">
                        📍 Jl. Olahraga No. 123, Jakarta<br>
                        📞 (021) 1234-5678<br>
                        📧 info@gofield.com
                    </div>
                </div>
                <div class="header-right">
                    <div class="invoice-title">INVOICE</div>
                    <div class="invoice-number">{{ $invoice->invoice_number }}</div>
                    <div class="invoice-date">{{ \Carbon\Carbon::parse($invoice->created_at)->locale('id')->isoFormat('D MMMM YYYY') }}</div>
                    <div class="status-badge status-{{ $invoice->status }}">
                        {{ strtoupper($invoice->status) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer & Payment Info -->
        <div class="info-section">
            <div class="info-column" style="width: 48%; display: inline-block; vertical-align: top;">
                <div class="info-title">📋 Informasi Pemesan</div>
                <div class="info-item"><span class="info-label">Nama:</span> {{ $invoice->booking->nama_pemesan }}</div>
                <div class="info-item"><span class="info-label">Email:</span> {{ $invoice->booking->email }}</div>
                <div class="info-item"><span class="info-label">Telepon:</span> {{ $invoice->booking->nomor_telepon }}</div>
                @if($invoice->booking->user)
                <div class="info-item"><span class="info-label">User ID:</span> #{{ $invoice->booking->user_id }}</div>
                @endif
            </div>
            
            <div class="info-column" style="width: 48%; display: inline-block; vertical-align: top; margin-left: 4%;">
                <div class="info-title">💳 Informasi Pembayaran</div>
                @if($invoice->payment_date)
                <div class="info-item"><span class="info-label">Tanggal Bayar:</span> {{ \Carbon\Carbon::parse($invoice->payment_date)->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}</div>
                @endif
                @if($invoice->payment_method)
                <div class="info-item"><span class="info-label">Metode:</span> {{ $invoice->payment_method }}</div>
                @endif
                <div class="info-item"><span class="info-label">Status:</span>
                    <span style="color: {{ $invoice->status === 'paid' ? '#10b981' : '#f59e0b' }}; font-weight: bold;">
                        {{ $invoice->status === 'paid' ? 'LUNAS' : 'PENDING' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Booking Details -->
        <div class="details-section">
            <div class="section-title">🏟️ Detail Booking</div>
            
            <table>
                <thead>
                    <tr>
                        <th>Kode Booking</th>
                        <th>Lapangan</th>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Durasi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>{{ $invoice->booking->booking_code }}</strong></td>
                        <td>{{ $invoice->booking->lapangan->title }}</td>
                        <td>{{ \Carbon\Carbon::parse($invoice->booking->tanggal)->locale('id')->isoFormat('dddd, D MMM YYYY') }}</td>
                        <td>{{ \Carbon\Carbon::parse($invoice->booking->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($invoice->booking->jam_selesai)->format('H:i') }}</td>
                        <td>{{ $invoice->booking->duration }}</td>
                    </tr>
                </tbody>
            </table>
            
            <div style="margin: 15px 0; padding: 15px; background: #eff6ff; border-left: 4px solid #3b82f6; border-radius: 5px;">
                <div style="font-size: 9pt; color: #1e40af; margin-bottom: 5px;"><strong>📍 Lokasi:</strong></div>
                <div style="font-size: 10pt; color: #1e3a8a;">{{ $invoice->booking->lapangan->location ?? 'GoField Sports Arena' }}</div>
                @if($invoice->booking->lapangan->sportType)
                <div style="font-size: 9pt; color: #3b82f6; margin-top: 5px;">
                    <strong>Kategori:</strong> {{ $invoice->booking->lapangan->sportType->name }}
                </div>
                @endif
            </div>
        </div>

        <!-- Payment Summary -->
        <div style="margin: 30px 0;">
            <div class="section-title">💰 Rincian Pembayaran</div>
            
            <table class="summary-table">
                <tr>
                    <td class="label">Subtotal:</td>
                    <td class="value">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
                </tr>
                @if($invoice->discount > 0)
                <tr>
                    <td class="label">Diskon:</td>
                    <td class="value" style="color: #10b981;">- Rp {{ number_format($invoice->discount, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td class="label">TOTAL PEMBAYARAN:</td>
                    <td class="value">Rp {{ number_format($invoice->total, 0, ',', '.') }}</td>
                </tr>
            </table>
            
            <div class="clearfix"></div>
        </div>

        <!-- Booking Code Section (replacement for QR) -->
        <div class="qr-section">
            <div class="qr-title">🎫 KODE BOOKING ANDA</div>
            <div class="booking-code">{{ $invoice->booking->booking_code }}</div>
            <div class="qr-note">
                Tunjukkan kode booking ini kepada petugas saat Anda tiba di lokasi.<br>
                Simpan invoice ini sebagai bukti pembayaran yang sah.
            </div>
        </div>

        @if($invoice->notes)
        <!-- Notes Section -->
        <div class="notes-section">
            <div class="notes-title">📝 Catatan:</div>
            <div class="notes-content">{{ $invoice->notes }}</div>
        </div>
        @endif

        <!-- Terms & Conditions -->
        <div class="terms-section">
            <div class="terms-title">⚠️ Syarat & Ketentuan</div>
            <ul class="terms-list">
                <li>Invoice ini adalah bukti pembayaran yang sah untuk booking lapangan.</li>
                <li>Harap datang 15 menit sebelum waktu booking untuk check-in.</li>
                <li>Bawa kartu identitas dan tunjukkan invoice ini kepada petugas.</li>
                <li>Pembatalan booking dapat dilakukan maksimal H-1 untuk pengembalian dana.</li>
                <li>Keterlambatan lebih dari 15 menit akan mengurangi waktu bermain Anda.</li>
                <li>Fasilitas lapangan harus digunakan sesuai peraturan yang berlaku.</li>
                <li>Untuk pertanyaan, hubungi customer service kami di (021) 1234-5678.</li>
            </ul>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-note">
                Terima kasih telah menggunakan layanan GoField!<br>
                Invoice ini digenerate secara otomatis pada {{ now()->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }} WIB
            </div>
            <div class="footer-contact">
                www.gofield.com | info@gofield.com | (021) 1234-5678
            </div>
            <div style="margin-top: 15px; font-size: 8pt; color: #000000;">
                GoField © {{ date('Y') }} - Platform Booking Lapangan Olahraga Terpercaya
            </div>
        </div>
    </div>
</body>
</html>
