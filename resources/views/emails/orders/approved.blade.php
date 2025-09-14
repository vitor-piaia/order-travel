@component('mail::message')
    # Pedido Aprovado 🎉

    Olá {{ $order->user->name }},

    Temos uma ótima notícia: seu pedido **#{{ $order->order_id }}** foi aprovado com sucesso! ✅

    @component('mail::panel')
        **Resumo do pedido:**
        - Nome: {{ $order->name }}
        - Data da partida: {{ $order->departure_date }}
        - Data de retorno: {{ $order->return_date }}
    @endcomponent

    Obrigado!
@endcomponent
