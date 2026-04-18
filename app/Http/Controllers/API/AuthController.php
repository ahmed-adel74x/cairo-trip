<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use ApiResponseTrait;

    // ─────────────────────────────────────────────────
    // POST /api/auth/register
    // ─────────────────────────────────────────────────
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'phone'    => $request->phone,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse(
            [
                'user'  => new UserResource($user),
                'token' => $token,
            ],
            'register_success',
            201
        );
    }

    // ─────────────────────────────────────────────────
    // POST /api/auth/login
    // ─────────────────────────────────────────────────
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->errorResponse('invalid_credentials', 401);
        }

        // Delete old tokens → create fresh one
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse(
            [
                'user'  => new UserResource($user),
                'token' => $token,
            ],
            'login_success',
            200
        );
    }

    // ─────────────────────────────────────────────────
    // POST /api/auth/logout
    // ─────────────────────────────────────────────────
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'logout_success', 200);
    }

    // ─────────────────────────────────────────────────
    // GET /api/auth/profile
    // ─────────────────────────────────────────────────
    public function profile(Request $request): JsonResponse
    {
        return $this->successResponse(
            ['user' => new UserResource($request->user())],
            'profile_fetched',
            200
        );
    }

    // ─────────────────────────────────────────────────
    // PUT /api/auth/profile
    // ─────────────────────────────────────────────────
    public function updateProfile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'               => 'sometimes|string|min:2|max:100',
            'phone'              => 'sometimes|nullable|string|min:11|max:15',
            'avatar'             => 'sometimes|image|mimes:jpeg,png,jpg,webp|max:2048',
            'preferred_language' => 'sometimes|in:ar,en',
        ]);

        if ($validator->fails()) {
            // نرسل الأخطاء مباشرة من الـ validator لضمان تبديل الـ :min
            return $this->errorResponse('validation_error', 422, $validator->errors()->toArray());
        }

        $user = $request->user();
        $data = $request->only(['name', 'phone', 'preferred_language']);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $path;
        }

        $user->update($data);

        return $this->successResponse(
            ['user' => new UserResource($user->fresh())],
            'profile_updated',
            200
        );
    }
}