<?php

namespace VATSIMUK\Auth\Remote\Middleware;

use Closure;
use Illuminate\Http\Request;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;

class JWTAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->bearerToken()) {
            abort('401');
        }

        $token = (new Parser())->parse((string) $request->bearerToken());

        if (!$token->verify(new Sha256(), new Key(config('app.secret')))) {
            abort('401');
        }



        return $next($request);
    }
}
