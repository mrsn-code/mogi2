@extends('layouts.default')

<!-- タイトル -->
@section('title','申請一覧')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('/css/') }}">
@endsection

<!-- 本体 -->
@section('content')
<!-- ヘッダー-->
@include('components.header')

<div class="request-list-container">
    <h1>申請一覧</h1>
    @if (session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif
    <div class="tabs">
        <a href="{{ route('attendance.request.index', ['status' => 'pending']) }}"
           class="{{ $status === 'pending' ? 'active' : '' }}">
            承認待ち
        </a>
        <a href="{{ route('attendance.request.index', ['status' => 'approved']) }}"
           class="{{ $status === 'approved' ? 'active' : '' }}">
            承認済み
        </a>
    </div>

    <table class="request-table">
        <thead>
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日</th>
                <th>申請理由</th>
                <th>申請日時</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($requests as $requestItem)
                <tr>
                    <td>{{ $requestItem->status_label }}</td>
                    <td>
                        {{ $requestItem->attendance->user->name }}
                    </td>
                    <td>
                        {{ \Carbon\Carbon::parse($requestItem->attendance->work_date)->format('Y/m/d') }}
                    </td>
                    <td>
                        {{ $requestItem->note }}
                    </td>
                    <td>
                        {{ $requestItem->created_at->format('Y/m/d H:i') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

