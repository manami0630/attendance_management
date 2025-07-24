@extends('layouts.master')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_list_user.css') }}" />
@endsection

@section('content')
<div class="content">
  <div class="heading">
    <h2>勤怠一覧</h2>
  </div>
  <div class="calendar-container">
    <button id="prevMonth">←前月</button>
    <span id="currentMonth"></span>
    <button id="nextMonth">翌月→</button>
  </div>
  <table class="table" id="attendanceTable">
    <tr class="label_row">
      <th class="label">日付</th>
      <th class="label">出勤</th>
      <th class="label">退勤</th>
      <th class="label">休憩</th>
      <th class="label">合計</th>
      <th class="label">詳細</th>
    </tr>
    <tbody id="attendanceBody"></tbody>
  </table>
  <script>
    const today = new Date();
    let currentMonth = today.getMonth();
    let currentYear = today.getFullYear();
    const monthNames = ["01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12"];

    function updateHeader() {
      document.getElementById('currentMonth').textContent = `${currentYear}/ ${monthNames[currentMonth]}`;
    }

    function updateCalendar() {
      updateHeader();

      const tbody = document.getElementById('attendanceBody');
      tbody.innerHTML = '';

      const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();

      for (let i = 1; i <= daysInMonth; i++) {
        const date = new Date(currentYear, currentMonth - 1, i).toISOString().slice(0, 10);
        const row = document.createElement('tr');
        row.innerHTML = `
          <td class="row">${date}</td>
          <td class="row">-</td>
          <td class="row">-</td>
          <td class="row">-</td>
          <td class="row">-</td>
          <td class="row"><a class="detail-btn" href="/attendance/${date}">詳細</a></td>
        `;
        tbody.appendChild(row);
      }

      fetch('/attendances/list?year=' + currentYear + '&month=' + (currentMonth + 1))
      .then(response => response.json())
      .then(data => {
        data.forEach(record => {
          const dateArray = record.date.split('-');
          const month = parseInt(dateArray[1]) - 1;
          const day = parseInt(dateArray[2]);
          if (month === currentMonth) {
            const row = document.querySelectorAll('#attendanceBody tr')[day];
            row.innerHTML = `
              <td class="row">${record.date}</td>
              <td class="row">${record.clock_in_time ?? '-'}</td>
              <td class="row">${record.clock_out_time ?? '-'}</td>
              <td class="row">${record.break_time ?? '-'}</td>
              <td class="row">${record.total_time ?? '-'}</td>
              <td class="row"><a class="detail-btn" href="/attendance/${record.id}">詳細</a></td>
            `;
          }
        });
      });
    }

    document.getElementById('prevMonth').addEventListener('click', () => {
      currentMonth--;
      if(currentMonth < 0) {
        currentMonth = 11;
        currentYear--;
      }
      updateCalendar();
    });

    document.getElementById('nextMonth').addEventListener('click', () => {
      currentMonth++;
      if(currentMonth > 11) {
        currentMonth = 0;
        currentYear++;
      }
      updateCalendar();
    });

    window.onload = function() {
      updateCalendar();
    };
  </script>
</div>
@endsection