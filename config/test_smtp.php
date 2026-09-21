<?php
declare(strict_types=1);

/*
 * Test d'envoi SMTP — BDJ Consulting
 *
 * À lancer en ligne de commande (jamais via le navigateur) :
 *   php config/test_smtp.php destinataire@example.com
 *
 * Le dossier config/ est bloqué par .htaccess : ce script n'est pas
 * accessible depuis le web.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Ce script s\'exécute uniquement en ligne de commande.');
}

$config = require __DIR__ . '/mail_config.php';
require __DIR__ . '/mail_helper.php';

$to = $argv[1] ?? ($config['contact_recipients'][0] ?? '');
if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Usage : php config/test_smtp.php destinataire@example.com\n");
    exit(1);
}

$smtp = is_array($config['smtp'] ?? null) ? $config['smtp'] : [];
$username = trim((string)($smtp['username'] ?? ''));
$password = (string)($smtp['password'] ?? '');

if ($username === '' || $password === '') {
    fwrite(STDERR, "Attention : identifiants SMTP absents (username/password).\n");
    fwrite(STDERR, "Le test retombera sur mail() et ne valide PAS l'envoi IONOS.\n\n");
}

$subject = 'Test SMTP BDJ Consulting — ' . date('Y-m-d H:i:s');
$body = "Test d'envoi depuis le site BDJ Consulting.\n\n"
      . "Si vous recevez ce message, la configuration SMTP fonctionne.\n";

$result = bdj_send_mail($config, [$to], $subject, $body, $to, 'BDJ Test');

if ($result['ok']) {
    echo "OK — message envoyé à $to\n";
    exit(0);
}

fwrite(STDERR, "ÉCHEC — " . $result['error'] . "\n");
exit(2);
