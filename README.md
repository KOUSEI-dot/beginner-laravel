# beginner-laravel

登録したユーザーが、商品を出品。購入できる Web アプリケーションです。

---

## 環境構築

以下の手順でローカル環境を構築できます。

```bash
# リポジトリをクローン
git clone https://github.com/KOUSEI-dot/beginner-laravel.git
cd beginner-laravel

docker-compose up --build
⇨一度ターミナルを閉じる。

cd beginner-laravel

docker-compose exec php bash


# パッケージインストール
composer install || composer update

exit

cd src


npm install

# フロントエンドアセットをビルド（ 開発用ビルド）
npm run dev   # 開発環境向け

# .env 設定
cp .env.example .env
php artisan key:generate

```

隠しファイルの.env を以下のように書き換える。⇩

```env
# DB 接続設定

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass

# メール送信設定 (Mailtrap を利用)

MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=xxxxxxxxxxxxxx # Mailtrap のダッシュボードに表示される値
MAIL_PASSWORD=yyyyyyyyyyyyyy # Mailtrap のダッシュボードに表示される値
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=noreply@example.test
MAIL_FROM_NAME="${APP_NAME}"

```

⚠️ MAIL_USERNAME と MAIL_PASSWORD は Mailtrap の管理画面から取得してください。

beginner-laravel/docs 内にある以下の画像ファイルを storage/app/public にコピーしてください。

```bash
・comment-icon.jpg
・HDD.jpg
・logo.svg
・star-icon.jpg
・userアイコン１.png
・コーヒーミル.jpg
・ショルダーバッグ.jpg
・タンブラー.jpg
・ノートPC.jpg
・マイク.jpg
・メイクセット.jpg
・革靴.jpg
・玉ねぎ３束.jpg
・時計.jpg

その後、シンボリックリンクを作成します。

php artisan storage:link

```

## 使用技術（実行環境）

- Laravel 8.x （composer.json の "laravel/framework": "^8.75" より）
- PHP 7.3〜8.x 対応
- MySQL（.env にて DB_CONNECTION=mysql が設定されているため）
- Laravel Fortify（認証機能）
- Laravel Sanctum（API トークン認証に対応可能）
- Guzzle HTTP Client（API 通信）
- CORS 対応（fruitcake/laravel-cors）
- Tinker（REPL 環境）
- Laravel Sail（ローカル開発用 Docker 環境オプション）
- テスト環境（PHPUnit、Mockery、Faker）
- Mailtrap（開発用 SMTP メール環境）
- Redis（オプション）
- .env に Redis の記載があり、キューやキャッシュでの利用を想定

## ER 図

![ER図](docs/ERD.png)

## URL

・開発環境：http://localhost
・phpMyAdmin:http://localhost:8000

# beginner-laravel
