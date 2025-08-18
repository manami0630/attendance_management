@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendances_by_staff_admin.css') }}" />
@endsection

@section('content')
<div class="content">
  <div class="heading">
    <h2>{{ $user->name ?? '' }}さんの勤怠</h2>
  </div>
  <div class="calendar-container">
    <button id="prevMonth">←前月</button>
    <span id="currentMonth"></span>
    <button id="nextMonth">翌月→</button>
  </div>
  <table class="table" id="attendanceTable">
    <thead>
      <tr class="label_row">
        <th class="label">日付</th>
        <th class="label">出勤</th>
        <th class="label">退勤</th>
        <th class="label">休憩</th>
        <th class="label">合計</th>
        <th class="label">詳細</th>
      </tr>
    </thead>
    <tbody id="attendanceBody"></tbody>
  </table>
  <div class="export-container">
    <form id="export-form" class="export__form" action="{{ route('api.staff.attendances.csv') }}" method="GET" target="_blank">
      <input type="hidden" name="year" id="export-year" value="">
      <input type="hidden" name="month" id="export-month" value="">
      <input type="hidden" name="user_id" id="export-user-id" value="{{ $user->id ?? '' }}">
    </form>
    <button id="exportButton" class="export__btn btn" type="button">CSV出力</button>
  </div>
</div>
<script>
    const userId = {{ $user->id }};
    const today = new Date();
    let currentMonth = today.getMonth();
    let currentYear = today.getFullYear();

    const monthNames = ["01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12"];

    function formatDateToYMD(dateObj) {
      const year = dateObj.getFullYear();
      const month = ('0' + (dateObj.getMonth() + 1)).slice(-2);
      const day = ('0' + dateObj.getDate()).slice(-2);
      return `${year}-${month}-${day}`;
    }

    function updateHeader() {
      document.getElementById('currentMonth').textContent = `📆${currentYear}/${monthNames[currentMonth]}`;
    }

    function clearTable() {
      document.getElementById('attendanceBody').innerHTML = '';
    }

    function createEmptyRow(dateStr) {
      const tbody = document.getElementById('attendanceBody');
      const row = document.createElement('tr');

      const dateObj = new Date(dateStr);
      const month = ('0' + (dateObj.getMonth() + 1)).slice(-2);
      const day = ('0' + dateObj.getDate()).slice(-2);
      const weekDays = ['日', '月', '火', '水', '木', '金', '土'];
      const weekDay = weekDays[dateObj.getDay()];

      const displayDate = `${month}/${day}（${weekDay}）`;

      row.innerHTML = `
        <td class="row">${displayDate}</td>
        <td class="row"></td>
        <td class="row"></td>
        <td class="row"></td>
        <td class="row"></td>
        <td class="row"><a class="detail-btn" href="javascript:void(0);" data-date="${dateStr}" >詳細</a></td>
      `;
      tbody.appendChild(row);
    }

    function updateCalendar() {
      updateHeader();
      clearTable();

      const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();

      for (let i = 1; i <= daysInMonth; i++) {
        const dateObj = new Date(currentYear, currentMonth, i);
        const dateStr = formatDateToYMD(dateObj);
        createEmptyRow(dateStr);
      }

      fetch(`/api/staff/attendances?year=${currentYear}&month=${currentMonth + 1}&user_id=${userId}`)
      .then(res => res.json())
      .then(data => {
        if (!Array.isArray(data)) {
          console.error('Unexpected data:', data);
          return;
        }

        data.forEach(record => {
          function formatDate(dateStr) {
            const dateObj = new Date(dateStr);
            const year = dateObj.getFullYear();
            const month = ('0' + (dateObj.getMonth() + 1)).slice(-2);
            const day = ('0' + dateObj.getDate()).slice(-2);
            const weekDays = ['日', '月', '火', '水', '木', '金', '土'];
            const weekDay = weekDays[dateObj.getDay()];
            return `${month}/${day}（${weekDay}）`;
          }
          function formatTime(timeStr) {
            if (!timeStr) {
              return '';
            }
            const [hours, minutes] = timeStr.split(':');
            return `${hours}:${minutes}`;
          }

          const dateParts = record.date.split('-');
          const yearIdx = parseInt(dateParts[0], 10);
          const monthIdx = parseInt(dateParts[1], 10) - 1;
          const dayIdx = parseInt(dateParts[2], 10);

          if (yearIdx === currentYear && monthIdx === currentMonth) {
            const index = dayIdx - 1;
            const rows = document.querySelectorAll('#attendanceBody tr');
            const row = rows[index];
            if (row) {
              row.innerHTML = `
                <td class="row">${formatDate(record.date)}</td>
                <td class="row">${formatTime(record.clock_in_time ?? '')}</td>
                <td class="row">${formatTime(record.clock_out_time ?? '')}</td>
                <td class="row">${record.user_break_times ?? ''}</td>
                <td class="row">${record.net_work_time ?? ''}</td>
                <td class="row"><a class="detail-btn" href="/attendance/${record.user_id}?date=${record.date}">詳細</a></td>
              `;
            }
          }
        });
      })
      .catch(error => {
        console.error('エラー:', error);
      });
    }

    document.getElementById('prevMonth').addEventListener('click', () => {
      currentMonth--;
      if (currentMonth < 0) {
        currentMonth = 11;
        currentYear--;
      }
      updateCalendar();
    });

    document.getElementById('nextMonth').addEventListener('click', () => {
      currentMonth++;
      if (currentMonth > 11) {
        currentMonth = 0;
        currentYear++;
      }
      updateCalendar();
    });

    window.onload = () => {
      updateCalendar();
    };

    document.addEventListener('DOMContentLoaded', () => {
      const exportForm = document.getElementById('export-form');
      const exportButton = document.getElementById('exportButton');

      function updateExportFields() {
        document.getElementById('export-year').value = currentYear;
        document.getElementById('export-month').value = (currentMonth + 1);
        document.getElementById('export-user-id').value = userId;
      }

      if (exportButton) {
        exportButton.addEventListener('click', (e) => {
          e.preventDefault();
          updateExportFields();
          exportForm.submit();
        });
      }
      updateExportFields();
    });
  </script>
@endsection
