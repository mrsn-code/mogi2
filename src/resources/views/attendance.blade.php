@extends('layouts.default')

<!-- タイトル -->
@section('title','勤怠登録画面')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('/css/')  }}">
@endsection

<!-- 本体 -->
@section('content')

@include('components.header')

@endsection