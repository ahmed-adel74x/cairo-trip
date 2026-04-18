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
     * ✅ Fix: استخدام الـ Validator نفسه عشان الـ placeholders تتبدل صح
     */
    protected function formatValidationErrors(\Illuminate\Validation\Validator $validator): array
    {
        $arValidation = require base_path('lang/ar/validation.php');
        $enValidation = require base_path('lang/en/validation.php');

        $formatted = [];

        // ── نبدل الـ locale لـ AR ونجيب الـ messages ──
        app()->setLocale('ar');
        $validator->setTranslator(app('translator'));

        // نحتاج نعمل custom translator بدل كده
        // الأسهل: نجيب الـ messages من الـ validator مباشرة
        // بس نبدل الـ lang file مؤقتاً

        $rawErrors = $validator->errors()->toArray();
        $messages  = $validator->failed(); // ['field' => ['Rule' => [params]]]

        foreach ($rawErrors as $field => $fieldMessages) {
            $arMessages = [];
            $enMessages = [];

            // الـ rules اللي failed لكل field
            $failedRules = $messages[$field] ?? [];

            foreach ($fieldMessages as $index => $originalMessage) {
                // نجيب اسم الـ Rule من الـ failed array
                $ruleName   = array_keys($failedRules)[$index] ?? null;
                $ruleParams = $ruleName ? array_values($failedRules)[$index] : [];

                if ($ruleName) {
                    $arMsg = $this->getValidationMessage(
                        $arValidation,
                        $ruleName,
                        $field,
                        $ruleParams,
                        'ar'
                    );
                    $enMsg = $this->getValidationMessage(
                        $enValidation,
                        $ruleName,
                        $field,
                        $ruleParams,
                        'en'
                    );
                } else {
                    $arMsg = $originalMessage;
                    $enMsg = $originalMessage;
                }

                $arMessages[] = $arMsg;
                $enMessages[] = $enMsg;
            }

            $formatted[$field] = [
                'ar' => $arMessages,
                'en' => $enMessages,
            ];
        }

        return $formatted;
    }

    /**
     * ✅ الدالة الجديدة - تجيب الـ message وتبدل كل الـ placeholders
     */
    private function getValidationMessage(
        array $langFile,
        string $ruleName,
        string $field,
        array $params,
        string $locale
    ): string {
        // Laravel بيحفظ الـ rule name بـ StudlyCase مثلاً: Min, Max, Between
        // نحوله لـ snake_case صغير
        $ruleKey = strtolower($ruleName);

        // بعض الـ rules عندها sub-types (string/numeric/file)
        $subType = 'string'; // default

        // نحدد الـ sub-type
        $numericRules = ['min', 'max', 'between', 'size'];
        if (in_array($ruleKey, $numericRules)) {
            // نعرف النوع من الـ field نفسه - بنفترض string افتراضياً
            // لو عندك numeric fields زي budget, rating هيبقى numeric
            $numericFields = [
                'rating', 'budget', 'person_count',
                'price_number', 'trip_id', 'place_id',
            ];
            if (in_array($field, $numericFields)) {
                $subType = 'numeric';
            }
        }

        // نجيب الـ message من الـ lang file
        $message = null;

        if (isset($langFile[$ruleKey])) {
            $entry = $langFile[$ruleKey];
            if (is_array($entry)) {
                // عنده sub-types (string/numeric/file)
                $message = $entry[$subType] ?? $entry['string'] ?? null;
            } else {
                $message = $entry;
            }
        }

        if (!$message) {
            return "{$field}: validation failed ({$ruleKey})";
        }

        // ── نبدل كل الـ Placeholders ──────────────────

        // :attribute
        $attributes     = $langFile['attributes'] ?? [];
        $attributeLabel = $attributes[$field] ?? $field;
        $message        = str_replace(':attribute', $attributeLabel, $message);

        // :min (index 0 من الـ params)
        if (isset($params[0])) {
            $message = str_replace(':min', $params[0], $message);
        }

        // :max (index 1 أو 0 حسب الـ rule)
        if ($ruleKey === 'max' && isset($params[0])) {
            $message = str_replace(':max', $params[0], $message);
        }

        // :between له min و max
        if ($ruleKey === 'between') {
            if (isset($params[0])) {
                $message = str_replace(':min', $params[0], $message);
            }
            if (isset($params[1])) {
                $message = str_replace(':max', $params[1], $message);
            }
        }

        // :size
        if ($ruleKey === 'size' && isset($params[0])) {
            $message = str_replace(':size', $params[0], $message);
        }

        // :values (للـ in rule)
        if ($ruleKey === 'in' && !empty($params)) {
            $message = str_replace(':values', implode(', ', $params), $message);
        }

        // :date (للـ after/before rules)
        if (in_array($ruleKey, ['after', 'before', 'after_or_equal', 'before_or_equal'])) {
            if (isset($params[0])) {
                $dateLabel = $params[0] === 'today'
                    ? ($locale === 'ar' ? 'اليوم' : 'today')
                    : $params[0];
                $message = str_replace(':date', $dateLabel, $message);
            }
        }

        // :other (للـ same/different rules)
        if (in_array($ruleKey, ['same', 'different']) && isset($params[0])) {
            $otherAttributes = $langFile['attributes'] ?? [];
            $otherLabel      = $otherAttributes[$params[0]] ?? $params[0];
            $message         = str_replace(':other', $otherLabel, $message);
        }

        return $message;
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