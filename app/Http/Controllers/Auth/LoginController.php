<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;

use App\Constants\AuthConstants;
use Illuminate\Http\JsonResponse;
use App\Http\Traits\HttpResponses;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;

class LoginController extends Controller
{
    use HttpResponses;


    public function loginApi(Request $request)
    {
        Log::info('Login API called', ['request' => $request->all()]);

        $incomingFields = $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        if (auth()->attempt($incomingFields)) {
            $user = User::where('username', $incomingFields['username'])->firstOrFail();
            $token = $user->createToken('MyApp')->plainTextToken;

            Log::info('Login successful', ['username' => $incomingFields['username']]);

            return response()->json(['token' => $token, 'user' => new UserResource($user)]);
        }

        Log::warning('Login failed', ['username' => $incomingFields['username']]);

        return response()->json(['message' => 'Unauthorized'], 401);
    }


    /**
     * Revoke the token that was used to authenticate the current request.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request...
        $request->user()->currentAccessToken()->delete();

        // Alternatively, if you want to log out from all devices by revoking all tokens:
        // $request->user()->tokens()->delete();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * @return JsonResponse
     */
    public function details(): JsonResponse
    {
        $user = auth()->user();

        return $this->success($user, '');
    }
}
