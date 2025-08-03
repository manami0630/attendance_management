@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/application_review_admin.css') }}" />
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>勤怠詳細</h2>
    </div>
    <form class="form" action="{{ route('user.attendance.approve', $application->id) }}" method="post">
    @csrf
        <table class="table">
            <tr class="row">
                <th class="label">名前</th>
                <td class="data">{{ $application->user->name ?? '' }}</td>
            </tr>
            <tr class="row">
                <th class="label">日付</th>
                <td class="data">
                    <input class="day" type="text" name="date" value="{{ $application->target_date ?? '' }}" readonly>
                </td>
            </tr>
            <tr class="row">
                <th class="label">出勤・退勤</th>
                <td class="data">
                    <input class="text" type="text" name="clock_in_time" value="{{ isset($application->clock_in_time) ? \Carbon\Carbon::parse($application->clock_in_time)->format('H:i') : '' }}">
                    <span class="space">~</span>
                    <input class="text" type="text" name="clock_out_time" value="{{ isset($application->clock_out_time) ? \Carbon\Carbon::parse($application->clock_out_time)->format('H:i') : '' }}">
                </td>
            </tr>
            @foreach ($breakApplications as $breakApplication)
            <tr class="row">
                @if($loop->first)
                <th class="label">休憩</th>
                @else
                <th class="label">休憩{{ $loop->iteration }}</th>
                @endif
                <td class="data">
                    <input class="text" type="text" name="break_start_time[]" value="{{ isset($breakApplication->break_start_time) ? \Carbon\Carbon::parse($breakApplication->break_start_time)->format('H:i') : '' }}">
                    <span class="space">~</span>
                    <input class="text" type="text" name="break_end_time[]" value="{{ isset($breakApplication->break_end_time) ? \Carbon\Carbon::parse($breakApplication->break_end_time)->format('H:i') : '' }}">
                </td>
            </tr>
            @endforeach
            <tr class="row">
                <th class="label">備考</th>
                <td class="data">
                    <input class="remarks" type="text" name="remarks" value="{{ $application->reason ?? '' }}">
                </td>
            </tr>
        </table>
        <div class="form__button">
            @if($application->status == '承認待ち')
                <input class="form__btn" type="submit" value="承認">
            @else
                <button class="application_btn" disabled>承認済み</button>
            @endif
        </div>
    </form>
</div>
@endsection