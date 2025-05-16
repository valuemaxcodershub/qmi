<body style="background-color: #f3f3f3;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 2%">
        <tr>
            <td align="center" bgcolor="#f3f3f3">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                    <tr>
                        <td align="left" bgcolor="#ffffff"
                            style="padding: 24px; font-family: Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
                            <p style="margin: 0;">
                                Hi <b>{{ $clientName }}, </b> <br><br>

                                You made an attempt to login to your {{ ucfirst($userType) }} account.
                                Your 6-Digit verification code is <strong>{{ $otpCode }}</strong>. <br><br>

                                This code was sent to you to verify your login. <br>

                                If you didn't initiate this request, kindly login now and change your password

                                <br><br>
                                Best regards, <br>
                                {{ config('app.name') }} Team
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

    </table>
</body>
