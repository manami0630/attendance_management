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

 ## テストアカウント
 ### 一般ユーザー
 - name:山田太郎
 - email:yamada@example.com
 - password:password123

 ### 管理者
 - name:山本太郎
 - email:yamamoto@example.com
 - password:password456
   
 ### PHPUnitを利用したテストに関して
 以下のコマンド:
 1. `docker-compose exec mysql bash`
 2. `mysql -u root -p`
  - パスワードはrootと入力
 3. `CREATE DATABASE demo_test;`
 4. `php artisan key:generate --env=testing`
 5. `php artisan migrate --env=testing`
 
## 使用技術
- PHP 7.4.9
- Laravel 8.83.8
- MySQL 8.0.26

## ER図
<img width="808" height="675" alt="スクリーンショット 2025-08-06 000135" src="https://github.com/user-attachments/assets/db1674c8-b4db-439b-9152-456edf3f9a2e" />

## URL
- 開発環境: [http://localhost](http://localhost)
- phpMyAdmin: [http://localhost:8080/](http://localhost:8080/)
- mailhog:  [http://localhost:8025/](http://localhost:8025/)
