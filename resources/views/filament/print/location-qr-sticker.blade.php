<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Stiker Rak Gudang (40x40mm) — PT INALUM</title>
    <style>
        @page {
            size: 40mm 40mm;
            margin: 0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f3f4f6;
            color: #111827;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Screen Control Bar */
        .no-print-toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #1e293b;
            color: #ffffff;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            z-index: 9999;
            font-size: 13px;
        }

        .no-print-toolbar .actions button {
            cursor: pointer;
            border: none;
            border-radius: 6px;
            padding: 8px 16px;
            font-weight: 600;
            font-size: 13px;
            margin-left: 8px;
            transition: background-color 0.2s;
        }

        .btn-print {
            background-color: #2563eb;
            color: #ffffff;
        }

        .btn-print:hover {
            background-color: #1d4ed8;
        }

        .btn-close {
            background-color: #475569;
            color: #ffffff;
        }

        .btn-close:hover {
            background-color: #334155;
        }

        .page-container {
            padding-top: 60px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            min-height: 100vh;
        }

        /* Label Physical Card (40x40mm) */
        .sticker-card {
            width: 40mm;
            height: 40mm;
            max-width: 40mm;
            max-height: 40mm;
            background: #ffffff;
            border: 1px dashed #cbd5e1;
            padding: 2.2mm 2mm 1.8mm 2mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            text-align: center;
            page-break-after: always;
            overflow: hidden;
        }

        .header-logo {
            height: 4.8mm;
            max-width: 28mm;
            object-fit: contain;
            margin-bottom: 0.5mm;
        }

        .qr-wrapper {
            width: 20mm;
            height: 20mm;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-wrapper svg {
            width: 100%;
            height: 100%;
            display: block;
        }

        .rack-code {
            font-size: 6.8pt;
            font-weight: 900;
            color: #0f172a;
            line-height: 1.1;
            letter-spacing: -0.2px;
            word-break: break-all;
            margin-top: 0.5mm;
        }

        .rack-name {
            font-size: 4.8pt;
            font-weight: 600;
            color: #334155;
            line-height: 1.1;
            max-height: 2.8mm;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            width: 100%;
        }

        .footer-tag {
            font-size: 3.8pt;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-top: 0.3px solid #cbd5e1;
            padding-top: 0.4mm;
            width: 100%;
        }

        /* Print Media Styles */
        @media print {
            body {
                background: none;
                margin: 0;
                padding: 0;
            }

            .no-print-toolbar {
                display: none !important;
            }

            .page-container {
                padding-top: 0;
                gap: 0;
            }

            .sticker-card {
                border: none;
                margin: 0;
                width: 40mm;
                height: 40mm;
                page-break-after: always;
            }

            .sticker-card:last-child {
                page-break-after: avoid;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-toolbar">
        <div>
            <strong>Pratinjau Cetak Stiker Rak Gudang (40 &times; 40 mm)</strong> &bull; Total: {{ count($stickers) }} Label
        </div>
        <div class="actions">
            <button class="btn-print" onclick="window.print()">Cetak Label (Print)</button>
            <button class="btn-close" onclick="window.close()">Tutup</button>
        </div>
    </div>

    <div class="page-container">
        @foreach ($stickers as $sticker)
            <div class="sticker-card">
                @if ($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="PT INALUM" class="header-logo">
                @else
                    <div style="font-size: 5pt; font-weight: 800; color: #1e3a8a; letter-spacing: 0.5px;">PT INALUM</div>
                @endif

                <div class="qr-wrapper">
                    {!! $sticker['qr_svg'] !!}
                </div>

                <div class="rack-code">{{ $sticker['code'] }}</div>
                <div class="rack-name">{{ $sticker['name'] }}</div>
                <div class="footer-tag">INTERNAL AUDIT &bull; LOGISTIK</div>
            </div>
        @endforeach
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.print();
            }, 350);
        });
    </script>
</body>
</html>

