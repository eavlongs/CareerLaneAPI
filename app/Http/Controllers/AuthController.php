<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Session;
use App\Models\User;
use App\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|string|email|max:50|unique:accounts',  // Check 'email' uniqueness in 'accounts'
            'password' => 'required|string|min:8',
            'confirm_password' => 'required|string|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $userAccount = Account::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // dd($userAccount->id);
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'account_id' => $userAccount->id,
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

        // return $user->createToken('user-token')->plainTextToken;
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
        $account = Account::where('id', $session->user_id)->first();
        if (!$account) {
            return ResponseHelper::buildErrorResponse("Account not found", 404);
        }

        $user = User::where('account_id', $account->id)->first();
        if (!$user) {
            return ResponseHelper::buildErrorResponse("User not found", 404);
        }

        // TODO: modify the fields as required
        $userToBeReturned = [];
        return ResponseHelper::buildSuccessResponse([
            'session' => $session,
            'user' => $userToBeReturned
        ]);
    }

    public function getUserSessions(Request $request)
    {
        $account_id = $request->account_id;

        $account = Account::where('id', $account_id)->first();
        if (!$account) {
            return ResponseHelper::buildErrorResponse("Account not found", 404);
        }

        $user = User::where('account_id', $account->id)->first();
        if (!$user) {
            return ResponseHelper::buildErrorResponse("User not found", 404);
        }

        $sessions = Session::where('user_id', $account_id)->get();

        // TODO: modify the fields as required
        $userToBeReturned = [];

        return ResponseHelper::buildSuccessResponse([
            'user' => $userToBeReturned,
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
            "user_id" => $request->account_id,
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

        $user = User::where('id', $request->account_id)->first();
        if (!$user) {
            return ResponseHelper::buildErrorResponse("User not found", 404);
        }

        $sessions = Session::where('user_id', $request->account_id)->get();
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
