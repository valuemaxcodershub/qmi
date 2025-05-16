<body style="background-color: #f3f3f3;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 2%">
        <tr>
            <td align="center" bgcolor="#f3f3f3">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                    <tr>
                        <td align="left" bgcolor="#ffffff"
                            style="padding: 24px; font-family: Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
                            <p style="margin: 0;">
                                Hey <b>{{ $customerName }}, </b> <br>
                                You recently requested for a reset password link. Kindly click the link below to
                                start your reset password process
                                <br><br>
                                <a href="{{ $url }}"
                                    style="background: #25f609; color: #fff; text-decoration: none; padding: 10px 30px 10px 30px;">
                                    <b>Click Here To Reset Password</b>
                                </a>
                                <br><br>

                                <span>Having issue clicking the link ? Copy the link below</span> <br>
                            <div style="background: #f3f3f3; padding: 15px">
                                <br>{{ $url }}
                            </div>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td align="center" bgcolor="#f3f3f3" style="padding: 24px;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                    <tr>
                        <td align="center" bgcolor="#f3f3f3"
                            style="padding: 12px 24px; font-family: Helvetica, Arial, sans-serif; font-size: 14px; line-height: 20px; color: #666;">
                            <p style="color: #000; margin: 0;">If you do not request for a password reset, you can
                                ignore or change your password to ensure maximum security of your account.</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
