<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeOption;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    public function allAttributes()
    {
        $attributes = Attribute::all();
        return Response()->json(['attributes' => $attributes], 200);
    }

    public function allOptions()
    {
        $options = AttributeOption::all();
        return Response()->json(['options' => $options], 200);
    }

    public function attributesByCategory(Request $request)
    {
        $attributes = Attribute::where('category_id', $request->category_id)->get();
        if ($attributes->isEmpty()) {
            return Response()->json(['message' => 'Attributes not found for the specified category'], 404);
        }
        return Response()->json(['attributes' => $attributes], 200);
    }

    public function optionsByAttribute(Request $request)
    {
        $options = AttributeOption::where('attribute_id', $request->attribute_id)->get();
        if ($options->isEmpty()) {
            return Response()->json(['message' => 'Options not found for the specified attribute '], 404);
        }
        return Response()->json(['options' => $options], 200);
    }

}
