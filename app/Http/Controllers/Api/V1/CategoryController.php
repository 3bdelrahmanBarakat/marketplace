<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Traits\ImageTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Response;

class CategoryController extends Controller
{
    use ImageTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $categories = Category::with('attributes.options')->whereNull('parent_id')
        ->get();

        return response()->json(['data' => $categories]);
    }

    public function selectCategory(): JsonResponse
    {
        $categories = Category::whereNull('parent_id')
        ->orderByRaw('priority IS NULL ASC, priority ASC')
        ->get();

        return response()->json(['data' => $categories]);
    }



    /**
     * Display the specified resource.
     */
    public function show(): JsonResponse
    {
        $category = Category::whereNull('parent_id')->findOrFail(request('id'));
        return Response()->json(['data' => $category]);
    }

    public function topCategories(): JsonResponse
    {
        $topCategories = DB::table('categories')
        ->whereIn('id', [3, 4, 31, 30, 24, 43])
        ->orderBy('priority')
        ->get();
        return Response()->json(['data' => $topCategories]);
    }

    public function UpdateCategoryPhoto(Request $request)
    {
        $imageLink = $this->uploadSliderImage($request->file('image'));


        $category = Category::find($request->id);
        $category->image_web = $imageLink;
        $category->image_app = $imageLink;
        $category->save();

        return Response()->json(['success' => true]);

    }


}
