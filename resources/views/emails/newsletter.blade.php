<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
    <style>
        /* Reset styles */
        body,
        table,
        td,
        p,
        a,
        li,
        blockquote {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        table,
        td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        img {
            -ms-interpolation-mode: bicubic;
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
        }

        body {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }

        /* Base styles */
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background-color: #f8fafc;
            color: #334155;
            line-height: 1.6;
        }

        /* Container */
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }

        /* Header */
        .email-header {
            background: linear-gradient(135deg, #059669 0%, #0d9488 50%, #0891b2 100%);
            padding: 40px 30px;
            text-align: center;
        }

        .logo-text {
            font-size: 32px;
            font-weight: 800;
            color: #ffffff;
            text-decoration: none;
            letter-spacing: -0.5px;
        }

        .logo-subtitle {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.85);
            margin-top: 8px;
        }

        /* Content */
        .email-content {
            padding: 40px 30px;
        }

        .greeting {
            font-size: 18px;
            color: #1e293b;
            margin-bottom: 20px;
        }

        .content-body {
            font-size: 16px;
            color: #475569;
            line-height: 1.8;
        }

        .content-body h1,
        .content-body h2,
        .content-body h3 {
            color: #1e293b;
            margin-top: 24px;
            margin-bottom: 12px;
        }

        .content-body p {
            margin-bottom: 16px;
        }

        .content-body a {
            color: #059669;
            text-decoration: underline;
        }

        .content-body img {
            max-width: 100%;
            height: auto;
            border-radius: 12px;
            margin: 16px 0;
        }

        .content-body ul,
        .content-body ol {
            padding-left: 24px;
            margin-bottom: 16px;
        }

        .content-body li {
            margin-bottom: 8px;
        }

        /* CTA Button */
        .cta-button {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #059669 0%, #0d9488 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            margin: 20px 0;
            text-align: center;
        }

        /* Divider */
        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
            margin: 30px 0;
        }

        /* Footer */
        .email-footer {
            background-color: #f8fafc;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }

        .footer-text {
            font-size: 13px;
            color: #94a3b8;
            margin-bottom: 16px;
        }

        .footer-links {
            margin-bottom: 16px;
        }

        .footer-link {
            color: #64748b;
            text-decoration: none;
            font-size: 13px;
            margin: 0 10px;
        }

        .footer-link:hover {
            color: #059669;
        }

        .unsubscribe-text {
            font-size: 12px;
            color: #94a3b8;
        }

        .unsubscribe-link {
            color: #64748b;
            text-decoration: underline;
        }

        .social-links {
            margin-bottom: 16px;
        }

        .social-link {
            display: inline-block;
            margin: 0 8px;
        }

        /* Responsive */
        @media screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
            }

            .email-header {
                padding: 30px 20px;
            }

            .email-content {
                padding: 30px 20px;
            }

            .logo-text {
                font-size: 26px;
            }
        }
    </style>
</head>

<body>
    <center style="width: 100%; background-color: #f8fafc; padding: 40px 0;">
        <!-- Email Container -->
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" class="email-container"
            style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">

            <!-- Header -->
            <tr>
                <td class="email-header"
                    style="background: linear-gradient(135deg, #059669 0%, #0d9488 50%, #0891b2 100%); padding: 40px 30px; text-align: center;">
                    <a href="{{ url('/') }}" class="logo-text"
                        style="font-size: 32px; font-weight: 800; color: #ffffff; text-decoration: none;">
                        TNLL Explore
                    </a>
                    <p class="logo-subtitle"
                        style="font-size: 14px; color: rgba(255, 255, 255, 0.85); margin-top: 8px; margin-bottom: 0;">
                        Jelajahi Keindahan Taman Nasional Lore Lindu
                    </p>
                </td>
            </tr>

            <!-- Content -->
            <tr>
                <td class="email-content" style="padding: 40px 30px;">
                    @if($recipientName)
                        <p class="greeting" style="font-size: 18px; color: #1e293b; margin-bottom: 20px;">
                            Halo <strong>{{ $recipientName }}</strong>,
                        </p>
                    @else
                        <p class="greeting" style="font-size: 18px; color: #1e293b; margin-bottom: 20px;">
                            Halo Sobat TNLL,
                        </p>
                    @endif

                    <div class="content-body" style="font-size: 16px; color: #475569; line-height: 1.8;">
                        {!! $content !!}
                    </div>

                    <div class="divider"
                        style="height: 1px; background: linear-gradient(90deg, transparent, #e2e8f0, transparent); margin: 30px 0;">
                    </div>

                    <p style="font-size: 14px; color: #64748b; text-align: center;">
                        Terima kasih telah menjadi bagian dari komunitas TNLL Explore!
                    </p>

                    <p style="text-align: center; margin-top: 20px;">
                        <a href="{{ url('/') }}" class="cta-button"
                            style="display: inline-block; padding: 14px 32px; background: linear-gradient(135deg, #059669 0%, #0d9488 100%); color: #ffffff; text-decoration: none; border-radius: 12px; font-weight: 600; font-size: 16px;">
                            Kunjungi Website
                        </a>
                    </p>
                </td>
            </tr>

            <!-- Footer -->
            <tr>
                <td class="email-footer"
                    style="background-color: #f8fafc; padding: 30px; text-align: center; border-top: 1px solid #e2e8f0;">
                    <p class="footer-text" style="font-size: 13px; color: #94a3b8; margin-bottom: 16px;">
                        TNLL Explore - Taman Nasional Lore Lindu<br>
                        Sulawesi Tengah, Indonesia
                    </p>

                    <div class="footer-links" style="margin-bottom: 16px;">
                        <a href="{{ url('/') }}" class="footer-link"
                            style="color: #64748b; text-decoration: none; font-size: 13px; margin: 0 10px;">Website</a>
                        <a href="{{ url('/destinations') }}" class="footer-link"
                            style="color: #64748b; text-decoration: none; font-size: 13px; margin: 0 10px;">Destinasi</a>
                        <a href="{{ url('/articles') }}" class="footer-link"
                            style="color: #64748b; text-decoration: none; font-size: 13px; margin: 0 10px;">Artikel</a>
                        <a href="{{ url('/contact') }}" class="footer-link"
                            style="color: #64748b; text-decoration: none; font-size: 13px; margin: 0 10px;">Kontak</a>
                    </div>

                    <p class="unsubscribe-text" style="font-size: 12px; color: #94a3b8;">
                        Email ini dikirim ke <strong>{{ $recipientEmail }}</strong><br>
                        Tidak ingin menerima email ini lagi?
                        <a href="{{ $unsubscribeUrl }}" class="unsubscribe-link"
                            style="color: #64748b; text-decoration: underline;">Berhenti Berlangganan</a>
                    </p>

                    <p style="font-size: 11px; color: #cbd5e1; margin-top: 16px;">
                        © {{ date('Y') }} TNLL Explore. All rights reserved.
                    </p>
                </td>
            </tr>

        </table>
    </center>
</body>

</html>