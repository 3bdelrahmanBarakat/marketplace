<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddtionalCategoryCreateRequest;
use App\Models\Category;
use Exception;
use Illuminate\Http\Request;

class AddtionalCategoryController extends Controller
{
    public function all()
    {
        $categories = Category::whereHas('parent.parent')->get();

                return response()->json(['data' => $categories], 200);
    }
    public function show(Request $request)
    {
        $addtionalCategory = Category::with('parent')->findOrFail($request['id']);

        return response()->json(['data' => $addtionalCategory], 200);
    }
    public function store(AddtionalCategoryCreateRequest $request)
    {
        try {
            $subcategory = Category::where('id', $request->parent_id)->whereNotNull('parent_id')->get();
            if(!$subcategory)
            {
                return response()->json(['error' => 'Subcategory not found'], 404);
            }

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
        
            $addtionalCategory = Category::create($data);
            return response()->json(['data' => $addtionalCategory], 201);
        } catch (Exception $e) {
            return response()->json($e, 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $data = $request->only(['name_ar', 'name_en']);
        
            $addtionalCategory = Category::where('id', $id)
                                ->whereNotNull('parent_id')
                                ->update($data);

            return response()->json(['success' => "Additional Category Has Been Updated Successfully"], 201);
        } catch (Exception $e) {
            return response()->json($e, 500);
        }
    }

    public function destroy(Request $request)
    {
        try {

            Category::whereNotNull('parent_id')->where('id', $request->id)->delete();
            return response()->json(['message' => 'Deleted']);
        } catch (Exception $e) {
            return response()->json($e, 500);
        }
    }

    
}
