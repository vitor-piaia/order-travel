<?php

namespace App\Http\Requests\Order;

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
            'name' => 'required|string|max:255',
            'destiny' => 'required|string|max:255',
            'departure_date' => 'required|date_format:d/m/Y',
            'return_date' => 'required|date_format:d/m/Y',
        ];
    }
}
