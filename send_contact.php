<?php
declare(strict_types=1);

$config = require __DIR__ . '/config/mail_config.php';
require __DIR__ . '/config/mail_helper.php';
$recipients = $config['contact_recipients'];
$fromEmail = $config['from'];

function clean_contact(string $v): string {
    return trim(preg_replace('/[\r\n\x00]+/', ' ', strip_tags($v)));
}

function is_bot_request(): bool {
    $website = trim((string)($_POST['website'] ?? ''));
    $ts = (int)($_POST['ts'] ?? 0);
    if ($website !== '') return true;
    if (!$ts || $ts > time() || time() - $ts > 2 * 24 * 3600) return true;
    return false;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contact.html');
    exit;
}

$nom = clean_contact($_POST['nom'] ?? '');
$entreprise = clean_contact($_POST['entreprise'] ?? '');
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$telephone = clean_contact($_POST['telephone'] ?? '');
$objet = clean_contact($_POST['objet'] ?? '');
$message = clean_contact($_POST['message'] ?? '');

$errors = [];
if (is_bot_request()) $errors[] = 'Requête invalide.';
if (!$nom) $errors[] = 'Nom obligatoire.';
if (!$email) $errors[] = 'Adresse e-mail invalide.';
if (!$message) $errors[] = 'Message obligatoire.';

if ($errors) {
    http_response_code(400);
    echo '<!doctype html><meta charset="utf-8"><link rel="stylesheet" href="assets/css/style.css">';
    echo '<div class="container" style="padding:70px 0"><div class="notice"><h2>Votre demande n’a pas pu être envoyée.</h2><ul>';
    foreach ($errors as $e) echo '<li>'.htmlspecialchars($e, ENT_QUOTES, 'UTF-8').'</li>';
    echo '</ul><a class="btn btn-primary" href="contact.html">Retour au formulaire</a></div></div>';
    exit;
}

$text  = "Nouvelle demande de contact depuis le site BDJ Consulting\n\n";
$text .= "Nom : $nom\n";
$text .= "Entreprise : $entreprise\n";
$text .= "Email : $email\n";
$text .= "Téléphone : $telephone\n";
$text .= "Objet : $objet\n\n";
$text .= "Message :\n$message\n\n";
$text .= "IP : " . ($_SERVER['REMOTE_ADDR'] ?? 'inconnue') . "\n";
$text .= "Date : " . date('Y-m-d H:i:s') . "\n";

$subject = 'Nouvelle demande de contact BDJ Consulting — ' . ($objet ?: 'Site web');
$result = bdj_send_mail($config, $recipients, $subject, $text, $email, $nom);

if (!$result['ok']) {
    error_log('BDJ send_contact: ' . $result['error']);
    http_response_code(500);
    echo '<!doctype html><meta charset="utf-8"><link rel="stylesheet" href="assets/css/style.css">';
    echo '<div class="container" style="padding:70px 0"><div class="notice"><h2>Envoi momentanément indisponible.</h2><p>La demande n’a pas pu être transmise. Vérifiez la configuration e-mail du serveur.</p><a class="btn btn-primary" href="contact.html">Retour</a></div></div>';
    exit;
}

echo '<!doctype html><meta charset="utf-8"><link rel="stylesheet" href="assets/css/style.css">';
echo '<div class="container" style="padding:70px 0"><div class="recruitment-banner">';
echo '<h2>Merci pour votre message !</h2>';
echo '<p>Votre demande a bien été transmise à BDJ Consulting.</p><br>';
echo '<a class="btn btn-light" href="index.html">Retour à l’accueil</a>';
echo '</div></div>';
