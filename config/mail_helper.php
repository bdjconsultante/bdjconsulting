<?php
declare(strict_types=1);

/*
 * BDJ Consulting — Envoi d'e-mails
 *
 * Envoie via PHPMailer + SMTP (IONOS) dès que la librairie est présente et
 * qu'un identifiant + mot de passe SMTP sont configurés. Sinon, repli sur
 * mail() (comportement historique) pour ne jamais casser le site.
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
    $username = trim((string)($smtp['username'] ?? ''));
    $password = (string)($smtp['password'] ?? '');
    if ($token !== '') $username = $token; // compatibilité Postmark (token en guise d'identifiant)

    // Prêt si un identifiant est fourni avec un mot de passe (IONOS)
    // ou un token (Postmark, sans mot de passe).
    $smtpReady = $bdj_phpmailer_loaded && $username !== '' && ($password !== '' || $token !== '');

    if ($smtpReady) {
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = $smtp['host'] ?? 'smtp.ionos.com';
            $mail->Port       = (int)($smtp['port'] ?? 587);
            $mail->SMTPAuth   = true;
            $mail->Username   = $username;
            $mail->Password   = $password;
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
    $primary = array_shift($to);
    if ($primary === null) return ['ok' => false, 'error' => 'Aucun destinataire configuré.'];

    $headers  = "From: " . ($config['from_name'] ?? 'BDJ Consulting') . " <$fromEmail>\r\n";
    if ($replyEmail !== '') $headers .= "Reply-To: $replyEmail\r\n";
    if ($to) $headers .= "Bcc: " . implode(', ', $to) . "\r\n"; // co-destinataires cachés entre eux
    $headers .= "X-Mailer: BDJ Consulting Website\r\n";

    if (!$attachments) {
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "Content-Transfer-Encoding: 8bit\r\n";
        $ok = @mail($primary, $subject, $text, $headers);
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

    $ok = @mail($primary, $subject, $body, $headers);
    return ['ok' => $ok, 'error' => $ok ? '' : 'mail() a échoué'];
}

/**
 * Limitation simple par IP (fichier temporaire) : autorise au plus $max
 * envois par $window secondes. Retourne true si la requête est autorisée.
 * Si le stockage temporaire n'est pas accessible, autorise (true) pour ne
 * jamais bloquer le site.
 */
function bdj_rate_limit(string $key, int $max = 5, int $window = 900): bool
{
    $dir = rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'bdj_rl';
    if (!is_dir($dir) && !@mkdir($dir, 0700, true) && !is_dir($dir)) {
        return true;
    }

    $file = $dir . DIRECTORY_SEPARATOR . hash('sha256', $key) . '.json';
    $now = time();
    $hits = [];

    if (is_file($file)) {
        $raw = @file_get_contents($file);
        $decoded = $raw !== false ? json_decode($raw, true) : null;
        if (is_array($decoded)) {
            foreach ($decoded as $t) {
                if (is_int($t) && $t > $now - $window) $hits[] = $t;
            }
        }
    }

    if (count($hits) >= $max) {
        return false;
    }

    $hits[] = $now;
    @file_put_contents($file, json_encode($hits), LOCK_EX);
    return true;
}