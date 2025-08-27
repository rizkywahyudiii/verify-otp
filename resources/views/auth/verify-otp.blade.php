@extends('layouts.app')

@section('title', 'Verifikasi OTP')

<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 20px;
        background-color: #f5f5f5;
    }
    .container {
        max-width: 400px;
        margin: 0 auto;
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .form-group {
        margin-bottom: 20px;
    }
    label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    input[type="text"] {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
    }
    .btn {
        background-color: #007bff;
        color: white;
        padding: 12px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        width: 100%;
        font-size: 16px;
        transition: background-color 0.3s ease;
    }
    .btn:hover {
        background-color: #0056b3;
    }
    .btn-success {
        background-color: #28a745;
        font-size: 14px;
        padding: 8px 16px;
        width: auto;
    }
    .btn-success:hover {
        background-color: #218838;
    }
    .btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .error {
        color: #dc3545;
        font-size: 14px;
        margin-top: 5px;
    }
    .nav-links {
        text-align: center;
        margin-top: 20px;
    }
    .nav-links a {
        color: #007bff;
        text-decoration: none;
        margin: 0 10px;
    }
    .nav-links a:hover {
        text-decoration: underline;
    }
    .timer {
        margin-top: 10px;
        color: #666;
        font-size: 14px;
    }
    .resend-section {
        margin-top: 15px;
    }
</style>

@section('content')
    <h2 style="text-align: center; margin-bottom: 30px;">Verifikasi OTP</h2>

    <p style="text-align: center; margin-bottom: 20px; color: #666;">
        Masukkan kode OTP yang telah dikirim ke email <strong>{{ old('email', session('email')) }}</strong>.
    </p>

    <form method="POST" action="{{ route('verify-otp') }}">
        @csrf

        <!-- Hidden email field -->
        <input type="hidden" name="email" value="{{ old('email', session('email')) }}">

        <div class="form-group">
            <label for="otp_code">Kode OTP</label>
            <input type="text" id="otp_code" name="otp_code" value="{{ old('otp_code') }}"
                   maxlength="6" pattern="[0-9]{6}" placeholder="Masukkan 6 digit kode OTP" required>
            @error('otp_code')
                <div class="error">{{ $message }}</div>
            @enderror
            @error('email')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn">Verifikasi OTP</button>
    </form>

    <div class="nav-links">
        <div style="margin-top: 15px;">
            <form method="POST" action="{{ route('resend-otp') }}" id="resendForm" style="display: inline;">
                @csrf
                <input type="hidden" name="email" value="{{ session('email', old('email')) }}">
                <button type="submit" id="resendBtn" class="btn btn-success" style="font-size: 14px; padding: 8px 16px;">
                    Kirim Ulang OTP
                </button>
            </form>
            <div id="timer" style="display: none; margin-top: 10px; color: #666; font-size: 14px;">
                Tunggu <span id="countdown">30</span> detik sebelum kirim ulang
            </div>
        </div>
        <br>
        Sudah punya akun?<a href="{{ route('login.show') }}">Login</a>
        <small style="color: #666; margin-top: 10px; display: block;">
            Email salah?<a href="{{ route('register.show') }}" style="color: #007bff;">Register Ulang</a>
        </small>
        <br>
    </div>

    <script>
        let countdown = 30; // 30 seconds
        let timer = null;
        const resendBtn = document.getElementById('resendBtn');
        const resendForm = document.getElementById('resendForm');
        const timerDiv = document.getElementById('timer');
        const countdownSpan = document.getElementById('countdown');

        function startTimer() {
            resendBtn.disabled = true;
            resendBtn.style.opacity = '0.5';
            resendBtn.style.cursor = 'not-allowed';
            timerDiv.style.display = 'block';

            timer = setInterval(() => {
                countdown--;
                countdownSpan.textContent = countdown;

                if (countdown <= 0) {
                    clearInterval(timer);
                    resendBtn.disabled = false;
                    resendBtn.style.opacity = '1';
                    resendBtn.style.cursor = 'pointer';
                    timerDiv.style.display = 'none';
                    countdown = 30;
                }
            }, 1000);
        }

        // Start timer when page loads
        startTimer();

        // Handle form submission
        resendForm.addEventListener('submit', function(e) {
            if (resendBtn.disabled) {
                e.preventDefault();
                return false;
            }
            startTimer();
        });
    </script>
@endsection
