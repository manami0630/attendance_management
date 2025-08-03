@extends('layouts.master')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_register_user.css') }}" />
@endsection

@section('content')
<form class="form" id="status-form">
    <div class="situation">{{ $record->status ?? '勤務外' }}</div>
    @php
        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
    @endphp
    <div class="day">
        {{ $now->format('Y年n月j日') }}（{{ $weekdays[$now->dayOfWeek] }}）
    </div>
    <div class="clock" id="clock">{{ $now->format('H:i') }}</div>
    <button type="button" class="clock_in_button" id="status-button" data-action="出勤">出勤</button>
    <button type="button" class="clock_out_button" id="leave" data-action="退勤" style="display:none;">退勤</button>
    <button type="button" class="break_start_button" id="break-in" data-action="休憩入" style="display:none;">休憩入</button>
    <button type="button" class="break_end_button" id="break-back" data-action="休憩戻" style="display:none;">休憩戻</button>
    <p class="content" id="content" style="display:none;">お疲れさまでした。</p>
</form>
<script>
    setInterval(function() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        document.getElementById('clock').textContent = hours + ':' + minutes;
    }, 1000);
</script>
<script>
    window.addEventListener('DOMContentLoaded', () => {
        fetch('/get-current-status', {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.currentStatus) {
                setButtonsByStatus(data.currentStatus);
            }
        });
    });

    function setButtonsByStatus(status) {
        if (status === '出勤中') {
            document.getElementById('status-button').style.display = 'none';
            document.getElementById('break-in').style.display = 'inline-block';
            document.getElementById('leave').style.display = 'inline-block';
            document.getElementById('break-back').style.display = 'none';
            document.getElementById('content').style.display = 'none';
        } else if (status === '休憩中') {
            document.getElementById('status-button').style.display = 'none';
            document.getElementById('break-in').style.display = 'none';
            document.getElementById('leave').style.display = 'none';
            document.getElementById('break-back').style.display = 'inline-block';
            document.getElementById('content').style.display = 'none';
        } else if (status === '退勤済') {
            document.getElementById('status-button').style.display = 'none';
            document.getElementById('break-in').style.display = 'none';
            document.getElementById('leave').style.display = 'none';
            document.getElementById('break-back').style.display = 'none';
            document.getElementById('content').style.display = 'inline-block';
        } else {
            document.getElementById('status-button').style.display = 'inline-block';
            document.getElementById('break-in').style.display = 'none';
            document.getElementById('break-back').style.display = 'none';
            document.getElementById('leave').style.display = 'none';
            document.getElementById('content').style.display = 'none';
        }
    }

    document.querySelectorAll('button[data-action]').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            const action = btn.dataset.action;
            fetch('/save-attendance', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({ status: action })
            })
            .then(res => res.json())
            .then(data => {
                window.location.reload();
            });
        });
    });
</script>
@endsection
