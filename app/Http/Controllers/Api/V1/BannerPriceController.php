<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Jobs\UpdateBannerPrice;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Validator;
use App\Models\Banner;
use App\Models\BannerPrice;
class BannerPriceController extends Controller
{
    use ApiResponses;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bannerPrices = BannerPrice::all();

        return response()->json([
            'data' => $bannerPrices,
        ]);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
            $validatedData = Validator::make($request->all(), [
            'image' => 'required',
            'action_type' => 'required|in:url,product,profile,whatsapp',
            'action' => 'required|string',
            'duration' => 'required|in:1,3,7',
        ]);
        if ($validatedData->fails()) {
            return $this->failed($validatedData->errors(), 422);
        }

        if ($request->image) {

            $filename =  time() . '.' . "png";
            $imagename =  $this->uploadImage($request->image, $filename, 'banner');
            $image_link = asset('images/banner/' . $imagename);
            $request['file'] =  $image_link;
        }

        $request['user_id'] = auth()->user()->id;


        Banner::create([
             'image' =>  $image_link  ,
             'action_type' => $request->action_type,
             'action' =>$request->action,
             'duration' => $request->duration
            
            ]);
        return $this->success('done', 'added Successfully . . .');
        
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $bannerPrice = BannerPrice::findOrFail(request('id'));

        return response()->json([
            'data' => $bannerPrice,
        ]);
    }

  


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {

        $validatedData = Validator::make($request->all(), [
            //'image' => 'image',
            'action_type' => 'in:url,product,profile,whatsapp',
            'action' => 'string',
            'duration' => 'in:1,3,7',
        ]);
        if ($validatedData->fails()) {
            return $this->failed($validatedData->errors(), 422);
        }
        if ($request->image) {

            $filename =  time() . '.' . "png";
            $imagename =  $this->uploadImage($request->image, $filename, 'banner');
            $image_link = asset('images/banner/' . $imagename);
            $request['file'] =  $image_link;
        }

          $request['user_id'] = auth()->user()->id;

          $request['id'] = request('id');
           Banner::where('id',$request['id'])->update([
             'image' =>  $image_link  ,
             'action_type' => $request->action_type,
             'action' =>$request->action,
             'duration' => $request->duration
            
            ]);
        return $this->success('done', 'updated Successfully . . .');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy()
    {
        $data = [
            "user_id" => auth()->user()->id,
            "id" => request('id')
        ];

        Banner::delete($data);
        return $this->success('done', 'Deleted Successfully . . .');
    }
}
