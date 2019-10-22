@extends ('emails.template')

@section('mail_content')
    Hi {{ $name }},
    <br>
    <br>

    @if ($status)
        You have been granted Admin access by {{$admin_name}} ({{ $admin_email }}).
        <br>
        You can access Admin console by following the link provided below.
    @else
        Your Admin access has been revoked by {{$admin_name}} ({{ $admin_email }}).
    @endif

    <br>
    <br>
    Thanks,
    <br>
    CoSound.com
    <br>
    <br>
@endsection

@if ($status)
    @section('mail_url')
        {{ $url }}
    @endsection

    @section('mail_button')
        Admin Console
    @endsection
@endif