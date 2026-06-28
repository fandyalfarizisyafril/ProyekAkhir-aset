<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Aset Diskominfotik Provinsi Riau</title>
    <style>
        @page {
            size: landscape;
            margin: 10mm;
        }

        body {
            color: #0f172a;
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 24px;
        }

        h1, h2, h3, p {
            margin: 0;
        }

        .header {
            border-bottom: 2px solid #0F3092;
            margin-bottom: 18px;
            padding-bottom: 14px;
        }

        .title {
            font-size: 20px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .subtitle {
            color: #475569;
            margin-top: 4px;
        }

        .meta,
        .summary {
            border: 1px solid #cbd5e1;
            border-collapse: collapse;
            margin-bottom: 16px;
            width: 100%;
        }

        .meta td,
        .summary td,
        .summary th,
        .assets td,
        .assets th {
            border: 1px solid #cbd5e1;
            padding: 8px;
        }

        .summary th,
        .assets th {
            background: #f1f5f9;
            font-size: 10px;
            text-align: left;
            text-transform: uppercase;
        }

        .assets {
            border: 1px solid #cbd5e1;
            border-collapse: collapse;
            width: 100%;
        }

        .text-right {
            text-align: right;
        }

        .currency {
            text-align: right;
            white-space: nowrap;
        }

        .print-actions {
            margin-bottom: 16px;
        }

        .print-button {
            background: #0F3092;
            border: 0;
            border-radius: 8px;
            color: #ffffff;
            cursor: pointer;
            font-size: 12px;
            font-weight: 700;
            padding: 10px 14px;
            text-transform: uppercase;
        }

        @media print {
            .print-actions {
                display: none;
            }

            body {
                margin: 0;
            }
        }
    </style>
</head>
<body>
    @php
        $formatNumber = fn ($value) => number_format((int) $value, 0, ',', '.');
        $formatCurrency = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
    @endphp

    <div class="print-actions">
        <button type="button" class="print-button" onclick="window.print()">Cetak / Simpan PDF</button>
    </div>

    <div class="header">
        <h1 class="title">Laporan Aset Diskominfotik Provinsi Riau</h1>
        <p class="subtitle">Rekapitulasi aset aktif terverifikasi berdasarkan filter laporan.</p>
    </div>

    <table class="meta">
        <tr>
            <td><strong>Periode</strong></td>
            <td>{{ $periodLabel }}</td>
            <td><strong>Bidang</strong></td>
            <td>{{ $bidangLabel }}</td>
        </tr>
        <tr>
            <td><strong>Jenis</strong></td>
            <td>{{ $filters['jenis'] }}</td>
            <td><strong>Kategori</strong></td>
            <td>{{ $filters['kategori'] }}</td>
        </tr>
        <tr>
            <td><strong>Kondisi</strong></td>
            <td>{{ $filters['kondisi'] }}</td>
            <td><strong>Tahun Penyusutan</strong></td>
            <td>{{ $depreciationYear }}</td>
        </tr>
        <tr>
            <td><strong>Tanggal Cetak</strong></td>
            <td>{{ now()->format('d M Y H:i') }}</td>
            <td></td>
            <td></td>
        </tr>
    </table>

    <table class="summary">
        <thead>
            <tr>
                <th>Total Aset</th>
                <th>Register</th>
                <th>SMKI</th>
                <th>Kondisi Baik</th>
                <th>Rusak / Perbaikan</th>
                <th>Nilai Perolehan</th>
                <th>Beban Penyusutan</th>
                <th>Nilai Buku</th>
                <th>Aset Nonaktif</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $formatNumber($summary['total']) }}</td>
                <td>{{ $formatNumber($summary['register']) }}</td>
                <td>{{ $formatNumber($summary['smki']) }}</td>
                <td>{{ $formatNumber($summary['good']) }}</td>
                <td>{{ $formatNumber($summary['lightDamage'] + $summary['heavyDamage']) }}</td>
                <td class="currency">{{ $formatCurrency($summary['registerValue']) }}</td>
                <td class="currency">{{ $formatCurrency($summary['depreciationExpense']) }}</td>
                <td class="currency">{{ $formatCurrency($summary['bookValue']) }}</td>
                <td>{{ $formatNumber($summary['deleted']) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="assets">
        <thead>
            <tr>
                <th>Aset</th>
                <th>Jenis</th>
                <th>Bidang</th>
                <th>Kategori</th>
                <th>Kondisi</th>
                <th>Status</th>
                <th class="text-right">Nilai Perolehan</th>
                <th class="text-right">Beban Penyusutan</th>
                <th class="text-right">Nilai Buku</th>
                <th>Tanggal Input</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assets as $asset)
                <tr>
                    <td>
                        <strong>{{ $asset->name }}</strong><br>
                        {{ $asset->code }}
                    </td>
                    <td>{{ $asset->type_label }}</td>
                    <td>{{ $asset->bidang->nama_bidang ?? '-' }}</td>
                    <td>{{ $asset->category ?? '-' }}</td>
                    <td>{{ $asset->condition ?? '-' }}</td>
                    <td>{{ $asset->status }}</td>
                    <td class="currency">{{ $asset->acquisition_value === null ? '-' : $formatCurrency($asset->acquisition_value) }}</td>
                    <td class="currency">{{ $asset->depreciation_expense === null ? '-' : $formatCurrency($asset->depreciation_expense) }}</td>
                    <td class="currency">{{ $asset->book_value === null ? '-' : $formatCurrency($asset->book_value) }}</td>
                    <td>{{ $asset->created_at?->format('d M Y H:i') ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center;">Belum ada aset terverifikasi yang cocok dengan filter laporan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <script>
        window.addEventListener('load', () => window.print());
    </script>
</body>
</html>
