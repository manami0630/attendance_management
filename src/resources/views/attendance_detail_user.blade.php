@extends('layouts.master')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_detail_user.css') }}" />
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>勤怠詳細</h2>
    </div>
    <form class="form" action="{{ route('user.attendance.update') }}" method="post">
    @csrf
    <input type="hidden" name="user_attendance_record_id" value="{{ $record->id ?? '' }}">
        @if($applicationStatus === '承認待ち')
        <table class="table">
            <tr class="row">
                <th class="label">名前</th>
                <td class="data">{{ $user->name ?? '-' }}</td>
            </tr>
            <tr class="row">
                <th class="label">日付</th>
                <td class="data"><input class="day" name="date" type="text" value="{{ $application->target_date ?? '' }}" readonly>
            </tr>
            <tr class="row">
                <th class="label">出勤・退勤</th>
                <td class="data">
                    <input class="applications_text" type="text" name="clock_in_time" value="{{ isset($application->clock_in_time) ? \Carbon\Carbon::parse($application->clock_in_time)->format('H:i') : '' }}">
                    <span class="space">~</span>
                    <input class="applications_text" type="text" name="clock_out_time" value="{{ isset($application->clock_out_time) ? \Carbon\Carbon::parse($application->clock_out_time)->format('H:i') : '' }}">
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
                    <input class="applications_text" type="text" name="break_start_time[]" value="{{ isset($breakApplication->break_start_time) ? \Carbon\Carbon::parse($breakApplication->break_start_time)->format('H:i') : '' }}">
                    <span class="space">~</span>
                    <input class="applications_text" type="text" name="break_end_time[]" value="{{ isset($breakApplication->break_end_time) ? \Carbon\Carbon::parse($breakApplication->break_end_time)->format('H:i') : '' }}">
                </td>
            </tr>
            @endforeach
            <tr class="row">
                <th class="label">備考</th>
                <td class="data">
                    <input class="applications_remarks" type="text" name="remarks" value="{{ $application->reason ?? '' }}">
                </td>
            </tr>
        </table>
        <div class="form__button">
            <p class="form__text">*承認待ちのため修正はできません。</p>
        </div>
        @else
        <table class="table">
            <tr class="row">
                <th class="label">名前</th>
                <td class="data">{{ $user->name ?? '-' }}</td>
            </tr>
            <tr class="row">
                <th class="label">日付</th>
                <td class="data"><input class="day" name="date" type="text" value="{{ $record->date ?? '' }}" readonly>
            </tr>
            <tr class="row">
                <th class="label">出勤・退勤</th>
                <td class="data">
                    <input class="text" type="text" name="clock_in_time" value="{{ isset($record->clock_in_time) ? \Carbon\Carbon::parse($record->clock_in_time)->format('H:i') : '' }}">
                    <span class="space">~</span>
                    <input class="text" type="text" name="clock_out_time" value="{{ isset($record->clock_out_time) ? \Carbon\Carbon::parse($record->clock_out_time)->format('H:i') : '' }}">
                    <div class="form__error">
                        @error('clock_in_time')
                        {{ $message }}
                        @enderror
                        @error('clock_out_time')
                        {{ $message }}
                        @enderror
                    </div>
                </td>
            </tr>
            @foreach($breaks as $index => $break)
            <tr class="row">
                @if($loop->first)
                <th class="label">休憩</th>
                @else
                <th class="label">休憩{{ $loop->iteration }}</th>
                @endif
                <td class="data">
                    <input class="text" type="text" name="break_start_time[]" value="{{ isset($break->break_start_time) ? \Carbon\Carbon::parse($break->break_start_time)->format('H:i') : '' }}">
                    <span class="space">~</span>
                    <input class="text" type="text" name="break_end_time[]" value="{{ isset($break->break_end_time) ? \Carbon\Carbon::parse($break->break_end_time)->format('H:i') : '' }}">
                    <div class="form__error">
                        @error('break_start_time.' . $index)
                        {{ $message }}
                        @enderror
                    </div>
                    <div class="form__error">
                        @error('break_end_time.' . $index)
                        {{ $message }}
                        @enderror
                    </div>
                </td>
            </tr>
            @endforeach
            @if($applicationStatus === '承認済み')
            <tr class="row">
                <th class="label">備考</th>
                <td class="data">
                    <input class="remarks" type="text" name="remarks" value="{{ $application->reason ?? '' }}">
                </td>
            </tr>
            @else
            <tr class="row">
                <th class="label">備考</th>
                <td class="data">
                    <textarea class="remarks" name="reason"></textarea>
                    <div class="form__error">
                        @error('reason')
                        {{ $message }}
                        @enderror
                    </div>
                </td>
            </tr>
            @endif
        </table>
        <div class="form__button">
            <input class="form__btn" type="submit" value="修正">
        </div>
        @endif
    </form>
</div>
@endsection
