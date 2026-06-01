@extends('layouts.default')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/details.css') }}">
@endsection

@include('components.header_admin')
@section('content')
<div class="attendance-detail-container">
    <div class="title__section">
        <h1>| 勤怠詳細</h1>
    </div>
    <table class="attendance-detail-table">
        <tr>
            <th>状態</th>
            <td>{{ $requestItem->status_label }}</td>
        </tr>
        <tr>
            <th>名前</th>
            <td>{{ $requestItem->user->name }}</td>
        </tr>
        <tr>
            <th>対象日</th>
            <td>
                {{ \Carbon\Carbon::parse($requestItem->attendance->work_date)->format('Y/m/d') }}
            </td>
        </tr>
        <tr>
            <th>出勤時間</th>
            <td>
                {{ $requestItem->requested_clock_in ? \Carbon\Carbon::parse($requestItem->requested_clock_in)->format('H:i') : '' }}
            </td>
        </tr>
        <tr>
            <th>退勤時間</th>
            <td>
                {{ $requestItem->requested_clock_out ? \Carbon\Carbon::parse($requestItem->requested_clock_out)->format('H:i') : '' }}
            </td>
        </tr>
        @foreach ($requestItem->breakCorrections as $index => $breakCorrection)
            <tr>
                <th>休憩{{ $index + 1 }}</th>
                <td>
                    {{ $breakCorrection->requested_break_start ? \Carbon\Carbon::parse($breakCorrection->requested_break_start)->format('H:i') : '' }}
                    〜
                    {{ $breakCorrection->requested_break_end ? \Carbon\Carbon::parse($breakCorrection->requested_break_end)->format('H:i') : '' }}
                </td>
            </tr>
        @endforeach
        <tr>
            <th>備考</th>
            <td>{{ $requestItem->note }}</td>
        </tr>
    </table>
    @if ($requestItem->status === \App\Models\AttendanceCorrection::STATUS_PENDING)
    <form method="POST" action="{{ route('attendance.request.approve', ['id' => $requestItem->id]) }}" class="details__wrapper">
        @csrf
        <div class="attendance-detail-actions">
            <button type="submit" class="approve-button">承認</button>
        </div>
    </form>
    @elseif ($requestItem->status === \App\Models\AttendanceCorrection::STATUS_APPROVED)
        <div class="details__wrapper">
            <div class="attendance-detail-actions__approved">
                <button type="button" class="approved-button" disabled>承認済み</button>
            </div>
        </div>
    @endif
</div>
@endsection