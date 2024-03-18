<?php

namespace App\Http\Controllers;


use App\Models\User;
use App\Models\Follow;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\UserResource;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\View;
use Intervention\Image\Facades\Image;
use App\Http\Resources\FollowResource;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;



class UserController extends Controller
{
    public function storeAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|max:3000',
        ]);

        $user = auth()->user();

        if ($request->hasFile('avatar')) {
            $uploadedFile = $request->file('avatar');

            $cloudinaryUpload = (new UploadApi())->upload($uploadedFile->getRealPath(), [
                'folder' => 'avatars'
            ]);
            $user->avatar = $cloudinaryUpload['url'];
        }

        $user->save();

        return back()->with('success', 'Avatar updated successfully.');
    }

    public function showAvatarForm()
    {
        return view('avatar-form');
    }

    private function getSharedData($user)
    {
        $currentlyFollowing = 0;

        if (auth()->check()) {
            $currentlyFollowing = Follow::where([['user_id', '=', auth()->user()->id], ['followeduser', '=', $user->id]])->count();
        }

        View::share('sharedData', ['currentlyFollowing' => $currentlyFollowing, 'avatar' => $user->avatar, 'username' => $user->username, 'postCount' => $user->posts()->count(), 'followerCount' => $user->followers()->count(), 'followingCount' => $user->followingTheseUsers()->count()]);
    }

    public function profile(User $user)
    {
        $this->getSharedData($user);
        return view('profile-posts', ['posts' => $user->posts()->latest()->get()]);
    }

    public function profileRaw(User $user)
    {
        return response()->json(['theHTML' => view('profile-posts-only', ['posts' => $user->posts()->latest()->get()])->render(), 'docTitle' => $user->username . "'s Profile"]);
    }

    public function profileFollowers(User $user)
    {
        $this->getSharedData($user);
        return view('profile-followers', ['followers' => $user->followers()->latest()->get()]);
    }

    public function profileFollowersRaw(User $user)
    {
        return response()->json(['theHTML' => view('profile-followers-only', ['followers' => $user->followers()->latest()->get()])->render(), 'docTitle' => $user->username . "'s Followers"]);
    }

    public function profileFollowing(User $user)
    {
        $this->getSharedData($user);
        return view('profile-following', ['following' => $user->followingTheseUsers()->latest()->get()]);
    }

    public function profileFollowingRaw(User $user)
    {
        return response()->json(['theHTML' => view('profile-following-only', ['following' => $user->followingTheseUsers()->latest()->get()])->render(), 'docTitle' => 'Who ' . $user->username . " Follows"]);
    }

    public function logout()
    {
        auth()->logout();
        return redirect('/')->with('success', 'You are now logged out.');
    }

    // public function showCorrectHomepage()
    // {
    //     if (auth()->check()) {
    //         return view('homepage-feed', ['posts' => auth()->user()->feedPosts()->latest()->paginate(4)]);
    //     } else {
    //         return view('homepage');
    //     }
    // }



    public function login(Request $request)
    {
        $incomingFields = $request->validate([
            'loginusername' => 'required',
            'loginpassword' => 'required'
        ]);

        if (auth()->attempt(['username' => $incomingFields['loginusername'], 'password' => $incomingFields['loginpassword']])) {
            $request->session()->regenerate();
            return redirect('/')->with('success', 'You have successfully logged in.');
        } else {
            return redirect('/')->with('failure', 'Invalid login.');
        }
    }

    public function register(Request $request)
    {
        $incomingFields = $request->validate([
            'username' => ['required', 'min:3', 'max:20', Rule::unique('users', 'username')],
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'password' => ['required', 'min:8', 'confirmed']
        ]);

        $incomingFields['password'] = bcrypt($incomingFields['password']);

        $user = User::create($incomingFields);
        auth()->login($user);
        return redirect('/')->with('success', 'Thank you for creating an account.');
    }


    public function storeAvatarApi(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|max:3000',
        ]);

        $user = auth()->user();

        if ($request->hasFile('avatar')) {
            $uploadedFile = $request->file('avatar');

            $cloudinaryUpload = (new UploadApi())->upload($uploadedFile->getRealPath(), [
                'folder' => 'avatars'
            ]);
            $user->avatar = $cloudinaryUpload['url'];
        }

        $user->save();

        return new UserResource($user);
    }
    // public function loginApi(Request $request)
    // {
    //     Log::info('Login API called', ['request' => $request->all()]);

    //     $incomingFields = $request->validate([
    //         'username' => 'required',
    //         'password' => 'required'
    //     ]);

    //     if (auth()->attempt($incomingFields)) {
    //         $user = User::where('username', $incomingFields['username'])->firstOrFail();
    //         $token = $user->createToken('ourapptoken')->plainTextToken;

    //         Log::info('Login successful', ['username' => $incomingFields['username']]);

    //         return response()->json(['token' => $token, 'user' => new UserResource($user)]);
    //     }

    //     Log::warning('Login failed', ['username' => $incomingFields['username']]);

    //     return response()->json(['message' => 'Unauthorized'], 401);
    // }



    // public function registerApi(Request $request)
    // {
    //     Log::info('Register API: Starting registration process');

    //     $validatedData = $request->validate([
    //         'username' => ['required', 'min:3', 'max:20', Rule::unique('users', 'username')],
    //         'email' => ['required', 'email', Rule::unique('users', 'email')],
    //         'password' => ['required', 'min:4', 'confirmed'],
    //     ]);

    //     Log::info('Register API: Validation passed');

    //     // For simplicity, let's log the validated data except the password
    //     Log::info('Register API: Validated data', ['data' => Arr::except($validatedData, ['password'])]);

    //     $validatedData['password'] = bcrypt($validatedData['password']);

    //     $user = User::create($validatedData);

    //     Log::info('Register API: User created', ['user_id' => $user->id]);

    //     $token = $user->createToken('ourapptoken')->plainTextToken;

    //     Log::info('Register API: Token created', ['token' => $token]);

    //     return response()->json(['message' => 'User registered successfully', 'token' => $token, 'user' => new UserResource($user)], 201);
    // }



    public function profileApi(User $user)
    {
        return new UserResource($user->load(['posts', 'followers', 'followingTheseUsers']));
    }


    public function profileFollowersApi(User $user)
    {

        return FollowResource::collection($user->followers()->latest()->get());
    }

    public function profileFollowingApi(User $user)
    {

        return FollowResource::collection($user->followingTheseUsers()->latest()->get());
    }
}
