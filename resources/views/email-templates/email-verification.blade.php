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
                                Thank you for showing interest in {{ config('app.name') }}, we apprecaite you. <br><br>

                                To verify your account, kindly click on the link below to complete your registration. <br/><br><br/><br>
                                <a href="{{ route('customer.auth.verify-customer', ['code' => $token]) }}" style="background: red; color: #fff; padding: 15px 25px; border: 1px solid red; margin-bottom: 10px; text-decoration: none; font-weight: bold; font-size: 20px; border-radius: 7px;">
                                    Verify Account
                                </a><br/><br><br/><br>
                                Alternatively, you can click or copy the link below to activate your account <br><br>
                                <span style="font-size: 16px">
                                    <a href="{{ route('customer.auth.verify-customer', ['code' => $token]) }}" style="text-decoration: none;">
                                        {{ route('customer.auth.verify-customer', ['code' => $token]) }}
                                    </a>    
                                </span>

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
