# デプロイ先ごとのフォーム動作

このプロジェクトは **どちらの環境でも同じコード** で動くようにしてあります。

| 環境 | フォームの動き |
|------|----------------|
| **PHP が動くサーバー**（レンタルサーバーなど） | 最初に `send.php` に送信。送信元・転送先・自動返信は `send.php` の設定で変更できます。 |
| **Railway など静的のみ** | `send.php` は動かないので、自動で Formspree に送信（フォールバック）。Formspree のフォームIDを 1 箇所入れるだけです。 |

---

## PHP 環境（レンタルサーバーなど）

- そのままアップロードすればフォームは動きます。
- 送信元・転送先・自動返信は **send.php の先頭** の定数で変更してください。

---

## Railway（静的サイト）でフォームを動かす手順

1. **Formspree に登録**  
   https://formspree.io でアカウント作成

2. **フォームを 1 つ作成**  
   「New Form」でフォームを作成し、**転送先メールアドレス**（届け先）を設定

3. **フォームIDを取得**  
   作成したフォームの URL が `https://formspree.io/f/xxxxxxxx` のようになっているので、  
   **`xxxxxxxx` の部分**（フォームID）をコピー

4. **index.html のフォームを 1 箇所だけ修正**  
   `data-fallback-formspree-id` にフォームIDを入れます：

   ```html
   <form ... data-fallback-formspree-id="ここにフォームID">
   ```

   例：フォームIDが `abcdexyz` なら

   ```html
   data-fallback-formspree-id="abcdexyz"
   ```

5. **プッシュ**  
   変更をコミットして Railway にプッシュ

送信時はまず `send.php` に送信し、失敗した場合（PHP が動かない環境）だけ自動で Formspree に送信されます。

---

## Formspree で設定できること

| 項目 | 設定場所 |
|------|----------|
| **転送アドレス**（届け先） | Formspree のフォーム設定で「Email」 |
| **送信元・表示** | Formspree のメール設定（ドメイン認証など） |
| **相手方への自動返信** | フォームの「Auto-Responder」で件名・本文を編集 |

無料プランは月 50 通までです。
