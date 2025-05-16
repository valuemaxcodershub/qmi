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
                                We hope this message finds you well. We wanted to provide you with an update on the
                                status of your recent upgrade request. Our team has diligently reviewed the details, and
                                we appreciate your patience throughout this process.
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
                                <li>Product Limit: {{ number_format($upgradeData['new_package']['product_limit']) }}
                                </li>
                            </ul>

                            <br><br>

                            Our team is currently in the final stages of reviewing the new upgrade to ensure it meets
                            our quality standards and aligns with your expectations. We understand the importance of
                            this upgrade to you and are committed to delivering a seamless and enhanced experience.
                            <br><br>
                            Once the review process is complete, we will promptly notify you of the approval status and
                            provide information on the next steps. If there are any additional details you would like to
                            share or inquire about during this review period, please feel free to reach out to our
                            support team at {{ strtolower(config('app.support_email')) }}
                            <br>
                            We appreciate your continued trust in our service and thank you for choosing <strong>{{ config('app.name') }}</strong> for your upgrade needs.
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
