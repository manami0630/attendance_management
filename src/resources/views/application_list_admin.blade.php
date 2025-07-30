@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/application_list_admin.css') }}" />
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>申請一覧</h2>
    </div>
    <div class="list__button">
        <a href="/stamp_correction_request/list?status=承認待ち" class="list__btn {{ $status == '承認待ち' ? 'active' : '' }}">承認待ち</a>
        <a href="/stamp_correction_request/list?status=承認済み" class="list__btn {{ $status == '承認済み' ? 'active' : '' }}">承認済み</a>
    </div>
    <table class="table">
        <tr class="label_row">
            <th class="label">状態</th>
            <th class="label">名前</th>
            <th class="label">対象日時</th>
            <th class="label">申請理由</th>
            <th class="label">申請日時</th>
            <th class="label">詳細</th>
        </tr>
        @foreach($applications as $application)
        <tr class="row">
            <td class="data">{{ $application->status ?? '-' }}</td>
            <td class="data">{{ $application->user->name ?? '-' }}</td>
            <td class="data">{{ $application->target_datetime ? $application->target_datetime->format('Y-m-d') : '-' }}</td>
            <td class="data">{{ $application->reason ?? '-' }}</td>
            <td class="data">{{ $application->created_at ? $application->created_at->format('Y-m-d') : '-' }}</td>
            <td class="data">
                <a class="detail_btn" href="/stamp_correction_request/approve/{{ $application->id }}">詳細</a>
            </td>
        </tr>
        @endforeach
    </table>
</div>
@endsection