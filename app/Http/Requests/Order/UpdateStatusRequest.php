<?php

namespace App\Http\Requests\Order;

use App\Enums\OrderEnum;
use App\Http\Requests\ApiRequest;

class UpdateStatusRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => 'required|exists:orders,order_id,status,'.OrderEnum::STATUS_REQUESTED,
            'status' => 'required|string|in:'.OrderEnum::STATUS_APPROVED.','.OrderEnum::STATUS_CANCELED,
        ];
    }
}
