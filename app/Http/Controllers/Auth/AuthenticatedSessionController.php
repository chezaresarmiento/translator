<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use App\Jobs\SendMailJob;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {

        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        // Dispatch your job using the user's credentials
        SendMailJob::dispatch([
            'email_from'      => 'do_not_reply@appsolution4u.com',
            'name_from'       => 'AppSolution Assistant',
            'email_recipient' => $user->email, // from the logged-in user
            'name_recipient'  => $user->name,  // from the logged-in user
            'subject'         => 'New Loging to AppSolution4u Detected',
            'template_id'     => 6759039,
            'variables'       => [
                'ip_address' => $request->ip()
            ],
        ]);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
