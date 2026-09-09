<?php
declare(strict_types=1);

$mailConfig = require __DIR__ . '/config/mail_config.php';
require __DIR__ . '/config/mail_helper.php';
$recruitmentRecipients = $mailConfig['recruitment_recipients'];
$fromEmail = $mailConfig['from'];

function clean(string $v): string { return trim(preg_replace('/[\r\n\x00]+/', ' ', strip_tags($v))); }

function is_bot_request(): bool {
    $website = trim((string)($_POST['website'] ?? ''));
    $ts = (int)($_POST['ts'] ?? 0);
    if ($website !== '') return true;
    if (!$ts || $ts > time() || time() - $ts > 2 * 24 * 3600) return true;
    return false;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: carrieres.html');
    exit;
}

$nom=clean($_POST['nom']??'');
$prenom=clean($_POST['prenom']??'');
$email=filter_var(trim($_POST['email']??''), FILTER_VALIDATE_EMAIL);
$telephone=clean($_POST['telephone']??'');
$poste=clean($_POST['poste']??'');
$ville=clean($_POST['ville']??'');
$experience=clean($_POST['experience']??'');
$message=clean($_POST['message']??'');
$consentement=isset($_POST['consentement']) && $_POST['consentement']==='1';

$errors=[];
if(is_bot_request()) $errors[]='Requête invalide.';
$maxFileSize = (int)$mailConfig['max_cv_size'];
$allowedExtensions = ['pdf','doc','docx'];
$allowedMime = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
];
if(!$nom||!$prenom) $errors[]='Nom et prénom obligatoires.';
if(!$email) $errors[]='Adresse e-mail invalide.';
if(!$telephone) $errors[]='Téléphone obligatoire.';
if(!$poste) $errors[]='Poste recherché obligatoire.';
if(!$consentement) $errors[]='Consentement obligatoire.';
if(!isset($_FILES['cv']) || $_FILES['cv']['error']!==UPLOAD_ERR_OK) $errors[]='CV obligatoire.';

if(isset($_FILES['cv']) && $_FILES['cv']['error']===UPLOAD_ERR_OK){
    $cv=$_FILES['cv'];
    if($cv['size'] > $maxFileSize) $errors[]='Le CV dépasse 5 Mo.';
    $mime=(new finfo(FILEINFO_MIME_TYPE))->file($cv['tmp_name']);
    $extension = strtolower(pathinfo($cv['name'], PATHINFO_EXTENSION));
    if(!in_array($mime,$allowedMime,true) || !in_array($extension,$allowedExtensions,true)) {
        $errors[]='Format non autorisé. PDF, DOC ou DOCX uniquement.';
    }
}

if($errors){
    http_response_code(400);
    echo '<!doctype html><meta charset="utf-8"><link rel="stylesheet" href="assets/css/style.css">';
    echo '<div class="container" style="padding:70px 0"><div class="notice"><h2>Votre candidature n’a pas pu être envoyée.</h2><ul>';
    foreach($errors as $e) echo '<li>'.htmlspecialchars($e,ENT_QUOTES,'UTF-8').'</li>';
    echo '</ul><a class="btn btn-primary" href="carrieres.html#recrutement">Retour au formulaire</a></div></div>';
    exit;
}

$cv=$_FILES['cv'];
$mime=(new finfo(FILEINFO_MIME_TYPE))->file($cv['tmp_name']);
$filename=preg_replace('/[^A-Za-z0-9._-]/','_',basename($cv['name']));

$text="Nouvelle candidature reçue depuis le site BDJ Consulting\n\n";
$text.="Nom : $nom\nPrénom : $prenom\nEmail : $email\nTéléphone : $telephone\n";
$text.="Poste : $poste\nVille : $ville\nExpérience : $experience\n\n";
$text.="Message :\n$message\n\n";
$text.="IP : ".($_SERVER['REMOTE_ADDR']??'inconnue')."\n";
$text.="Date : ".date('Y-m-d H:i:s')."\n";

$subject='Nouvelle candidature BDJ Consulting — '.$poste;
$result=bdj_send_mail($mailConfig,$recruitmentRecipients,$subject,$text,$email,$prenom.' '.$nom,[
    ['path'=>$cv['tmp_name'], 'name'=>$filename, 'mime'=>$mime],
]);

if(!$result['ok']){
    error_log('BDJ send_recruitment: '.$result['error']);
    http_response_code(500);
    echo '<!doctype html><meta charset="utf-8"><link rel="stylesheet" href="assets/css/style.css">';
    echo '<div class="container" style="padding:70px 0"><div class="notice"><h2>Envoi momentanément indisponible.</h2><p>Votre candidature n’a pas pu être transmise. Vérifiez la configuration e-mail du serveur.</p><a class="btn btn-primary" href="carrieres.html#recrutement">Retour</a></div></div>';
    exit;
}

echo '<!doctype html><meta charset="utf-8"><link rel="stylesheet" href="assets/css/style.css">';
echo '<div class="container" style="padding:70px 0"><div class="recruitment-banner">';
echo '<h2>Merci pour votre candidature !</h2>';
echo '<p>Votre CV et vos informations ont bien été transmis à BDJ Consulting.</p><br>';
echo '<a class="btn btn-light" href="index.html">Retour à l’accueil</a>';
echo '</div></div>';
?>