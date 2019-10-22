<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="utf-8">
        <style type="text/css">
            body {
                background-color: #fff;
                color: #636b6f;
                font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            }

            .full-height {
                height: 100vh;
            }
            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }
            .text-center {
                text-align: center;
            }
            .logo {
                height: 120px;
                width: 120px;
                border-radius: 60px;
            }
            .m-t-1 {
                margin-top: 10px;
            }
            .m-t-5 {
                margin-top: 50px;
            }
            .btn {
                color: #FFFFFF !important;
                border-radius: 0.3em;
                line-height: 1.5;
                font-size: 1.25rem;
                cursor: pointer;
                text-decoration: none;
                padding: 8px 20px 8px 20px;
                -webkit-transform: translate3d(0, -20px, 0);
                transform: translate3d(0, -20px, 0);
                background-image: -webkit-linear-gradient(80deg, #8E5ACD 0%, #21B0B0 100%);
                background-image: -o-linear-gradient(80deg, #8E5ACD 0%, #21B0B0 100%);
                background-image: linear-gradient(170deg, #8E5ACD 0%, #21B0B0 100%);
                text-decoration: none;
            }
        </style>

        <!-- Favicon -->
        <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon" />
        <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon" />
    </head>
    <body style="background-color: #FFF;">
        <div class="flex-center position-ref full-height m-t-5" style="align-items: start;">
            <div>
                <!-- Mail Header -->
                <div class="text-center">
                    <img src="{{ url('/images/logo.png') }}" class="logo" />
                    <h4 class="bold grey" style="margin-top: -20px;">CoSound</h4>
                </div>

                <!-- Mail Body -->
                <div style="margin-top: 50px;">
                    <!-- Mail Content -->
                    @yield('mail_content', 'This is mail content')

                    <!-- Mail Button -->
                    @if (trim($__env->yieldContent('mail_button')))
                        <div class="flex-center text-center m-t-5">
                            <a class="btn" href="@yield('mail_url', '/')">@yield('mail_button', 'GET STARTED')</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </body>
</html>
