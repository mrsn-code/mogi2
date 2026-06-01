@extends('layouts.default')
<!-- タイトル -->
@section('title','スタッフ一覧')
<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('/css/list.css') }}">
@endsection

<!-- 本体 -->
@section('content')

<!-- ヘッダー -->
@include('components.header_admin')
<div class="attendance-list-container">
    <div class="title__section">
        <h1>| スタッフ一覧</h1>
    </div>
    <table class="attendance-table">
        <thead>
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($staffs as $staff)
                <tr>
                    <td>{{ $staff->name }}</td>
                    <td>{{ $staff->email }}</td>
                    <td>
                        <a href="{{ route('admin.attendance.staff', ['id' => $staff->id]) }}">
                            詳細
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection