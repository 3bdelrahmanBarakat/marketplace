<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\ChatSent;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetChatRequest;
use App\Http\Requests\SendMessageRequest;
use App\Models\Message;
use App\Models\User;
use App\Traits\ApiResponses;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    use ApiResponses;

    public function getChat(GetChatRequest $request): JsonResponse
    {
        $sender = Auth::user()->id;
        $messages = Message::getChatMessages($sender, $request->user_id);
        $users = User::whereIn('id', $messages->pluck('sender')->merge($messages->pluck('receiver'))->unique()->toArray())->get();
        return $this->success(['users' => $users, 'messages' => $messages], 'Chat messages retrieved successfully');
    }


    public function sendMessage(SendMessageRequest $request) 
    {
        try {
            \DB::beginTransaction();
    
            $sender = Auth::user()->id;
            Message::create([
                'sender' => $sender,
                'receiver' => $request->receiver,
                'message' => $request->message
            ]);
    
            $receiver = User::where('id', $request->receiver)->first(); 
            $sender = User::where('id', $sender)->first(); 
            if (!$receiver) {
                throw new \Exception('Receiver not found');
            }
    
            \broadcast(new ChatSent($receiver, $sender, $request->message));
    
            \DB::commit();
    
            return $this->success('done', 'Message is sent successfully');
        } catch (\Exception $e) {
            \DB::rollBack();
            return $this->error('error', 'Failed to send message: ' . $e->getMessage());
        }


    }

    public function adminChats(): JsonResponse
    {
        $adminId = Auth::id(); 

        
        // $clientIds = Message::where(function ($query) use ($adminId) {
        //     $query->where('sender', $adminId)
        //           ->orWhere('receiver', $adminId);
        // })
        // ->orderBy('created_at', 'desc') // Order by newest message first
        // ->pluck('sender')
        // ->merge(Message::where(function ($query) use ($adminId) {
        //     $query->where('sender', $adminId)
        //           ->orWhere('receiver', $adminId);
        // })
        // ->orderBy('created_at', 'desc') // Order by newest message first
        // ->pluck('receiver'))
        // ->unique()
        // ->values();

        $clientIds = Message::select(DB::raw('IF(sender = ?, receiver, sender) AS client_id'), DB::raw('MAX(created_at) AS latest_message'))
                   ->where(function ($query) use ($adminId) {
                       $query->where('sender', $adminId)
                             ->orWhere('receiver', $adminId);
                   })
                   ->groupBy(DB::raw('IF(sender = ?, receiver, sender)'))
                   ->orderBy('latest_message', 'desc')
                   ->get();


                   

        
        $clients = User::whereIn('id', $clientIds)->get(['id', 'name','type']);

        return $this->success($clients, 'List of clients the admin has chatted with');
    }
}
