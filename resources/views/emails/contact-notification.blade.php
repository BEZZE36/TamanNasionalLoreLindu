<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Kontak Baru</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f7f7f7;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f7f7f7; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                    {{-- Header --}}
                    <tr>
                        <td style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 40px 40px 35px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td>
                                        <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #10b981, #14b8a6); border-radius: 12px; display: inline-block; text-align: center; line-height: 50px;">
                                            <span style="font-size: 24px;">✉️</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-top: 20px;">
                                        <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 600;">Pesan Kontak Baru</h1>
                                        <p style="margin: 8px 0 0; color: #94a3b8; font-size: 14px;">Dari formulir kontak TNLL</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    {{-- Content --}}
                    <tr>
                        <td style="padding: 40px;">
                            {{-- Badge --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-bottom: 30px;">
                                <tr>
                                    <td>
                                        <span style="display: inline-block; padding: 6px 14px; background-color: #ecfdf5; color: #059669; font-size: 12px; font-weight: 600; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px;">{{ $data['subject'] }}</span>
                                    </td>
                                </tr>
                            </table>
                            
                            {{-- Sender Info --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f8fafc; border-radius: 12px; padding: 24px; margin-bottom: 24px;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                            {{-- Name --}}
                                            <tr>
                                                <td style="padding-bottom: 16px; border-bottom: 1px solid #e2e8f0;">
                                                    <p style="margin: 0 0 4px; color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Nama</p>
                                                    <p style="margin: 0; color: #1e293b; font-size: 16px; font-weight: 600;">{{ $data['name'] }}</p>
                                                </td>
                                            </tr>
                                            {{-- Email --}}
                                            <tr>
                                                <td style="padding: 16px 0; border-bottom: 1px solid #e2e8f0;">
                                                    <p style="margin: 0 0 4px; color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Email</p>
                                                    <p style="margin: 0;"><a href="mailto:{{ $data['email'] }}" style="color: #3b82f6; font-size: 16px; text-decoration: none;">{{ $data['email'] }}</a></p>
                                                </td>
                                            </tr>
                                            {{-- Phone --}}
                                            @if(!empty($data['phone']))
                                            <tr>
                                                <td style="padding-top: 16px;">
                                                    <p style="margin: 0 0 4px; color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">WhatsApp</p>
                                                    <p style="margin: 0;"><a href="https://wa.me/{{ preg_replace('/[^\d]/', '', $data['phone']) }}" style="color: #22c55e; font-size: 16px; text-decoration: none;">{{ $data['phone'] }}</a></p>
                                                </td>
                                            </tr>
                                            @endif
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            {{-- Message --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td>
                                        <p style="margin: 0 0 12px; color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Pesan</p>
                                        <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px;">
                                            <p style="margin: 0; color: #334155; font-size: 15px; line-height: 1.7; white-space: pre-wrap;">{{ $data['message'] }}</p>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            
                            {{-- CTA Button --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top: 32px;">
                                <tr>
                                    <td align="center">
                                        <a href="mailto:{{ $data['email'] }}" style="display: inline-block; padding: 14px 32px; background: linear-gradient(135deg, #10b981, #14b8a6); color: #ffffff; font-size: 14px; font-weight: 600; text-decoration: none; border-radius: 10px;">Balas Email</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    {{-- Footer --}}
                    <tr>
                        <td style="background-color: #f8fafc; padding: 24px 40px; text-align: center; border-top: 1px solid #e2e8f0;">
                            <p style="margin: 0; color: #94a3b8; font-size: 13px;">
                                Email ini dikirim otomatis dari formulir kontak<br>
                                <strong style="color: #64748b;">Taman Nasional Lore Lindu</strong>
                            </p>
                            <p style="margin: 16px 0 0; color: #cbd5e1; font-size: 11px;">
                                {{ now()->format('d M Y, H:i') }} WITA
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
