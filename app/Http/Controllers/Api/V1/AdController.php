<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Ad;
use App\Models\AdImage;
use App\Models\User;
use App\Models\Category;
use App\Traits\ImageTrait;
use App\Http\Traits\AdTrait;
use App\Jobs\AdEndPromotion;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\PromoteRequest;
use App\Http\Requests\storeRejectionMessageRequest;
use App\Models\AdAttribute;
use App\Models\Attribute;
use App\Models\Client;
use App\Models\Comment;
use App\Models\Like;
use Illuminate\Support\Facades\DB;
use App\Models\PromotionPlan;
use App\Models\RejectionMessage;
use App\Models\Transaction;
use Auth;
use Exception;
use Illuminate\Support\Facades\Storage as FacadesStorage;
use Storage;

class AdController extends Controller
{
    use ImageTrait;
    use ApiResponses;



    public function approvedAds(): JsonResponse
    {
        try {
            $ads = Ad::where('approved', true)->paginate(30);

            return $this->success($ads, 'Done');
        } catch (Exception $e) {
            return response()->json($e, 500);
        }
    }


    public function index()
    {
        try {
            $region = request('region');
            $ads = Ad::where('region', $region)
                ->sortedAds()->paginate(30);
            return $this->success($ads, 'Done');
        } catch (Exception $e) {
            return response()->json($e, 500);
        }
    }

