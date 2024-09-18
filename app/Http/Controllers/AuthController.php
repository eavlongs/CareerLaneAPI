<?php

namespace App\Http\Controllers;

use App\Enums\ProviderEnum;
use App\ENums\UserTypeEnum;
use App\Mail\TestEmail;
use App\Models\Account;
use App\Models\AccountProvider;
use App\Models\Company;
use App\Models\EmailVerifyToken;
use App\Models\Provider;
use App\Models\Session;
use App\Models\User;
use App\ResponseHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Test\Constraint\ResponseHeaderLocationSame;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $lowerCasedEmail = strtolower($request->email);
        $validator = Validator::make([...$request->all(), "email" => $lowerCasedEmail], [
            'first_name' => 'required|string|min:1|max:50',
            'last_name' => 'required|string|min:1|max:50',
            'email' => 'required|string|email|max:50|unique:accounts',  // Check 'email' uniqueness in 'accounts'
            'password' => 'required|string|min:8',
            'confirm_password' => 'required|string|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $userAccount = Account::create([
            'email' => $lowerCasedEmail,
            'password' => Hash::make($request->password),
        ]);


        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'account_id' => $userAccount->id,
        ]);

        return ResponseHelper::buildSuccessResponse([
            'account_id' => $userAccount->id
        ]);
    }

    public function registerCompany(Request $request)
    {
        $lowerCasedEmail = strtolower($request->email);
        $validator = Validator::make([...$request->all(), "email" => $lowerCasedEmail], [
            'company_name' => 'required|string|min:1|max:50',
            'email' => 'required|string|email|max:50|unique:accounts',  // Check 'email' uniqueness in 'accounts'
            'password' => 'required|string|min:8',
            'confirm_password' => 'required|string|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $companyAccount = Account::create([
            'email' => $lowerCasedEmail,
            'password' => Hash::make($request->password),
        ]);

        $company = Company::create([
            'name' => $request->company_name,
            'account_id' => $companyAccount->id,
        ]);
        $token = str()->random(60);
        $expiresAt = Carbon::now()->addMonth()->format('Y-m-d H:i:s');
        $emailVerifyToken =  EmailVerifyToken::create([
            'token' => $token,
            'account_id' => $companyAccount->id,
            'expires_at' => $expiresAt,
        ]);


        return ResponseHelper::buildSuccessResponse([
            'account_id' => $companyAccount->id
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

        $existingAccount = Account::where('email', strtolower($request->email))->first();

        if (!$existingAccount) {
            Hash::make($request->password);
            return response()->json(['error' => 'Incorrect username or password'], 400);
        }

        if (!Hash::check($request->password, $existingAccount->password)) {
            return response()->json(['error' => 'Incorrect username or password'], 400);
        }

        if (! $existingAccount || ! Hash::check($request->password, $existingAccount->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return ResponseHelper::buildSuccessResponse([
            'account_id' => $existingAccount->id
        ]);
    }

    public function logout(Request $request)
    {

        return ResponseHelper::buildSuccessResponse();
    }

    public function loginProvider(Request $request){
        
        $validator = Validator::make($request->all(), [
            'provider' => 'required|int', // value must be in ProviderEnum range
            'provider_id' => 'required|string', // limit length
            'avatar_url' => 'required|url', 
            'first_name' => 'required|string',
            'last_name' => 'string', // if send null from frontend, error
            'provider_account_profile' => 'string'// limit length
            // more data about user like name
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422); // use ResponseHelper
        }

        $existingProvider = Provider::where('provider', $request->provider) // check provider = $request->provider
                ->where('id_from_provider', $request->provider_id) // check provider_id = $request->provider_id
                ->first();

        if ($existingProvider) {
            $existingAccountProvider = AccountProvider::where('provider_id', $existingProvider->id)->first();

            // just in case provider exists, but AccountProvider doesn't exist

            if ($existingAccountProvider) {
                return ResponseHelper::buildSuccessResponse([
                    'account_id' => $existingAccountProvider->account_id
                ]);
            };

            $account = Account::create();

            $accountProvider = AccountProvider::create([
                'account_id' => $account->id,
                'provider_id' => $existingProvider->id,
            ]);

            // TODO: create user
            $user = User::create([
                'account_id' => $account->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'avatar_url' => $request->avatar_url,
            ]);

            return ResponseHelper::buildSuccessResponse([
                'account_id' => $account->id
            ]);
        }
        
        $provider = Provider::create([
            'provider' => $request->provider,
            'id_from_provider' => $request->provider_id,
            'provider_account_profile' => $request->provider_account_profile,
        ]);

        $account = Account::create();

        $account_provider = AccountProvider::create([
            'account_id' => $account->id,
            'provider_id' => $provider->id,
        ]);

        // TODO: create user
        $user = User::create([
            'account_id' => $account->id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'avatar_url' => $request->avatar_url,
        ]);

        return ResponseHelper::buildSuccessResponse([
            'account_id' => $account->id
        ]);
    }

    public function user(Request $request)
    {
        return $request->user();
    }

    public function sendEmail(Request $request)
{
    $session = Session::where('id', $request->session_id)->first();
    $account = $session->account_id;
    $accountVerify = EmailVerifyToken::where('account_id', $account)->first();
    $token = $accountVerify->token;

    
    $verificationUrl = env('FRONTEND_URL') . '/verify-email?token=' . $token;

    Mail::to('zhoubovisal@gmail.com')->send(new TestEmail($verificationUrl));

    return ResponseHelper::buildSuccessResponse();
}
    public function verifyToken(Request $request)
{
    $token = $request->query('token');

        if (!$token) {
            return ResponseHelper::buildErrorResponse();
        }

        $emailVerifyToken = EmailVerifyToken::where('token', $token)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$emailVerifyToken) {
            $token = str()->random(60);
            $expiresAt = Carbon::now()->addMonth()->format('Y-m-d H:i:s');
            $emailVerifyToken =  EmailVerifyToken::create([
            'token' => $token,
            'account_id' => $companyAccount->id,
            'expires_at' => $expiresAt,
        ]);
            return ResponseHelper::buildErrorResponse();
        }

        // return ResponseHelper::buildSuccessResponse();
        return ResponseHelper::buildSuccessResponse();

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
                "company_name" => $company->name,
            ];
        } else {
            $userToBeReturned = [
                "id" => $user->id,
                "account_id" => $user->account_id,
                "avatar_url" => $user->avatar_url,
                "role" => UserTypeEnum::USER,
                "first_name" => $user->first_name,
                "last_name" => $user->last_name,
            ];
        }

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
                "company_name" => $company->name,
            ];
        } else {
            $userToBeReturned = [
                "id" => $user->id,
                "account_id" => $user->account_id,
                "avatar_url" => $user->avatar_url,
                "role" => UserTypeEnum::USER,
                "first_name" => $user->first_name,
                "last_name" => $user->last_name,
            ];
        }

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
