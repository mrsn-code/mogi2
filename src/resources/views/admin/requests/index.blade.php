@extends('layouts.default')
@section('css')
<link rel="stylesheet" href="{{ asset('/css/requests.css') }}">
@endsection

<!-- ヘッダー -->
@include('components.header_admin')
@section('content')
<div class="request-list-container">
    <div class="title__section">
        <h1>| 申請一覧</h1>
    </div>
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
    <hr class="hline">
    <table class="request-table">
        <thead>
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日時</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($requests as $requestItem)
                <tr>
                    <td>{{ $requestItem->status_label }}</td>
                    <td>{{ $requestItem->user->name }}</td>
                    <td>
                        {{ \Carbon\Carbon::parse($requestItem->attendance->work_date)->format('Y/m/d') }}
                    </td>
                    <td>{{ $requestItem->note }}</td>
                    <td>
                        {{ $requestItem->created_at->format('Y/m/d H:i') }}
                    </td>
                    <td>
                        <a href="{{ route('attendance.request.show', ['id' => $requestItem->id]) }}">
                            詳細
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection