<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnsurePermissionIsLVL1
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('token');
        if ($token == NULL) {
            return response("no_login", 401);
        } else {
            $user = DB::table('users')->where('remember_token', '=', $token);
            if ($user->first() == NULL) {
                return response("no_login", 401);
            } else {
                if ($user->first()->permission > 1) {
                    return response("token_expired", 401);
                } else {
                    if (strtotime($user->first()->token_expire_time) < time()) {
                        return response("token_expired", 401);
                    } else {
                        $user->update(['token_expire_time' => date('Y-m-d H:i:s', time() + 60 * 10)]);
                        return $next($request);
                    }
                }
            }
        }
    }
}