
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Inventory Stock Management</title>

    <style>
        @page {
            margin: 30px 25px 45px 25px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #333333;
        }

        .header {
            width: 100%;
            border-bottom: 3px solid #c90020;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .logo-cell {
            width: 175px;
            vertical-align: middle;
        }

        .logo {
            width: 150px;
            height: auto;
        }

        .company-info {
            text-align: right;
            vertical-align: middle;
        }

        .company-name {
            font-size: 15px;
            font-weight: bold;
            color: #111111;
        }

        .company-subtitle {
            font-size: 9px;
            color: #555555;
            margin-top: 5px;
        }

        .report-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-top: 18px;
            margin-bottom: 8px;
            color: #222222;
        }

        .subtitle {
            text-align: center;
            line-height: 1.7;
            color: #555555;
            margin-bottom: 18px;
        }

        h3 {
            text-align: center;
            font-size: 11px;
            margin-top: 15px;
            margin-bottom: 9px;
            color: #222222;
        }

        .summary,
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary {
            margin-bottom: 18px;
        }

        .summary th,
        .summary td,
        .data-table th,
        .data-table td {
            border: 1px solid #cccccc;
            padding: 7px;
        }

        .summary th {
            background-color: #eeeeee;
            width: 35%;
            text-align: left;
        }

        .summary td {
            text-align: left;
        }

        .data-table {
            margin-top: 10px;
        }

        .data-table thead {
            display: table-header-group;
        }

        .data-table th {
            background-color: #e5e7eb;
            text-align: center;
            font-weight: bold;
        }

        .data-table tr:nth-child(even) {
            background-color: #f8f8f8;
        }

        .data-table tr {
            page-break-inside: avoid;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .metadata {
            width: 100%;
            margin-top: 15px;
            border-collapse: collapse;
        }

        .metadata td {
            padding: 3px;
            font-size: 8px;
        }

        .footer {
            position: fixed;
            bottom: -28px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #777777;
            border-top: 1px solid #cccccc;
            padding-top: 6px;
        }

        .footer .page-number:after {
            content: counter(page);
        }

        .footer .page-count:after {
            content: counter(pages);
        }

        .empty-data {
            text-align: center;
            padding: 12px;
        }
    </style>
</head>

<body>

    <!-- HEADER IDENTITAS TEMPAT PKL -->
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    <img
                        src="{{ public_path('images/logo-dahana.jpg') }}"
                        class="logo"
                        alt="Logo Dahana"
                    >
                </td>

                <td class="company-info">
                    <div class="company-name">
                        PT Dahana
                    </div>

                    <div class="company-subtitle">
                        Inventory Stock Management System
                    </div>

                    <div class="company-subtitle">
                        Laporan Pengelolaan Persediaan Barang
                    </div>
                </td>
            </tr>
        </table>
    </div>


    <!-- JUDUL LAPORAN -->
    <div class="report-title">
        LAPORAN INVENTORY STOCK MANAGEMENT
    </div>


    <!-- METADATA LAPORAN -->
    <div class="subtitle">
        Periode:
        {{ $report['date_from'] }}
        sampai
        {{ $report['date_to'] }}

        <br>

        Filter Tipe:
        {{ $report['type'] }}

        <br>

        Jenis Laporan:
        {{ strtoupper($report['view'] ?? 'summary') }}
    </div>


    <!-- RINGKASAN LAPORAN -->
    <h3>Ringkasan Laporan</h3>

    <table class="summary">
        <tr>
            <th>Total Transaksi</th>
            <td>
                {{ number_format($report['summary']['total_transactions'], 0, ',', '.') }}
            </td>
        </tr>

        <tr>
            <th>Total Transaksi IN</th>
            <td>
                {{ number_format($report['summary']['total_in'], 0, ',', '.') }}
            </td>
        </tr>

        <tr>
            <th>Total Transaksi OUT</th>
            <td>
                {{ number_format($report['summary']['total_out'], 0, ',', '.') }}
            </td>
        </tr>

        <tr>
            <th>Total Nilai Transaksi</th>
            <td>
                Rp {{ number_format($report['summary']['total_value'], 2, ',', '.') }}
            </td>
        </tr>

        <tr>
            <th>Rata-rata Transaksi</th>
            <td>
                Rp {{ number_format($report['summary']['average_value'], 2, ',', '.') }}
            </td>
        </tr>

        <tr>
            <th>Transaksi Tertinggi</th>
            <td>
                Rp {{ number_format($report['summary']['highest_value'], 2, ',', '.') }}
            </td>
        </tr>

        <tr>
            <th>Transaksi Terendah</th>
            <td>
                Rp {{ number_format($report['summary']['lowest_value'], 2, ',', '.') }}
            </td>
        </tr>
    </table>


    <!-- DETAIL TRANSAKSI -->
    @if (($report['view'] ?? 'summary') === 'detail')

        <h3>Detail Transaksi</h3>

        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode Transaksi</th>
                    <th>Tanggal</th>
                    <th>Tipe</th>
                    <th>Produk</th>
                    <th>SKU</th>
                    <th>Jumlah</th>
                    <th>Harga</th>
                    <th>Subtotal</th>
                    <th>Pengguna</th>
                </tr>
            </thead>

            <tbody>
                @php
                    $number = 1;
                @endphp

                @forelse ($report['transactions'] as $transaction)

                    @foreach ($transaction->details as $detail)

                        <tr>
                            <td class="text-center">
                                {{ $number++ }}
                            </td>

                            <td>
                                {{ $transaction->transaction_code }}
                            </td>

                            <td>
                                {{ $transaction->created_at->format('d-m-Y H:i') }}
                            </td>

                            <td class="text-center">
                                {{ $transaction->type }}
                            </td>

                            <td>
                                {{ $detail->product->name ?? '-' }}
                            </td>

                            <td>
                                {{ $detail->product->sku ?? '-' }}
                            </td>

                            <td class="text-center">
                                {{ number_format($detail->quantity, 0, ',', '.') }}
                            </td>

                            <td class="text-right">
                                Rp {{ number_format($detail->price, 2, ',', '.') }}
                            </td>

                            <td class="text-right">
                                Rp {{ number_format($detail->subtotal, 2, ',', '.') }}
                            </td>

                            <td>
                                {{ $transaction->user->name ?? '-' }}
                            </td>
                        </tr>

                    @endforeach

                @empty

                    <tr>
                        <td colspan="10" class="empty-data">
                            Tidak ada data transaksi.
                        </td>
                    </tr>

                @endforelse
            </tbody>
        </table>

    @endif


    <!-- METADATA PENCETAK -->
    <table class="metadata">
        <tr>
            <td width="25%">
                Dicetak oleh
            </td>

            <td>
                : {{ auth()->user()->name ?? 'Pengguna Sistem' }}
            </td>
        </tr>

        <tr>
            <td>
                Tanggal cetak
            </td>

            <td>
                : {{ now()->format('d-m-Y H:i:s') }}
            </td>
        </tr>
    </table>


    <!-- FOOTER -->
    <div class="footer">
        PT Dahana - Inventory Stock Management System
        <br>
        Halaman <span class="page-number"></span>
        dari <span class="page-count"></span>
    </div>

</body>
</html>
