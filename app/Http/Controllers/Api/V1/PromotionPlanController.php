<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\PromotionPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PromotionPlanController extends Controller
{
    public function index(): JsonResponse
    {
        $promotionPlans = PromotionPlan::all();

        return response()->json([
            'data' => $promotionPlans,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'one_day_price' => 'required|string',
            'three_day_price' => 'required|string',
            'seven_day_price' => 'required|string'
        ]);

        
        if ($validatedData->fails()) {
            return $this->failed($validatedData->errors(), 422);
        }
        $validatedData = $validatedData->validated();
        
        $promotionPlan = PromotionPlan::create($validatedData);

        return response()->json([
            'data' => $promotionPlan,
        ]);
    }
    
    public function update(Request $request, $id): JsonResponse
    {
        $validatedData = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'one_day_price' => 'nullable|string',
            'three_day_price' => 'nullable|string',
            'seven_day_price' => 'nullable|string'
        ]);

        
        if ($validatedData->fails()) {
            return $this->failed($validatedData->errors(), 422);
        }
        $validatedData = $validatedData->validated();

        $promotionPlan = PromotionPlan::findOrFail($id)->update($validatedData);

        return response()->json([
            'data' => $promotionPlan,
        ]);
    }
    public function destroy($id): JsonResponse
    {
        $promotionPlan = PromotionPlan::findOrFail($id)->delete();

        return response()->json([
            'success' => "Promotion Plan Has Been Deleted Successfully"
        ]);
    }

    public function show(): JsonResponse
    {
        $promotionPlan = PromotionPlan::findOrFail(request('id'));

        return response()->json([
            'data' => $promotionPlan,
        ]);
    }
}
