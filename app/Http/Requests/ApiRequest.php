<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

abstract class ApiRequest extends FormRequest
{
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();
        $message = implode(', ', array_column($errors, 0));

        throw new HttpResponseException(response()->json(['message' => $message], Response::HTTP_UNPROCESSABLE_ENTITY));
    }
}
