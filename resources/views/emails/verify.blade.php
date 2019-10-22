@extends ('emails.template')

@section('mail_content')
    Hi {{ $name }},
    <br>
    <br>
    Thank you for creating an account with us. Don't forget to complete your registration!
    <br>
    Please click on the link below or copy it into the address bar of your browser to confirm your email address:
    <br>
    <br>
    Thanks,
    <br>
    CoSound.com
    <br>
    <br>        
@endsection

@section('mail_url', url('user/verify', $confirmation_code))

@section('mail_button', 'Confirm E-Mail')
