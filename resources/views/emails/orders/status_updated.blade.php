<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status do Pedido Atualizado</title>
</head>
<body>
    <h1>Status do Pedido #{{ $order->id }} Atualizado</h1>
    <p>O status do seu pedido foi atualizado para: {{ $order->status }}.</p>
    <p>Obrigado por comprar conosco!</p>
</body>
</html>
