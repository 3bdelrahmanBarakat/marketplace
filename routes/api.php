<?php

use App\Http\Controllers\AddRoleToUserContoller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AdController;
use App\Http\Controllers\Api\V1\AddtionalCategoryController;
use App\Http\Controllers\Api\V1\AdminController;
use App\Http\Controllers\Api\V1\AttributeController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\LikeController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\BannerController;
use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\PayMobController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\SplashAdController;
use App\Http\Controllers\Api\V1\FavouriteController;
use App\Http\Controllers\Api\V1\BannerPriceController;
use App\Http\Controllers\Api\V1\ChatController;
use App\Http\Controllers\Api\V1\ForgotPasswordController;
use App\Http\Controllers\Api\V1\ResetPasswordController;
use App\Http\Controllers\Api\V1\CompanyController;
use App\Http\Controllers\Api\V1\ContactUsController;
use App\Http\Controllers\Api\V1\MessageController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\v1\PermissionController;
use App\Http\Controllers\Api\V1\PlanController;
use App\Http\Controllers\Api\V1\SubcategoryController;
use App\Http\Controllers\Api\V1\PromotionPlanController;
use App\Http\Controllers\Api\v1\RolesAndAdminPermission;
use App\Http\Controllers\Api\V1\SplashAdPriceController;
use App\Http\Controllers\RoleController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::middleware('auth:sanctum')->prefix('auth/')->group(function () {
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('permissions', PermissionController::class);
    Route::post('/add-role-to-user', [AddRoleToUserContoller::class, 'addRoleToUser']);
    Route::delete('/remove-role-from-user', [AddRoleToUserContoller::class, 'removeRoleFromUser']);
});

