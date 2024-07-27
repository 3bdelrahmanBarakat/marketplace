<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdAttribute extends Model
{
    use HasFactory;
    protected $table = 'ad_attribute';

    protected $fillable = ['ad_id', 'attribute_id', 'attribute_option_id'];

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }

    public function attribute()
    {
        return $this->belongsTo(Attribute::class, 'attribute_id');
    }

    public function attributeOption()
    {
        return $this->belongsTo(AttributeOption::class);
    }
}
