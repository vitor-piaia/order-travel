<?php

namespace App\Http\Requests\Order;

use App\Enums\OrderEnum;
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
            'order_id' => 'required|exists:orders,order_id,user_id,'.$userId.',status,'.OrderEnum::STATUS_REQUESTED,
            'destiny' => 'required|string|max:255',
            'departure_date' => 'required|date_format:d/m/Y|before_or_equal:return_date',
            'return_date' => 'required|date_format:d/m/Y|after_or_equal:departure_date',
        ];
    }
}
