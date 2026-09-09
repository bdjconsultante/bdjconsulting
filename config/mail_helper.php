<?php
declare(strict_types=1);

/*
 * BDJ Consulting — Envoi d'e-mails
 *
 * Envoie via PHPMailer + SMTP (Postmark) dès que le librairie est présente
 * et qu'un token SMTP est configuré. Sinon, repli sur mail() (comportement
 * historique) pour ne jamais casser le site.
 *
 * Installation de PHPMailer (au choix) :
 *   - Composer :  composer require phpmailer/phpmailer   (utilise vendor/autoload.php)
 *   - Manuel    :  déposer le dossier "phpmailer/src" dans assets/lib/phpmailer/
 */

$bdj_phpmailer_loaded = false;
foreach ([
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../assets/lib/phpmailer/src/PHPMailer.php',
] as $bdj_file) {
    if (!is_file($bdj_file)) continue;
    if (basename($bdj_file) === 'autoload.php') {
        require_once $bdj_file;
    } else {
        $bdj_src = dirname($bdj_file);
        require_once $bdj_src . '/Exception.php';
        require_once $bdj_src . '/PHPMailer.php';
        if (is_file($bdj_src . '/SMTP.php')) require_once $bdj_src . '/SMTP.php';
    }
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        $bdj_phpmailer_loaded = true;
        break;
    }
}
unset($bdj_file);

/**
 * Résultat : ['ok' => bool, 'error' => string].
 *
 * @param array $config      Contenu de mail_config.php
 * @param string[] $to       Destinataires
 * @param string $subject    Sujet (déjà assaini)
 * @param string $text       Corps texte brut
 * @param string $replyEmail Email du visiteur (Reply-To)
 * @param string $replyName  Nom du visiteur
 * @param array[] $attachments [['path'=>..., 'name'=>..., 'mime'=>...], ...]
 */
function bdj_send_mail(
    array $config,
    array $to,
    string $subject,
    string $text,
    string $replyEmail = '',
    string $replyName = '',
    array $attachments = []
): array {
    global $bdj_phpmailer_loaded;

    $smtp = is_array($config['smtp'] ?? null) ? $config['smtp'] : [];
    $token = trim((string)($smtp['token'] ?? ''));

    if ($bdj_phpmailer_loaded && $token !== '') {
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = $smtp['host'] ?? 'smtp.postmarkapp.com';
            $mail->Port       = (int)($smtp['port'] ?? 587);
            $mail->SMTPAuth   = true;
            $mail->Username   = $token;
            $mail->Password   = (string)($smtp['password'] ?? '');
            $mail->SMTPSecure = $smtp['encryption'] ?? 'tls';
            $mail->CharSet    = 'UTF-8';
            $mail->Encoding   = 'base64';

            $mail->setFrom($config['from'], $config['from_name'] ?? 'BDJ Consulting');
            if ($replyEmail !== '') $mail->addReplyTo($replyEmail, $replyName !== '' ? $replyName : $replyEmail);
            foreach ($to as $dest) $mail->addAddress($dest);
            $mail->Subject = $subject;
            $mail->isHTML(false);
            $mail->Body = $text;
            foreach ($attachments as $a) {
                $mail->addStringAttachment((string)file_get_contents($a['path']), $a['name'], 'base64', $a['mime']);
            }
            $mail->send();
            return ['ok' => true, 'error' => ''];
        } catch (Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    // --- Repli : mail() ---
    $fromEmail = $config['from'];
    $headers  = "From: " . ($config['from_name'] ?? 'BDJ Consulting') . " <$fromEmail>\r\n";
    if ($replyEmail !== '') $headers .= "Reply-To: $replyEmail\r\n";
    $headers .= "X-Mailer: BDJ Consulting Website\r\n";

    if (!$attachments) {
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "Content-Transfer-Encoding: 8bit\r\n";
        $ok = @mail(implode(', ', $to), $subject, $text, $headers);
        return ['ok' => $ok, 'error' => $ok ? '' : 'mail() a échoué'];
    }

    $boundary = md5((string)microtime(true));
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

    $body = "--$boundary\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $body .= $text . "\r\n";
    foreach ($attachments as $a) {
        $body .= "--$boundary\r\n";
        $body .= "Content-Type: {$a['mime']}; name=\"{$a['name']}\"\r\n";
        $body .= "Content-Disposition: attachment; filename=\"{$a['name']}\"\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $body .= chunk_split(base64_encode((string)file_get_contents($a['path']))) . "\r\n";
    }
    $body .= "--$boundary--\r\n";

    $ok = @mail(implode(', ', $to), $subject, $body, $headers);
    return ['ok' => $ok, 'error' => $ok ? '' : 'mail() a échoué'];
}