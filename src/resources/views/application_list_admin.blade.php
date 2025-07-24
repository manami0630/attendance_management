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
        <a href="#" class="list__btn">承認待ち</a>
        <a href="#" class="list__btn">承認済み</a>
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
        <tr class="row">
            <td class="data">q</td>
            <td class="data">a</td>
            <td class="data">a</td>
            <td class="data">a</td>
            <td class="data">a</td>
            <td class="data">
                <a class="detail-btn" href="#">詳細</a>
            </td>
        </tr>
    </table>
</div>
@endsection