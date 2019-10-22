@extends('layout')

@section('body')
    <div class="card" style="width: 70rem;">
        <div class="card-img-top text-center">
            <div class="title">
            CoSound                
            </div>
        </div>
        <div class="card-body">
        	<h4 class="card-title text-center">
        		@if (isset($success_message))
                    <div class="alert alert-success" role="alert">
                        {{ $success_message }}
                    </div>
                @endif

                @if (isset($error_message))
                    <div class="alert alert-danger" role="alert">
                        {{ $error_message }}
                    </div>
                @endif	
        	</h4>
        </div>
    </div>
@endsection
