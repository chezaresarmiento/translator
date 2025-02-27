<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Jobs\SendMailJob;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $user = $request->user();
       

        SendMailJob::dispatch([
            'email_from' => 'do_not_reply@appsolution4u.com',
            'name_from' => 'AppSolution Assistant',
            'email_recipient' => $user->email,
            'name_recipient' => $user->name,
            'subject' => 'Password Updated',
            'template_id' => 6763339,
            'variables' => ["any"=>"can not be empty"]
        ]);

        return back();
    }
}
