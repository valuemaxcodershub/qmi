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
                                We hope this message finds you well. Thank you for considering
                                {{ ucfirst(config('app.name')) }} for your business needs. We appreciate your trust in
                                our services.
                                <br>
                                We regret to inform you that your recent request for a business package upgrade has been
                                declined. We understand that this decision may be disappointing, and we want to provide
                                you with some reasons for the decision.
                                <br><br>

                            <div style="background: #f3f3f3; padding: 15px">
                                {{ $messageContent }}
                            </div>
                            <br><br>
                            We understand the importance of meeting our customers' needs and would be happy to discuss
                            alternative options that may better suit your current circumstances. Our support team is
                            here to assist you with any questions or concerns. Feel free to reach out to
                            {{ strtolower(config('app.support_email')) }}. We also value your feedback; your insights help us continuously improve our products.
                            <br><br>
                            Thank you for choosing {{ ucfirst(config('app.name')) }}.
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