    public function show(Request $request): JsonResponse
    {
        try {
            $ad = Ad::with('comments','adAttributes.attribute','adAttributes.attributeOption')->findOrFail($request->id);
            if ($ad) {
                $ad->increment('views');
                $userLiked = false;
                if ($request->has('user_id')) {
                    $userId = $request->user_id;
                    $userLiked = $ad->likes()->where('user_id', $userId)->exists();
                }

                $client = Client::where('user_id', $ad->user_id)->first();
            
            $ad->client = $client;

                $ad->setAttribute('userLiked', $userLiked);
                return $this->success($ad, 'Done');
            } else {
                return $this->failed('This Ads Not Found', 400);
            }
        } catch (Exception $e) {
            return response()->json($e, 500);
        }
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
        $request['user_id'] = auth()->user()->id;

        $promotionData = $this->processPromotionPlan($request);
        if ($promotionData['error']) {
            return response()->json(['error' => $promotionData['error']], 400);
        }

        $ad = Ad::create([

            'type' => $request->type,
            'user_type' => $request->user_type,
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'city' => $request->city,
            'region' => $request->region,
            'category_id' => $request->category_id,
            'user_id' => $request->user_id,
            'phone_number_visibility' => $request->phone_number_visibility,
            'company_id' => $request->company_id ?? null,
            'promotion_plan_id'=> $request->promotion_plan_id ?? null,
            'promotion_price'=> $promotionData['promotionPrice'],
            'promotion_expiry'=> $promotionData['promotionExpiry'],
            // 'sub_category_id' => $request->sub_category_id ?? 0,
            'additional_category_id' =>$request->additional_category_id

        ]);
        
        foreach($request['attributes'] as $attribute)
        {
            AdAttribute::create([
                'ad_id' => $ad['id'],
                'attribute_id' => $attribute['attribute_id'],
                'attribute_option_id' => $attribute['attribute_option_id'],
            ]);
        }

        if(!$request->has('phone_number_visibility'))
       {
           $request['phone_number_visibility'] = 0;
       }

        
        $files = [];
        if ($request->images) {
            foreach ($request->images as $key => $image) {
                $extension = $image->getClientOriginalExtension();
                $filename = $key . time() . '.' . $extension;
                $imagename =  $this->uploadImage($image, $filename, 'product');
                $image_link = asset('images/product/' . $imagename);

                AdImage::create([
                    'ad_id' => $ad->id,
                    'image' => $image_link
                ]);
                array_push($files, $image_link);
            }
        }
        $request['links'] = $files;

        return $this->success('done', 'added Successfully . . .');
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
        $user_type = auth()->user()->type;
        $validatedData = Validator::make($request->all(), [
            'type' => 'required|in:product,service,required_service',
            'title' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'city' => 'required|string',
            'region' => 'required|in:egypt,morocco',
            'category_id' => 'required',
            //'company_id' => 'nullable|numeric',
            'approved' => 'nullable|integer',
            'phone_number_visibility' => 'nullable|boolean',
            'additional_category_id' => 'nullable',
            'attributes' => 'required|array',
            'attributes.*.attribute_id' => 'required',
            'attributes.*.attribute_option_id' => 'required',
            //'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);
        if ($validatedData->fails()) {
            return $this->failed($validatedData->errors(), 422);
        }
        $validatedData = $validatedData->validated();
        if($user_type != "admin")
        {
            $validatedData['approved'] = 0;
            $ad = Ad::where('user_id',auth()->user()->id)->where('id',$request['id'])->first();
        }else{
            $ad = Ad::find($request['id']);
        }

        if($request->has('image_id'))
        {
            $this->deleteAdPhoto($request['image_id'],$ad);
        }


        $files = [];
        if ($request->images) {
            foreach ($request->images as $key => $image) {
                $extension = $image->getClientOriginalExtension();
                $filename = $key . time() . '.' . $extension;
                $imagename =  $this->uploadImage($image, $filename, 'product');
                $image_link = asset('images/product/' . $imagename);

                AdImage::create([
                    'ad_id' => $ad->id,
                    'image' => $image_link
                ]);
                array_push($files, $image_link);
            }
        }
        $request['links'] = $files;
        
        $ad->update($validatedData);
        return $this->success('done', 'Updated Successfully . . .');
    }

    private function deleteAdPhoto($image_id, $ad)
    {
        $photo = AdImage::where('ad_id', $ad->id)
            ->where('id', $image_id)
            ->first();
            
            $pathParts = explode('/', $photo->image);
            $imageName = $pathParts[count($pathParts) - 1]; 
            $filePath = public_path(implode('/', array_slice($pathParts, 3)));
            unlink($filePath);
           
            $photo->delete();
    }


    /**
     * Remove the specified resource from storage.
     */

    public function destroy()
    {
        try {
            $adId = request('id');

            Comment::where('ad_id', $adId)->delete();
            Ad::where('id', $adId)
                ->delete();
            return $this->success('done', 'Deleted Successfully . . .');
        } catch (Exception $e) {
            return response()->json($e, 500);
        }
    }



    public function promote(PromoteRequest $request)
    {
        try {
            DB::beginTransaction();
            $user = User::whereId(auth()->user()->id)->first();

            $adId = request('adId');
            $ad = Ad::findOrFail($adId);
            if ($ad->promotion_expiry >= now()) {
                return response()->json(['message' => 'Ad Already Promoted']);
            }
            $promotionPlan = PromotionPlan::findOrFail($request->promotion_plan_id);
            $duration = $request->duration;

            $promotionPrice = $promotionPlan->getPromotionPrice($duration);

            $ad->promote($promotionPlan, $duration);
            $ad->save();

            // Create a new transaction record
            $transaction = new Transaction();
            $transaction->type = 'ad_promotion';
            $transaction->ad_id = $ad->id;
            $transaction->amount = $promotionPrice;
            $transaction->save();




            if ($user->wallet == 0.0 || $user->wallet < 0.0 || $user->wallet < $transaction->amount) {
                return response()->json(['message' => 'Wallet is Empty']);
            } else {
                $trans = $user->update(
                    [
                        "wallet" =>  $user->wallet - $promotionPrice
                    ]

                );

                return response()->json(['data' => $trans, 'message' => 'the trans is Done']);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            echo $e->getMessage();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Ad Promoted Successfully . . .',
        ], 200);
    }
    public function endPromotion()
    {
        $adId = request('adId');
        $data['id'] = $adId;
        try {
            $ad = Ad::findOrFail($adId);

            $ad->endPromotion();
        } catch (\Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Ad Promotion Ended Succsessfully . . .',
        ], 200);
    }



    public function getProducts(): JsonResponse
    {
        $region = request('region');
        $category_id = request('category_id');
        $ads = Ad::where('type', 'product')
            ->where('region', $region)
            ->where(function ($query) use ($category_id) {
                $query->where('category_id', $category_id)
                    ->orWherehas('category', function ($query) use ($category_id) {
                        $query->whereNotNull('parent_id')
                            ->where('parent_id', $category_id);
                    });
            })->sortedAds()->paginate(30);

        return $this->success($ads, 'Done');
    }

    public function getTypes(): JsonResponse
    {
        $region = request('region');
        $category_id = request('category_id');
        $type = request('type');
        $ads = Ad::where('type', $type)
            ->where('approved', false)
            ->where('region', $region)
            ->where(function ($query) use ($category_id) {
                $query->where('category_id', $category_id)
                    ->orWherehas('category', function ($query) use ($category_id) {
                        $query->whereNotNull('parent_id')
                            ->where('parent_id', $category_id);
                    });
            })
            ->sortedAds()->paginate(30);
        return $this->success($ads, 'Done');
    }


    public function byCategory(): JsonResponse
    {
        $region = request('region');
        $categoryId = request('category_id');
        // Get the category and its child categories
        $category = Category::with('children')->findOrFail($categoryId);
        $categoryIds = $category->children->pluck('id')->push($categoryId);

        $ads = Ad::where('approved', true)
            ->whereIn('category_id', $categoryIds)
            ->where('region', $region)
            ->sortedAds()->paginate(30);
        return $this->success($ads, 'Done');
    }

    public function bySubCategory(): JsonResponse
    {

        $region = request('region');
        $categoryId = request('sub_category_id');
        // Get the category and its child categories

        $ads = Ad::where('approved', true)
            ->where('category_id', $categoryId)
            ->where('region', $region)
            ->sortedAds()->paginate(30);
        return $this->success($ads, 'Done');
    }

    public function byCompany(): JsonResponse
    {
        $region = request('region');
        $companyId = request('company_id');
        $ads = Ad::where('approved', true)
            ->where('company_id', $companyId)
            ->where('region', $region)
            ->sortedAds()->paginate(30);

        return $this->success($ads, 'Done');
    }

    public function getPending(): JsonResponse
    {
        $ads = Ad::where('approved', false)
            ->where('region', request('region'))
            ->sortedAds()->paginate(30);
        return $this->success($ads, 'Done');
    }

    private function processPromotionPlan(Request $request)
{
    $promotionData = [
        'promotionPrice' => null,
        'promotionExpiry' => null,
        'error' => null,
    ];

    if ($request->has('promotion_plan_id')) {
        $promotionPlan = PromotionPlan::find($request->promotion_plan_id);
        if (!$promotionPlan) {
            $promotionData['error'] = 'Promotion plan not found';
            return $promotionData;
        }

        $promotionData['promotionPrice'] = $promotionPlan->price;
        $promotionData['promotionExpiry'] = now()->addDays($promotionPlan->number_of_days);

        $userWallet = auth()->user()->wallet;
        if ($userWallet < $promotionData['promotionPrice']) {
            $promotionData['error'] = 'Insufficient funds in your wallet';
            return $promotionData;
        }
    }

    return $promotionData;
}

    public function searchAd(): JsonResponse
    {
        $search = request('search');
        $region = request('region');
        $ads = Ad::query();

        if ($search) {
            $ads->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%")
                    ->orWhereHas('category', function ($query) use ($search) {
                        $query->where('name_en', 'like', "%$search%")
                            ->orWhere('name_ar', 'like', "%$search%");
                    });
            });
        }

