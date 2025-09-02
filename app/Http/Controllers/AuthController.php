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
use Illuminate\Support\Facades\Log;

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
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).+$/',
            ],
        ], [
            'password.min' => 'Password minimal 8 karakter.',
            'password.regex' => 'Password harus mengandung huruf besar, huruf kecil, angka, dan simbol.',
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

        Log::info('OTP_SENT', [
            'context' => 'register',
            'email' => $user->email,
            'user_id' => $user->id,
            'expires_at' => now()->addMinutes(10)->toDateTimeString(),
        ]);

        return redirect()->route('verify-otp.show')
            ->with('success', 'Silakan cek email Anda untuk mendapatkan kode OTP.')
            ->with('email', $request->email); // Pass email to next page
    }

    /**
     * Show the OTP verification form.
     */
    public function showVerifyOTP()
    {
        // Ambil email dari session atau old input (flash) agar tetap di halaman saat error
        $email = session('email') ?? old('email');
        if (!$email) {
            return redirect()->route('register.show')
                ->with('error', 'Silakan register atau login terlebih dahulu untuk mendapatkan kode OTP.');
        }

        // Pastikan user ada (untuk mencegah akses dengan email non-eksisten)
        $user = User::where('email', $email)->first();
        if (!$user) {
            return redirect()->route('register.show')
                ->with('error', 'Email tidak ditemukan. Silakan register terlebih dahulu.');
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

        // Cek OTP dan alasan kegagalan (invalid vs expired)
        $otpAny = OTPCode::where('user_id', $user->id)
            ->where('otp_code', $request->otp_code)
            ->latest()
            ->first();

        if (!$otpAny) {
            Log::warning('OTP_VERIFY_FAILED', [
                'email' => $request->email,
                'reason' => 'invalid',
            ]);
            return back()->withErrors(['otp_code' => 'Kode OTP tidak valid atau sudah expired.'])
                ->with('email', $request->email);
        }

        if ($otpAny->expires_at <= now()) {
            Log::warning('OTP_VERIFY_FAILED', [
                'email' => $request->email,
                'reason' => 'expired',
            ]);
            return back()->withErrors(['otp_code' => 'Kode OTP tidak valid atau sudah expired.'])
                ->with('email', $request->email);
        }

        // Jika ini dari proses registrasi, tandai terverifikasi jika belum
        if (!$user->is_verified) {
            $user->markAsVerified();
        }

        // Delete the used OTP
        $otpAny->delete();

        // Auto login user setelah OTP valid (untuk login maupun registrasi)
        Auth::login($user);
        $request->session()->regenerate();

        Log::info('OTP_VERIFY_SUCCESS', [
            'email' => $request->email,
            'user_id' => $user->id,
            'verified_at' => now()->toDateTimeString(),
        ]);

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

        // Validasi kredensial tanpa login terlebih dahulu
        if (!Auth::validate($credentials)) {
            return back()->withErrors([
                'email' => 'Email atau password salah.',
            ]);
        }

        // Ambil user terkait
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors([
                'email' => 'Email tidak terdaftar.',
            ]);
        }

        // Generate dan kirim OTP setiap login
        // Rate limit: tunggu 30 detik jika baru minta
        $recentOTP = OTPCode::where('user_id', $user->id)
            ->where('created_at', '>', now()->subSeconds(30))
            ->first();

        if (!$recentOTP) {
            // Hapus OTP lama
            OTPCode::where('user_id', $user->id)->delete();

            $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            OTPCode::create([
                'user_id' => $user->id,
                'otp_code' => $otpCode,
                'expires_at' => now()->addMinutes(10),
            ]);

            Mail::to($user->email)->send(new OTPMail($otpCode, $user->name));

            Log::info('OTP_SENT', [
                'context' => 'login',
                'email' => $user->email,
                'user_id' => $user->id,
                'expires_at' => now()->addMinutes(10)->toDateTimeString(),
            ]);
        }

        // Arahkan ke halaman verifikasi OTP, simpan email ke session
        return redirect()->route('verify-otp.show')
            ->with('success', 'Kode OTP telah dikirim ke email Anda. Silakan masukkan untuk melanjutkan login.')
            ->with('email', $request->email);
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

        // Hapus pembatasan: izinkan resend OTP baik saat register maupun login

        // Check if there's a recent OTP (within 30 seconds)
        $recentOTP = OTPCode::where('user_id', $user->id)
            ->where('created_at', '>', now()->subSeconds(30))
            ->first();

        if ($recentOTP) {
            Log::notice('OTP_RESEND_RATE_LIMITED', [
                'email' => $request->email,
                'cooldown_seconds' => 30,
            ]);
            return back()->withErrors(['email' => 'Harap tunggu 30 detik sebelum meminta OTP baru.'])->withInput();
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

        Log::info('OTP_SENT', [
            'context' => 'resend',
            'email' => $user->email,
            'user_id' => $user->id,
            'expires_at' => now()->addMinutes(10)->toDateTimeString(),
        ]);

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
