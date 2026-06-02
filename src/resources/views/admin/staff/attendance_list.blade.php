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
        <h1>| {{ $user->name }} さんの勤怠</h1>
    </div>
    <div class="time-navigation">
        <a href="{{ route('admin.attendance.staff', [
            'id' => $user->id,
            'month' => $previousMonth
        ]) }}">
            ← 前月
        </a>
        <a href="{{ route('admin.attendance.staff', [
            'id' => $user->id,
            'month' => $nextMonth
        ]) }}">
            次月 →
        </a>
    </div>
    <table class="attendance-table">
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attendanceList as $row)
                @php
                    $date = $row['date'];
                    $attendance = $row['attendance'];
                @endphp
                <tr>
                    <td>
                        {{ $date->format('m/d') }}
                        {{ ['日', '月', '火', '水', '木', '金', '土'][$date->dayOfWeek] }}
                    </td>
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
                                <span class="detail">詳細</span>
                            </a>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection