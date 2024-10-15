<?php

namespace App\Http\Controllers;

use App\Constants;
use App\FileHelper;
use App\Models\Account;
use App\Models\Session;
use App\Models\User;
use App\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function userProfileInformation(Request $request)
    {
        $sessionId = $request->cookie('auth_session');
        $session = Session::where('id', $sessionId)->first();
        $account_id = $session->account_id;
        $user = User::where('account_id', $account_id)->first();
        $account = Account::where('id', $account_id)->first();
        // If user is not found
        if (!$user) {
            return ResponseHelper::buildErrorResponse();
        }

        // Return user data to the front-end
        return ResponseHelper::buildSuccessResponse([
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $account->email,
            'avatar_url' => $user->avatar_url,
            'job_title' => $user->job_title,
            'job_level' => $user->job_level,
        ]);
    }
    public function editUserProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'string',
            'job_title' => 'required|string:',
            'job_level' => 'required|int',
        ]);

        if (!$validator) {
            return ResponseHelper::buildErrorResponse();
        }
        $sessionId = $request->cookie('auth_session');
        $session = Session::where('id', $sessionId)->first();
        $account_id = $session->account_id;
        $user = User::where('account_id', $account_id)->first();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->job_title = $request->job_title;
        $user->job_level = $request->job_level;

        $user->save();

        return ResponseHelper::buildSuccessResponse("Edit Successful");
    }
    public function uploadProfilePicture(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            ['profile' => "required|image|mimes:jepg,jpg,png,webp|max:" . Constants::$MAX_FILE_SIZE]
        );
        $sessionId = $request->cookie('auth_session');
        $session = Session::where('id', $sessionId)->first();
        $account_id = $session->account_id;
        $user = User::where('account_id', $account_id)->first();

        $avatar = $request->file('profile');
        $avatarFileName = FileHelper::saveFile($avatar);


        $user->avatar_url = $avatarFileName;
        $user->save();

        return ResponseHelper::buildSuccessResponse("Successfully Upload Picture");
    }
}