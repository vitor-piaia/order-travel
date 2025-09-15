<?php

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use Exception;
use Illuminate\Contracts\Validation\ValidationRule;

class AfterToday implements ValidationRule
{
    protected string $format;

    public function __construct(string $format = 'd/m/Y')
    {
        $this->format = $format;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $date = Carbon::createFromFormat($this->format, $value);

            if ($date->lt(now()->startOfDay())) {
                $fail("The {$attribute} field must be a date before today.");
            }
        } catch (Exception $e) {
            $fail("The {$attribute} field must be a date before today.");
        }
    }
}
