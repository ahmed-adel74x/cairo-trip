<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\BudgetRequest;
use App\Http\Resources\BudgetResultResource;
use App\Models\Place;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    use ApiResponseTrait;

    // ──────────────────────────────────────────────────
    // POST /api/budget/calculate
    // ──────────────────────────────────────────────────
    public function calculate(BudgetRequest $request): JsonResponse
    {
        $budget      = (float) $request->budget;
        $personCount = (int) ($request->person_count ?? 1);

        // جلب كل الأماكن مرتبة من الأرخص للأغلى
        $allPlaces = Place::active()
            ->orderBy('price_number')
            ->get();

        $selectedPlaces    = collect();
        $remainingBudget   = $budget;
        $totalCostSelected = 0;

        foreach ($allPlaces as $place) {
            $costForThisPlace = $place->price_number * $personCount;

            // الأماكن المجانية دايماً تتضمن
            if ($costForThisPlace == 0) {
                $place->total_cost = 0;
                $place->can_afford = true;
                $selectedPlaces->push($place);
                continue;
            }

            // الأماكن المدفوعة - نشوف لو الميزانية تكفي
            if ($costForThisPlace <= $remainingBudget) {
                $place->total_cost  = $costForThisPlace;
                $place->can_afford  = true;
                $remainingBudget   -= $costForThisPlace;
                $totalCostSelected += $costForThisPlace;
                $selectedPlaces->push($place);
            }
        }

        // الأماكن اللي مش في الاختيار
        $selectedIds       = $selectedPlaces->pluck('id')->toArray();
        $notSelectedPlaces = $allPlaces->filter(function ($place) use ($selectedIds) {
            return !in_array($place->id, $selectedIds);
        })->each(function ($place) use ($personCount) {
            $place->total_cost = $place->price_number * $personCount;
            $place->can_afford = false;
        })->values();

        return $this->successResponse(
            [
                // ── معلومات الميزانية ──────────────────────
                'budget_info' => [
                    'entered_budget' => [
                        'number' => $budget,
                        'ar'     => number_format($budget, 0) . ' جنيه',
                        'en'     => number_format($budget, 0) . ' EGP',
                    ],
                    'person_count' => $personCount,

                    // إجمالي تكلفة الأماكن المختارة
                    'total_cost' => [
                        'number' => $totalCostSelected,
                        'ar'     => $totalCostSelected == 0
                                        ? 'مجاني'
                                        : number_format($totalCostSelected, 0) . ' جنيه',
                        'en'     => $totalCostSelected == 0
                                        ? 'Free'
                                        : number_format($totalCostSelected, 0) . ' EGP',
                    ],

                    // المبلغ المتبقي (دايماً موجب أو صفر)
                    'remaining_budget' => [
                        'number' => $remainingBudget,
                        'ar'     => number_format($remainingBudget, 0) . ' جنيه',
                        'en'     => number_format($remainingBudget, 0) . ' EGP',
                    ],

                    // ملخص للعرض
                    'summary' => [
                        'ar' => 'بميزانية ' . number_format($budget, 0) . ' جنيه يمكنك زيارة '
                                . $selectedPlaces->count() . ' أماكن'
                                . ($totalCostSelected > 0
                                    ? ' بتكلفة ' . number_format($totalCostSelected, 0) . ' جنيه'
                                    : ' مجاناً')
                                . ' ويتبقى ' . number_format($remainingBudget, 0) . ' جنيه',
                        'en' => 'With ' . number_format($budget, 0) . ' EGP you can visit '
                                . $selectedPlaces->count() . ' places'
                                . ($totalCostSelected > 0
                                    ? ' costing ' . number_format($totalCostSelected, 0) . ' EGP'
                                    : ' for free')
                                . ' with ' . number_format($remainingBudget, 0) . ' EGP remaining',
                    ],
                ],

                // ── إحصائيات ────────────────────────────────
                'stats' => [
                    'selected_count'     => $selectedPlaces->count(),
                    'not_selected_count' => $notSelectedPlaces->count(),
                    'total_places'       => $allPlaces->count(),
                ],

                // ── الأماكن المختارة ضمن الميزانية ──────────
                'selected_places' => BudgetResultResource::collection($selectedPlaces),

                // ── الأماكن خارج الميزانية ───────────────────
                'not_selected_places' => BudgetResultResource::collection($notSelectedPlaces),
            ],
            'budget_calculated',
            200
        );
    }

    // ──────────────────────────────────────────────────
    // GET /api/budget/suggestions
    // ──────────────────────────────────────────────────
    public function suggestions(Request $request): JsonResponse
    {
        $places = Place::active()->orderBy('price_number')->get();

        $suggestions = [
            [
                'label'        => ['ar' => 'مجاني تماماً',   'en' => 'Completely Free'],
                'budget'       => 0,
                'places_count' => $places->where('price_number', 0)->count(),
                'description'  => [
                    'ar' => 'أماكن يمكن زيارتها مجاناً',
                    'en' => 'Places you can visit for free',
                ],
            ],
            [
                'label'        => ['ar' => 'ميزانية منخفضة', 'en' => 'Low Budget'],
                'budget'       => 100,
                'places_count' => $places->where('price_number', '<=', 100)->count(),
                'description'  => [
                    'ar' => 'أماكن بأقل من 100 جنيه للشخص',
                    'en' => 'Places under 100 EGP per person',
                ],
            ],
            [
                'label'        => ['ar' => 'ميزانية متوسطة', 'en' => 'Medium Budget'],
                'budget'       => 200,
                'places_count' => $places->where('price_number', '<=', 200)->count(),
                'description'  => [
                    'ar' => 'أماكن بأقل من 200 جنيه للشخص',
                    'en' => 'Places under 200 EGP per person',
                ],
            ],
            [
                'label'        => ['ar' => 'ميزانية مرتفعة', 'en' => 'High Budget'],
                'budget'       => 500,
                'places_count' => $places->where('price_number', '<=', 500)->count(),
                'description'  => [
                    'ar' => 'أماكن بأقل من 500 جنيه للشخص',
                    'en' => 'Places under 500 EGP per person',
                ],
            ],
            [
                'label'        => ['ar' => 'كل الأماكن', 'en' => 'All Places'],
                'budget'       => $places->max('price_number') ?? 0,
                'places_count' => $places->count(),
                'description'  => [
                    'ar' => 'جميع الأماكن المتاحة',
                    'en' => 'All available places',
                ],
            ],
        ];

        return $this->successResponse(
            [
                'suggestions' => $suggestions,
                'price_range' => [
                    'min' => [
                        'number' => $places->min('price_number') ?? 0,
                        'ar'     => 'مجاني',
                        'en'     => 'Free',
                    ],
                    'max' => [
                        'number' => $places->max('price_number') ?? 0,
                        'ar'     => number_format($places->max('price_number') ?? 0, 0) . ' جنيه',
                        'en'     => number_format($places->max('price_number') ?? 0, 0) . ' EGP',
                    ],
                ],
                'free_places' => $places->where('is_free', true)->count(),
                'paid_places' => $places->where('is_free', false)->count(),
            ],
            'budget_suggestions',
            200
        );
    }
}