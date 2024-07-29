@component('mail::message')
    # Hello {{ $username }},

    Thank you for registering with Made Solution. To complete your registration, please verify your email address by clicking the button below.

    @component('mail::button', ['url' => $verificationUrl])
        Verify Account
    @endcomponent

    Or you can add this code to complete the verification: <strong>{{ $emailVerificationCode }}</strong>

    If you did not create an account, no further action is required.

    Thank you for using Made Solution!

    Regards,<br>
    {{ config('app.name') }}
@endcomponent
