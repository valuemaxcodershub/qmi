<body style="background-color: #f3f3f3;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 2%">
        <tr>
            <td align="center" bgcolor="#f3f3f3">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                    <tr>
                        <td align="left" bgcolor="#ffffff"
                            style="padding: 24px; font-family: Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
                            <p style="margin: 0;">
                                Hey <b>{{ ucwords($seller->f_name . ' ' . $seller->l_name) }}, </b> <br><br>
                                Thank you for showing interest in upgrading your account with us to enjoy more of our
                                amazing features. We are thrilled to inform you that we have received your request, and
                                our team is working diligently to process it.
                                <br><br>
                                Your commitment to enhancing your experience with our services means a lot to us, and we
                                appreciate your trust in {{ config('app.name') }}. We understand that you're eager to access
                                the additional features and benefits that come with the upgraded account.
                                <br><br>
                                Rest assured, our team is dedicated to making this process smooth and efficient for you.
                                If there are any further details required or updates on the status of your request, we
                                will keep you informed promptly.
                                <br><br>
                                If you have any questions or concerns, please feel free to reach out to our customer
                                support team at {{ config('app.support_email') }}.
                                <br><br>
                                Best Regards,
                                <br>
                                The {{ config('app.name') }} Team
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
