<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Traits\Loggable;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;

class AuthController extends Controller
{
    use Loggable;
    public function register(RegisterRequest $request)
    {
        $verificationToken = Str::random(60); // Jangan lupa use Illuminate\Support\Str;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'email_verification_token' => $verificationToken // Pastikan ini ada
        ]);

        $user->sendEmailVerificationNotification();
        return response()->json(['user' => $user], 201);
    }
    public function login(LoginRequest $request)
    {
        if (!$token = JWTAuth::attempt($request->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $this->logActivity('login', auth()->user());
        return $this->respondWithToken($token);
    }

    public function logout()
    {
        \Log::channel('auth')->info('User logout', [
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        $this->logActivity('logout', auth()->user());

        try {
            $token = JWTAuth::getToken();

            // Invalidasi token dan tambahkan ke blacklist
            JWTAuth::invalidate($token);

            // Hapus token dari user jika disimpan di database
            auth()->user()->update(['api_token' => null]);

            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil'
            ]);

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token sudah kadaluarsa'
            ], 401);

        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid'
            ], 401);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout gagal: '.$e->getMessage()
            ], 500);
        }
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60
        ]);
    }

    public function getUser()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            return response()->json([
                'user' => $user->only(['id', 'name', 'email', 'email_verified_at'])
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'User not found'], 404);
        }
    }

    public function verifyEmail(Request $request)
    {
        $request->validate(['token' => 'required']);

        $user = User::where('email_verification_token', $request->token)->firstOrFail();
        $user->update([
            'email_verified_at' => now(),
            'email_verification_token' => null
        ]);

        return response()->json(['message' => 'Email berhasil diverifikasi']);
    }

    public function resendVerificationEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();
        abort_if(!$user, 404, 'User not found');
        abort_if($user->hasVerifiedEmail(), 400, 'Email already verified');
        $user->sendEmailVerificationNotification();
        return response()->json([
            'message' => 'Verification link resent',
            'expires_in' => config('auth.verification.expire', 60)
        ]);
    }

}
