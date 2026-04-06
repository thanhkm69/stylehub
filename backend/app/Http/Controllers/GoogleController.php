<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

use Laravel\Socialite\Socialite;


class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function callback()
    {
        $googlebUser = Socialite::driver('google')->stateless()->user();

        $role = User::firstWhere("email", $googlebUser->email)->role ?? "User";

        $user = User::updateOrCreate([
            "email" => $googlebUser->email
        ], [
            'name' => $googlebUser->name,
        ]);

        $token = $user->createToken("token", [$role ?? "User"])->plainTextToken;
        return redirect(config('app.frontend_url') . "?token=$token");
    }
}
