<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): JsonResponse
    {
        if ($request->user()) { // already authenticated
            return UserResource::make($request->user())->response();
        }
        $request->authenticate();
        $request->session()->regenerate();

        return UserResource::make($request->user())->response();
    }

    /**
     * Handle an incoming authentication request.
     */
    public function currentUser(Request $request): JsonResponse
    {
        return UserResource::make($request->user())->response();
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
