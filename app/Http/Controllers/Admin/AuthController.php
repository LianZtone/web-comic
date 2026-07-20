<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Notifications\AdminTwoFactorCodeNotification;
use App\Support\AdminTwoFactor;
use App\Support\FormCaptcha;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(Request $request): View|RedirectResponse
    {
        if ($request->user()?->is_admin) {
            return redirect()->route('admin.comics.index');
        }

        if (AdminTwoFactor::pendingUser($request)) {
            return redirect()->route('admin.two-factor.challenge');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        FormCaptcha::validate($request, 'admin-login', $request->input('captcha_answer'));

        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        if (! Auth::attempt([
            'email' => $validated['email'],
            'password' => $validated['password'],
        ], $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Email atau password admin tidak cocok.'])
                ->onlyInput('email');
        }

        if (! $request->user()?->is_admin) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['email' => 'Akun ini tidak memiliki akses admin.'])
                ->onlyInput('email');
        }

        $admin = $request->user();
        $remember = $request->boolean('remember');
        $code = AdminTwoFactor::begin($request, $admin, $remember);

        Auth::logout();
        $request->session()->regenerateToken();

        $admin->notify(new AdminTwoFactorCodeNotification($code));

        return redirect()
            ->route('admin.two-factor.challenge')
            ->with('status', 'Kode verifikasi admin sudah dikirim ke email akun kamu.');
    }

    public function showTwoFactorChallenge(Request $request): View|RedirectResponse
    {
        if ($request->user()?->is_admin) {
            return redirect()->route('admin.comics.index');
        }

        $admin = AdminTwoFactor::pendingUser($request);

        if (! $admin) {
            return redirect()->route('admin.login');
        }

        return view('admin.auth.two-factor', [
            'email' => $admin->email,
        ]);
    }

    public function verifyTwoFactor(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $admin = AdminTwoFactor::verify($request, $request->input('code'));
        $remember = AdminTwoFactor::remember($request);

        AdminTwoFactor::clear($request);

        Auth::login($admin, $remember);
        $request->session()->regenerate();

        return redirect()->intended(route('admin.comics.index'));
    }

    public function resendTwoFactor(Request $request): RedirectResponse
    {
        $admin = AdminTwoFactor::pendingUser($request);

        if (! $admin) {
            return redirect()->route('admin.login');
        }

        $code = AdminTwoFactor::begin($request, $admin, AdminTwoFactor::remember($request));
        $admin->notify(new AdminTwoFactorCodeNotification($code));

        return redirect()
            ->route('admin.two-factor.challenge')
            ->with('status', 'Kode verifikasi admin baru sudah dikirim.');
    }

    public function logout(Request $request): RedirectResponse
    {
        AdminTwoFactor::clear($request);
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
