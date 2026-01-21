<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #f9fafb;
            border-radius: 8px;
            padding: 30px;
            border: 1px solid #e5e7eb;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #1f2937;
            margin: 0;
            font-size: 24px;
        }
        .content {
            background-color: white;
            padding: 25px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .button {
            display: inline-block;
            padding: 14px 32px;
            background-color: #4F46E5;
            color: white !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #4338CA;
        }
        .footer {
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            margin-top: 20px;
        }
        .link-box {
            background-color: #f3f4f6;
            padding: 15px;
            border-radius: 4px;
            word-break: break-all;
            margin: 15px 0;
        }
        .warning {
            color: #dc2626;
            font-size: 14px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PhotoIn</h1>
        </div>
        
        <div class="content">
            <h2>Halo, {{ $nama }}!</h2>
            
            <p>Terima kasih telah mendaftar di <strong>Sistem Rental Kamera</strong>.</p>
            
            <p>Untuk mengaktifkan akun Anda, silakan klik tombol di bawah ini:</p>
            
            <div style="text-align: center;">
                <a href="{{ $url }}" class="button">
                    Verifikasi Email Saya
                </a>
            </div>
            
            <p>Atau copy link berikut ke browser Anda:</p>
            
            <div class="link-box">
                <a href="{{ $url }}">{{ $url }}</a>
            </div>
            
            <p class="warning">
                ⚠️ Link verifikasi ini akan kadaluarsa dalam <strong>24 jam</strong>.
            </p>
            
            <p>Jika Anda tidak mendaftar di sistem kami, abaikan email ini.</p>
        </div>
        
        <div class="footer">
            <p>Email ini dikirim otomatis, mohon tidak membalas email ini.</p>
            <p>&copy; {{ date('Y') }} Rental Kamera. All rights reserved.</p>
        </div>
    </div>
</body>
</html>