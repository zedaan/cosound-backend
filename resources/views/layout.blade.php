<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title', 'CoSound')</title>

        <!-- Styles -->
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Raleway', sans-serif;
                font-weight: 100;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 12px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
        </style>

        <!-- Favicon -->
        <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon" />
        <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon" />
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            <div style="margin-bottom: 50px">
                <div class="text-center" style="margin-bottom: 30px">
                    <img src="/images/logo.png" class="logo shadow" />
                </div>
                @yield('body')        
            </div>
            <div class="footer">
                <div class="flex-class align-center">
                    <p class="large">
                    ©2018-{{ date('Y') }} CoSound. All Rights Reserved.
                    </p>
                </div>
            </div>
        </div>
            
        
        <!-- JavaScript -->
        <script src="{{ asset('js/app.js') }}"></script>
    </body>
</html>
