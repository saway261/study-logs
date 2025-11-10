# 学習記録アプリ

## 概要 Summary

インプット→アウトプットを効果的・習慣的にするための日報あるいは学習記録webアプリケーション。


## 詳細 Detail
### 開発環境
#### 使用技術
- WSL(Ubuntu)
- PHP 8.2.29
- Symfony 7.3.4 
- Docker 28.5.1
- MySQL 8.0.44

#### 使用ツール
- Composer 2.8.12
- Cursor
- Xdebug
- Git
- GitHub
- phpMyAdmin


### ER図 Entity-Relation Diagram

```mermaid
erDiagram

    POST {
        INT id PK
        DATE date
        TEXT content
        BOOL is_deleted
    }
    SUBJECT {
        INT id PK
        VARCHAR name
        INT status_is FK "外部キー (SUBJECT_STATUS.id)"
        BOOL is_deleted
    }
    POST_SUBJECT {
        INT id PK
        INT subject_id FK "外部キー (SUBJECT.id)"
        INT post_id FK "外部キー (POST.id)"
        INT minutes
        VARCHAR summary
    }
    SUBJECT_STATUS {
        INT id PK
        VARCHAR status UK
    }

    POST ||--o{ POST_SUBJECT : "has many"
    SUBJECT ||--o{ POST_SUBJECT : "is linked to"
    SUBJECT_STATUS ||--o{ SUBJECT : "categorizes"

```

### シーケンス図 Sequence Diagram
#### ▼日報管理機能
```mermaid
sequenceDiagram
    actor Browser
    participant C as Controller
    participant DB as Database
    
    Note over Browser, DB: 日報詳細取得
    Browser ->> C: GET /posts/{date}
    C ->> DB: dateが完全一致かつアクティブな<br>POSTを要求
    DB -->> C: POSTを返す
    break POSTが空の場合
        C -->> Browser: 404 NotFound:記事が存在しません。
    end
    C -->> Browser: 200 OK (日報データが返る)

    Note over Browser, DB: 日報新規作成
    Browser ->> C: GET /posts/new
    C -->> Browser: 日報新規作成フォーム
    Browser ->> C: POST /posts<br>フォームを送信
    C ->> DB: アクティブなSUBJECT.nameのリストを要求
    DB -->> C: アクティブなSUBJECT.nameのリスト
    alt 科目名がリストに存在しない場合
        C->>DB :SUBJECTを新規作成
    end
    C ->> DB: POSTを新規作成
    DB -->> C: POSTを返す
    C ->> C: GET /posts/{date}
    C -->> Browser: 200 OK (新規作成した日報データが返る)
```


#### ▼科目管理機能
```mermaid
sequenceDiagram
    actor Browser
    participant C as Controller
    participant DB as Database

    Note over Browser, DB: 科目一覧取得
    Browser ->> C: GET /subjects
    C ->> DB: アクティブなSUBJECT一覧を要求
    DB -->> C: アクティブなSUBJECT一覧を返す
    C -->> Browser: 200 OK (SUBJECT一覧が返る)

    Note over Browser, DB: 科目新規作成
    Browser ->> C: POST /subjects
    C ->> DB: SUBJECTを新規作成
    alt SUBJECT.nameが重複する場合
        DB -->> C: UniqueConstraintViolationException
        C -->> Browser: 302 Found<br>同名の学習中・学習完了科目が既に存在します。
    else 
        C -->> Browser: 200 OK<br>科目を追加しました。
    end

    Note over Browser, DB: 科目更新
    Browser ->> C: GET /subjects/{Id}/edit
    C -->> Browser: 科目更新フォーム
    Browser ->> C: PUT /subjects/{id}
    C ->> DB: SUBJECTを更新
    alt SUBJECT.nameが重複する場合
        DB -->> C: UniqueConstraintViolationException
        C -->> Browser: 409 Conflict<br>同名の学習中・学習完了科目が既に存在します。
    else 
        C -->> Browser: 200 OK /302 redirect <br>科目を更新しました。(SUBJECT一覧が返る)
    end

    Note over Browser, DB: 科目削除(論理)
    Browser ->> C: GET /subjects/{Id}/edit
    C -->> Browser: 科目更新フォーム
    Browser ->> C: DELETE /subjects/{id}
    C ->> DB: SUBJECTを更新
    C -->> Browser: 200 OK /302 redirect <br>科目を削除しました。(SUBJECT一覧が返る)

    
```

## 今後の課題
- 日報編集・削除機能の実装
- posts/{date}: {date}が形式不正、NotFoundの際の例外ハンドリング
- 認証機能実装およびCSRF対策有効化
