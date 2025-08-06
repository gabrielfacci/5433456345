<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>CRM Cadastros</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 4px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
<h2 style="text-align:center">CRM CADASTRO DE USUARIOS</h2>
<table>
    <thead>
        <tr>
            <th>Nome</th>
            <th>Email</th>
            <th>Telefone</th>
            <th>Data Cadastro</th>
        </tr>
    </thead>
    <tbody>
    @foreach($records as $rec)
        <tr>
            <td>{{ $rec->name }}</td>
            <td>{{ $rec->email }}</td>
            <td>{{ $rec->phone }}</td>
            <td>{{ \Carbon\Carbon::parse($rec->created_at)->format('d/m/Y H:i') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html> 