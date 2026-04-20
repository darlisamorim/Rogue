<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirect(string $provider)
    {
        abort_unless(in_array($provider, ['google', 'facebook']), 404);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider)
    {
        abort_unless(in_array($provider, ['google', 'facebook']), 404);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception) {
            return redirect()->route('login')->withErrors([
                'email' => 'Não foi possível autenticar com ' . ucfirst($provider) . '. Tente novamente.',
            ]);
        }

        // Busca por usuário existente com o mesmo e-mail
        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            // Atualiza dados do provider se ainda não tinha
            if (!$user->provider) {
                $user->update([
                    'provider'   => $provider,
                    'avatar_url' => $user->avatar_url ?? $socialUser->getAvatar(),
                ]);
            }
        } else {
            $user = User::create([
                'name'              => $socialUser->getName(),
                'email'             => $socialUser->getEmail(),
                'provider'          => $provider,
                'avatar_url'        => $socialUser->getAvatar(),
                'password'          => bcrypt(Str::random(32)),
                'email_verified_at' => now(), // Google/Facebook já verificam o e-mail
                'credit_balance'    => 0,
            ]);
        }

        Auth::login($user, remember: true);

        return redirect()->intended(route('dashboard'));
    }
}
