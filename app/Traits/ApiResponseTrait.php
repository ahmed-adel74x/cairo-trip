<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    /**
     * Success Response
     */
    protected function successResponse(
        mixed $data,
        string $messageKey,
        int $code = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $this->buildMessage($messageKey),
            'data'    => $data,
        ], $code);
    }

    /**
     * Error Response
     */
    protected function errorResponse(
        string $messageKey,
        int $code,
        ?array $errors = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $this->buildMessage($messageKey),
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Build bilingual message from lang files
     */
    private function buildMessage(string $key): array
    {
        $arMessages = require base_path('lang/ar/messages.php');
        $enMessages = require base_path('lang/en/messages.php');

        return [
            'ar' => $arMessages[$key] ?? $key,
            'en' => $enMessages[$key] ?? $key,
        ];
    }

    /**
     * Format validation errors to bilingual format
     */
    protected function formatValidationErrors(array $errors): array
    {
        $arValidation = require base_path('lang/ar/validation.php');
        $enValidation = require base_path('lang/en/validation.php');

        $formatted = [];

        foreach ($errors as $field => $messages) {
            $arMessages = [];
            $enMessages = [];

            foreach ($messages as $message) {
                $key = $this->guessValidationKey($message);

                if ($key) {
                    $arMsg = $this->getNestedValue($arValidation, $key);
                    $enMsg = $this->getNestedValue($enValidation, $key);

                    $arMessages[] = $arMsg
                        ? $this->replaceAttribute($arMsg, $field, $arValidation)
                        : $message;

                    $enMessages[] = $enMsg
                        ? $this->replaceAttribute($enMsg, $field, $enValidation)
                        : $message;
                } else {
                    $arMessages[] = $message;
                    $enMessages[] = $message;
                }
            }

            $formatted[$field] = [
                'ar' => $arMessages,
                'en' => $enMessages,
            ];
        }

        return $formatted;
    }

    /**
     * Get nested value from array using dot notation
     * e.g. 'min.string' → $array['min']['string']
     */
    private function getNestedValue(array $array, string $key): ?string
    {
        $keys  = explode('.', $key);
        $value = $array;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return null;
            }
            $value = $value[$k];
        }

        return is_string($value) ? $value : null;
    }

    /**
     * Guess validation rule key from Laravel error message
     */
    private function guessValidationKey(string $message): ?string
    {
        $messageLower = strtolower($message);

        $map = [
            'required'          => 'required',
            'valid email'       => 'email',
            'email address'     => 'email',
            'confirmation'      => 'confirmed',
            'already been taken'=> 'unique',
            'at least'          => 'min.string',
            'not be greater'    => 'max.string',
            'must be between'   => 'between.numeric',
            'must be an integer'=> 'integer',
            'must be a number'  => 'numeric',
            'selected'          => 'in',
            'must be an image'  => 'image',
            'must be a file'    => 'mimes',
            'valid date'        => 'date',
        ];

        foreach ($map as $keyword => $key) {
            if (str_contains($messageLower, $keyword)) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Replace :attribute placeholder with translated field name
     */
    private function replaceAttribute(
        string $message,
        string $field,
        array $langFile
    ): string {
        $attributes     = $langFile['attributes'] ?? [];
        $attributeLabel = $attributes[$field] ?? $field;

        return str_replace(':attribute', $attributeLabel, $message);
    }

    /**
     * Get bilingual status label
     */
    protected function getStatusLabel(string $status): array
    {
        $labels = [
            'pending'     => ['ar' => 'قيد الانتظار',  'en' => 'Pending'],
            'confirmed'   => ['ar' => 'مؤكد',           'en' => 'Confirmed'],
            'cancelled'   => ['ar' => 'ملغي',           'en' => 'Cancelled'],
            'completed'   => ['ar' => 'مكتملة',         'en' => 'Completed'],
            'upcoming'    => ['ar' => 'قادمة',          'en' => 'Upcoming'],
            'in_progress' => ['ar' => 'قيد المعالجة',  'en' => 'In Progress'],
            'resolved'    => ['ar' => 'تم الحل',        'en' => 'Resolved'],
        ];

        return $labels[$status] ?? ['ar' => $status, 'en' => $status];
    }

    /**
     * Get bilingual rating label
     */
    protected function getRatingLabel(float $rating): array
    {
        $labels = [
            1 => ['ar' => 'سيء',      'en' => 'Poor'],
            2 => ['ar' => 'مقبول',    'en' => 'Fair'],
            3 => ['ar' => 'جيد',      'en' => 'Good'],
            4 => ['ar' => 'جيد جداً', 'en' => 'Very Good'],
            5 => ['ar' => 'ممتاز',    'en' => 'Excellent'],
        ];

        $rounded = (int) round($rating);
        $rounded = max(1, min(5, $rounded));

        return $labels[$rounded];
    }
}