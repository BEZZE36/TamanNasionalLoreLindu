<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegisterForm()
    {
        return \Inertia\Inertia::render('Auth/Register');
    }

    /**
     * Step 1: Send OTP for registration.
     */
    public function sendRegisterOtp(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Check cooldown
        $cooldown = Otp::canResend($request->email, Otp::TYPE_REGISTER);
        if (!$cooldown['can_resend']) {
            return back()->withErrors([
                'otp' => "Tunggu {$cooldown['wait_seconds']} detik sebelum mengirim ulang OTP.",
            ]);
        }

        // Generate and send OTP
        $otp = Otp::generateOtp($request->email, Otp::TYPE_REGISTER);

        \Log::info('OTP Generated', [
            'email' => $request->email,
            'otp_code' => $otp->otp_code,
            'expires_at' => $otp->expires_at,
        ]);

        try {
            Mail::to($request->email)->send(new OtpMail($otp->otp_code, 'register', Otp::EXPIRY_SECONDS));
            \Log::info('OTP Email sent successfully', ['email' => $request->email]);

            // Record attempt for progressive cooldown
            $attempt = Otp::recordAttempt($request->email, Otp::TYPE_REGISTER);
            $nextCooldown = Otp::getCooldownForAttempt($attempt);

        } catch (\Exception $e) {
            \Log::error('OTP Email failed', [
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors([
                'email' => 'Gagal mengirim email OTP: ' . $e->getMessage(),
            ]);
        }

        // Store registration data in session for later use
        $request->session()->put('register_data', [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => $request->password,
        ]);

        return \Inertia\Inertia::render('Auth/VerifyOtp', [
            'email' => $request->email,
            'type' => 'register',
            'expirySeconds' => Otp::EXPIRY_SECONDS,
            'maskedEmail' => Otp::maskEmail($request->email),
            'nextCooldown' => $nextCooldown ?? 60,
        ]);
    }

    /**
     * Step 2: Verify OTP and create account.
     */
    public function verifyRegisterOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        // Verify OTP
        if (!Otp::verifyOtp($request->email, $request->otp, Otp::TYPE_REGISTER)) {
            return back()->withErrors([
                'otp' => 'Kode OTP salah atau sudah kedaluwarsa.',
            ]);
        }

        // Clear OTP attempt count on successful verification
        Otp::clearAttempts($request->email, Otp::TYPE_REGISTER);

        // Get registration data from session
        $registerData = $request->session()->get('register_data');
        if (!$registerData || $registerData['email'] !== $request->email) {
            return redirect()->route('register')->withErrors([
                'email' => 'Sesi pendaftaran tidak valid. Silakan daftar ulang.',
            ]);
        }

        // Create user
        $user = User::create([
            'name' => $registerData['name'],
            'email' => $registerData['email'],
            'phone' => $registerData['phone'],
            'password' => Hash::make($registerData['password']),
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        // Clear session data
        $request->session()->forget('register_data');

        // Login user
        Auth::login($user);

        return redirect()->intended('/dashboard')
            ->with('success', 'Selamat datang di TNLL Explore!');
    }

    /**
     * Resend OTP for registration.
     */
    public function resendRegisterOtp(Request $request)
    {
        \Log::info('Resend OTP called', ['email' => $request->email]);

        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Check if registration data exists
        $registerData = $request->session()->get('register_data');
        if (!$registerData || $registerData['email'] !== $request->email) {
            \Log::warning('Resend OTP - invalid session', ['email' => $request->email]);
            return response()->json([
                'success' => false,
                'message' => 'Sesi tidak valid.',
            ], 400);
        }

        // Check cooldown
        $cooldown = Otp::canResend($request->email, Otp::TYPE_REGISTER);
        \Log::info('Resend OTP - cooldown check', $cooldown);

        if (!$cooldown['can_resend']) {
            return response()->json([
                'success' => false,
                'message' => "Tunggu {$cooldown['wait_seconds']} detik.",
                'wait_seconds' => $cooldown['wait_seconds'],
            ], 429);
        }

        // Generate and send OTP
        $otp = Otp::generateOtp($request->email, Otp::TYPE_REGISTER);

        try {
            Mail::to($request->email)->send(new OtpMail($otp->otp_code, 'register', Otp::EXPIRY_SECONDS));
            \Log::info('Resend OTP - email sent, recording attempt');

            // Record attempt for progressive cooldown
            $attempt = Otp::recordAttempt($request->email, Otp::TYPE_REGISTER);
            $nextCooldown = Otp::getCooldownForAttempt($attempt);

            \Log::info('Resend OTP - attempt recorded', [
                'attempt' => $attempt,
                'next_cooldown' => $nextCooldown,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP berhasil dikirim ulang.',
            'expiry_seconds' => Otp::EXPIRY_SECONDS,
            'next_cooldown' => $nextCooldown,
            'attempt' => $attempt,
        ]);
    }

    /**
     * Show forgot password form.
     */
    public function showForgotPassword()
    {
        return \Inertia\Inertia::render('Auth/ForgotPassword');
    }

    /**
     * Find account by email, username, or phone.
     */
    public function findAccount(Request $request)
    {
        $request->validate([
            'search' => ['required', 'string', 'min:3'],
        ]);

        $search = $request->search;

        \Log::info('Finding account', ['search' => $search]);

        // First try exact match
        $user = User::where('email', $search)
            ->orWhere('username', $search)
            ->orWhere('phone', $search)
            ->first();

        // If not found, try partial match
        if (!$user) {
            $user = User::where('email', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->first();
        }

        \Log::info('Account search result', [
            'search' => $search,
            'found' => $user ? true : false,
            'user_id' => $user?->id,
        ]);

        if (!$user) {
            return back()->withErrors([
                'search' => 'Akun tidak ditemukan.',
            ]);
        }

        // Store found user in session
        $request->session()->put('reset_user_id', $user->id);

        return \Inertia\Inertia::render('Auth/ForgotPassword', [
            'step' => 2,
            'account' => [
                'username' => Otp::maskUsername($user->username),
                'email' => Otp::maskEmail($user->email),
                'phone' => Otp::maskPhone($user->phone),
                'avatar' => $user->avatar_url,
            ],
        ]);
    }

    /**
     * Send OTP for password reset.
     */
    public function sendResetOtp(Request $request)
    {
        $userId = $request->session()->get('reset_user_id');
        if (!$userId) {
            return redirect()->route('password.request')->withErrors([
                'search' => 'Sesi tidak valid. Silakan cari akun Anda kembali.',
            ]);
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('password.request')->withErrors([
                'search' => 'Akun tidak ditemukan.',
            ]);
        }

        // Check cooldown
        $cooldown = Otp::canResend($user->email, Otp::TYPE_RESET);
        if (!$cooldown['can_resend']) {
            return back()->withErrors([
                'otp' => "Tunggu {$cooldown['wait_seconds']} detik sebelum mengirim ulang OTP.",
            ]);
        }

        // Generate and send OTP
        $otp = Otp::generateOtp($user->email, Otp::TYPE_RESET);

        try {
            Mail::to($user->email)->send(new OtpMail($otp->otp_code, 'reset', Otp::EXPIRY_SECONDS));
        } catch (\Exception $e) {
            return back()->withErrors([
                'email' => 'Gagal mengirim email OTP. Silakan coba lagi.',
            ]);
        }

        return \Inertia\Inertia::render('Auth/VerifyOtp', [
            'email' => $user->email,
            'type' => 'reset',
            'expirySeconds' => Otp::EXPIRY_SECONDS,
            'maskedEmail' => Otp::maskEmail($user->email),
        ]);
    }

    /**
     * Verify OTP for password reset.
     */
    public function verifyResetOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $userId = $request->session()->get('reset_user_id');
        $user = User::find($userId);

        if (!$user || $user->email !== $request->email) {
            return redirect()->route('password.request')->withErrors([
                'search' => 'Sesi tidak valid.',
            ]);
        }

        // Verify OTP
        if (!Otp::verifyOtp($request->email, $request->otp, Otp::TYPE_RESET)) {
            return back()->withErrors([
                'otp' => 'Kode OTP salah atau sudah kedaluwarsa.',
            ]);
        }

        // Mark session as OTP verified
        $request->session()->put('reset_otp_verified', true);

        return \Inertia\Inertia::render('Auth/ResetPassword', [
            'email' => $user->email,
        ]);
    }

    /**
     * Resend OTP for password reset.
     */
    public function resendResetOtp(Request $request)
    {
        $userId = $request->session()->get('reset_user_id');
        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi tidak valid.',
            ], 400);
        }

        // Check cooldown
        $cooldown = Otp::canResend($user->email, Otp::TYPE_RESET);
        if (!$cooldown['can_resend']) {
            return response()->json([
                'success' => false,
                'message' => "Tunggu {$cooldown['wait_seconds']} detik.",
                'wait_seconds' => $cooldown['wait_seconds'],
            ], 429);
        }

        // Generate and send OTP
        $otp = Otp::generateOtp($user->email, Otp::TYPE_RESET);

        try {
            Mail::to($user->email)->send(new OtpMail($otp->otp_code, 'reset', Otp::EXPIRY_SECONDS));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP berhasil dikirim ulang.',
            'expiry_seconds' => Otp::EXPIRY_SECONDS,
        ]);
    }

    /**
     * Reset password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $userId = $request->session()->get('reset_user_id');
        $otpVerified = $request->session()->get('reset_otp_verified');

        if (!$userId || !$otpVerified) {
            return redirect()->route('password.request')->withErrors([
                'email' => 'Sesi tidak valid. Silakan ulangi proses reset password.',
            ]);
        }

        $user = User::find($userId);
        if (!$user || $user->email !== $request->email) {
            return redirect()->route('password.request')->withErrors([
                'email' => 'Akun tidak ditemukan.',
            ]);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Clear session
        $request->session()->forget(['reset_user_id', 'reset_otp_verified']);

        return redirect()->route('login')
            ->with('success', 'Password berhasil diubah. Silakan login dengan password baru.');
    }

    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return \Inertia\Inertia::render('Auth/Login');
    }

    /**
     * Handle user login.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Check if user exists and is Google-only account
        $user = User::where('email', $request->email)->first();
        if ($user && $user->password === null && $user->google_id !== null) {
            return back()->withErrors([
                'email' => 'Akun ini terdaftar menggunakan Google. Silakan login dengan Google.',
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Update last login
            Auth::user()->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            return redirect()->intended('/dashboard')
                ->with('success', 'Selamat datang kembali!');
        }

        return back()->withErrors([
            'email' => 'Email atau password tidak valid.',
        ])->onlyInput('email');
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')
            ->with('success', 'Anda telah keluar dari akun.');
    }

    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle()
    {
        return \Laravel\Socialite\Facades\Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function handleGoogleCallback()
    {
        try {
            /** @var \Laravel\Socialite\Two\User $googleUser */
            $googleUser = \Laravel\Socialite\Facades\Socialite::driver('google')->user();

            // Check if user exists by Google ID
            $user = User::where('google_id', $googleUser->id)->first();

            if ($user) {
                // User already registered via Google, login
                Auth::login($user);
                $user->updateLoginInfo();
                return redirect()->intended('/dashboard')
                    ->with('success', 'Selamat datang kembali!');
            }

            // Check if email already used by a non-Google account
            $existingUser = User::where('email', $googleUser->email)->first();

            \Log::info('Google OAuth - checking existing user', [
                'email' => $googleUser->email,
                'existing_user_found' => $existingUser ? true : false,
                'has_password' => $existingUser ? !empty($existingUser->password) : null,
                'has_google_id' => $existingUser ? !empty($existingUser->google_id) : null,
            ]);

            if ($existingUser) {
                // Email already registered - check if it has a password (non-Google registration)
                if (!empty($existingUser->password)) {
                    return redirect()->route('login')->withErrors([
                        'email' => 'Email Google Anda (' . $googleUser->email . ') sudah terdaftar menggunakan password. Silakan login dengan email dan password, atau gunakan fitur Lupa Password untuk reset.',
                    ]);
                }

                // Existing account without password - update with Google ID
                $existingUser->update([
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar ?? $existingUser->avatar,
                ]);
                Auth::login($existingUser);
                $existingUser->updateLoginInfo();
                return redirect()->intended('/dashboard')
                    ->with('success', 'Selamat datang kembali!');
            }

            // Create new user with Google
            $newUser = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                'password' => null, // No password = Google only account
                'avatar' => $googleUser->avatar,
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            Auth::login($newUser);
            return redirect()->intended('/dashboard')
                ->with('success', 'Selamat datang di TNLL Explore!');

        } catch (\Exception $e) {
            \Log::error('Google OAuth failed', ['error' => $e->getMessage()]);
            return redirect()->route('login')->withErrors([
                'email' => 'Login dengan Google gagal, silakan coba lagi.',
            ]);
        }
    }
}
