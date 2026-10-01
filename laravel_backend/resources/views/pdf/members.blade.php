<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Member PDF</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #333;
            padding: 6px;
            font-size: 10px;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<div style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px;">
    <img src="{{ public_path('storage/4steplogo.png') }}" width="90">

    <p style="font-size:12px;">
        Member Report
    </p>
</div>

<table>
    <thead>
        <tr>
            @foreach ($columns as $col)
                <th>{{ $col['label'] }}</th>
            @endforeach
        </tr>
    </thead>

    <tbody>
        @foreach ($records as $record)
            <tr>
                @foreach ($columns as $col)

                    @php
                        $value = data_get($record, $col['name']);

                        // ✅ NULL handling
                        if (empty($value)) {
                            $value = '-';
                        }

                        // ✅ Date formatting
                        if (in_array($col['name'], ['created_at', 'updated_at', 'dob', 'activation_date']) && $value !== '-') {
                            $value = \Carbon\Carbon::parse($value)->format('d-m-Y');
                        }

                        // ✅ Active status
                        if ($col['name'] === 'is_active') {
                            $value = $value == 1 ? 'Active' : 'Inactive';
                        }

                        // ✅ Status (numeric → readable)
                        if ($col['name'] === 'status') {
                            $value = $value == 1 ? 'Active' : 'Inactive';
                        }

                        // ✅ Position formatting
                        if ($col['name'] === 'position') {
                            $value = ucfirst($value);
                        }
                    @endphp

                    <td>{{ $value }}</td>

                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>