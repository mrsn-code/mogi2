@extends('layouts.default')
@section('css')
<link rel="stylesheet" href="{{asset('css/verify.css')}}">
@endsection

@section('content')
<div class="page__wrapper">
    <p class="verify-email__message">
        登録していただいたメールアドレスに確認メールを送付しました。<br>
        メール認証を完了してください。
    </p>
    
    @if (session('status'))
        <p style="color: green;">
            {{ session('status') }}
        </p>
    @endif
    <div class="auth__button">
        <a href="http://localhost:8025" target="_blank" >
            認証はこちらから
        </a>
    </div>
    
    <form method="POST" action="{{route('verification.send')}}">
        @csrf
        <button class="retransmission__button" type="submit">認証メールを再送する</button>
    </form>
</div>
@endsection