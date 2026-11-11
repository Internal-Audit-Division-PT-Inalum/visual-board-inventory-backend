<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Label Barang Aset (25x25mm) — PT INALUM</title>
    <style>
        @page {
            size: 25mm 25mm;
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

        /* Mini Label Card (25x25mm) */
        .sticker-card {
            width: 25mm;
            height: 25mm;
            max-width: 25mm;
            max-height: 25mm;
            background: #ffffff;
            border: 1px dashed #cbd5e1;
            padding: 1.2mm 1mm 1mm 1mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            text-align: center;
            page-break-after: always;
            overflow: hidden;
        }

        .item-sku {
            font-size: 4.8pt;
            font-weight: 900;
            color: #0f172a;
            line-height: 1.05;
            letter-spacing: -0.1px;
            word-break: break-all;
            width: 100%;
        }

        .qr-wrapper {
            width: 15mm;
            height: 15mm;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-wrapper svg {
            width: 100%;
            height: 100%;
            display: block;
        }

        .property-stamp {
            font-size: 3.1pt;
            font-weight: 800;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.1px;
            line-height: 1.05;
            width: 100%;
            border-top: 0.2px solid #cbd5e1;
            padding-top: 0.3mm;
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
                width: 25mm;
                height: 25mm;
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
            <strong>Pratinjau Cetak Label Barang Aset (25 &times; 25 mm)</strong> &bull; Total: {{ count($stickers) }} Label
        </div>
        <div class="actions">
            <button class="btn-print" onclick="window.print()">Cetak Label (Print)</button>
            <button class="btn-close" onclick="window.close()">Tutup</button>
        </div>
    </div>

    <div class="page-container">
        @foreach ($stickers as $sticker)
            <div class="sticker-card">
                <div class="item-sku">{{ $sticker['sku'] }}</div>

                <div class="qr-wrapper">
                    {!! $sticker['qr_svg'] !!}
                </div>

                <div class="property-stamp">
                    PROPERTY OF PT INALUM &bull; IIA
                </div>
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

