<?php

namespace App\Http\Controllers;

use App\ENums\UserTypeEnum;
use App\Models\Account;
use App\Models\Company;
use App\Models\Session;
use App\Models\User;
use App\ResponseHelper;
use Carbon\Carbon;
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

        return ResponseHelper::buildSuccessResponse([
            'user_id' => $userAccount->id
        ]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::buildValidationErrorResponse($validator->errors());
        }

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $existingUser = Account::where('email', strtolower($request->email))->first();

        if (!$existingUser) {

            Hash::make($request->password);
            return response()->json(['error' => 'Incorrect username or password'], 400);
        }

        if (!Hash::check($request->password, $existingUser->password)) {
            return response()->json(['error' => 'Incorrect username or password'], 400);
        }

        if (! $existingUser || ! Hash::check($request->password, $existingUser->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return ResponseHelper::buildSuccessResponse([
            'user_id' => $existingUser->id
        ]);
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

        $account = Account::where('id', $session->account_id)->first();
        if (!$account) {
            return ResponseHelper::buildErrorResponse("Account not found", 404);
        }

        $userToBeReturned = [];

        $user = User::where('account_id', $account->id)->first();
        if (!$user) {
            $company = Company::where('account_id', $account->id)->first();
            if (!$company) {
                return ResponseHelper::buildErrorResponse("User not found", 404);
            }
            $userToBeReturned = [
                "id" => $company->id,
                "account_id" => $company->account_id,
                "avatar_url" => $company->logo_url,
                "role" => UserTypeEnum::COMPANY,
                "company_name" => $company->company_name,
            ];
        }

        $userToBeReturned = [
            "id" => $user->id,
            "account_id" => $user->account_id,
            "avatar_url" => $user->avatar_url,
            "role" => UserTypeEnum::USER,
            "first_name" => $user->first_name,
            "last_name" => $user->last_name,
        ];

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

        $userToBeReturned = [];

        $user = User::where('account_id', $account->id)->first();
        if (!$user) {
            $company = Company::where('account_id', $account->id)->first();
            if (!$company) {
                return ResponseHelper::buildErrorResponse("User not found", 404);
            }
            $userToBeReturned = [
                "id" => $company->id,
                "account_id" => $company->account_id,
                "avatar_url" => $company->logo_url,
                "role" => UserTypeEnum::COMPANY,
                "company_name" => $company->company_name,
            ];
        }

        $userToBeReturned = [
            "id" => $user->id,
            "account_id" => $user->account_id,
            "avatar_url" => $user->avatar_url,
            "role" => UserTypeEnum::USER,
            "first_name" => $user->first_name,
            "last_name" => $user->last_name,
        ];

        $sessions = Session::where('user_id', $account_id)->get();

        return ResponseHelper::buildSuccessResponse([
            'user' => $userToBeReturned,
            'sessions' => $sessions
        ]);
    }

    public function setSession(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userId' => 'required|string',
            'id' => 'required|string',
            'expiresAt' => 'required|date',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::buildValidationErrorResponse($validator->errors());
        }

        $session = new Session([
            "account_id" => $request->userId,
            "id" => $request->id,
            "expires_at" => Carbon::parse($request->expiresAt)
        ]);

        $session->save();

        return ResponseHelper::buildSuccessResponse($session);
    }

    public function updateSessionExpiration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string',
            'expires_at' => 'required|date',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::buildValidationErrorResponse($validator->errors());
        }

        $session = Session::where('id', $request->session_id)->first();
        if (!$session) {
            return ResponseHelper::buildErrorResponse("Session not found", 404);
        }

        $session->expires_at = $request->expires_at;
        $session->save();

        return ResponseHelper::buildSuccessResponse($session);
    }

    public function deleteSession(Request $request)
    {
        $session = Session::where('id', $request->session_id)->first();
        if (!$session) {
            return ResponseHelper::buildErrorResponse("Session not found", 404);
        }

        $session->delete();

        return ResponseHelper::buildSuccessResponse();
    }

    public function deleteUserSessions(Request $request)
    {
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