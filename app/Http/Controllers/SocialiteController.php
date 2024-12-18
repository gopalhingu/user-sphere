<?php

namespace App\Http\Controllers;
 
use App\Models\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
 
class SocialiteController extends Controller
{
    public function redirect(string $provider)
    {
        $this->validateProvider($provider);
 
        return Socialite::driver($provider)->redirect();
    }
 
    public function callback(string $provider, Request $request)
    {
        $this->validateProvider($provider);

        /*
        $response = Socialite::driver($provider)->user();

        $user = User::firstWhere(['email' => $response->getEmail()]);
 
        if ($user) {
            $user->update([$provider . '_id' => $response->getId()]);
            $user->assignRole('user');
        } else {
            $user = User::create([
                $provider . '_id' => $response->getId(),
                'name'            => $response->getName(),
                'email'           => $response->getEmail(),
                'password'        => '',
            ]);
        }
 
        auth()->login($user);
        $user->assignRole('user');
        return redirect()->intended(route('filament.admin.pages.dashboard'));
        */

        //----------------------------------------------------------------------------------------------------------------

        $code = $request->get('code');
        $state = $request->get('state');

        // if ($state !== csrf_token()) {
        //     return redirect('/admin/login')->withErrors(['error' => 'Invalid state token']);
        // }

        try {
            $http = new Client(['verify' => false]);
            $response = $http->post('https://oauth2.googleapis.com/token', [
                'form_params' => [
                    'client_id' => env('GOOGLE_CLIENT_ID'),
                    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
                    'redirect_uri' => env('GOOGLE_REDIRECT_URI'),
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $accessToken = $data['access_token'];

            // Fetch user details
            $userResponse = $http->get('https://www.googleapis.com/oauth2/v1/userinfo?alt=json', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
            ]);

            $response = json_decode($userResponse->getBody()->getContents(), true);

            $user = User::firstWhere(['email' => $response['email']]);
 
            if ($user) {
                $user->update([$provider . '_id' => $response['id']]);
                $user->assignRole('user');
            } else {
                $user = User::create([
                    $provider . '_id'   => $response['id'],
                    'name'              => $response['name'],
                    'email'             => $response['email'],
                    'email_verified_at' => Carbon::now(),
                    'password'          => '',
                ]);
            }
    
            auth()->login($user);
            $user->assignRole('user');
            return redirect()->intended(route('filament.admin'));
        } catch (\Exception $e) {
            return redirect('/admin/login')->with(['error' => $e->getMessage()]);
        }
    }
 
    protected function validateProvider(string $provider): array
    {
        return $this->getValidationFactory()->make(
            ['provider' => $provider],
            ['provider' => 'in:google']
        )->validate();
    }
}