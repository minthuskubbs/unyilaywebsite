<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; background: #f5f5f5; padding: 40px 20px; margin: 0;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <table role="presentation" width="480" cellpadding="0" cellspacing="0" style="background: #ffffff; border-radius: 12px; padding: 40px;">
                    <tr>
                        <td>
                            <h1 style="font-size: 20px; margin: 0 0 16px; color: #1a1a1a;">Reset your password</h1>
                            <p style="font-size: 15px; line-height: 1.6; color: #444; margin: 0 0 24px;">
                                Hi {{ $name }},<br><br>
                                We received a request to reset the password for your U Nyi Lay Silver Shop account. Click the button below to choose a new password. This link expires in 60 minutes.
                            </p>
                            <p style="margin: 0 0 24px;">
                                <a href="{{ $resetUrl }}" style="display: inline-block; background: #1a1a1a; color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 999px; font-size: 15px;">Reset Password</a>
                            </p>
                            <p style="font-size: 13px; line-height: 1.6; color: #888; margin: 0;">
                                If you didn't request this, you can safely ignore this email — your password won't change.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
