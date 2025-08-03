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
    // BladeのユーザIDをJavaScript側に渡す  
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
      document.getElementById('currentMonth').textContent = `${currentYear}/${monthNames[currentMonth]}`;  
    }  

    function clearTable() {  
      document.getElementById('attendanceBody').innerHTML = '';  
    }  

    function createEmptyRow(dateStr) {  
      const tbody = document.getElementById('attendanceBody');  
      const row = document.createElement('tr');  

      const dateObj = new Date(dateStr);  
      const weekDays = ['日', '月', '火', '水', '木', '金', '土'];  
      const weekDay = weekDays[dateObj.getDay()];  
      const monthDisplay = ('0' + (dateObj.getMonth() + 1)).slice(-2);  
      const dayDisplay = ('0' + dateObj.getDate()).slice(-2);  

      const displayDate = `${monthDisplay}/${dayDisplay}（${weekDay}）`;  

      row.innerHTML = `  
        <td class="row" data-date="${dateStr}">${displayDate}</td>  
        <td class="row"></td>  
        <td class="row"></td>  
        <td class="row"></td>  
        <td class="row"></td>  
        <td class="row"><a class="detail-btn" href="/attendance/${dateStr}">詳細</a></td>  
      `;  
      tbody.appendChild(row);  
    }  

    function updateCalendar() {  
      updateHeader();  
      clearTable();  

      const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();  

      // すべての日付の行を作成  
      for (let i = 1; i <= daysInMonth; i++) {  
        const dateObj = new Date(currentYear, currentMonth, i);  
        const dateStr = formatDateToYMD(dateObj);  
        createEmptyRow(dateStr);  
      }  

      // APIからデータ取得  
      fetch(`/admin/attendance/staff/${userId}?year=${currentYear}&month=${currentMonth + 1}`)  
        .then(res => {
          if (!res.ok) throw new Error(`HTTPエラー: ${res.status}`);
          return res.json();
        })
        .then(data => {
          if (!Array.isArray(data)) {
            console.error('予期しないデータ形式:', data);
            return;
          }

          // 取得したレコードを日付にマッチさせて反映
          data.forEach(record => {
            const recordDate = record.date; // 'YYYY-MM-DD'
            const rowIndex = (() => {
              const dateParts = recordDate.split('-');
              const yearIdx = parseInt(dateParts[0], 10);
              const monthIdx = parseInt(dateParts[1], 10) - 1;
              const dayIdx = parseInt(dateParts[2], 10);
              if (yearIdx === currentYear && monthIdx === currentMonth) {
                return dayIdx - 1; // 0-based index
              }
              return -1;
            })();

            if (rowIndex >= 0) {
              const rows = document.querySelectorAll('#attendanceBody tr');
              const row = rows[rowIndex];
              if (row) {
                // 各項目フォーマット
                const formatTime = t => t ?? '-';

                row.innerHTML = `
                  <td class="row">${(() => {
                    const dateObj = new Date(record.date);
                    const weekDays = ['日', '月', '火', '水', '木', '金', '土'];
                    const weekDay = weekDays[dateObj.getDay()];
                    const monthDisplay = ('0' + (dateObj.getMonth() + 1)).slice(-2);
                    const dayDisplay = ('0' + dateObj.getDate()).slice(-2);
                    return `${monthDisplay}/${dayDisplay}（${weekDay}）`;
                  })()}</td>
                  <td class="row">${formatTime(record.clock_in_time)}</td>
                  <td class="row">${formatTime(record.clock_out_time)}</td>
                  <td class="row">${record.user_break_times ?? '-'}</td>
                  <td class="row">${record.net_work_time ?? '-'}</td>
                  <td class="row"><a class="detail-btn" href="/attendance/${record.id}">詳細</a></td>
                `;
              }
            }
          });
        })
        .catch(e => {
          console.error('データ取得エラー:', e);
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
  </script>
</div>
@endsection
