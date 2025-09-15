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

/**
 * @OA\Info(
 *      version="1.0.0",
 *      x={
 *          "logo": {
 *              "url": "https://via.placeholder.com/190x90.png?text=L5-Swagger"
 *          }
 *      },
 *      title="Order Travel",
 *      description="Internal control for travel requests",
 *
 *      @OA\Contact(
 *          email="vitor.piaia@hotmail.com"
 *      ),
 * )
 */
class AuthController extends Controller
{
    public function __construct(private readonly UserService $userService) {}

    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Auth"},
     *     summary="Create user",
     *     description="Create user with name, email and password.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *
     *             @OA\Property(property="name", type="string", example="João Silva"),
     *             @OA\Property(property="email", type="string", format="email", example="joao@email.com"),
     *             @OA\Property(property="password", type="string", format="password", example="12345678"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="12345678")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ"),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Error validation",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="The email field must be a valid email address.")
     *         )
     *     )
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = $this->userService->store($request->validated());
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'token' => $token,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error($e);

            return response()->json([
                'message' => __('message.error.default'),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Auth"},
     *     summary="Login",
     *     description="User authentication with email and password.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"email","password"},
     *
     *             @OA\Property(property="email", type="string", format="email", example="joao@email.com"),
     *             @OA\Property(property="password", type="string", format="password", example="12345678"),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User authenticated successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ"),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Error validation",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="The email field must be a valid email address.")
     *         )
     *     )
     * )
     */
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
                'token' => $token,
            ], Response::HTTP_OK);
        } catch (JWTException $e) {
            Log::error($e);

            return response()->json([
                'message' => __('message.error.token'),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Auth"},
     *     summary="Logout",
     *     description="User logout.",
     *
     *     @OA\Response(
     *         response=200,
     *         description="User logged out",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Successfully logged out"),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function logout(): JsonResponse
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json([
            'message' => __('message.success.logout'),
        ], Response::HTTP_OK);
    }
}
