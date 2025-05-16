<body style="background-color: #f3f3f3;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 2%">
        <tr>
            <td align="center" bgcolor="#f3f3f3">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                    <tr>
                        <td align="left" bgcolor="#ffffff"
                            style="padding: 24px; font-family: Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
                            <p style="margin: 0;">
                                Dear <b>{{ $user->f_name . ' ' . $user->l_name }}, </b> <br>
                                We are thrilled to inform you that your business upgrade request has been successfully
                                approved! Our team has carefully reviewed your submissions, and we're confident that
                                these updates will elevate your experience with {{ ucfirst(config('app.name')) }}.
                                <br><br>

                                <strong>Previous Upgrade Package:</strong> <br>

                            <ul>
                                <li> Name: {{ $upgradeData['old_package']['name'] }}</li>
                                <li>Rank Color: {{ $upgradeData['old_package']['color'] }}</li>
                                <li>Product Limit: {{ number_format($upgradeData['old_package']['product_limit']) }}
                                </li>
                            </ul>

                            <br>

                            <strong>New Upgrade Package:</strong> <br>

                            <ul>
                                <li> Name: {{ $upgradeData['new_package']['name'] }}</li>
                                <li>Rank Color: {{ $upgradeData['new_package']['color'] }}</li>
                                <li>Product Limit: {{ number_format($upgradeData['new_package']['product_limit']) }}</li>
                            </ul>

                            <br><br>

                            Our support team is here to assist you with any questions or concerns. Feel free to reach
                            out to {{ strtolower(config('app.support_email')) }}. We also value your feedback; your
                            insights help us
                            continuously improve our products.

                            We appreciate your patience and collaboration throughout this process. Thank you for
                            choosing {{ ucfirst(config('app.name')) }}, and we hope you enjoy the enhanced experience
                            with the upgraded
                            package.
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
