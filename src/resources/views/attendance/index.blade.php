@extends('layouts.default')

<!-- タイトル -->
@section('title','勤怠登録画面')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('/css/attendance.css') }}">
@endsection

<!-- 本体 -->
@section('content')

<!-- ヘッダーの切り替え -->
@if ($attendance && $attendance->status === 'finished')
    @include('components.header_finished')
@else
    @include('components.header')
@endif

<div class="container">
    <!-- ステータス -->
    <div class="status__wrapper">
        @if ($attendance)
            {{ $attendance->status_label }}
        @else
            勤務外
        @endif
    </div>

    <!-- 時刻表示部分 -->
    <p class="calendar" id="calendar"></p>
    <p class="clock" id="clock"></p>
    <script>
        function updateClock() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const date = String(now.getDate()).padStart(2, '0');

            const weekdays = ['日', '月', '火', '水', '木', '金', '土'];
            const weekday = weekdays[now.getDay()];

            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const currentDate = `${year}年${month}月${date}日(${weekday})`;
            const currentTime = `${hours}:${minutes}`;
            document.getElementById('calendar').textContent = currentDate;
            document.getElementById('clock').textContent = currentTime;
        }
        updateClock();
        setInterval(updateClock, 1000);
    </script>

    @if (session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif
    @if (session('error'))
        <p style="color: red;">{{ session('error') }}</p>
    @endif
    @php
        $status = $attendance?->status ?? 'off';
    @endphp

    <div>
        @if (!$attendance)
            {{-- 勤務外 --}}
            <form method="POST" action="{{ route('attendance.clockIn') }}">
                @csrf
                <button class="attendance__button" type="submit">出勤</button>
            </form>
        @elseif ($status === 'working')
            {{-- 出勤中 --}}
            <form method="POST" action="{{ route('attendance.clockOut') }}" style="display:inline;">
                @csrf
                <button class="attendance__button" type="submit">退勤</button>
            </form>
            <form method="POST" action="{{ route('attendance.breakIn') }}" style="display:inline;">
                @csrf
                <button class="break__button" type="submit">休憩入</button>
            </form>
        @elseif ($status === 'breaking')
            {{-- 休憩中 --}}
            <form method="POST" action="{{ route('attendance.breakOut') }}">
                @csrf
                <button class="break__button" type="submit">休憩戻</button>
            </form>
        @elseif ($status === 'finished')
            {{-- 退勤済 --}}
            <p>お疲れ様でした。</p>
        @endif
    </div>
</div>
@endsection