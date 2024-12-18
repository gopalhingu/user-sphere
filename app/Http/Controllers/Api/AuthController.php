<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

/**
 * @OA\Info(
 *     title="Authentication API",
 *     version="1.0.0",
 *     description="API for user authentication with JWT"
 * )
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Auth Management"},
     *     summary="Register a new user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "password_confirmation"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(response=201, description="User registered successfully"),
     *     @OA\Response(response=422, description="Validation errors")
     * )
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole('user');

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'User successfully registered',
            'token' => $token,
            'user' => $user,
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Auth Management"},
     *     summary="Log in a user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Successfully logged in"),
     *     @OA\Response(response=401, description="Invalid credentials")
     * )
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        return response()->json([
            'message' => 'Successfully logged in',
            'token' => $token,
            'user' => auth()->user(),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Auth Management"},
     *     summary="Log out the authenticated user",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Response(response=200, description="Successfully logged out"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * @OA\Get(
     *     path="/api/user",
     *     tags={"User Management"},
     *     summary="Get user details",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Response(response=200, description="User details fetched successfully"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function authDetail()
    {
        return auth()->user();
    }

    /**
     * @OA\Post(
     *     path="/api/refresh",
     *     summary="Refresh JWT Token",
     *     tags={"Authentication"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully refreshed JWT token",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", description="New JWT token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Token not provided or invalid",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized, invalid or expired refresh token"
     *     ),
     * )
     */
    public function refresh()
    {
        try {
            // Check if the refresh token exists in the request
            $token = JWTAuth::getToken();
            
            if (!$token) {
                return response()->json(['error' => 'Refresh token not provided.'], 400);
            }

            // Attempt to refresh the token
            $refreshedToken = JWTAuth::refresh($token);

            // Return the new token
            return response()->json(['token' => $refreshedToken]);
        } catch (TokenExpiredException $e) {
            // If the refresh token is expired
            return response()->json(['error' => 'Refresh token has expired. Please login again.'], 401);
        } catch (JWTException $e) {
            // If there is any issue with the JWT token
            return response()->json(['error' => 'Could not refresh token.'], 400);
        }
    }
}
