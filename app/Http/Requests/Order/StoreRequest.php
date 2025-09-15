<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\ApiRequest;
use App\Rules\AfterToday;

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
            'departure_date' => [
                'required',
                'date_format:d/m/Y',
                'before_or_equal:return_date',
                new AfterToday,
            ],
            'return_date' => 'required|date_format:d/m/Y|after_or_equal:departure_date',
        ];
    }
}
