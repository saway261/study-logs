## PHPミニブログ開発計画（Symfony + Docker構成）

### フェーズ0：環境構築（1週目）

**目的：** Symfonyアプリがローカルで起動することを確認。
**作業内容：**

* Docker ComposeでPHP 8.2 + Apache + MySQL構成を作成。

  * サービス例：`app`（Symfonyコンテナ）, `db`（MySQL8）, `phpmyadmin`（任意）。
* `symfony new miniblog --webapp` でプロジェクト生成。
* `.env`のDB接続をMySQLに設定。
* `docker-compose exec app symfony console doctrine:database:create` が成功することを確認。

---

### フェーズ1：ドメイン設計・DBマイグレーション（2週目）

**目的：** 要件に基づくエンティティ・テーブルを定義。
**作業内容：**

* Entity作成

  * `Post`（1日1件、自由記述・日付ユニーク）
  * `Subject`（科目カタログ）
  * `PostSubject`（記事×科目の内容・学習時間）
  * `Admin`（許可メール保持）
* Doctrineアノテーションで関連付け（OneToMany, ManyToOne）。
* `doctrine:migrations:diff` → `migrations:migrate`。
* `php bin/console doctrine:schema:validate` で確認。

---

### フェーズ2：基本機能の実装（3〜4週目）

**目的：** 投稿〜閲覧の基本導線を構築。
**作業内容：**

* ルーティング：

  * `/`：カレンダー表示
  * `/post/{date}`：記事詳細
  * `/post/new`：投稿フォーム
* Controller / Repository / Twigで実装。
* 投稿フォームではCSRF対策と簡易バリデーション。
* カレンダー表示はDoctrineで当月分の記事日付を取得して印を付ける。

---

### フェーズ3：認証（5週目）

**目的：** 管理機能を本人のみ利用可能に。
**作業内容：**

* `knpuniversity/oauth2-client-bundle` を導入。
* Google OAuth 2.0認証を設定。
* 許可メールのみが投稿・編集・削除できるように制御。
* 認証情報は`.env.local`に保持。

---

### フェーズ4：編集・削除・共有機能（6〜7週目）

**目的：** 投稿後の操作を整備。
**作業内容：**

* 記事詳細から「修正」「削除」「コピーして共有」を追加。
* 削除は論理削除。
* 共有テキスト生成はService層で実装。
* Twigで前日／翌日へのナビゲーションリンクを表示。

---

### フェーズ5：デザイン・最終調整（8週目）

**目的：** 公開可能な最小完成版。
**作業内容：**

* Bootstrapを軽く導入（または自作CSS）。
* スマホ表示確認（レスポンシブ対応）。
* SEO最小設定（タイトル・meta）。
* `.env.production` 設定・MySQL dump。
* レンタルサーバー（CoreServer等）へデプロイ。

---

### フェーズ6（任意）：運用拡張

* 管理操作ログ保存
* 投稿一覧API化（将来的にNext.jsで表示）
* 月次バックアップスクリプト

---

この全体で**約8週間（週10時間想定）**。
Symfonyの仕組みに慣れるには、まずフェーズ0〜2を丁寧に進めるのが肝です。DoctrineやTwigを使いこなす段階で、Symfonyらしい流れがつかめます。

---

次に詳細設計へ進む際は、
**Docker構成ファイル例**（`docker-compose.yml`）と**Symfonyプロジェクトディレクトリ構成**を提示して、環境構築フェーズを具体化すると良いです。

続けて「フェーズ0（環境構築）」のdocker-compose設計から書き起こしましょうか？
