<?php
/**
 * お問い合わせフォーム送信・自動返信
 *
 * 設定項目：
 * - 送信元アドレス（From）
 * - 転送アドレス（届け先）
 * - 相手方（お客様）への自動返信メールの内容
 * - こちら側（管理者）へ届く通知メールの内容
 */

// ============================================================
// 設定（ここを編集してください）
// ============================================================

// --- 送信元アドレス ---
// メールの「差出人」として表示されるアドレス（サーバーで送信許可されているアドレスにしてください）
define('SENDER_EMAIL', 'noreply@example.com');
define('SENDER_NAME', 'ジョブプロマーケ');  // 差出人表示名

// --- 転送アドレス ---
// フォーム内容を受け取るメールアドレス（貴社の担当者アドレス）
define('FORWARD_EMAIL', 'sc30kd35ma30@gmail.com');

// --- 相手方（お客様）への自動返信メール ---
define('AUTOREPLY_SUBJECT', '【ジョブプロマーケ】お問い合わせを受け付けました');
define('AUTOREPLY_BODY', <<<EOT
お問い合わせいただきありがとうございます。
ジョブプロマーケでございます。

以下の内容でお問い合わせを受け付けいたしました。
内容を確認のうえ、担当者よりご連絡させていただきます。

今しばらくお待ちくださいますようお願い申し上げます。

────────────────────────────────
【お問い合わせ内容】
※このメールは自動送信されています。
────────────────────────────────
EOT);

// --- こちら側（管理者）へ届く通知メール ---
define('ADMIN_SUBJECT', '【ジョブプロマーケ】お問い合わせがありました');
define('ADMIN_HEADER', <<<EOT
Webサイトのお問い合わせフォームから新しい申し込みがありました。

────────────────────────────────
EOT);
define('ADMIN_FOOTER', <<<EOT

────────────────────────────────
※このメールはフォームから自動送信されています。
EOT);

// ============================================================
// 処理（通常は編集不要）
// ============================================================

header('Content-Type: application/json; charset=UTF-8');
mb_language('Japanese');
mb_internal_encoding('UTF-8');

$result = ['ok' => false, 'message' => ''];

// POST のみ受付
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['ok' => false, 'message' => '不正なリクエストです。']);
  exit;
}

// 入力取得・サニタイズ
$company = isset($_POST['company']) ? trim($_POST['company']) : '';
$name    = isset($_POST['name'])    ? trim($_POST['name'])    : '';
$email   = isset($_POST['email'])   ? trim($_POST['email'])   : '';
$phone   = isset($_POST['phone'])   ? trim($_POST['phone'])   : '';
$inquiry = isset($_POST['inquiry']) ? trim($_POST['inquiry']) : '';

// 必須チェック
if ($company === '' || $name === '' || $email === '' || $phone === '') {
  echo json_encode(['ok' => false, 'message' => '必須項目をご入力ください。']);
  exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  echo json_encode(['ok' => false, 'message' => '正しいメールアドレスを入力してください。']);
  exit;
}

// 相手方への自動返信本文（入力内容を追記）
$customer_body = AUTOREPLY_BODY . "\n";
$customer_body .= "会社名： " . $company . "\n";
$customer_body .= "ご担当者名： " . $name . "\n";
$customer_body .= "Email： " . $email . "\n";
$customer_body .= "電話番号： " . $phone . "\n";
$customer_body .= "お問い合わせ：\n" . ($inquiry !== '' ? $inquiry : '（未入力）') . "\n\n";
$admin_body = ADMIN_HEADER . "\n";
$admin_body .= "会社名： " . $company . "\n";
$admin_body .= "担当者名： " . $name . "\n";
$admin_body .= "Email： " . $email . "\n";
$admin_body .= "電話番号： " . $phone . "\n";
$admin_body .= "お問い合わせ：\n" . ($inquiry !== '' ? $inquiry : '（未入力）') . "\n";
$admin_body .= ADMIN_FOOTER;

$from_header = 'From: ' . mb_encode_mime_header(SENDER_NAME, 'UTF-8', 'B') . ' <' . SENDER_EMAIL . '>';
$reply_to = 'Reply-To: ' . $email;  // 返信はお客様アドレスへ
$content_type = 'Content-Type: text/plain; charset=UTF-8';

$headers_admin = $from_header . "\r\n" . $reply_to . "\r\n" . $content_type . "\r\n";
$headers_customer = $from_header . "\r\n" . $content_type . "\r\n";

$sent_admin = @mb_send_mail(FORWARD_EMAIL, ADMIN_SUBJECT, $admin_body, $headers_admin);
$sent_autoreply = @mb_send_mail($email, AUTOREPLY_SUBJECT, $customer_body, $headers_customer);

if ($sent_admin || $sent_autoreply) {
  echo json_encode(['ok' => true, 'message' => '送信しました。']);
} else {
  echo json_encode(['ok' => false, 'message' => '送信に失敗しました。しばらくしてから再度お試しください。']);
}
