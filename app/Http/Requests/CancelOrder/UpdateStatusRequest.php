<?php

namespace App\Http\Requests\CancelOrder;

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
            'id' => 'required|exists:cancel_orders_approved,id,status,' . OrderEnum::STATUS_REQUESTED,
            'status' => 'required|string|in:' . OrderEnum::STATUS_APPROVED . ',' . OrderEnum::STATUS_CANCELED,
        ];
    }
}
