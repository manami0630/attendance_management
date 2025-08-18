@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_detail_admin.css') }}" />
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>勤怠詳細</h2>
    </div>
    @if(!$record)
        <p>該当する勤怠データがありません。</p>
    @else
        <form class="form" action="{{ route('attendance.save', ['id' => $record->id]) }}" method="post">
        @csrf
            <input type="hidden" name="user_attendance_record_id" value="{{ $record->id ?? '' }}">
            <table class="table">
                <tr class="row">
                    <th class="label">名前</th>
                    <td class="data">{{ $record->user->name ?? '-' }}</td>
                </tr>
                <tr class="row">
                    <th class="label">日付</th>
                    <td class="data">
                        <input class="day" name="date_display" type="text" value="{{ isset($record->date) ? \Carbon\Carbon::parse($record->date)->format('Y年') : '' }}" readonly>
                        <span class="space"></span>
                        <input class="day" name="date_display" type="text" value="{{ isset($record->date) ? \Carbon\Carbon::parse($record->date)->format('n月j日') : '' }}" readonly>
                        <input type="hidden" name="date" value="{{ isset($record->date) ? $record->date : '' }}">
                    </td>
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
                        <input class="text" type="text" name="break_start_time[{{ $index }}]" value="{{ isset($break->break_start_time) ? \Carbon\Carbon::parse($break->break_start_time)->format('H:i') : '' }}">
                        <span class="space">~</span>
                        <input class="text" type="text" name="break_end_time[{{ $index }}]" value="{{ isset($break->break_end_time) ? \Carbon\Carbon::parse($break->break_end_time)->format('H:i') : '' }}">
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
                <tr class="row">
                    <th class="label">備考</th>
                    <td class="data">
                        <input class="remarks" type="text" name="reason" value="{{ $record->reason ?? '' }}">
                        <div class="form__error">
                            @error('reason')
                            {{ $message }}
                            @enderror
                        </div>
                    </td>
                </tr>
            </table>
            <div class="form__button">
                <input class="form__btn" type="submit" value="修正">
            </div>
        </form>
    @endif
</div>
@endsection
