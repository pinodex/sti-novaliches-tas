@component('mail::message')
# Hello {{ $recipient->name }}

A password reset has been requested. Please click the button below to set your new password.

@component('mail::button', [
    'url' => $url,
    'color' => 'blue'
])

    Reset Password

@endcomponent

The link will be available until {{ $expiry }}

@component('mail::subcopy')
    If you’re having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser: [{{ $url }}]({{ $url }})<br />

    This is an automatically generated email &mdash; please do not reply. If you have questions, please contact your department head or your academic head.
@endcomponent
@endcomponent
