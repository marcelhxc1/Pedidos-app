<!-- resources/views/emails/orders/created.blade.php -->
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido Criado</title>
</head>
<body>
    <h1>Seu pedido foi criado com sucesso!</h1>
    <p>Detalhes do pedido:</p>
    <ul>
        <li><strong>ID do Pedido:</strong> {{ $order->id }}</li>
        <li><strong>Valor Total:</strong> {{ $order->total_value }}</li>
        <li><strong>Status:</strong> {{ $order->status }}</li>
    </ul>
</body>
</html>
