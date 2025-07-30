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
        <table class="table">
            <tr class="row">
                <th class="label">名前</th>
                <td class="data">{{ $user->name ?? '-' }}</td>
            </tr>
            <tr class="row">
                <th class="label">日付</th>
                <td class="data"><input class="day" type="text" name="date" value="{{ $record->date ?? '' }}" readonly></td>
            </tr>
            <tr class="row">
                <th class="label">出勤・退勤</th>
                <td class="data"><input class="text" type="text" name="clock_in_time" value="{{ $record->clock_in_time ?? '' }}">
                <span class="space">~</span>
                <input class="text" type="text" name="clock_out_time" value="{{ $record->clock_out_time ?? '' }}">
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
            @foreach($breaks as $break)
            <tr class="row">
                @if($loop->first)
                <th class="label">休憩</th>
                @else
                <th class="label">休憩{{ $loop->iteration }}</th>
                @endif
                <td class="data">
                    <input class="text" type="text" name="break_start_time" value="{{ $break->break_start_time ?? '' }}">
                    <span class="space">~</span>
                    <input class="text" type="text" name="break_end_time" value="{{ $break->break_end_time ?? '' }}">
                    <div class="form__error">
                        @error('break_start_time')
                        {{ $message }}
                        @enderror
                        @error('break_end_time')
                        {{ $message }}
                        @enderror
                    </div>
                </td>
            </tr>
            @endforeach
            <tr class="row">
                <th class="label">備考</th>
                <td class="data">
                <textarea class="remarks" name="remarks"></textarea>
                    <div class="form__error">
                        @error('remarks')
                        {{ $message }}
                        @enderror
                    </div>
                </td>
            </tr>
        </table>
        <div class="form__button">
            @if($applicationStatus !== '承認待ち')
                <input class="form__btn" type="submit" value="修正">
            @else
                <p class="form__text">*承認待ちのため修正はできません。</p>
            @endif
        </div>
    </form>
</div>
@endsection