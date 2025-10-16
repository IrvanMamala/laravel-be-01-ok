<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTSubject;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|max:12',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user->makeHidden(['password']),
            'token' => $token
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8|max:12',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'error' => 'Invalid Email'
            ], 401);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'error' => 'Incorrect Password'
            ], 401);
        }

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Login successfully',
            'user' => $user->makeHidden(['password']),
            'token' => $token
        ], 200);
    }

    public function dashboard(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) { // ✅ perbaikan nama exception
            return response()->json(['error' => 'Token is Invalid'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) { // ✅ perbaikan nama exception
            return response()->json(['error' => 'Token is Expired'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['error' => 'Token not found'], 401);
        }

        return response()->json([
            'message' => 'Welcome to your dashboard',
            'user' => $user->makeHidden(['password'])
        ], 200);
    }

    public function logout(Request $request)
    {
        try {
            $token = JWTAuth::getToken();

            if (!$token) {
                return response()->json(['error' => 'Token not provided'], 401);
            }

            JWTAuth::invalidate($token);

            return response()->json(['message' => 'Logout successfully'], 200);

        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) { // ✅ perbaikan nama exception
            return response()->json(['error' => 'Failed to logout, token invalid'], 401);
        }
    }


    // melihat data tanpa perlu login
    public function allUsers()
    {
        $data = User::all();
        return response()->json($data);
    }


    // public function allUsers(Request $request)
    // {
    //     try {
    //         // Pastikan token valid & user terautentikasi
    //         $authUser = JWTAuth::parseToken()->authenticate();

    //         // Ambil semua user, kecuali kolom password
    //         $users = User::select('id', 'name', 'email', 'created_at')
    //             ->orderBy('id', 'asc')
    //             ->get();

    //         return response()->json([
    //             "status" => true,
    //             "message" => "List of users fetched successfully",
    //             "data" => $users
    //         ], 200);
    //     } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
    //         return response()->json(["status" => false, "message" => "Token is Invalid"], 401);
    //     } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
    //         return response()->json(["status" => false, "message" => "Token is Expired"], 401);
    //     } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
    //         return response()->json(["status" => false, "message" => "Token not found"], 401);
    //     } catch (Exception $e) {
    //         return response()->json([
    //             "status" => false,
    //             "message" => "Failed to fetch users",
    //             "error" => $e->getMessage(),
    //         ], 500);
    //     }
    // }
}
