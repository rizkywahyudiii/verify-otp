<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\OTPCode;
use Illuminate\Console\Command;

class GenerateOTP extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'otp:generate {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate OTP for testing purposes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User dengan email {$email} tidak ditemukan.");
            return 1;
        }

        // Generate OTP
        $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Save OTP to database
        OTPCode::create([
            'user_id' => $user->id,
            'otp_code' => $otpCode,
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->info("OTP berhasil dibuat untuk user: {$user->name}");
        $this->info("Email: {$user->email}");
        $this->info("OTP Code: {$otpCode}");
        $this->info("Expires at: " . now()->addMinutes(10)->format('Y-m-d H:i:s'));

        return 0;
    }
}
