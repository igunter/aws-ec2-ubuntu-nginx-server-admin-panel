<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    public function username(): string
    {
        return 'credential';
    }

    public function login(Request $request)
    {
        $request->validate([
            'credential' => ['required', 'string'],
            'password'   => ['required', 'string'],
        ]);

        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }

        $remember = $request->boolean('remember');

        if (Auth::attempt(['email' => $request->credential, 'password' => $request->password], $remember)) {
            $request->session()->regenerate();
            $this->clearLoginAttempts($request);

            $redirectTo = Auth::user()->account_id ? route('portal.dashboard') : $this->redirectTo;
            return redirect()->intended($redirectTo);
        }

        $this->incrementLoginAttempts($request);

        return back()->withInput($request->only('credential', 'remember'))->withErrors([
            'credential' => trans('auth.failed'),
        ]);
    }
}
