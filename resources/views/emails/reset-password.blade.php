@extends ('emails.template')

@section('mail_content')
    Hi {{ $name }},
    <br>
    You are receiving this email because we received a password reset request for your account.
    <br>
    If you did not request a password reset, no further action is required.
    <br>
@endsection

@section('mail_url', url('api/password/reset', urlencode($token)))

@section('mail_button', 'Change Password')
