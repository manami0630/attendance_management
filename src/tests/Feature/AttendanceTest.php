<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserAttendanceRecord;
use App\Models\UserBreakTime;
use App\Models\UserApplication;
use App\Models\UserBreakApplication;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    //4.日時取得機能
    public function test_it_displays_current_date_and_time()
    {
        $user = User::factory()->create([
            'email' => 'general1@gmail.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'user',
        ]);

        $this->actingAs($user);

        $now = now();

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee($now->format('Y年n月j日'));
        $response->assertSee($now->format('H:i'));
    }

    //5.ステータス確認機能
    public function test_勤務外it_displays_status_when_off_duty()
    {
        $user = User::factory()->create([
            'email' => 'general1@gmail.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'user',
        ]);

        $this->actingAs($user);

        $record = (object) ['status' => null];

        $now = now();

        $response = $this->view('attendance_register_user', compact('record', 'now'));

        $response->assertSee('勤務外');
    }

    public function test_出勤中it_displays_status_when_off_duty()
    {
        $user = User::factory()->create([
            'email' => 'general1@gmail.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'user',
        ]);

        $this->actingAs($user);

        $record = (object) ['status' => null];

        $now = now();

        $response = $this->view('attendance_register_user', compact('record', 'now'));

        $response->assertSee('出勤中');
    }

    public function test_休憩中it_displays_status_when_off_duty()
    {
        $user = User::factory()->create([
            'email' => 'general1@gmail.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'user',
        ]);

        $this->actingAs($user);

        $record = (object) ['status' => null];

        $now = now();

        $response = $this->view('attendance_register_user', compact('record', 'now'));

        $response->assertSee('休憩中');
    }

    public function test_退勤済it_displays_status_when_off_duty()
    {
        $user = User::factory()->create([
            'email' => 'general1@gmail.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'user',
        ]);

        $this->actingAs($user);

        $record = (object) ['status' => null];

        $now = now();

        $response = $this->view('attendance_register_user', compact('record', 'now'));

        $response->assertSee('退勤済');
    }

    //6.出勤機能
    public function test_it_sets_status_to_clocked_in_when_clock_in_button_is_pressed()
    {
        $user = User::factory()->create([
            'email' => 'general1@gmail.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'user',
        ]);

        $this->actingAs($user);

        $response = $this->postJson('/save-attendance', [
            'status' => '出勤'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('user_attendance_records', [
            'user_id' => $user->id,
            'status' => '出勤中',
        ]);
    }

    public function test_it_does_not_show_clock_in_button_when_status_is_clocked_out()
    {
        $user = User::factory()->create([
            'email' => 'general1@gmail.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'user',
        ]);

        UserAttendanceRecord::create([
            'user_id' => $user->id,
            'status' => '退勤済',
            'date' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->get('/get-current-status');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'currentStatus' => '退勤済',
        ]);

        $this->assertStringNotContainsString('出勤', $response->getContent());
    }

    public function test_clock_in_time_is_displayed_on_attendance_list()
    {
        $user = User::factory()->create([
            'email' => 'general1@gmail.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'user',
        ]);

        UserAttendanceRecord::create([
            'user_id' => $user->id,
            'date' => '2025-08-26',
            'clock_in_time' => '09:00',
            'clock_out_time' => null,
        ]);

        $this->actingAs($user);

        $response = $this->post('/attendance/list');

        $response->assertSee('09:00');
    }

    //7.休憩機能
    public function test_it_sets_status_to_clocked_in_when_break_start_button_is_pressed()
    {
        $user = User::factory()->create([
            'email' => 'general1@gmail.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'user',
        ]);

        $this->actingAs($user);

        $response = $this->postJson('/save-attendance', [
            'status' => '休憩入'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('user_attendance_records', [
            'user_id' => $user->id,
            'status' => '休憩中',
        ]);
    }

    public function test_it_shows_break_in_button_when_status_is_clocked_in()
    {
        $user = User::factory()->create([
            'name' => 'テストユーザー',
            'email' => 'testuser@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'user',
        ]);

        $record = UserAttendanceRecord::create([
            'user_id' => $user->id,
            'status' => '出勤中',
            'date' => '2025-08-26',
            'clock_in_time' => '09:00',
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);

        $response->assertSee('休憩入');
    }

    public function test_it_sets_status_to_clocked_in_when_break_end_button_is_pressed()
    {
        $user = User::factory()->create([
            'email' => 'general1@gmail.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'user',
        ]);

        $this->actingAs($user);

        $response = $this->postJson('/save-attendance', [
            'status' => '休憩戻'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('user_attendance_records', [
            'user_id' => $user->id,
            'status' => '出勤中',
        ]);
    }

    public function test_it_shows_break_out_button_when_status_is_clocked_in()
    {
        $user = User::factory()->create([
            'name' => 'テストユーザー',
            'email' => 'testuser@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'user',
        ]);

        $record = UserAttendanceRecord::create([
            'user_id' => $user->id,
            'status' => '休憩中',
            'date' => '2025-08-26',
            'clock_in_time' => '09:00',
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);

        $response->assertSee('休憩戻');
    }

    public function test_break_time_is_displayed_on_attendance_list()
    {
        $user = User::factory()->create([
            'email' => 'general1@gmail.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'user',
        ]);

        UserAttendanceRecord::create([
            'user_id' => $user->id,
            'date' => '2025-08-26',
            'break_start_time' => '11:00',
            'break_end_time' => "12:00",
        ]);

        $this->actingAs($user);

        $response = $this->post('/attendance/list');

        $response->assertSee('11:00');
        $response->assertSee('12:00');
    }

    //8.退勤機能
    public function test_it_sets_status_to_clocked_in_when_clock_out_button_is_pressed()
    {
        $user = User::factory()->create([
            'email' => 'general1@gmail.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'user',
        ]);

        $this->actingAs($user);

        $response = $this->postJson('/save-attendance', [
            'status' => '退勤'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('user_attendance_records', [
            'user_id' => $user->id,
            'status' => '退勤済',
        ]);
    }

    public function test_clock_out_time_is_displayed_on_attendance_list()
    {
        $user = User::factory()->create([
            'email' => 'general1@gmail.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'user',
        ]);

        UserAttendanceRecord::create([
            'user_id' => $user->id,
            'date' => '2025-08-26',
            'clock_in_time' => '09:00',
            'clock_out_time' => "17:00",
        ]);

        $this->actingAs($user);

        $response = $this->post('/attendance/list');

        $response->assertSee('17:00');
    }

    //10.勤怠詳細情報取得機能（一般ユーザー）
    public function test_selected_date_is_displayed_correctly()
    {
        $user = User::factory()->create([
            'name' => 'テストユーザー',
            'email' => 'general1@gmail.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'user',
        ]);

        $attendance = [
            'user_id' => $user->id,
            'date' => '2025-08-27',
        ];

        $this->actingAs($user);

        $response = $this->get('/attendance/1');

        $response->assertStatus(200);

        $response->assertSee('テストユーザー');
        $response->assertSee('2025年');
        $response->assertSee('8月27日');
    }

    public function test_user_attendance_times_are_displayed_correctly()
    {
        $user = User::factory()->create([
            'name' => 'テストユーザー',
            'email' => 'general1@gmail.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'user',
        ]);

        $attendance = UserAttendanceRecord ::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-08-27',
            'clock_in_time' => '09:00:00',
            'clock_out_time' => '17:00:00',
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/' . $attendance->id);

        $response->assertStatus(200);

        $response->assertSee('09:00');
        $response->assertSee('17:00');
    }

    //11.勤怠詳細情報修正機能（一般ユーザ）
    public function test_error_when_clock_in_after_clock_out()
    {
        $user = User::factory()->create([
            'email' => 'general1@gmail.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'user',
        ]);

        $this->actingAs($user);

        $this->withoutMiddleware(\App\Http\Middleware\Authenticate::class);

        $response = $this->from('/attendance/1')->post('/user_attendance/update', [
            'clock_in_time'    => '17:30:00',
            'clock_out_time'   => '17:00:00',
            'reason'           => 'テスト',
        ]);

        $response->assertRedirect('/attendance/1');

        $response->assertSessionHasErrors(['clock_out_time' => '出勤時間もしくは退勤時間が不適切な値です']);
    }

    public function test_error_when_break_start_after_clock_out()
    {
        $user = User::factory()->create([
            'email' => 'general1@gmail.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'user',
        ]);
        $this->actingAs($user);

        $this->withoutMiddleware(\App\Http\Middleware\Authenticate::class);

        $response = $this->from('/attendance/1')->post('/user_attendance/update', [
            'clock_in_time'    => '09:00:00',
            'clock_out_time'   => '18:00:00',
            'break_start_time' => ['18:30:00'],
            'break_end_time'   => ['18:45:00'],
            'reason'           => 'テスト',
        ]);

        $response->assertRedirect('/attendance/1');

        $response->assertSessionHasErrors(['break_start_time.0' => '休憩時間が不適切な値です']);
    }

    public function test_error_when_break_out_after_clock_out()
    {
        $user = User::factory()->create([
            'email' => 'general1@gmail.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'user',
        ]);
        $this->actingAs($user);

        $this->withoutMiddleware(\App\Http\Middleware\Authenticate::class);

        $response = $this->from('/attendance/1')->post('/user_attendance/update', [
            'clock_in_time'    => '09:00:00',
            'clock_out_time'   => '18:00:00',
            'break_start_time' => ['12:30:00'],
            'break_end_time'   => ['18:45:00'],
            'reason'           => 'テスト',
        ]);

        $response->assertRedirect('/attendance/1');

        $response->assertSessionHasErrors(['break_end_time.0' => '休憩時間もしくは退勤時間が不適切な値です']);
    }

    public function test_user_validation_error_reason()
    {
        $user = User::factory()->create([
            'email' => 'general1@gmail.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'user',
        ]);

        $this->actingAs($user);

        $this->withoutMiddleware(\App\Http\Middleware\Authenticate::class);

        $response = $this->from('/attendance/1')->post('/user_attendance/update', [
            'clock_in_time'    => '06:00:00',
            'clock_out_time'   => '15:00:00',
            'break_start_time' => '11:00:00',
            'break_end_time'   => '12:00:00',
            'reason'           => '',
        ]);

        $response->assertRedirect('/attendance/1');

        $response->assertSessionHasErrors(['reason']);

        $errors = session('errors');
        $this->assertEquals('備考を記入してください', $errors->first('reason'));
    }

    public function test_display_pending_applications_when_pending_button_clicked()
    {
        $user = User::factory()->create([
            'email' => 'general1@gmail.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'user',
        ]);

        $this->actingAs($user);

        $pendingApplication = UserApplication::factory()->create([
            'status' => '承認待ち',
            'user_id' => $user->id,
            'target_date' => now(),
            'reason' => 'テスト1',
        ]);

        UserApplication::factory()->create([
            'status' => '承認済み',
            'user_id' => $user->id,
            'target_date' => now(),
            'reason' => 'テスト2',
        ]);

        $response = $this->get('/stamp_correction_request/list?status=承認待ち');

        $response->assertStatus(200);

        $response->assertSee($pendingApplication->reason);
        $response->assertSee($pendingApplication->user->name);
        $response->assertSee(\Carbon\Carbon::parse($pendingApplication->target_date)->format('Y/m/d'));
        $response->assertSee($pendingApplication->created_at->format('Y/m/d'));

        $response->assertDontSee('テスト2');
    }

    public function test_display_approved_applications_when_approved_button_clicked()
    {
        $user = User::factory()->create([
            'email' => 'general1@gmail.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'user',
        ]);

        $this->actingAs($user);

        UserApplication::factory()->create([
            'status' => '承認待ち',
            'user_id' => $user->id,
            'target_date' => now(),
            'reason' => 'テスト1',
        ]);

        $approvedApplication = UserApplication::factory()->create([
            'status' => '承認済み',
            'user_id' => $user->id,
            'target_date' => now(),
            'reason' => 'テスト2',
        ]);

        $response = $this->get('/stamp_correction_request/list?status=承認済み');

        $response->assertStatus(200);

        $response->assertSee($approvedApplication->reason);
        $response->assertSee($user->name);
        $response->assertSee(\Carbon\Carbon::parse($approvedApplication->target_date)->format('Y/m/d'));
        $response->assertSee($approvedApplication->created_at->format('Y/m/d'));

        $response->assertDontSee('テスト1');
    }

    //13.勤怠詳細情報取得・修正機能（管理者）
    public function test_admin_error_when_clock_in_after_clock_out()
    {
        $user = User::factory()->create([
            'email' => 'general2@gmail.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'admin',
        ]);

        $this->actingAs($user);

        $this->withoutMiddleware(\App\Http\Middleware\Authenticate::class);

        $response = $this->from('/attendance/1')->post('/attendance/1', [
            'clock_in_time'    => '17:30:00',
            'clock_out_time'   => '17:00:00',
            'reason'           => 'テスト',
        ]);

        $response->assertRedirect('/attendance/1');

        $response->assertSessionHasErrors(['clock_out_time' => '出勤時間もしくは退勤時間が不適切な値です']);
    }

    public function test_admin_error_when_break_start_after_clock_out()
    {
        $user = User::factory()->create([
            'email' => 'general2@gmail.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'admin',
        ]);
        $this->actingAs($user);

        $this->withoutMiddleware(\App\Http\Middleware\Authenticate::class);

        $response = $this->from('/attendance/1')->post('/attendance/1', [
            'clock_in_time'    => '09:00:00',
            'clock_out_time'   => '18:00:00',
            'break_start_time' => ['18:30:00'],
            'break_end_time'   => ['18:45:00'],
            'reason'           => 'テスト',
        ]);

        $response->assertRedirect('/attendance/1');

        $response->assertSessionHasErrors(['break_start_time.0' => '休憩時間が不適切な値です']);
    }

    public function test_admin_error_when_break_out_after_clock_out()
    {
        $user = User::factory()->create([
            'email' => 'general2@gmail.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'admin',
        ]);
        $this->actingAs($user);

        $this->withoutMiddleware(\App\Http\Middleware\Authenticate::class);

        $response = $this->from('/attendance/1')->post('/attendance/1', [
            'clock_in_time'    => '09:00:00',
            'clock_out_time'   => '18:00:00',
            'break_start_time' => ['12:30:00'],
            'break_end_time'   => ['18:45:00'],
            'reason'           => 'テスト',
        ]);

        $response->assertRedirect('/attendance/1');

        $response->assertSessionHasErrors(['break_end_time.0' => '休憩時間もしくは退勤時間が不適切な値です']);
    }

    public function test_admin_validation_error_reason()
    {
        $user = User::factory()->create([
            'email' => 'general2@gmail.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'admin',
        ]);

        $this->actingAs($user);

        $this->withoutMiddleware(\App\Http\Middleware\Authenticate::class);

        $response = $this->from('/attendance/1')->post('/attendance/1', [
            'clock_in_time'    => '06:00:00',
            'clock_out_time'   => '15:00:00',
            'break_start_time' => '11:00:00',
            'break_end_time'   => '12:00:00',
            'reason'           => '',
        ]);

        $response->assertRedirect('/attendance/1');

        $response->assertSessionHasErrors(['reason']);

        $errors = session('errors');
        $this->assertEquals('備考を記入してください', $errors->first('reason'));
    }

    //14.ユーザー情報取得機能（管理者）
    public function test_it_displays_all_users_names_and_emails()
    {
        $users = User::factory()->count(3)->create([
            'email_verified_at' => now(),
            'role' => 'user',
        ]);

        $user = User::factory()->create([
            'email' => 'general2@gmail.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'admin',
        ]);

        $this->actingAs($user);

        $response = $this->get('/admin/staff/list');

        foreach ($users as $user) {
            $response->assertSeeText($user->name);
            $response->assertSeeText($user->email);
        }

        $response->assertStatus(200);
    }

    //15.勤怠情報修正機能（管理者）
    public function test_admin_display_pending_applications_when_pending_button_clicked()
    {
        $user = User::factory()->create([
            'email' => 'general2@gmail.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'admin',
        ]);

        $this->actingAs($user);

        $pendingApplication = UserApplication::factory()->create([
            'status' => '承認待ち',
            'user_id' => $user->id,
            'target_date' => now(),
            'reason' => 'テスト1',
        ]);

        UserApplication::factory()->create([
            'status' => '承認済み',
            'user_id' => $user->id,
            'target_date' => now(),
            'reason' => 'テスト2',
        ]);

        $response = $this->get('/stamp_correction_request/list?status=承認待ち');

        $response->assertStatus(200);

        $response->assertSee($pendingApplication->reason);
        $response->assertSee($pendingApplication->user->name);
        $response->assertSee(\Carbon\Carbon::parse($pendingApplication->target_date)->format('Y/m/d'));
        $response->assertSee($pendingApplication->created_at->format('Y/m/d'));

        $response->assertDontSee('テスト2');
    }

    public function test_admin_display_approved_applications_when_approved_button_clicked()
    {
        $user = User::factory()->create([
            'email' => 'general2@gmail.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'admin',
        ]);

        $this->actingAs($user);

        UserApplication::factory()->create([
            'status' => '承認待ち',
            'user_id' => $user->id,
            'target_date' => now(),
            'reason' => 'テスト1',
        ]);

        $approvedApplication = UserApplication::factory()->create([
            'status' => '承認済み',
            'user_id' => $user->id,
            'target_date' => now(),
            'reason' => 'テスト2',
        ]);

        $response = $this->get('/stamp_correction_request/list?status=承認済み');

        $response->assertStatus(200);

        $response->assertSee($approvedApplication->reason);
        $response->assertSee($user->name);
        $response->assertSee(\Carbon\Carbon::parse($approvedApplication->target_date)->format('Y/m/d'));
        $response->assertSee($approvedApplication->created_at->format('Y/m/d'));

        $response->assertDontSee('テスト1');
    }

    public function test_admin_can_view_application_details()
    {
        $user = User::factory()->create([
            'email' => 'general2@gmail.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'admin',
        ]);

        $this->actingAs($user);

        $application = UserApplication::factory()->create([
            'status' => '承認待ち',
            'user_id' => $user->id,
            'target_date' => now(),
            'reason' => 'テスト理由',
            'clock_in_time' => now()->setTime(9, 0),
            'clock_out_time' => now()->setTime(17, 0),
        ]);

        $breakApplication = UserBreakApplication::factory()->create([
            'application_id' => $application->id,
            'user_id' => $user->id,
            'break_start_time' => now()->setTime(12, 0),
            'break_end_time' => now()->setTime(12, 30),
            'date' => now()->toDateString(),
        ]);

        $response = $this->get("/stamp_correction_request/approve/{$application->id}");

        $response->assertStatus(200);

        $response->assertSee($application->user->name);
        $response->assertSee(\Carbon\Carbon::parse($application->target_date)->format('Y年'));
        $response->assertSee(\Carbon\Carbon::parse($application->target_date)->format('n月j日'));
        $response->assertSee(\Carbon\Carbon::parse($application->clock_in_time)->format('H:i'));
        $response->assertSee(\Carbon\Carbon::parse($application->clock_out_time)->format('H:i'));
        $response->assertSee($application->reason);

        $response->assertSee('12:00');
        $response->assertSee('12:30');

        $response->assertSee('承認');
    }

    public function test_admin_can_approve_application_and_update_attendance_record()
    {
        $user = User::factory()->create([
            'email' => 'general2@gmail.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'admin',
        ]);

        $this->actingAs($user);

        $application = UserApplication::factory()->create([
            'user_id' => $user->id,
            'status' => '承認待ち',
            'clock_in_time' => '09:00:00',
            'clock_out_time' => '17:00:00',
            'target_date' => '2025-08-26',
        ]);
        $attendanceRecord = UserAttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-08-26',
            'clock_in_time' => null,
            'clock_out_time' => null,
        ]);

        $response = $this->actingAs($user)->post(route('user.attendance.approve', $application->id), [
            'breaks' => [],
        ]);

        $application->refresh();
        $this->assertEquals('承認済み', $application->status);

        $attendanceRecord->refresh();
        $this->assertEquals('09:00:00', $attendanceRecord->clock_in_time);
        $this->assertEquals('17:00:00', $attendanceRecord->clock_out_time);

        $response->assertRedirect(route('application.details', $application->id));
    }
}
