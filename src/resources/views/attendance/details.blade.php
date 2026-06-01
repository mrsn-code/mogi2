@extends('layouts.default')

<!-- タイトル -->
@section('title','勤怠詳細')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('/css/details.css') }}">
@endsection

<!-- 本体 -->
@section('content')
<!-- ヘッダー-->
@include('components.header')
<div class="attendance-detail-container">
    <div class="title__section">
        <h1>| 勤怠詳細</h1>
    </div>
    @if (session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif
    @if ($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if ($pendingCorrection)
        <!-- 承認待ちがある場合：編集不可 -->
        <table class="attendance-detail-table">
            <tr>
                <th>名前</th>
                <td>{{ $attendance->user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td>{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y/m/d') }}</td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>
                    {{ $pendingCorrection->requested_clock_in
                        ? \Carbon\Carbon::parse($pendingCorrection->requested_clock_in)->format('H:i')
                        : '' }}
                    ~
                    {{ $pendingCorrection->requested_clock_out
                        ? \Carbon\Carbon::parse($pendingCorrection->requested_clock_out)->format('H:i')
                        : '' }}
                </td>
            </tr>
            @foreach ($pendingCorrection->breakCorrections as $index => $breakCorrection)
                <tr>
                    <th>休憩{{ $index + 1 }}</th>
                    <td>
                        {{ $breakCorrection->requested_break_start
                            ? \Carbon\Carbon::parse($breakCorrection->requested_break_start)->format('H:i')
                            : '' }}
                        〜
                        {{ $breakCorrection->requested_break_end
                            ? \Carbon\Carbon::parse($breakCorrection->requested_break_end)->format('H:i')
                            : '' }}
                    </td>
                </tr>
            @endforeach
            <tr>
                <th>備考</th>
                <td>{{ $pendingCorrection->note }}</td>
            </tr>
        </table>
        <p class="pending-message">
            *承認待ちのため修正はできません
        </p>
    @else

    <!-- 承認待ちがない場合：編集可能 -->
    <form method="POST" action="{{ route('attendance.request.store', ['id' => $attendance->id]) }}">
        @csrf
        <table class="attendance-detail-table">
            <tr>
                <th>名前</th>
                <td>{{ $attendance->user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td>
                    {{ \Carbon\Carbon::parse($attendance->work_date)->format('Y/m/d') }}
                </td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>
                    <input
                        type="time"
                        name="clock_in"
                        value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}"
                    >
                    ~
                    <input
                        type="time"
                        name="clock_out"
                        value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}"
                    >
                </td>
            </tr>
            @if ($attendance->breaks->count() > 0)
                @foreach ($attendance->breaks as $index => $break)
                    <tr>
                        <th>休憩時間{{ $index + 1 }}</th>
                        <td>
                            <input
                                type="time"
                                name="breaks[{{ $break->id }}][break_start]"
                                value="{{ old('breaks.' . $break->id . '.break_start', $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '') }}"
                            >
                            〜
                            <input
                                type="time"
                                name="breaks[{{ $break->id }}][break_end]"
                                value="{{ old('breaks.' . $break->id . '.break_end', $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '') }}"
                            >
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <th>休憩</th>
                    <td>休憩なし</td>
                </tr>
            @endif
            <tr>
                <th>備考</th>
                <td>
                    <textarea name="note" rows="4">{{ old('note', $attendance->note) }}</textarea>
                </td>
            </tr>
        </table>
        <div class="details__wrapper">
            <div class="attendance-detail-actions">
                <button type="submit">修正</button>
            </div>
        </div>
    </form>
    @endif
</div>
@endsection