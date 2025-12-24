<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exception Report - {{ $exhibition->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .exhibition-info {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f5f5f5;
        }
        .client-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .client-header {
            background-color: #333;
            color: white;
            padding: 10px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            background-color: #ffc107;
            color: #000;
            border-radius: 3px;
            font-size: 10px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Exception Report</h1>
        <p>Admin Overrides Report</p>
        <p>Generated on: {{ now()->format('F d, Y h:i A') }}</p>
    </div>

    <div class="exhibition-info">
        <strong>Exhibition:</strong> {{ $exhibition->name }}<br>
        <strong>Venue:</strong> {{ $exhibition->venue }}, {{ $exhibition->city }}<br>
        <strong>Date:</strong> {{ $exhibition->start_date->format('M d, Y') }} - {{ $exhibition->end_date->format('M d, Y') }}<br>
        <strong>End Date:</strong> {{ $exhibition->end_date->format('M d, Y') }}
    </div>

    @if($groupedExceptions->isEmpty())
        <div style="text-align: center; padding: 40px;">
            <p>No exceptions found for the selected criteria.</p>
        </div>
    @else
        @foreach($groupedExceptions as $userId => $clientExceptions)
            @php
                $client = $clientExceptions->first()->user;
            @endphp
            <div class="client-section">
                <div class="client-header">
                    {{ $client->name }} 
                    @if($client->company_name)
                        - {{ $client->company_name }}
                    @endif
                    <span style="float: right;">{{ $clientExceptions->count() }} override(s)</span>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th style="width: 15%;">Date & Time</th>
                            <th style="width: 12%;">Type</th>
                            <th style="width: 20%;">Description</th>
                            <th style="width: 18%;">Old Value</th>
                            <th style="width: 18%;">New Value</th>
                            <th style="width: 10%;">Booking #</th>
                            <th style="width: 7%;">Admin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clientExceptions as $exception)
                            <tr>
                                <td>{{ $exception->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    <span class="badge">{{ ucfirst(str_replace('_', ' ', $exception->exception_type)) }}</span>
                                </td>
                                <td>{{ $exception->description }}</td>
                                <td>
                                    @if($exception->old_value)
                                        @if(is_array($exception->old_value))
                                            {{ json_encode($exception->old_value, JSON_UNESCAPED_SLASHES) }}
                                        @else
                                            {{ $exception->old_value }}
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($exception->new_value)
                                        @if(is_array($exception->new_value))
                                            {{ json_encode($exception->new_value, JSON_UNESCAPED_SLASHES) }}
                                        @else
                                            {{ $exception->new_value }}
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $exception->booking->booking_number ?? '-' }}</td>
                                <td>{{ $exception->createdBy->name ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @endif

    <div class="footer">
        <p>This report was generated on {{ now()->format('F d, Y h:i A') }}</p>
        <p>Total Exceptions: {{ $exceptions->count() }} | Total Clients: {{ $groupedExceptions->count() }}</p>
    </div>
</body>
</html>

