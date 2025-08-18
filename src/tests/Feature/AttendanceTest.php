<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserAttendanceRecord;
use App\Models\UserBreakTime;
use App\Models\UserApplication;
use App\Models\UserBreakApplication;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function user_can_view_current_datetime_formatted_on_attendance_screen()
    {
        // 1. 必要なデータを準備（任意: ユーザー作成・認証）
        $user = User::factory()->create([
            'password' => bcrypt('password'), // ログイン時のパスワード
        ]);

        // 認証状態を設定（画面表示が認証済みユーザー前提の場合）
        $this->actingAs($user);

        // 現在時刻を取得（テスト内での基準時刻）
        $now = Carbon::now();

        // 2. 勤怠打刻画面へアクセス
        // 実際のURLに合わせて変更してください
        $response = $this->get('/attendance');

        // 3. 応答の HTML に現在時刻が所定の形式で表示されていることを検証
        // 例: UI に "YYYY-MM-DD HH:mm" の形式で表示されている場合
        // 実際のフォーマットが異なる場合 format を調整してください
        $expectedFormatted = $now->format('Y-m-d H:i');

        // ここでは単純に HTML 内容に日時が含まれているかを検証します。
        // より厳密に検証したい場合は、レスポンスの content を直接照合してください。
        $response->assertStatus(200);
        $response->assertSee($expectedFormatted);
    }

}
