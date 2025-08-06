<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>CRM Depositantes</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 4px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
<h2 style="text-align:center">CRM DEPOSITANTES</h2>
<table>
    <thead>
        <tr>
            <th>Nome</th>
            <th>Email</th>
            <th>Telefone</th>
            <th>Qtd. Depósitos</th>
            <th>Total Depositado</th>
            <th>Último Depósito</th>
        </tr>
    </thead>
    <tbody>
    @foreach($records as $rec)
        <tr>
            <td>{{ $rec->name }}</td>
            <td>{{ $rec->email }}</td>
            <td>{{ $rec->phone }}</td>
            <td>{{ $rec->deposits_count }}</td>
            <td>{{ number_format($rec->deposits_total, 2, ',', '.') }}</td>
            <td>{{ optional($rec->last_deposit_at)->format('d/m/Y H:i') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html> 