        $ads->where('approved', true)
            ->where('region', $region);

        $results = $ads->sortedAds()->paginate(30);

        return $this->success($results, 'Done');
    }

    public function filterByAttributes(Request $request): JsonResponse
    {
        $region = request('region');
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'attributes' => 'array',
            'attributes' => 'nullable|array',
            'attributes.*.attribute_id' => 'nullable',
            'attributes.*.attribute_option_id' => 'nullable|array',
            'city' => 'nullable|string',
            'subCategory' => 'nullable|exists:ads,sub_category_id',
            'additionalCategory' => 'nullable|exists:ads,additional_category_id',
            'price_from' => 'nullable|numeric',
            'price_to' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ], 400);
        }

        $categoryID = $request->input('category_id');
        $attributeOptions = $request->input('attributes');
        $city = $request->input('city');
        $subCategoryID = $request->input('subCategory');
        $additionalCategoryID = $request->input('additionalCategory');
        $priceFrom = $request->input('price_from');
        $priceTo = $request->input('price_to');

        $ads = Ad::with('adAttributes.attribute','adAttributes.attributeOption')->where('approved', true)
            ->where('category_id', $categoryID)
            ->where('region', $region);

        if ($city) {
            $ads->where('city', $city);
        }

        if ($subCategoryID) {
            $ads->where('sub_category_id', $subCategoryID);
        }
        
        if($additionalCategoryID)
        {
            $ads->where('additional_category_id', $additionalCategoryID);
        }

        // if (!empty($attributeOptions)) {
        //     foreach ($attributeOptions as $attribute) {
        //         $attributeId = $attribute['attribute_id'];
        //         $optionId = $attribute['attribute_option_id'];
        //         $ads->whereHas('attributes', function ($query) use ($attributeId, $optionId) {
        //             $query->where('attribute_id', $attributeId)
        //                 ->where('attribute_option_id', $optionId);
        //         });
        //     }
        // }

        if (!empty($attributes)) {
            $ads->whereHas('attributes', function ($query) use ($attributes) {
                foreach ($attributes as $attribute) {
                    $attributeId = $attribute['attribute_id'];
                    $optionIds = $attribute['attribute_option_id'];
        
                    $query->where('attribute_id', $attributeId)
                          ->whereIn('attribute_option_id', $optionIds);
                }
            });
        }

        if ($priceFrom && $priceTo) {
            $ads->whereBetween('price', [$priceFrom, $priceTo]);
        } elseif ($priceFrom) {
            $ads->where('price', '>=', $priceFrom);
        } elseif ($priceTo) {
            $ads->where('price', '<=', $priceTo);
        }

        $filteredAds = $ads->distinct()->sortedAds()->paginate(30);

        return response()->json([
            'data' => $filteredAds
        ]);
    }

    public function userAds()
    {
        $ads = Ad::where('user_id', request('id'))->paginate(30);

        return $this->success($ads, 'Done');
    }

    public function prometedUserAds()
    {
        $ads = Ad::where('user_id', request('id'))->where('promotion_plan_id', '!=', null)->paginate(30);
        return $this->success($ads, 'Done');
    }

    public function addRejectionMessage(storeRejectionMessageRequest $request){
        $ad = Ad::where('id',$request->ad_id)->where('approved', 2)->first();
        if(!$ad){
            return response()->json(['error' => 'Ad not found'], 404);
        }
        if ($ad->rejectionMessages()->exists()) {
            return response()->json(['error' => 'A rejection message already exists for this ad'], 400);
        }
        RejectionMessage::create([
            'ad_id' => $ad->id,
            'message' => $request->message
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Rejection Message Added Succsessfully . . .',
        ], 200);

    }

}
