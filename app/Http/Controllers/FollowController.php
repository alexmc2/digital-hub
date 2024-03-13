<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Follow;
use Illuminate\Http\Request;
use App\Http\Resources\FollowResource;

class FollowController extends Controller
{

    public function createFollow(User $user)
    {
        // you cannot follow yourself
        if ($user->id == auth()->user()->id) {
            return back()->with('failure', 'You cannot follow yourself.');
        }

        // you cannot follow someone you're already following
        $existCheck = Follow::where([['user_id', '=', auth()->user()->id], ['followeduser', '=', $user->id]])->count();

        if ($existCheck) {
            return back()->with('failure', 'You are already following that user.');
        }

        $newFollow = new Follow;
        $newFollow->user_id = auth()->user()->id;
        $newFollow->followeduser = $user->id;
        $newFollow->save();

        return back()->with('success', 'User successfully followed.');
    }

    public function removeFollow(User $user)
    {
        Follow::where([['user_id', '=', auth()->user()->id], ['followeduser', '=', $user->id]])->delete();
        return back()->with('success', 'User succesfully unfollowed.');
    }
    public function createFollowApi(User $user)
    {
        if ($user->id == auth()->user()->id) {
            return response()->json(['message' => 'You cannot follow yourself.'], 422);
        }

        $existCheck = Follow::where([['user_id', '=', auth()->user()->id], ['followeduser', '=', $user->id]])->exists();

        if ($existCheck) {
            return response()->json(['message' => 'You are already following that user.'], 422);
        }

        $newFollow = Follow::create(['user_id' => auth()->user()->id, 'followeduser' => $user->id]);

        return new FollowResource($newFollow);
    }

    public function removeFollowApi(User $user)
    {
        $deleted = Follow::where([['user_id', '=', auth()->user()->id], ['followeduser', '=', $user->id]])->delete();

        if ($deleted) {
            return response()->json(['message' => 'User successfully unfollowed.'], 200);
        }

        return response()->json(['message' => 'Unfollow action could not be completed.'], 404);
    }
}
