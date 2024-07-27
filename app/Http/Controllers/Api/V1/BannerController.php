<?php

namespace App\Http\Controllers\Api\V1;

use Carbon\Carbon;
use App\Models\Banner;
use App\Jobs\AddBanner;
use App\Jobs\DeleteBanner;
use App\Jobs\UpdateBanner;
use App\Traits\ImageTrait;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class BannerController extends Controller
{
    use ImageTrait;
    use ApiResponses;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $banners = Banner::all();
        return response()->json(['data' => $banners]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
            'expiry_date' => 'required|in:1,3,7',

        ]);

        if ($validatedData->fails()) {
            return $this->failed($validatedData->errors(), 422);
        }

        $validatedData = $validatedData->validated();

        $image_link = null;


        if ($request->image) {
            $filename =  time() . '.' . "png";
            $imagename =  $this->uploadImage($request->image, $filename, 'banner');
            $image_link = asset('images/banner/' . $imagename);
        }


        $bannerData = [
            'image' =>  $image_link,
            'action_type' => $request->action_type,
            'action' => $request->action,
            'expiry_date' => $request->expiry_date,
            'user_id' => auth()->user()->id
        ];

        Banner::create($bannerData);

        return $this->success('done', 'Added Successfully . . .');
    }


    /**
     * Display the specified resource.
     */
    public function show()
    {
        $banner = Banner::findOrFail(request('id'));
        return response()->json([
            'data' => $banner
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
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
        Banner::where('id',  $request['id'])->update([
            'image' =>  $image_link,
            'action_type' => $request->action_type,
            'action' => $request->action,
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

        Banner::where('id', $data['id'])->delete();
        return $this->success('done', 'Deleted Successfully . . .');
    }
}
