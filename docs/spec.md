# PHPミニブログ 技術仕様書（素案・v2）

## 0. 前提
- 投稿者は本人のみ（Google OAuth 2.0で許可メール1件を認可）。
- **1日1本**の学習記録。
- 各記事は**科目ごとの学習内容と学習時間（15分刻み）**を保持。
- PC/スマホ対応、SEO最小・SNS共有想定（X等）。
- **削除は論理削除**（`is_deleted`/`deleted_at`）。

---

## 1. 技術スタック / 運用
- 言語：PHP 8.2 以上
- Webサーバ：Apache 2.4（レンタルサーバ標準）
- DB：MySQL 8.x（`utf8mb4` / `utf8mb4_general_ci`）
- 認証：Google OAuth 2.0（OpenID Connect / emailスコープ）
- 依存管理：Composer（必要最小限。例：Google API Client）
- 文字コード：`utf8mb4`（絵文字対応）
- タイムゾーン：Asia/Tokyo（PHP `date_default_timezone_set('Asia/Tokyo')`）

---

## 2. ディレクトリ構成（バニラPHP想定）

```
/ (project root)
├─ public/             # ドキュメントルート
│  ├─ index.php        # ルーティングエントリ
│  ├─ assets/
│  │  ├─ css/          # Minimal CSS（自作）
│  │  ├─ js/           # 必要最小のバニラJS
│  │  └─ img/
│  └─ .htaccess        # フロントコントローラに集約
├─ app/
│  ├─ Controller/
│  │  ├─ PostController.php
│  │  ├─ CalendarController.php
│  │  └─ AuthController.php
│  ├─ Model/
│  │  ├─ Post.php
│  │  ├─ Subject.php
│  │  ├─ PostSubject.php
│  │  └─ Admin.php
│  ├─ Service/
│  │  ├─ AuthService.php
│  │  ├─ PostService.php
│  │  └─ ShareService.php   # 共有テキスト生成など
│  ├─ Repository/
│  │  ├─ PostRepository.php
│  │  ├─ SubjectRepository.php
│  │  └─ AdminRepository.php
│  ├─ View/       # PHPテンプレート（共通レイアウト含む）
│  │  ├─ layout.php
│  │  ├─ calendar.php
│  │  ├─ post_form.php
│  │  ├─ post_detail.php  
│  │  └─ post_edit.php
│  └─ Support/
│     ├─ DB.php     # PDO生成・接続
│     ├─ Csrf.php
│     ├─ Validator.php
│     └─ Router.php
├─ config/
│  ├─ app.php       # ベースURL・タイムゾーン等
│  └─ oauth.php     # GoogleクライアントID/Secret、Callback
├─ database/
│  ├─ migrations/           # SQLファイル
│  └─ seeds/                # 初期データ（subjects等）
├─ logs/                    # アプリログ（必要なら）
├─ vendor/                  # Composer
├─ .env                     # DB接続/許可メール等（配布は.env.sample）
└─ composer.json
```

---

## 3. ルーティング（例）
- `GET /` … カレンダー表示（当月、記事有日に●）
- `GET /post/{YYYY-MM-DD}` … 記事詳細（=確認画面）
- `GET /post/new` … 投稿フォーム
- `POST /post` … 投稿（保存→**詳細へ即リダイレクト＝公開**）
- `GET /post/{date}/edit` … 編集フォーム
- `POST /post/{date}/update` … 更新（同一URL維持、保存→**詳細へ即リダイレクト＝公開**)
- `POST /post/{date}/delete` … 論理削除（is_deleted=1, deleted_at）
- `POST /post/{date}/sharetext` … 共有テキストを生成し返却
- `GET /auth/login` / `GET /auth/callback` / `POST /auth/logout`

※ すべてCSRFトークン検証（POST）。管理操作は要ログイン＋許可メール一致。

---

## 4. 画面仕様（技術観点）
### 4.1 カレンダー
- サーバー側で月次グリッドを生成（PHP）し、記事有日は●印。
- 前月/翌月は`?ym=2025-09`のようなクエリで遷移。
- 今日のハイライトはサーバー側で判定しCSSクラス付与。

### 4.2 投稿・編集フォーム（バニラJS最小）
- 入力項目：日付、科目、科目内容、学習時間（15分刻み）、自由記述。
- バリデーション：日付重複禁止、学習時間は15の倍数のみ。

### 4.3 記事詳細（=確認）
- 表示：日付／科目一覧（内容・時間）／自由記述。
- 操作：修正・コピーして共有・前日/翌日移動・削除。

---

## 5. データモデル（概要）
- `post`：1日1件、自由記述（1000字以内）、論理削除。
- `subject`：科目カタログ、論理削除対応。
- `post_subject`：記事と科目の関係（内容＋学習時間）。
- `admin`：許可メール保持。

制約例：
- `post_date` UNIQUE。
- `study_minutes` CHECK(15の倍数, 0-1440範囲)。

---

## 6. 認証・認可
- Google OAuth 2.0（emailスコープ）。
- 許可メール一致時のみ管理機能を解放。

---

## 7. セキュリティ
- HTTPS必須、CSRFトークン、XSS対策、SQLプレースホルダ必須。
- セッション：HttpOnly + Secure。

---

## 8. 運用
- レンタルサーバー：public/を公開ディレクトリに配置。
- 環境設定：`.env` でDB接続・許可メール・OAuth情報管理。
- バックアップ：月1回のDBダンプ。
- ログ：エラーログ + 管理操作の最低限の記録。
