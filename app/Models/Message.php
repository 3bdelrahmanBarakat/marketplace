<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    protected $fillable=[
        'sender',
        'receiver',
        'message'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public static function getChatMessages($senderId, $receiverId)
    {
        return self::where(function ($query) use ($senderId, $receiverId) {
            $query->where('sender', $senderId)->where('receiver', $receiverId);
        })->orWhere(function ($query) use ($senderId, $receiverId) {
            $query->where('sender', $receiverId)->where('receiver', $senderId);
        })->orderBy('created_at', 'asc')->get();
    }
}
