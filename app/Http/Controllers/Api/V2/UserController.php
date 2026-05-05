<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\UserCollection;
use App\Models\User;
use Illuminate\Http\Request;

use Laravel\Sanctum\PersonalAccessToken;


class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('throttle:1,1')->only('send_new_verification_link');
    }
    public function info($id)
    {
        return new UserCollection(User::where('id', auth()->user()->id)->get());
    }

    public function updateName(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        $user->update([
            'name' => $request->name
        ]);
        return response()->json([
            'message' => translate('Profile information has been updated successfully')
        ]);
    }

    public function getUserInfoByAccessToken(Request $request)
    {

        $false_response = [
            'result' => false,
            'id' => 0,
            'name' => "",
            'email' => "",
            'avatar' => "",
            'avatar_original' => "",
            'phone' => ""
        ];



        $token = PersonalAccessToken::findToken($request->access_token);
        if (!$token) {
            return response()->json($false_response);
        }

        $user = $token->tokenable;



        if ($user == null) {
            return response()->json($false_response);
        }

        return response()->json([
            'result' => true,
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'avatar_original' => uploaded_asset($user->avatar_original),
            'phone' => $user->phone
        ]);
    }

    public function send_new_verification_link(Request $request){
        $user = User::findOrFail($request->user_id);
      if($user){
        try{
        $user->sendEmailVerificationNotification();
        return response()->json(['result' => true, 'message' => 'Email sent']);
        }catch(\Exception $e ){
            return response()->json(['result' => false, 'message' => translate($e->getMessage())]);
        }
      }
      return response()->json(['result' => false, 'message' => 'Error']);
    }

    public function check_email_verification(Request $request){
        $user = User::findOrFail($request->user_id);
        if($user->email_verified_at != null){
            return response()->json(['result' => true, 'message' => translate('verified')]);
        }
        return response()->json(['result' => false, 'message' => translate('Not verified')]);
    }
}
