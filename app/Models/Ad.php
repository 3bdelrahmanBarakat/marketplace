<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Ad
 *
 * @property int $id
 * @property string $type
 * @property string $title
 * @property string $description
 * @property string $price
 * @property string $city
 * @property string $region
 * @property int $category_id
 * @property int $views
 * @property int $user_id
 * @property int|null $company_id
 * @property int $approved
 * @property string|null $promotion_expiry
 * @property string|null $promotion_price
 * @property int|null $promotion_plan_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attribute> $attributes
 * @property-read int|null $attributes_count
 * @property-read \App\Models\Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Comment> $comments
 * @property-read int|null $comments_count
 * @property-read \App\Models\Comment $commentsCount
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AdImage> $images
 * @property-read int|null $images_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Like> $likes
 * @property-read int|null $likes_count
 * @property-read \App\Models\Like $likesCount
 * @property-read \App\Models\PromotionPlan|null $promotionPlan
 * @method static \Illuminate\Database\Eloquent\Builder|Ad newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ad newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ad query()
 * @method static \Illuminate\Database\Eloquent\Builder|Ad sortedAds()
 * @method static \Illuminate\Database\Eloquent\Builder|Ad whereApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ad whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ad whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ad whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ad whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ad whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ad whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ad wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ad wherePromotionExpiry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ad wherePromotionPlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ad wherePromotionPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ad whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ad whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ad whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ad whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ad whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ad whereViews($value)
 * @mixin \Eloquent
 */
class Ad extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'price', 'city', 'region', 'category_id','sub_category_id', 'additional_category_id', 'user_id', 'company_id', 'type', 'views', 'image', 'approved', 'phone_number_visibility', 'promotion_expiry', 'promotion_price', 'promotion_plan_id'];

    protected $with = ['images', 'category', 'adAttributes', 'likesCount', 'commentsCount','rejectionMessages'];

    protected $orderBy = 'created_at';
    protected $sortDirection = 'desc';

    public function scopeSortedAds($query)
    {
        return $query->orderByRaw("CASE
                WHEN promotion_plan_id = 1 THEN 0
                WHEN promotion_plan_id = 2 THEN 1
                ELSE 2
            END, $this->orderBy $this->sortDirection");
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function images()
    {
        return $this->hasMany(AdImage::class);
    }


    public function category()
    {
        return $this->belongsTo(Category::class);
    }


    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'ad_attribute')
            ->withPivot(['attribute_option_id'])
            ->withTimestamps()
            ->with('options');
    }

    public function adAttributes()
    {
        return $this->hasMany(AdAttribute::class);
            
    }


    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function likesCount()
    {
        return $this->hasOne(Like::class)
            ->selectRaw('ad_id, count(*) as likes_count')
            ->groupBy('ad_id')
            ->withDefault(['likes_count' => 0]);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function commentsCount()
    {
        return $this->hasOne(Comment::class)
            ->selectRaw('ad_id, count(*) as comments_count')
            ->groupBy('ad_id')
            ->withDefault(['comments_count' => 0]);
    }

    public function promotionPlan()
    {
        return $this->belongsTo(PromotionPlan::class);
    }

    public function isPromoted()
    {
        return $this->promotion_plan_id !== null && $this->promotion_expiry !== null && $this->promotion_expiry >= Carbon::now();
    }

    public function promote($promotionPlan, $duration)
    {
        $this->promotion_plan_id = $promotionPlan->id;
        $this->promotion_price = $this->getPromotionPrice($promotionPlan, $duration);
        $this->promotion_expiry = Carbon::now()->addDays($duration);
        $this->save();
    }

    public function getPromotionPrice($promotionPlan, $duration)
    {
        switch ($duration) {
            case 1:
                return $promotionPlan->one_day_price;
            case 3:
                return $promotionPlan->three_day_price;
            case 7:
                return $promotionPlan->seven_day_price;
            default:
                return null;
        }
    }

    public function endPromotion()
    {
        $this->promotion_plan_id = null;
        $this->promotion_price = null;
        $this->promotion_expiry = null;
        $this->save();
    }
    public function getImageAttribute()
    {


        return asset('images/product/'. $this->attributes['image']);
    }

    public function rejectionMessages()
    {
        return $this->hasMany(RejectionMessage::class);
    }
}
