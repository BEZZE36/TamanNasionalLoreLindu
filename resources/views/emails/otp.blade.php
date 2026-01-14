<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $type === 'register' ? 'Kode OTP Pendaftaran' : 'Kode OTP Reset Password' }}</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #0a0f1a;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #0a0f1a;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 500px; margin: 0 auto;">
                    <!-- Logo -->
                    <tr>
                        <td style="text-align: center; padding-bottom: 30px;">
                            <img src="{{ config('app.url') }}/assets/logo.png" alt="TNLL Explore" style="width: 60px; height: 60px;">
                            <h1 style="color: #10b981; font-size: 24px; margin: 15px 0 0 0;">TNLL Explore</h1>
                        </td>
                    </tr>
                    
                    <!-- Main Card -->
                    <tr>
                        <td>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(20, 184, 166, 0.05) 100%); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 16px;">
                                <tr>
                                    <td style="padding: 40px 30px;">
                                        <!-- Title -->
                                        <h2 style="color: #ffffff; font-size: 22px; margin: 0 0 10px 0; text-align: center;">
                                            {{ $type === 'register' ? 'Verifikasi Email Anda' : 'Reset Password' }}
                                        </h2>
                                        <p style="color: #9ca3af; font-size: 14px; margin: 0 0 30px 0; text-align: center; line-height: 1.6;">
                                            {{ $type === 'register' ? 'Gunakan kode OTP berikut untuk menyelesaikan pendaftaran akun Anda.' : 'Gunakan kode OTP berikut untuk mereset password Anda.' }}
                                        </p>
                                        
                                        <!-- OTP Code Box -->
                                        <div style="background: rgba(16, 185, 129, 0.1); border: 2px dashed #10b981; border-radius: 12px; padding: 25px; text-align: center; margin-bottom: 25px;">
                                            <p style="color: #9ca3af; font-size: 12px; margin: 0 0 10px 0; text-transform: uppercase; letter-spacing: 1px;">Kode OTP Anda</p>
                                            <h1 style="color: #10b981; font-size: 42px; font-weight: bold; letter-spacing: 8px; margin: 0; font-family: 'Courier New', monospace;">{{ $otpCode }}</h1>
                                        </div>
                                        
                                        <!-- Warning -->
                                        <div style="background: rgba(245, 158, 11, 0.1); border-left: 4px solid #f59e0b; border-radius: 0 8px 8px 0; padding: 15px; margin-bottom: 25px;">
                                            <p style="color: #fbbf24; font-size: 13px; margin: 0;">
                                                ⏱️ <strong>Kode ini akan kedaluwarsa dalam {{ $expirySeconds }} detik.</strong>
                                            </p>
                                        </div>
                                        
                                        <!-- Security Note -->
                                        <p style="color: #6b7280; font-size: 12px; margin: 0; text-align: center; line-height: 1.6;">
                                            Jika Anda tidak meminta kode ini, abaikan email ini. Jangan bagikan kode OTP ini kepada siapa pun.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="text-align: center; padding-top: 30px;">
                            <p style="color: #4b5563; font-size: 12px; margin: 0;">
                                &copy; {{ date('Y') }} TNLL Explore. Platform E-Ticketing Resmi Taman Nasional Lore Lindu.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
