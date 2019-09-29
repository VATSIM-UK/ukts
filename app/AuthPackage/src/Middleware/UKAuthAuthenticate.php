<?php

namespace VATSIMUK\Auth\Remote\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\UnauthorizedException;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;

class UKAuthAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
       if(!Auth::check()){
           throw new AuthenticationException(
               'Unauthenticated.', 'ukauth', ''
           );
       }
        return $next($request);
    }
}
