<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *  @property int $id
 *  @property int $user_id
 *  @property int $payment_id
 *  @property int $payment_status
 *  @property string $amount
 *
 */


class Wallet extends Model
{
    use HasFactory;


    protected $fillable=[
        'user_id',
        'payment_id',
        'payment_status',
        'amount'
    ];


    public function user(){
        return $this->belongsTo(User::class);
    }
}
