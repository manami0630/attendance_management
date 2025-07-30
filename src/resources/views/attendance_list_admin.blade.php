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
        document.getElementById('currentDate').textContent = currentDate.toLocaleDateString(undefined, options);

        document.getElementById('dateHeading').textContent =`${currentDate.getFullYear()}年${currentDate.getMonth() + 1}月${currentDate.getDate()}日 の勤怠`;
      }

      function fetchAttendanceData() {
        const dateStr = currentDate.toISOString().split('T')[0]; // Y-M-D形式
        fetch(`/api/attendance?date=${dateStr}`)
        .then(res => res.json())
        .then(data => {
          const tbody = document.getElementById('attendanceTableBody');
          tbody.innerHTML = '';

          if (!data.records || data.records.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6">データがありません</td></tr>';
            return;
          }

          data.records.forEach(record => {
            const row = document.createElement('tr');
            row.innerHTML = `
            <td class="date">${record.user ? record.user.name : ''}</td>
            <td class="date">${record.clock_in_time ?? ''}</td>
            <td class="date">${record.clock_out_time ?? ''}</td>
            <td class="date">${record.user_break_times ?? ''}</td>
            <td class="date">${record.net_work_time ?? ''}</td>
            <td class="date"><a class="detail-btn" href="/admin/attendance/${record.id}">詳細</a></td>
            `;
            tbody.appendChild(row);
          });
        })
        .catch(error => console.error('エラー:', error));
      }
    </script>
  </div>
</div>
@endsection