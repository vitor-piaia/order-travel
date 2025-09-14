@component('mail::message')
    # Pedido Reprovado ❌

    Olá {{ $order->user->name }},

    Infelizmente, seu pedido **#{{ $order->order_id }}** não foi aprovado.

    Se tiver dúvidas, entre em contato com nossa equipe de suporte.
    Obrigado por confiar em nós.
@endcomponent
