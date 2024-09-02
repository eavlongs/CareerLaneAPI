<?php

namespace App\Http\Controllers;

use App\Models\Session;
use App\Models\User;
use App\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'firstname' => 'required|string|max:50',
            'lastname' => 'required|string|max:50',
            'email' => 'required|string|email|max:50|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'firstname' => $request->firstName,
            'lastname' => $request->lastName,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'User registered successfully']);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return $user->createToken('user-token')->plainTextToken;
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function user(Request $request)
    {
        return $request->user();
    }

    // functions required by Lucia
    public function getSessionAndUser(Request $request)
    {
        $session_id = $request->session_id;
        $session = Session::where('id', $session_id)->first();
        if (!$session) {
            return ResponseHelper::buildErrorResponse("Session not found", 404);
        }
        $user = User::where('id', $session->user_id)->first();
        if (!$user) {
            return ResponseHelper::buildErrorResponse("User not found", 404);
        }

        return ResponseHelper::buildSuccessResponse([
            'session' => $session,
            'user' => $user
        ]);
    }

    public function getUserSessions(Request $request)
    {
        $user_id = $request->user_id;

        $user = User::where('id', $user_id)->first();
        if (!$user) {
            return ResponseHelper::buildErrorResponse("User not found", 404);
        }

        $sessions = Session::where('user_id', $user_id)->get();

        return ResponseHelper::buildSuccessResponse([
            'user' => $user,
            'sessions' => $sessions
        ]);
    }

    public function setSession(Request $request)
    {
        $request->validate([
            'user_id' => 'required|string',
            'session_id' => 'required|string',
            'expires_at' => 'required|date',
        ]);

        $session = new Session([
            "user_id" => $request->user_id,
            "session_id" => $request->session_id,
            "expires_at" => $request->expires_at,
        ]);

        $session->save();

        return ResponseHelper::buildSuccessResponse($session);
    }

    public function updateSessionExpiration(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
            'expires_at' => 'required|date',
        ]);

        $session = Session::where('session_id', $request->session_id)->first();
        if (!$session) {
            return ResponseHelper::buildErrorResponse("Session not found", 404);
        }

        $session->expires_at = $request->expires_at;
        $session->save();

        return ResponseHelper::buildSuccessResponse($session);
    }

    public function deleteSession(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
        ]);

        $session = Session::where('session_id', $request->session_id)->first();
        if (!$session) {
            return ResponseHelper::buildErrorResponse("Session not found", 404);
        }

        $session->delete();

        return ResponseHelper::buildSuccessResponse();
    }

    public function deleteUserSessions(Request $request)
    {
        $request->validate([
            'user_id' => 'required|string',
        ]);

        $user = User::where('id', $request->user_id)->first();
        if (!$user) {
            return ResponseHelper::buildErrorResponse("User not found", 404);
        }

        $sessions = Session::where('user_id', $request->user_id)->get();
        foreach ($sessions as $session) {
            $session->delete();
        }

        return ResponseHelper::buildSuccessResponse();
    }

    public function deleteExpiredSessions(Request $request)
    {
        Session::where('expires_at', '<', now())->delete();
        return ResponseHelper::buildSuccessResponse();
    }
}