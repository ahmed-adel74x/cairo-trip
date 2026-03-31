<?php

namespace App\Http\Requests;

use App\Traits\ApiResponseTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BookingRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'place_id'     => 'required|integer|exists:places,id',
            'booking_date' => 'required|date|after:today',
            'person_count' => 'required|integer|min:1|max:20',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $errors = $this->formatValidationErrors(
            $validator->errors()->toArray()
        );

        throw new HttpResponseException(
            $this->errorResponse('validation_error', 422, $errors)
        );
    }
}