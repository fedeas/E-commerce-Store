<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\StorefrontController;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(?string $locale = null): View
    {
        return view('auth.login', app(StorefrontController::class)->sharedStorefrontData($locale, 'login'));
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request, ?string $locale = null): RedirectResponse
    {
        $locale = app(StorefrontController::class)->setStorefrontLocale($locale);

        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended('/' . $locale);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request, ?string $locale = null): RedirectResponse
    {
        $locale = in_array($locale, ['en', 'el'], true) ? $locale : App::getLocale();

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/' . (in_array($locale, ['en', 'el'], true) ? $locale : 'en'));
    }
}
