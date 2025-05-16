<body style="background-color: #f3f3f3;">
    <div>
        Hi <strong>{{ $sellerName }}</strong> <br><br>

        Kindly click on the link below to complete your registration <br> <br>

        <a href="{{ route('shop.verify-seller', ['token' => $token]) }}" style="color: #b61c1e; text-decoration: none;">
            {{ route('shop.verify-seller', ['token' => $token]) }}
        </a>
        <br><br>
        Thank You <br>
        <strong>{{ config('app.name') }}

    </div>
</body>
