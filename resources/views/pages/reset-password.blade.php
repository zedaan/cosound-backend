@extends('layout')

@section('body')
    <div class="card" style="width: 70rem;">
        <div class="card-img-top text-center">
            <div class="title">
                CoSound
            </div>
        </div>
        <div class="card-body">
            <h4 class="card-title text-center">Reset Password</h4>
            
            @if ($errors->any())
                <div class="alert alert-danger" style="padding-bottom: 1px;">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ url('password/reset') }}">
                @csrf
                <input type="hidden" name="password_reset" value="{{ $token }}">
                <div class="form-group">
                    <label for="exampleInputEmail1">E-mail address</label>
                    <input type="email" name="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Enter e-mail">
                    <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small>
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">New Password</label>
                    <input type="password" name="password" class="form-control" id="exampleInputPassword1" placeholder="New Password">
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword2">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" id="exampleInputPassword2" placeholder="New Password">
                </div>
                <button type="submit" class="btn btn-primary float-right">Update</button>
            </form>
        </div>
    </div>
@endsection