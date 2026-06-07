<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user'  => $request->user(),
                'roles' => fn () => $request->user()?->roles->pluck('nom') ?? collect(),
            ],
            'flash' => [
                'success'       => session('success'),
                'error'         => session('error'),
                'temp_password' => session('temp_password'),
                'temp_user'     => session('temp_user'),
                'temp_email'    => session('temp_email'),
            ],
        ];
    }
}
