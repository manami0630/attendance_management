@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/staff_list_admin.css') }}" />
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>スタッフ一覧</h2>
    </div>
    <table class="table">
        <tr class="label_row">
            <th class="label">名前</th>
            <th class="label">メールアドレス</th>
            <th class="label">月次勤怠</th>
        </tr>
        @foreach ($users as $user)
        <tr class="row">
            <td class=" date">{{ $user->name ?? '' }}</td>
            <td class="date">{{ $user->email ?? '' }}</td>
            <td class="date">
                <a class="detail-btn" href="/admin/attendance/staff/{{ $user->id }}">詳細</a>
            </td>
        </tr>
        @endforeach
    </table>
</div>
@endsection