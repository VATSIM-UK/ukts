<?php

namespace VATSIMUK\Auth\Remote\Controllers;

use App\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;

class AuthenticationController extends Controller
{
    public function login()
    {
        $query = http_build_query([
            'client_id' => config('ukauth.client_id'),
            'redirect_uri' => route('auth.login.verify'),
            'response_type' => 'code',
            'scope' => '',
        ]);

        return config('ukauth.root_url') . config('ukauth.oauth_path') . '/authorize?' . $query;
    }

    public function verifyLogin(Request $request)
    {
        $http = new Client();

        $response = $http->post(config('ukauth.root_url') . config('ukauth.oauth_path') . '/token', [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'client_id' => config('ukauth.client_id'),
                'client_secret' => config('ukauth.client_secret'),
                'redirect_uri' => route('auth.login.verify'),
                'code' => $request->code,
            ],
        ]);

        $response = json_decode((string)$response->getBody(), true);

        // Create JWT for service auth
        $user = User::findWithAccessToken($response['access_token'], ['name_first', 'name_last']);

        $expires = Carbon::now()->addSeconds($response['expires_in'])->getTimestamp();

        $token = (new Builder())->issuedBy(url('/'))
            ->permittedFor(url('/'))
            ->issuedAt(time())
            ->expiresAt($expires)
            ->withClaim('uid', $user->id)
            ->withClaim('name_first', $user->name_first)
            ->withClaim('name_last', $user->name_last)
            ->withClaim('access_token', $response['access_token'])
            ->getToken(new Sha256(), new Key(config('app.secret')));


        return redirect(url('/auth/complete') . "?token=$token&expires_at=$expires");
    }
}
