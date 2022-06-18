<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserType
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param $type
     * @return JsonResponse
     */
    public function handle(Request $request, Closure $next, $type)
    {
        if (User::TYPE_SLUGS[auth()->user()->type] !== $type) {
            return response()->json([
               'status' => 404,
               'message' => 'Error with user type.'
            ]);
        }

        return $next($request);
    }
}
