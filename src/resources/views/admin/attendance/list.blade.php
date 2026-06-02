@extends('layouts.default')
<!-- タイトル -->
@section('title','勤怠一覧')
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
        <h1>| {{ \Carbon\Carbon::parse($currentDate)->format('Y/m/d') }}の勤怠一覧</h1>
    </div>
    <div class="time-navigation">
        <a href="{{ route('admin.attendance.list', ['date' => $previousDate]) }}" class="change-button">
            ← 前日
        </a>
        <h2>{{ $currentDate->format('Y/m/d') }}</h2>
        <a href="{{ route('admin.attendance.list', ['date' => $nextDate]) }}" class="change-button">
            翌日 →
        </a>
    </div>
    <table class="attendance-table">
        <thead>
            <tr>
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                @php
                    $attendance = $user->attendances->first();
                @endphp
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>
                        @if ($attendance && $attendance->clock_in)
                            {{ \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}
                        @endif
                    </td>
                    <td>
                        @if ($attendance && $attendance->clock_out)
                            {{ \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') }}
                        @endif
                    </td>
                    <td>
                        @if ($attendance)
                            {{ $attendance->total_break_time }}
                        @endif
                    </td>
                    <td>
                        @if ($attendance)
                            {{ $attendance->total_work_time }}
                        @endif
                    </td>
                    <td>
                        @if ($attendance)
                            <a href="{{ route('admin.attendance.details', ['id' => $attendance->id]) }}">
                                詳細
                            </a>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection