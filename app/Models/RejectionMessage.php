<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RejectionMessage extends Model
{
    use HasFactory;
    protected $fillable = ['ad_id', 'message'];

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }
}
