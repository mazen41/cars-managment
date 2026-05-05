<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ translate('Car Inspection Report') }} - {{ $carInspection->inspection_number }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta charset="UTF-8">
    <style media="all">
        body {
            font-family: '<?php echo  $font_family ?>';
            font-weight: normal;
            direction: <?php echo  $direction ?>;
            text-align: <?php echo  $text_align ?>;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .header {
            background: #667eea;
            color: white;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
        }

        .header h1 {
            margin: 0 0 10px 0;
            font-size: 24px;
        }

        .header h2 {
            margin: 0 0 10px 0;
            font-size: 18px;
            font-weight: normal;
        }

        .header p {
            margin: 0;
            font-size: 11px;
        }

        .inspection-details {
            background: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
        }

        .details-grid {
            display: table;
            width: 100%;
        }

        .details-row {
            display: table-row;
        }

        .details-cell {
            display: table-cell;
            padding: 5px 10px 5px 0;
            vertical-align: top;
            width: 50%;
        }

        .details-label {
            font-weight: bold;
            color: #495057;
            margin-right: 10px;
        }

        .section-card {
            border: 1px solid #dee2e6;
            margin-bottom: 15px;
            page-break-inside: avoid;
        }

        .section-header {
            background: #f8f9fa;
            padding: 12px 15px;
            border-bottom: 1px solid #dee2e6;
        }

        .section-title {
            font-weight: bold;
            font-size: 14px;
            margin: 0 0 5px 0;
        }

        .section-description {
            font-size: 10px;
            color: #6c757d;
            margin: 0;
        }

        .section-stats {
            float: right;
            font-size: 10px;
            color: #6c757d;
        }

        .section-content {
            padding: 15px;
        }

        .field-row {
            border-bottom: 1px solid #f1f1f1;
            padding: 8px 0;
            display: table;
            width: 100%;
        }

        .field-row:last-child {
            border-bottom: none;
        }

        .field-label {
            display: table-cell;
            font-weight: bold;
            color: #495057;
            width: 40%;
            vertical-align: top;
            padding-right: 15px;
        }

        .field-value {
            display: table-cell;
            vertical-align: top;
        }

        .field-notes {
            font-size: 10px;
            color: #6c757d;
            margin-top: 3px;
            font-style: italic;
        }

        .status-badge-inline {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            display: inline-block;
        }

        .status-completed {
            background: #d4edda;
            color: #155724;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-failed {
            background: #f8d7da;
            color: #721c24;
        }

        .completion-bar {
            background: #e9ecef;
            height: 6px;
            margin-top: 5px;
            position: relative;
        }

        .completion-fill {
            background: #28a745;

        }

        .summary-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }

        .summary-row {
            display: table-row;
        }

        .summary-cell {
            display: table-cell;
            text-align: center;
            padding: 10px;
            border: 1px solid #dee2e6;
            background: #f8f9fa;
        }

        .summary-number {
            font-size: 18px;
            font-weight: bold;
            color: #495057;
            margin-bottom: 5px;
        }

        .summary-label {
            font-size: 10px;
            color: #6c757d;
            text-transform: uppercase;
        }

        .no-value {
            color: #6c757d;
            font-style: italic;
        }

        .required-field {
            color: #dc3545;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 10px;
            color: #6c757d;
        }

        .page-break {
            page-break-before: always;
        }

        h3 {
            color: #495057;
            font-size: 16px;
            margin: 20px 0 10px 0;
        }

        .notes-section {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            margin-top: 20px;
        }

        .notes-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #856404;
        }
        /* Attachments / image gallery for PDF previews */
        .attachments-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 12px;
        }

        .attachment-item {
            width: calc(33.333% - 6.66px);
            page-break-inside: avoid;
            -webkit-column-break-inside: avoid;
            break-inside: avoid;
            text-align: center;
        }

        .attachment-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #e6e6e6;
            display: block;
        }

        .attachment-caption {
            font-size: 10px;
            color: #6c757d;
            margin-top: 6px;
            word-break: break-word;
        }

        @media (max-width: 900px) {
            .attachment-item { width: calc(50% - 5px); }
            .attachment-item img { height: 140px; }
        }

        @media (max-width: 480px) {
            .attachment-item { width: 100%; }
            .attachment-item img { height: 120px; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <img src="{{ uploaded_asset(get_setting('site_icon')) }}" alt="{{ get_setting('site_name') }}" style="height: 100px">
        <h1>{{ translate('Car Inspection Report') }}</h1>
        <h2>{{ translate('Inspection #') }}{{ $carInspection->inspection_number }}</h2>
        <p>{{ translate('Generated on') }}: {{ now()->format('F j, Y \a\t g:i A') }}</p>
        <!-- Report score -->
        @if($carInspection->total_score)
            <div class="d-flex align-items-center">
                <span>{{translate('Score')}}: </span>
                <span class="mr-2">{{ number_format($carInspection->total_score, 1) }}%</span>
                @php
                    $score_color = 'danger';
                    if($carInspection->total_score >= 90) $score_color = 'success';
                    elseif($carInspection->total_score >= 75) $score_color = 'info';
                    elseif($carInspection->total_score >= 60) $score_color = 'warning';
                @endphp
                <div class="progress flex-1" style="height: 6px; width: 200px;">
                    <div class="progress-bar bg-{{ $score_color }}" style="width: {{ $carInspection->total_score }}%"></div>
                </div>
            </div>
        @endif
    </div>

    <!-- Inspection Details -->
    <div class="inspection-details">
        <div class="details-grid">
            <div class="details-row">
                <div class="details-cell">
                    <h3>{{ translate('Vehicle Information') }}</h3>
                    <div class="field-row">
                        <span class="details-label">{{ translate('VIN') }}:</span>
                        {{ $carInspection->car->vin ?? translate('N/A') }}
                    </div>
                    <div class="field-row">
                        <span class="details-label">{{ translate('Brand') }}:</span>
                        {{ $carInspection->car->brand->name ?? translate('N/A') }}
                    </div>
                    <div class="field-row">
                        <span class="details-label">{{ translate('Model') }}:</span>
                        {{ $carInspection->car->model->name ?? translate('N/A') }}
                    </div>
                    <div class="field-row">
                        <span class="details-label">{{ translate('Category') }}:</span>
                        {{ $carInspection->car->category->name ?? translate('N/A') }}
                    </div>
                    <div class="field-row">
                        <span class="details-label">{{ translate('Year') }}:</span>
                        {{ $carInspection->car->manufacture_year ?? translate('N/A') }}
                    </div>
                    <div class="field-row">
                        <span class="details-label">{{ translate('Transmission') }}:</span>
                        {{ translate(ucfirst(str_replace('_', ' ', $carInspection->car->transmission))) ?? translate('N/A') }}
                    </div>
                     <div class="field-row">
                        <span class="details-label">{{ translate('Fuel type') }}:</span>
                        {{ translate(ucfirst(str_replace('_', ' ', $carInspection->car->fuel_type))) ?? translate('N/A') }}
                    </div>
                </div>
                <div class="details-cell">
                    <h3>{{ translate('Inspection Information') }}</h3>
                    <div class="field-row">
                        <span class="details-label">{{ translate('Type') }}:</span>
                        {{ $carInspection->inspectionType->name }}
                    </div>
                    <div class="field-row">
                        <span class="details-label">{{ translate('Inspector') }}:</span>
                        {{ $carInspection->inspector->shop_name ?? translate('N/A') }}
                    </div>
                    <div class="field-row">
                        <span class="details-label">{{ translate('Requester') }}:</span>
                        {{ $carInspection->requester->name ?? translate('N/A') }}
                    </div>
                    <div class="field-row">
                        <span class="details-label">{{ translate('Scheduled Date') }}:</span>
                        {{ $carInspection->scheduled_at ? $carInspection->scheduled_at->format('M j, Y g:i A') : translate('N/A') }}
                    </div>
                    <div class="field-row">
                        <span class="details-label">{{ translate('Completed Date') }}:</span>
                        {{ $carInspection->completed_at ? $carInspection->completed_at->format('M j, Y g:i A') : translate('N/A') }}
                    </div>
                    <div class="field-row">
                        <span class="details-label">{{ translate('Status') }}:</span>
                        <span class="status-badge-inline status-{{ strtolower($carInspection->status) }}">
                            {{ ucfirst($carInspection->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inspection Sections -->
    @foreach($sectionData as $sectionId => $data)
        <div class="section-card">
            <div class="section-header">
                <div class="section-title">{{ $data['section']->name }}</div>
                @if($data['section']->description)
                    <div class="section-description">{{ $data['section']->description }}</div>
                @endif
            </div>
            <div class="section-content">
                @if(count($data['fields']) > 0)
                    @foreach($data['fields'] as $fieldData)
                        <div class="field-row">
                            <div class="field-label">
                                {{ $fieldData['field']->name }}
                                @if($fieldData['field']->is_required)
                                    <span class="required-field">*</span>
                                @endif
                            </div>
                            <div class="field-value">
                                @if($fieldData['value'])
                                    @switch($fieldData['field']->field_type)
                                        @case('text')
                                        @case('textarea')
                                            {{ $fieldData['value']->value ?: translate('No value provided') }}
                                            @break

                                        @case('select')
                                        @case('radio')
                                            {{ $fieldData['value']->value ?: translate('No selection made') }}
                                            @break

                                        @case('checkbox')
                                            @php
                                                $checkboxValues = json_decode($fieldData['value']->value, true);
                                            @endphp
                                            @if(is_array($checkboxValues) && count($checkboxValues) > 0)
                                                {{ implode(', ', $checkboxValues) }}
                                            @else
                                                {{ translate('No selections made') }}
                                            @endif
                                            @break

                                        @case('file')
                                            @if($fieldData['value']->value)
                                                {{ translate('File uploaded') }}: {{ basename($fieldData['value']->value) }}
                                            @else
                                                {{ translate('No file uploaded') }}
                                            @endif
                                            @break

                                        @case('date')
                                            {{ $fieldData['value']->value ? \Carbon\Carbon::parse($fieldData['value']->value)->format('M j, Y') : translate('No date selected') }}
                                            @break

                                        @case('number')
                                            {{ $fieldData['value']->value ?: translate('No value provided') }}
                                            @break

                                        @default
                                            {{ $fieldData['value']->value ?: translate('No value provided') }}
                                    @endswitch

                                    @if($fieldData['value']->notes)
                                        <div class="field-notes">
                                            <strong>{{ translate('Notes') }}:</strong> {{ $fieldData['value']->notes }}
                                        </div>
                                    @endif
                                    @if(!empty($fieldData['value']->file_attachments) && count($fieldData['value']->file_attachments) > 0)
                                    <div class="attachments-grid">
                                        @foreach ($fieldData['value']->file_attachments as $attachment)
                                            <div class="attachment-item">
                                                <img src="{{ $attachment['url'] }}" alt="{{ $attachment['name'] ?? 'attachment' }}">
                                                @if(!empty($attachment['name']))
                                                    <div class="attachment-caption">{{ $attachment['name'] }}</div>
                                                @else
                                                    <div class="attachment-caption">{{ basename($attachment['url']) }}</div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                    @endif
                                @else
                                    <span class="no-value">{{ translate('Not completed') }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="no-value" style="text-align: center; padding: 20px;">
                        {{ translate('No fields defined for this section') }}
                    </div>
                @endif
            </div>
        </div>
    @endforeach

    <!-- Inspector Notes -->
    @if($carInspection->inspector_notes)
        <div class="notes-section">
            <div class="notes-title">{{ translate('Inspector Notes') }}</div>
            <div>{{ $carInspection->inspector_notes }}</div>
        </div>
    @endif

     @if($carInspection->recommendations)
        <div class="notes-section">
            <div class="notes-title">{{ translate('Recommendations') }}</div>
            <div>{{ $carInspection->recommendations }}</div>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>{{ translate('This report was automatically generated from the car inspection system.') }}</p>
        <p>{{ translate('Report ID') }}: {{ $carInspection->inspection_number }} | {{ translate('Generated') }}: {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>
