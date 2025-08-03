# 勤怠管理アプリ

## 環境構築

### Dockerビルド
1. `git clone git@github.com:manami0630/attendance_management.git`
2. `docker-compose up -d --build`

* MySQLは、OSによって起動しない場合があるので、それぞれのPCに合わせてdocker-compose.ymlファイルを編集してください。

### Laravel環境構築
1. `docker-compose exec php bash`
2. `composer install`
3. .env.exampleファイルから.envを作成し、環境変数を変更
4. `php artisan key:generate`
5. `php artisan migrate`
6. `php artisan db:seed`
7. `php artisan storage:link`

* .envに以下の値を入力してください。
   - MAIL_FROM_ADDRESS=例: your-email@example.com

## 使用技術
- PHP 7.4.9
- Laravel 8.83.8
- MySQL 8.0.26

## ER図
<img width="853" height="711" alt="スクリーンショット 2025-08-03 204210" src="https://github.com/user-attachments/assets/cd017f00-2a9b-4f64-9ca1-aa6c3148d3dd" />


## URL
- 開発環境: [http://localhost](http://localhost)
- phpMyAdmin: [http://localhost:8080/](http://localhost:8080/)
- mailhog:  [http://localhost:8025/](http://localhost:8025/)
