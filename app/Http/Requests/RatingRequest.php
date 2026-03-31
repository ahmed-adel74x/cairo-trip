<?php

namespace App\Http\Requests;

use App\Traits\ApiResponseTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RatingRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'trip_id' => 'required|integer|exists:trips,id',
            'rating'  => 'required|numeric|min:1|max:5',
            'comment' => 'nullable|string|max:500',
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