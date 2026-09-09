<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $reportTitle ?? \App\Models\SystemSetting::get('institution_name', 'University') . ' Report' }}</title>
    <style>
        @page {
            margin: 10mm 8mm 12mm 8mm;
            size: a4 landscape;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 7.5pt;
            color: #1e293b;
            line-height: 1.2;
            background: #ffffff;
        }

        /* Header */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #0B5D3B;
            margin-bottom: 6px;
            padding-bottom: 4px;
        }

        .header-title-main {
            font-size: 13pt;
            font-weight: bold;
            color: {{ \App\Models\SystemSetting::get('theme_primary_color', '#0B5D3B') }};
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .header-subtitle {
            font-size: 7.5pt;
            color: #64748b;
            font-weight: 500;
        }

        .report-badge {
            font-size: 10.5pt;
            font-weight: bold;
            color: #0f172a;
            text-align: right;
        }

        .report-id {
            font-family: monospace;
            font-size: 8pt;
            color: #64748b;
            text-align: right;
        }

        /* Metadata Card */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            margin-bottom: 6px;
        }

        .meta-table td {
            padding: 3px 6px;
            font-size: 7pt;
            vertical-align: middle;
        }

        .meta-label {
            color: #64748b;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 6pt;
            letter-spacing: 0.3px;
        }

        .meta-value {
            color: #0f172a;
            font-weight: 600;
            font-size: 7.5pt;
        }

        /* Data Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .data-table thead {
            display: table-header-group;
        }

        .data-table th {
            background-color: {{ \App\Models\SystemSetting::get('theme_primary_color', '#0B5D3B') }};
            color: #ffffff;
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            padding: 4px 3px;
            text-align: left;
            border: 0.5px solid #09482E;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .data-table tr {
            page-break-inside: avoid;
        }

        .data-table td {
            padding: 3px 3px;
            font-size: 7pt;
            border: 0.5px solid #cbd5e1;
            color: #334155;
            vertical-align: middle;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        /* Badges & Accents */
        .mono-ref {
            font-family: monospace;
            font-weight: bold;
            color: {{ \App\Models\SystemSetting::get('theme_primary_color', '#0B5D3B') }};
            font-size: 7pt;
        }

        .status-pill {
            display: inline-block;
            padding: 1px 3px;
            font-size: 6pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            border-radius: 2px;
            white-space: nowrap;
        }

        .status-ready, .status-approved, .status-returned, .status-active {
            background-color: #dcfce7;
            color: #15803d;
            border: 0.5px solid #bbf7d0;
        }

        .status-pending, .status-processing, .status-in_storage, .status-under_review, .status-found_unclaimed {
            background-color: #fef3c7;
            color: #b45309;
            border: 0.5px solid #fde68a;
        }

        .status-rejected, .status-failed, .status-disposed, .status-suspended, .status-withdrawn {
            background-color: #ffe4e6;
            color: #be123c;
            border: 0.5px solid #fecdd3;
        }

        .status-reported, .status-lost, .status-found_claimed, .status-default {
            background-color: #e0f2fe;
            color: #0369a1;
            border: 0.5px solid #bae6fd;
        }

        .empty-state {
            padding: 20px;
            text-align: center;
            color: #64748b;
            font-style: italic;
            font-size: 8.5pt;
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <!-- Official Header -->
    <table class="header-table">
        <tr>
            <td style="width: 60%; vertical-align: middle;">
                <div class="header-title-main">{{ \App\Models\SystemSetting::get('institution_name', 'University') }}</div>
                <div class="header-subtitle">{{ \App\Models\SystemSetting::get('system_short_name', 'LFMS') }} &bull; Official Report</div>
            </td>
            <td style="width: 40%; vertical-align: middle; text-align: right;">
                <div class="report-badge">{{ $reportTitle }}</div>
                <div class="report-id">#REP-{{ str_pad((string)$report->id, 5, '0', STR_PAD_LEFT) }}</div>
            </td>
        </tr>
    </table>

    <!-- Metadata Section -->
    <table class="meta-table">
        <tr>
            <td style="width: 25%;">
                <div class="meta-label">Generated On</div>
                <div class="meta-value">{{ now()->format('Y-m-d H:i:s T') }}</div>
            </td>
            <td style="width: 25%;">
                <div class="meta-label">Generated By</div>
                <div class="meta-value">{{ $report->requester?->full_name ?? 'System Administrator' }}</div>
            </td>
            <td style="width: 35%;">
                <div class="meta-label">Applied Filters</div>
                <div class="meta-value">{{ $filterSummary }}</div>
            </td>
            <td style="width: 15%; text-align: right;">
                <div class="meta-label">Total Records</div>
                <div class="meta-value" style="color: #0B5D3B; font-size: 8.5pt;">{{ number_format(count($rows)) }}</div>
            </td>
        </tr>
    </table>

    <!-- Data Rows -->
    @if(count($rows) > 0)
        @php
            $colWidths = match($report->report_type) {
                'items', 'item_list' => ['4%', '12%', '18%', '6%', '12%', '10%', '12%', '12%', '7%', '7%'],
                'returns' => ['4%', '12%', '22%', '15%', '15%', '12%', '10%', '10%'],
                'claim_summary' => ['6%', '12%', '16%', '12%', '26%', '14%', '14%'],
                'user_activity' => ['5%', '18%', '21%', '12%', '10%', '14%', '10%', '10%'],
                'resolution_time' => ['6%', '14%', '24%', '14%', '14%', '14%', '14%'],
                'search_analytics' => ['7%', '27%', '10%', '14%', '10%', '14%', '18%'],
                default => ['5%', '16%', '18%', '10%', '17%', '14%', '20%'],
            };
        @endphp
        <table class="data-table">
            <thead>
                <tr>
                    @foreach($headers as $colIdx => $header)
                        <th style="width: {{ $colWidths[$colIdx] ?? 'auto' }};">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    <tr>
                        @foreach($row as $colIndex => $cell)
                            @php
                                $headerName = $headers[$colIndex] ?? '';
                                $isRef = in_array(strtolower($headerName), ['id', 'reference code', 'item reference', 'claim id', 'user id', 'search id']);
                                $isStatus = in_array(strtolower($headerName), ['status', 'condition', 'event', 'role', 'zero-result failure']);
                            @endphp
                            <td>
                                @if($isRef)
                                    <span class="mono-ref">{{ $cell }}</span>
                                @elseif($isStatus)
                                    @php
                                        $slug = strtolower(str_replace([' ', '-'], '_', (string)$cell));
                                    @endphp
                                    <span class="status-pill status-{{ $slug }}">{{ $cell }}</span>
                                @else
                                    {{ $cell }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty-state">
            No records found matching the specified report criteria and filters.
        </div>
    @endif

    <!-- DomPDF Script for Page Numbering & Footer -->
    <script type="text/php">
        if (isset($pdf)) {
            $footerText = \App\Models\SystemSetting::get('pdf_footer_text', 'Official System Report • Confidential');
            $pageText = "Page {PAGE_NUM} of {PAGE_COUNT}";
            
            $size = 7;
            $font = $fontMetrics->getFont("Helvetica", "normal");
            $pageWidth = $pdf->get_width();
            $pageHeight = $pdf->get_height();
            $y = $pageHeight - 16;

            // Draw thin line above footer
            $pdf->line(22, $y - 3, $pageWidth - 22, $y - 3, array(0.85, 0.88, 0.92), 0.5);

            // Left footer
            $pdf->text(22, $y, $footerText, $font, $size, array(0.45, 0.5, 0.55));

            // Right page number
            $textWidth = $fontMetrics->getTextWidth($pageText, $font, $size);
            $pdf->page_text($pageWidth - 22 - $textWidth, $y, $pageText, $font, $size, array(0.35, 0.4, 0.45));
        }
    </script>
</body>
</html>
