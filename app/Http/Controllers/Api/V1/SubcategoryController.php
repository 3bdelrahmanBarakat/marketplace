<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Exception;
use Illuminate\Http\JsonResponse;
use App\Traits\ImageTrait;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;



class SubcategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use ImageTrait;

    public function index(): JsonResponse
    {
        try {
            if (request('category_id')) {
                $categories = Category::where('parent_id', request('category_id'))
                ->whereHas('parent', function ($query) {
                    $query->doesntHave('parent');
                })->get();
                // $categories = Category::where('parent_id', request('category_id'))->get();
            } else {
                $categories = Category::whereHas('parent', function ($query) {
                    $query->doesntHave('parent');
                })->get();
                // $categories = Category::whereNotNull('parent_id')->get();
            }
            return Response()->json(['data' => $categories]);
        } catch (Exception $e) {
            return response()->json($e, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(): JsonResponse
    {
        

        try {
            $category = Category::whereNotNull('parent_id')->where('id', request('id'))->first();
            if ($category) {

                return Response()->json(['data' => $category]);
            } else {
                return Response()->json(['message' => 'SubCategory Not Found']);
            }
        } catch (Exception $e) {
            return response()->json($e, 500);
        }
    }


    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
            'parent_id' => 'nullable|integer|exists:categories,id',
            'image_web' => 'nullable|max:2048',
            'image_app' => 'nullable|max:2048',

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        try {
            $data = $request->only(['name_ar', 'name_en', 'parent_id']);
            
            if ($request->image_app) {
    
                $filename =  time() . '.' . "png";
                $imagename =  $this->uploadImage($request->image_app, $filename, 'subcategories_app');
                $image_link = asset('images/subcategories_app/' . $imagename);
                $data['image_app'] =   $imagename;
            }
            
            
            
            if ($request->image_web) {

                $filename =  time() . '.' . "png";
                $imagename =  $this->uploadImage($request->image_web, $filename, 'subcategories_web');
                $image_link = asset('images/subcategories_web/' . $imagename);
                $data['image_web'] =   $imagename;
            }
        
            $subCategory = Category::create($data);
            return response()->json(['data' => $subCategory], 201);
        } catch (Exception $e) {
            return response()->json($e, 500);
        }
    }

    public function update(Request $request): JsonResponse
    {
        try {
            if ($request->hasFile('image_web')) {
                $image_web = $request->file('image_web');
                $filename = time() . '.' . 'png';
                $folderPath = 'images/subcategories_web/';
                $image_web->move(public_path($folderPath), $filename);
                $file_web = $folderPath . $filename;
            }

            if ($request->hasFile('image_app')) {
                $image_app = $request->file('image_app');
                $filename = time() . '.' . 'png';
                $folderPath = 'images/subcategories_app/';
                $image_app->move(public_path($folderPath), $filename);
                $file_app = $folderPath . $filename;
            }
            $category = Category::findOrFail($request->id);

            $category->update([
                "name_ar" => $request->name_ar ?? $category->name_ar,
                "name_en" => $request->name_en ?? $category->name_en,
                "name_fr" => $request->name_fr ?? $category->name_fr,
                "parent_id" => $request->parent_id ?? $category->parent_id,
                "image_app" => $file_app ?? $category->image_app,
                "image_web" => $file_web ?? $category->image_web,
            ]);


            return response()->json(['message' => 'Updated', 'data' => $category]);
        } catch (Exception $e) {
            return response()->json($e, 500);
        }
    }



    public function destroy(Request $request)
    {
        try {

            Category::whereNotNull('parent_id')->where('id', request('id'))->delete();
            return response()->json(['message' => 'Deleted']);
        } catch (Exception $e) {
            return response()->json($e, 500);
        }
    }
}