Route::group(['prefix' => 'auth/'], function () {
    Route::post('forgot-password', [ForgotPasswordController::class, 'forgotPassword']);
    Route::patch('reset-password', [ResetPasswordController::class, 'PasswordReset']);
    Route::get('check-otp', [ResetPasswordController::class, 'checkOtp']);
    Route::post('register', [AuthController::class, 'registration']);
    Route::post('login', [AuthController::class, 'login']);
    Route::get('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('/fcm-token', [AuthController::class, 'fcmToken'])->middleware('auth:sanctum');
});

//checkout_processed
Route::get('callback', [PayMobController::class, 'callback']);

Route::get('user', [UserController::class, 'show'])
    ->middleware('auth:sanctum');

Route::group(['prefix' => 'users'], function () {
    Route::get('', [UserController::class, 'index'])
        ->middleware(['auth:sanctum', 'is.admin']);
    Route::get('{user}', [UserController::class, 'show'])
        ->middleware('auth:sanctum');

    Route::post('user/gift', [UserController::class, 'addGift'])
        ->middleware(['auth:sanctum', 'is.admin']);


    Route::get('get/wallet', [UserController::class, 'getWallet'])
        ->middleware('auth:sanctum');

    Route::post('share/points', [UserController::class, 'share_points_wallet'])
        ->middleware('auth:sanctum');

    //wallet_charging

    Route::post('pay', [PayMobController::class, 'pay'])
        ->middleware('auth:sanctum');


    //////////////////////
    Route::patch('', [UserController::class, 'update'])
        ->middleware('auth:sanctum');
    Route::delete('', [UserController::class, 'destroy'])
        ->middleware(['auth:sanctum', 'is.admin']);
});

Route::middleware(['auth:sanctum', 'is.admin'])->group(function () {
    Route::patch('admins', [AdminController::class, 'update']);
    Route::delete('admins', [AdminController::class, 'destroy']);
    Route::get('admin', [AdminController::class, 'show']);
});

Route::resource('admins', AdminController::class)
    ->middleware(['auth:sanctum', 'is.admin'])->only(['index', 'store']);
Route::post('admins/profile', [AdminController::class, 'uploadProfile'])
    ->middleware(['auth:sanctum', 'is.admin']);


Route::get('client', [ClientController::class, 'show'])
    ->middleware('auth:sanctum');
Route::group(['prefix' => 'clients'], function () {
    Route::get('', [ClientController::class, 'index'])
        ->middleware(['auth:sanctum', 'is.admin']);

    Route::patch('', [ClientController::class, 'update'])
        ->middleware('auth:sanctum');
    Route::post('/profile', [ClientController::class, 'uploadProfile'])
        ->middleware('auth:sanctum');
    Route::patch('/profile-photo', [ClientController::class, 'deleteProfilePhoto'])
        ->middleware('auth:sanctum');
    Route::delete('', [ClientController::class, 'destroy'])
        ->middleware(['auth:sanctum', 'is.admin']);
});

Route::get('company', [CompanyController::class, 'show'])
    ->middleware('auth:sanctum');
Route::group(['prefix' => 'companies'], function () {
    Route::get('', [CompanyController::class, 'index']);

    Route::post('', [CompanyController::class, 'store'])
        ->middleware('auth:sanctum');
    Route::patch('', [CompanyController::class, 'update'])
        ->middleware('auth:sanctum');
    Route::post('{company}/profile', [CompanyController::class, 'uploadProfile'])
        ->middleware('auth:sanctum');
    Route::delete('', [CompanyController::class, 'destroy'])
        ->middleware(['auth:sanctum', 'is.admin']);
});

Route::group(['prefix' => 'notifications'], function () {
    Route::resource('', NotificationController::class)
        ->middleware(['auth:sanctum', 'is.admin'])->only(['store']);
});


Route::post('messages', [MessageController::class, 'store'])
    ->middleware(['auth:sanctum']);


Route::post('ad', [AdController::class, 'store'])->middleware(['auth:sanctum']);

Route::patch('/ad', [AdController::class, 'update'])->middleware(['auth:sanctum']);
Route::patch('/ad', [AdController::class, 'update'])->middleware(['auth:sanctum']);
Route::delete('/ad', [AdController::class, 'destroy'])->middleware(['auth:sanctum']);

Route::post('/ad/rejection-message', [AdController::class, 'addRejectionMessage'])->middleware(['auth:sanctum', 'is.admin']);


Route::post('/ad/promote', [AdController::class, 'promote'])->middleware(['auth:sanctum']);
Route::get('/ad/end-promotion', [AdController::class, 'endPromotion'])->middleware(['auth:sanctum']);


Route::patch('promotion_plan', PlanController::class)->middleware(['auth:sanctum', 'is.admin']);
Route::patch('splash-ad', [SplashAdController::class, 'update'])->middleware(['auth:sanctum', 'is.admin']);
Route::delete('splash-ad', [SplashAdController::class, 'destroy'])->middleware(['auth:sanctum', 'is.admin']);
Route::apiResource('splash-ad', SplashAdController::class)->middleware(['auth:sanctum', 'is.admin'])->only(['store']);
Route::post('/comment', [CommentController::class, 'store'])->middleware(['auth:sanctum']);
Route::delete('/comment', [CommentController::class, 'destroy'])->middleware(['auth:sanctum']);
Route::post('/like', [LikeController::class, 'likeAd'])->middleware(['auth:sanctum']);
Route::delete('/unlike', [LikeController::class, 'unlikeAd'])->middleware(['auth:sanctum']);
Route::patch('banner', [BannerController::class, 'update'])->middleware(['auth:sanctum']);
Route::delete('banner', [BannerController::class, 'destroy'])->middleware(['auth:sanctum']);
Route::apiResource('banner', BannerController::class)->middleware(['auth:sanctum'])->only(['store']);
Route::patch('banner-pricing', [BannerPriceController::class, 'update'])->middleware(['auth:sanctum']);

Route::patch('splash-ad-pricing', SplashAdPriceController::class)->middleware(['auth:sanctum', 'is.admin']);


Route::post('ad/favourite', [FavouriteController::class, 'store'])->middleware(['auth:sanctum']);

Route::delete('ad/unfavourite', [FavouriteController::class, 'destroy'])->middleware(['auth:sanctum']);

//chat
Route::get('chat', [ChatController::class, 'getChat'])->middleware(['auth:sanctum']);
Route::post('chat', [ChatController::class, 'sendMessage'])->middleware(['auth:sanctum']);
Route::get('chats', [ChatController::class, 'adminChats'])->middleware(['auth:sanctum', 'is.admin']);


// Route::prefix('V1')->group(function () {
    Route::post('update-photoc', [CategoryController::class, 'UpdateCategoryPhoto']);
//categories
Route::get('categories', [CategoryController::class, 'index']);
Route::get('filter/categories', [CategoryController::class, 'selectCategory']);
Route::get('category', [CategoryController::class, 'show']);
Route::get('top-categories', [CategoryController::class, 'topCategories']);
Route::get('subcategories', [SubcategoryController::class, 'index']);   
Route::get('subcategory', [SubcategoryController::class, 'show']);
Route::post('subcategory/create', [SubcategoryController::class, 'store'])->middleware(['auth:sanctum', 'is.admin']);
Route::post('subcategory/update', [SubcategoryController::class, 'update'])->middleware(['auth:sanctum', 'is.admin']);
Route::post('subcategory/delete', [SubcategoryController::class, 'destroy'])->middleware(['auth:sanctum', 'is.admin']);

Route::get('addtionalcategory', [AddtionalCategoryController::class, 'show']);
Route::get('addtionalcategories', [AddtionalCategoryController::class, 'all']);
Route::post('addtionalcategory', [AddtionalCategoryController::class, 'store'])->middleware(['auth:sanctum', 'is.admin']);
Route::patch('addtionalcategory/{id}', [AddtionalCategoryController::class, 'update'])->middleware(['auth:sanctum', 'is.admin']);
Route::delete('addtionalcategory', [AddtionalCategoryController::class, 'destroy'])->middleware(['auth:sanctum', 'is.admin']);

//ads
Route::get('/approved-ads', [AdController::class, 'approvedAds']);
Route::get('/ad', [AdController::class, 'index']);
Route::get('/ad/show', [AdController::class, 'show']);
Route::get('/ad/types', [AdController::class, 'getTypes']);
Route::get('/ad/pending', [AdController::class, 'getPending']);
Route::get('/ad/category', [AdController::class, 'byCategory']);
Route::get('/ad/subcategory', [AdController::class, 'bySubCategory']);
Route::get('/ad/company', [AdController::class, 'byCompany']);
Route::post('/ad/filter', [AdController::class, 'filterByAttributes']);
Route::get('/ad/search', [AdController::class, 'searchAd']);
Route::get('/user/ads', [AdController::class, 'userAds']);

Route::get('/all-attributes', [AttributeController::class, 'allAttributes']);
Route::get('/all-options', [AttributeController::class, 'allOptions']);
Route::get('/category/attributes', [AttributeController::class, 'attributesByCategory']);
Route::get('/attribute/options', [AttributeController::class, 'optionsByAttribute']);


Route::get('/user/comments', [CommentController::class, 'userComments'])->middleware(['auth:sanctum']);
Route::get('/user/likes', [LikeController::class, 'userLikes'])->middleware(['auth:sanctum']);
Route::get('/user/favourites', [FavouriteController::class, 'userFavourites'])->middleware(['auth:sanctum']);
Route::get('/user/prometedads', [AdController::class, 'prometedUserAds']);
//banners
Route::get('banners', [BannerController::class, 'index']);
Route::get('banner', [BannerController::class, 'show']);



//promotion plans
Route::get('promotion_plans', [PromotionPlanController::class, 'index']);
Route::get('promotion_plan', [PromotionPlanController::class, 'show']);
Route::post('promotion_plan', [PromotionPlanController::class, 'store'])->middleware(['auth:sanctum', 'is.admin']);
Route::patch('promotion_plan/{id}', [PromotionPlanController::class, 'update'])->middleware(['auth:sanctum', 'is.admin']);
Route::delete('promotion_plan/{id}', [PromotionPlanController::class, 'destroy'])->middleware(['auth:sanctum', 'is.admin']);

// banner pricing
Route::get('banner-pricings', [BannerPriceController::class, 'index']);
Route::get('banner-pricing', [BannerPriceController::class, 'show']);

// splash ad pricing
Route::get('splash-ad-pricings', [SplashAdPriceController::class, 'index']);
Route::get('splash-ad-pricing', [SplashAdPriceController::class, 'show']);

//splash ads
Route::get('/splash-ad/random', [SplashAdController::class, 'randomIndex']);
Route::get('splash-ads', [SplashAdController::class, 'index']);
Route::get('splash-ad', [SplashAdController::class, 'show']);
// });

Route::post('/contact', [ContactUsController::class, 'sendInquiry']);
