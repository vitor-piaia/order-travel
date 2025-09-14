<?php

namespace App\Http\Requests\CancelOrder;

use App\Http\Requests\ApiRequest;

class StoreRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => 'required|integer',
            'message' => 'required|string|max:2000',
        ];
    }
}
