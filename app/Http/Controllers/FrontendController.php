<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;

class FrontendController extends Controller
{
    public function __invoke()
    {
        $user = Auth::user();

        return view('app', [
            'user' => $user ? UserResource::make($user) : null,
        ]);
    }
}
