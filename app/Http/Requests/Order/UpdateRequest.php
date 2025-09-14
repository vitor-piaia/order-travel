<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\ApiRequest;

class UpdateRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = auth()->id();
        return [
            'order_id' => 'required|exists:orders,order_id,user_id,' . $userId,
            'destiny' => 'required|string|max:255',
            'departure_date' => 'required|date_format:d/m/Y',
            'return_date' => 'required|date_format:d/m/Y',
        ];
    }
}
