@extends ('emails.template')

@section('mail_content')
    Hi {{ $name }},
    <br>
    <br>
    Your service <b>({{ $service_title }})</b> has been approved.
    <br>
    You can view your service on marketplace by following the link provided below.
    <br>
    <br>
    Thanks,
    <br>
    CoSound.com
    <br>
    <br>
@endsection

@section('mail_url', url($service_url))

@section('mail_button', 'View Service')
