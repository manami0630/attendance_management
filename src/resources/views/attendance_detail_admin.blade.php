@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_detail_admin.css') }}" />
@endsection

@section('content')
@section('content')
<div class="content">
    <div class="heading">
        <h2>勤怠詳細</h2>
    </div>
    <form class="form">
        <table class="table">
            <tr class="row">
                <th class="label">名前</th>
                <td class="data">{{ $user->name ?? '-' }}</td>
            </tr>
            <tr class="row">
                <th class="label">日付</th>
                <td class="data">{{ $record->date ?? '-' }}</td>
            </tr>
            <tr class="row">
                <th class="label">出勤・退勤</th>
                <td class="data"><input type="text" name="clock_in_time" value="{{ $record->clock_in_time ?? '' }}">
                <span class="space">~</span>
                <input type="text" name="clock_out_time" value="{{ $record->clock_out_time ?? '' }}">
                </td>
            </tr>
            @foreach($breaks as $break)
            <tr class="row">
                @if($loop->first)
                <th class="label">休憩</th>
                @else
                <th class="label">休憩{{ $loop->iteration }}</th>
                @endif
                <td class="data"><input type="text" name="break_start_time" value="{{ $break->break_start_time ?? '' }}">
                <span class="space">~</span>
                <input type="text" name="break_end_time" value="{{ $break->break_end_time ?? '' }}"></td>
            </tr>
            @endforeach
            <tr class="row">
                <th class="label">備考</th>
                <td class="data">
                <textarea class="remarks"></textarea></td>
            </tr>
        </table>
        <div class="form__button">
            <input class="form__btn" type="submit" value="修正">
        </div>
    </form>
</div>
@endsection