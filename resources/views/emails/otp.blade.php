<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode OTP Verifikasi</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #007bff, #0056d2);
            color: #fff;
            padding: 25px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
        }
        .content {
            padding: 30px;
        }
        .content p {
            margin: 12px 0;
            font-size: 15px;
            color: #444;
        }
        .otp-code {
            background: #007bff;
            color: #fff;
            font-size: 30px;
            font-weight: bold;
            padding: 20px;
            text-align: center;
            border-radius: 10px;
            margin: 25px 0;
            letter-spacing: 6px;
            box-shadow: 0 3px 8px rgba(0,123,255,0.3);
        }
        ul {
            margin: 15px 0;
            padding-left: 20px;
            color: #555;
            font-size: 14px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 13px;
            color: #777;
            background: #f1f3f6;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Verifikasi Akun</h1>
        </div>

        <div class="content">
            <p>Halo <strong>{{ $userName }}</strong>,</p>

            <p>Terima kasih telah mendaftar. Berikut adalah kode OTP untuk verifikasi akun Anda:</p>

            <div class="otp-code">
                {{ $otpCode }}
            </div>

            <p><strong>Penting:</strong></p>
            <ul>
                <li>Kode OTP ini berlaku selama 10 menit</li>
                <li>Jangan bagikan kode ini kepada siapapun</li>
                <li>Jika Anda tidak merasa mendaftar, abaikan email ini</li>
            </ul>

            <p>Silakan masukkan kode di atas pada halaman verifikasi OTP untuk menyelesaikan proses pendaftaran.</p>

            <p>Terima kasih,<br>
            Tim Support</p>
        </div>

        <div class="footer">
            <p>Email ini dikirim secara otomatis, mohon tidak membalas email ini.</p>
        </div>
    </div>
</body>
</html>
