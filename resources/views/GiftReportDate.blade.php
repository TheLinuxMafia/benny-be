<!DOCTYPE html>
<html>
<head>
    <title>Report gift card </title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <style>
        * {
            font-size: 10px;
        }
    </style>
</head>
<body>
<b>Cliente:</b> {{$user->nome}} {{$user->cognome}} <BR> <b>Card:</b> {{ $user->gift}} <BR>
@if ($user->targa) <b>Targa:</b> {{ $user->targa }}@endif
<table class="table table-sm mt-2">
    <thead>
        <th>Data</th>
        <th>Ora</th>
        <th>Tipo</th>
        <th>Valore</th>
        <th>Credito</th>
    </thead>
    <tbody>
    @foreach($reports as $report)
    <tr>
        <td>
        {{ date('d/m/Y', strtotime($report->data_movimento)) }}
        </td>
        <td>
            {{ date('H:i:s', strtotime($report->data_movimento)) }}
        </td>
        @if ($report->tipo_mov === 'carico')
        <td>
            Ricarica
        </td>
         @endif
         @if ($report->tipo_mov === 'scarico')
        <td>
            Pagamento
        </td>
         @endif

        <td>
        € {{$report->valore}}
        </td>
        <td>
        € {{$report->stato}}
        </td>
    </tr>
    @endforeach
    </tbody>
</table>





</body>
</html>