<?php

namespace App\Http\Controllers\Api\V1;


use App\Jobs\UnLike;
use App\Models\Like;
use App\Traits\ApiResponses;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    use ApiResponses;

    public function likeAd()
    {
        $id=request('id');
        $data = [
            "user_id" => auth()->user()->id,
            "ad_id" => $id
        ];

        $like = Like::where('ad_id', $id)
            ->where('user_id', auth()->user()->id)
            ->first();

            if ($like) {
                return response()->json(['error' => 'You have already liked this ad.'], 409);
            }

       Like::create($data);
        return $this->success('done', 'Liked Successfully . . .');
    }

    public function unlikeAd(Request $request)
    {

        try {
            $like = Like::where('ad_id', $request->id)
            ->where('user_id', auth()->user()->id)
            ->first();

        if (!$like) {
            return response()->json(['error' => 'You have not liked this ad.'], 409);
        }

        $like->delete();
        return $this->success('done', 'Ad is unLiked Successfully . . .');

        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        
    }


    public function userLikes(){
        $likes= Like::where('user_id', auth()->user()->id)->with('ad')->paginate(30);
        return $this->success($likes, 'Done');
    }
}
