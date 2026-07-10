<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\MetricRecorder;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    public function login(Request $request)
    {
        $start = MetricRecorder::start();

        $this->validateLogin($request);

        if (method_exists($this, 'hasTooManyLoginAttempts') && $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            $request->session()->save();
            $result = MetricRecorder::finish($start);
            $storageBytes = MetricRecorder::sessionStorageBytes($request);
            MetricRecorder::log(
                'session',
                'login',
                $request,
                $result['duration_ms'],
                $result['memory_usage'],
                $result['query_count'],
                $storageBytes,
                true
            );

            return $this->sendLoginResponse($request);
        }

        if (method_exists($this, 'incrementLoginAttempts')) {
            $this->incrementLoginAttempts($request);
        }

        $result = MetricRecorder::finish($start);
        MetricRecorder::log(
            'session',
            'login_failed',
            $request,
            $result['duration_ms'],
            $result['memory_usage'],
            $result['query_count'],
            0,
            false
        );

        return $this->sendFailedLoginResponse($request);
    }
}
