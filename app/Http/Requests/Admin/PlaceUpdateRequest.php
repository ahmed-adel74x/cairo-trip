<?php

namespace App\Http\Requests\Admin;

use App\Traits\ApiResponseTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PlaceUpdateRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_ar'          => 'sometimes|string|max:255',
            'name_en'          => 'sometimes|string|max:255',
            'description_ar'   => 'sometimes|string',
            'description_en'   => 'sometimes|string',
            'image'            => 'sometimes|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_free'          => 'sometimes|boolean',
            'price_ar'         => 'sometimes|string',
            'price_en'         => 'sometimes|string',
            'price_number'     => 'sometimes|numeric|min:0',
            'working_hours_ar' => 'sometimes|string',
            'working_hours_en' => 'sometimes|string',
            'location_ar'      => 'sometimes|string',
            'location_en'      => 'sometimes|string',
            'activities_ar'    => 'sometimes|array|min:1',
            'activities_ar.*'  => 'sometimes|string',
            'activities_en'    => 'sometimes|array|min:1',
            'activities_en.*'  => 'sometimes|string',
            'is_active'        => 'sometimes|boolean',
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