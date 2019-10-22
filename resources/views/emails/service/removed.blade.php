@extends ('emails.template')

@section('mail_content')
    Hi {{ $name }},
    <br>
    <br>
    Your service <b>({{ $service_title }})</b> has been removed.
    <br>
    @if (trim($comment))
        <br>
        Message from admin:
        <br>
        {{ $comment }}
        <br>
    @endif

    <br>
    You can create new service by following the link provided below.
    <br>
    <br>
    Thanks,
    <br>
    CoSound.com
    <br>
    <br>
@endsection

@section('mail_url', url($url))

@section('mail_button', 'Create New Service')
