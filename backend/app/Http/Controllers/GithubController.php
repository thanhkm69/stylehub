<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Laravel\Socialite\Socialite;


class GithubController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('github')->stateless()->redirect();
    }

    public function callback()
    {
        $githubUser = Socialite::driver('github')->stateless()->user();

        $role = User::firstWhere("email", $githubUser->email)->role ?? "User";

        $user = User::updateOrCreate([
            "email" => $githubUser->email
        ], [
            'name' => $githubUser->nickname,
            'email_verified_at' => Carbon::now(),
        ]);

        $token = $user->createToken("token", [$role ?? "User"])->plainTextToken;
        return redirect(config('app.frontend_url') . "?token=$token");
    }
}
