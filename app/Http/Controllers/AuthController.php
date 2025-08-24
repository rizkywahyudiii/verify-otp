<?php

namespace App\Http\Controllers;

use App\Mail\OTPMail;
use App\Models\OTPCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle user registration.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_verified' => false,
        ]);

        // Generate OTP
        $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Save OTP to database
        OTPCode::create([
            'user_id' => $user->id,
            'otp_code' => $otpCode,
            'expires_at' => now()->addMinutes(10), // OTP expires in 10 minutes
        ]);

        // Send OTP email
        Mail::to($user->email)->send(new OTPMail($otpCode, $user->name));

        return redirect()->route('verify-otp.show')
            ->with('success', 'Registrasi berhasil! Silakan cek email Anda untuk kode OTP.')
            ->with('email', $request->email); // Pass email to next page
    }

    /**
     * Show the OTP verification form.
     */
    public function showVerifyOTP()
    {
        // Check if email exists in session
        $email = session('email');
        if (!$email) {
            return redirect()->route('register.show')
                ->with('error', 'Silakan register terlebih dahulu untuk mendapatkan kode OTP.');
        }

        // Check if user is already verified
        $user = User::where('email', $email)->first();
        if (!$user) {
            return redirect()->route('register.show')
                ->with('error', 'Email tidak ditemukan. Silakan register terlebih dahulu.');
        }

        if ($user->is_verified) {
            return redirect()->route('login.show')
                ->with('error', 'Akun Anda sudah diverifikasi. Silakan login.');
        }

        return view('auth.verify-otp');
    }

    /**
     * Handle OTP verification.
     */
    public function verifyOTP(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp_code' => 'required|string|size:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return redirect()->route('register.show')
                ->with('error', 'Email tidak ditemukan. Silakan register terlebih dahulu.');
        }

        $otpCode = OTPCode::where('user_id', $user->id)
            ->where('otp_code', $request->otp_code)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otpCode) {
            return back()->withErrors(['otp_code' => 'Kode OTP tidak valid atau sudah expired.'])
                ->with('email', $request->email);
        }

        // Mark user as verified
        $user->markAsVerified();

        // Delete the used OTP
        $otpCode->delete();

        // Auto login user after successful verification
        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', 'Verifikasi berhasil! Selamat datang di dashboard.');
    }

    /**
     * Show the login form.
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle user login.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if (!$user->is_verified) {
                Auth::logout();
                return redirect()->route('verify-otp.show')
                    ->with('error', 'Akun Anda belum diverifikasi. Silakan verifikasi terlebih dahulu.')
                    ->with('email', $request->email); // Pass email for auto-fill
            }

            $request->session()->regenerate();
            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ]);
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.show');
    }

    /**
     * Handle resend OTP.
     */
    public function resendOTP(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return redirect()->route('register.show')
                ->with('error', 'Email tidak ditemukan. Silakan register terlebih dahulu.');
        }

        // Check if user is already verified
        if ($user->is_verified) {
            return redirect()->route('login.show')
                ->with('error', 'Akun Anda sudah diverifikasi. Silakan login.');
        }

        // Check if there's a recent OTP (within 2 minutes)
        $recentOTP = OTPCode::where('user_id', $user->id)
            ->where('created_at', '>', now()->subMinutes(2))
            ->first();

        if ($recentOTP) {
            return back()->withErrors(['email' => 'Harap tunggu 2 menit sebelum meminta OTP baru.']);
        }

        // Delete old OTP codes
        OTPCode::where('user_id', $user->id)->delete();

        // Generate new OTP
        $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Save new OTP to database
        OTPCode::create([
            'user_id' => $user->id,
            'otp_code' => $otpCode,
            'expires_at' => now()->addMinutes(10),
        ]);

        // Send new OTP email
        Mail::to($user->email)->send(new OTPMail($otpCode, $user->name));

        return back()->with('success', 'OTP baru telah dikirim ke email Anda.')
            ->with('email', $request->email);
    }

    /**
     * Handle user account deletion.
     */
    public function deleteAccount(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = Auth::user();

        // Verify password before deletion
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Password salah.']);
        }

        // Delete all OTP codes for this user
        OTPCode::where('user_id', $user->id)->delete();

        // Delete the user record
        User::where('id', $user->id)->delete();

        // Logout user
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.show')
            ->with('success', 'Akun Anda berhasil dihapus.');
    }
}
