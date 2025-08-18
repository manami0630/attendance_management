@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_list_admin.css') }}" />
@endsection

@section('content')
<div class="content">
  <div class="heading">
    <h2 id="dateHeading">
      {{ $record->date ?? '' }} の勤怠
    </h2>
  </div>
  <div class="calendar-container">
    <button id="prevDay">←前日</button>
    <span id="currentDate"></span>
    <button id="nextDay">翌日→</button>
  </div>
  <table class="table">
    <thead>
      <tr class="label_row">
        <th class="label">名前</th>
        <th class="label">出勤</th>
        <th class="label">退勤</th>
        <th class="label">休憩</th>
        <th class="label">合計</th>
        <th class="label">詳細</th>
      </tr>
    </thead>
    <tbody id="attendanceTableBody"></tbody>
  </table>
  <script>
    let currentDate = new Date();

    document.addEventListener('DOMContentLoaded', () => {
      updateDisplay();
      fetchAttendanceData();

      document.getElementById('prevDay').addEventListener('click', () => {
        currentDate.setDate(currentDate.getDate() - 1);
        updateDisplay();
        fetchAttendanceData();
      });

      document.getElementById('nextDay').addEventListener('click', () => {
        currentDate.setDate(currentDate.getDate() + 1);
        updateDisplay();
        fetchAttendanceData();
      });
    });

    function updateDisplay() {
      const options = { year: 'numeric', month: '2-digit', day: '2-digit' };
      const year = currentDate.getFullYear();
      const month = ('0' + (currentDate.getMonth() + 1)).slice(-2);
      const day = ('0' + currentDate.getDate()).slice(-2);
      const formattedDate = `📆${year}/${month}/${day}`;

      document.getElementById('currentDate').textContent = formattedDate;

      document.getElementById('dateHeading').textContent = `${year}年${month}月${day}日 の勤怠`;
    }

    function formatTime(timeStr) {
      if (!timeStr) {
        return '';
      }
      const [hours, minutes] = timeStr.split(':');
      return `${hours}:${minutes}`;
    }

    function fetchAttendanceData() {
      const dateStr = `${currentDate.getFullYear()}-${('0' + (currentDate.getMonth() + 1)).slice(-2)}-${('0' + currentDate.getDate()).slice(-2)}`;

      fetch(`/api/attendance?date=${dateStr}`)
      .then(res => res.json())
      .then(data => {
        console.log('API response data:', data);
        const tbody = document.getElementById('attendanceTableBody');
        tbody.innerHTML = '';

        data.records.forEach(record => {
          const row = document.createElement('tr');
          row.className = 'row';

          const userName = record.user ? record.user.name : '';
          const clockIn = record.clock_in_time ?? '';
          const clockOut = record.clock_out_time ?? '';
          const breakTimes = record.user_break_times ?? '';
          const netWorkTime = record.net_work_time ?? '';

          row.innerHTML = `
            <td class="date">${escapeHtml(userName)}</td>
            <td class="date">${escapeHtml(formatTime(clockIn))}</td>
            <td class="date">${escapeHtml(formatTime(clockOut))}</td>
            <td class="date">${escapeHtml(breakTimes)}</td>
            <td class="date">${escapeHtml(netWorkTime)}</td>
            <td class="date"><a class="detail-btn" href="/attendance/${record.user_id}?date=${dateStr}">詳細</a></td>
          `;
          tbody.appendChild(row);
        });
      })
      .catch(error => {
        console.error('エラー:', error);
      });
    }
    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }
  </script>
</div>
@endsection
