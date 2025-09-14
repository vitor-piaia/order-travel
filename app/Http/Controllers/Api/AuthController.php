<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\UserService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct(private readonly UserService $userService){}

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = $this->userService->store($request->validated());
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'token' => $token
            ],Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error($e);
            return response()->json([
                'message' => __('message.error.default')
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        try {
            if (! JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            $user = auth()->user();
            $token = JWTAuth::claims(['role' => $user->role])->fromUser($user);

            return response()->json([
                'token' => $token
            ], Response::HTTP_OK);
        } catch (JWTException $e) {
            Log::error($e);
            return response()->json([
                'message' => __('message.error.token')
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

//    // Get authenticated user
//    public function getUser()
//    {
//        try {
//            if (! $user = JWTAuth::parseToken()->authenticate()) {
//                return response()->json(['error' => 'User not found'], 404);
//            }
//        } catch (JWTException $e) {
//            return response()->json(['error' => 'Invalid token'], 400);
//        }
//
//        return response()->json(compact('user'));
//    }

    public function logout(): JsonResponse
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json([
            'message' => __('message.success.logout')
        ], Response::HTTP_OK);
    }
}
