<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Detail Aset - {{ $assetData->code }}</title>
        <style>
            :root {
                color-scheme: light;
                --blue: #0f3092;
                --blue-dark: #002d84;
                --blue-soft: #ebf3ff;
                --border: #dbe4f0;
                --muted: #64748b;
                --muted-soft: #94a3b8;
                --page: #f8fafc;
                --panel: #ffffff;
                --text: #0f172a;
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                min-height: 100vh;
                background: var(--page);
                color: var(--text);
                font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                -webkit-font-smoothing: antialiased;
            }

            .qr-page {
                width: min(100%, 820px);
                margin: 0 auto;
                padding: 24px 16px 40px;
            }

            .asset-card {
                overflow: hidden;
                border: 1px solid var(--border);
                border-radius: 18px;
                background: var(--panel);
                box-shadow: 0 10px 28px rgba(15, 23, 42, 0.08);
            }

            .asset-hero {
                background: var(--blue);
                color: #ffffff;
                padding: 24px;
            }

            .hero-meta {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 14px;
                margin-bottom: 18px;
            }

            .type-badge {
                display: inline-flex;
                align-items: center;
                min-height: 26px;
                padding: 5px 12px;
                border: 1px solid rgba(255, 255, 255, 0.25);
                border-radius: 7px;
                background: rgba(255, 255, 255, 0.14);
                color: #ffffff;
                font-size: 11px;
                font-weight: 800;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            .brand {
                color: #dbeafe;
                font-size: 11px;
                font-weight: 800;
                letter-spacing: 0.08em;
                text-align: right;
                text-transform: uppercase;
            }

            h1 {
                margin: 0;
                font-size: clamp(26px, 8vw, 36px);
                line-height: 1.12;
                font-weight: 850;
                letter-spacing: 0;
            }

            .asset-code {
                margin: 10px 0 0;
                color: #dbeafe;
                font-size: 15px;
                font-weight: 750;
            }

            .asset-body {
                padding: 24px;
            }

            .info-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 14px;
                margin-bottom: 24px;
            }

            .info-box {
                min-width: 0;
                border: 1px solid var(--border);
                border-radius: 14px;
                background: #f8fafc;
                padding: 15px 16px;
            }

            .label {
                display: block;
                margin-bottom: 7px;
                color: var(--muted-soft);
                font-size: 10px;
                font-weight: 850;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            .value {
                display: block;
                overflow-wrap: anywhere;
                color: #1e293b;
                font-size: 14px;
                font-weight: 750;
                line-height: 1.45;
            }

            .detail-table {
                overflow: hidden;
                margin-bottom: 24px;
                border: 1px solid var(--border);
                border-radius: 14px;
            }

            .detail-row {
                display: grid;
                grid-template-columns: minmax(132px, 180px) minmax(0, 1fr);
                border-bottom: 1px solid #edf2f7;
            }

            .detail-row:last-child {
                border-bottom: 0;
            }

            .detail-label,
            .detail-value {
                padding: 14px 16px;
                font-size: 13px;
                line-height: 1.4;
            }

            .detail-label {
                background: #f8fafc;
                color: var(--muted-soft);
                font-weight: 850;
                letter-spacing: 0.07em;
                text-transform: uppercase;
            }

            .detail-value {
                overflow-wrap: anywhere;
                color: #1e293b;
                font-weight: 650;
            }

            .section-title {
                margin: 0 0 12px;
                color: #0f172a;
                font-size: 12px;
                font-weight: 850;
                letter-spacing: 0.07em;
                text-transform: uppercase;
            }

            .description {
                margin: 0;
                border: 1px solid var(--border);
                border-radius: 14px;
                background: #f8fafc;
                padding: 15px 16px;
                color: #475569;
                font-size: 14px;
                font-weight: 500;
                line-height: 1.65;
            }

            .footer-note {
                margin-top: 24px;
                border-top: 1px solid #edf2f7;
                padding-top: 16px;
                color: var(--muted-soft);
                font-size: 10px;
                font-weight: 850;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            @media (max-width: 640px) {
                .qr-page {
                    padding: 18px 12px 34px;
                }

                .asset-card {
                    border-radius: 16px;
                }

                .asset-hero,
                .asset-body {
                    padding: 20px;
                }

                .hero-meta {
                    align-items: flex-start;
                    flex-direction: column;
                    gap: 10px;
                }

                .brand {
                    text-align: left;
                }

                .info-grid {
                    grid-template-columns: 1fr;
                }

                .detail-row {
                    grid-template-columns: 1fr;
                }

                .detail-label {
                    padding-bottom: 5px;
                }

                .detail-value {
                    padding-top: 0;
                    background: #f8fafc;
                }
            }
        </style>
    </head>
    <body>
        <main class="qr-page">
            <div class="asset-card">
                <div class="asset-hero">
                    <div class="hero-meta">
                        <span class="type-badge">
                            {{ $assetData->type_label }}
                        </span>
                        <span class="brand">
                            SIMA Diskominfotik Riau
                        </span>
                    </div>
                    <h1>
                        {{ $assetData->title }}
                    </h1>
                    <p class="asset-code">
                        {{ $assetData->code }}
                    </p>
                </div>

                <div class="asset-body">
                    <div class="info-grid">
                        <div class="info-box">
                            <span class="label">Kategori</span>
                            <span class="value">{{ $assetData->category }}</span>
                        </div>
                        <div class="info-box">
                            <span class="label">Kondisi</span>
                            <span class="value">{{ $assetData->condition }}</span>
                        </div>
                        <div class="info-box">
                            <span class="label">Lokasi</span>
                            <span class="value">{{ $assetData->location ?: '-' }}</span>
                        </div>
                        <div class="info-box">
                            <span class="label">Penanggung Jawab</span>
                            <span class="value">{{ $assetData->responsible_person ?: '-' }}</span>
                        </div>
                    </div>

                    <div class="detail-table">
                        @foreach($assetData->detail_rows as $label => $value)
                            <div class="detail-row">
                                <div class="detail-label">{{ $label }}</div>
                                <div class="detail-value">{{ $value }}</div>
                            </div>
                        @endforeach
                    </div>

                    <div>
                        <h2 class="section-title">
                            Keterangan
                        </h2>
                        <p class="description">
                            {{ $assetData->description ?: 'Tidak ada keterangan tambahan.' }}
                        </p>
                    </div>

                    <div class="footer-note">
                        Data ditampilkan dari QR Code aset terverifikasi.
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>
