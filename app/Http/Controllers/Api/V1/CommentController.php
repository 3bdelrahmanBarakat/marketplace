<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Comment;
use App\Jobs\AddComment;
use App\Jobs\DeleteComment;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{

    use ApiResponses;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $id=request('id');
        $validatedData = Validator::make($request->all(), [
            'content' => 'required|string|max:255',
        ]);


        if ($validatedData->fails()) {
            return $this->failed($validatedData->errors(), 422);
        }
        $request['user_id'] = auth()->user()->id;
        $request['user_name'] = auth()->user()->name;
        $request['ad_id'] = $id;
       Comment::create($request->all());
        return $this->success('done', 'added Successfully . . .');
    }






    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {

        $request['user_id'] = auth()->user()->id;
        $request['ad_id'] = request('id');
        $comment = Comment::find($request['id']);
        $comment->delete();
        return $this->success('done', 'deleted Successfully . . .');
    }

    public function userComments(){
        $comments= Comment::where('user_id',auth()->user()->id)->with('ad')->paginate(30);
        return $this->success($comments, 'Done');
    }
}
