<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

use Laravel\Socialite\Socialite;


class FacebookController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('facebook')->stateless()->redirect();
    }

    public function callback()
    {
        $facebookUser = Socialite::driver('facebook')->stateless()->user();

        $role = User::firstWhere("email", $facebookUser->email)->role ?? "User";

        $user = User::updateOrCreate([
            "email" => $facebookUser->email
        ], [
            'name' => $facebookUser->name,
        ]);

        $token = $user->createToken("token", [$role])->plainTextToken;
        return redirect(config('app.frontend_url') . "?token=$token");
    }
}
