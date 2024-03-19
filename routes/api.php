<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;

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



Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});

// Publicly accessible routes
Route::get('/', [PostController::class, 'index']);
Route::get('/posts', [PostController::class, 'showAllPosts']);
Route::get('/posts/popular', [PostController::class, 'showPopularPosts']);
Route::get('/posts/newest', [PostController::class, 'showNewestPosts']);
Route::get('/posts/topic/{topicName}', [PostController::class, 'showPostsByTopic']);

Route::get('/post/{post}', [PostController::class, 'viewSinglePostApi']); // Viewing a single post
Route::get('/search/{term}', [PostController::class, 'searchApi']); // Search posts


Route::get('/profile/{user:username}', [UserController::class, 'profileApi']); // Viewing a user profile
Route::get('/profile/{user:username}/followers', [UserController::class, 'profileFollowersApi']); // Viewing user's followers
Route::get('/profile/{user:username}/following', [UserController::class, 'profileFollowingApi']); // Viewing who the user is following

// Authentication & Registration (Guest only)
// Route::post('/register', [UserController::class, 'registerApi'])->middleware('guest');
// Route::post('/register', [UserController::class, 'registerApi']);
// Route::post('/login', [UserController::class, 'loginApi'])->middleware('guest');



Route::post('login', [LoginController::class, 'loginApi']);
Route::post('register', RegisterController::class);


// Protected routes requiring authentication
Route::middleware('auth:sanctum')->group(function () {
    // Actions requiring the user to be logged in
    // Route::post('/logout', [UserController::class, 'logoutApi']);
    Route::post('/manage-avatar', [UserController::class, 'storeAvatarApi']);

    // Posting related routes
    Route::post('/create-post', [PostController::class, 'storeNewPostApi']);
    Route::put('/post/{post}', [PostController::class, 'updatePostApi'])->middleware('can:update,post');
    Route::delete('/delete-post/{post}', [PostController::class, 'deleteApi'])->middleware('can:delete,post');

    // Following related routes
    Route::post('/create-follow/{user:username}', [FollowController::class, 'createFollowApi']);
    Route::post('/remove-follow/{user:username}', [FollowController::class, 'removeFollowApi']);
});

// Fetch current user details (Protected)
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::group(['middleware' => 'api.auth'], function () {
    Route::get('user', [LoginController::class, 'details']);
    Route::get('logout', [LoginController::class, 'logout']);
});
