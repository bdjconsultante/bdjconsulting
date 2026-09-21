# Configuration des e-mails — BDJ Consulting V6.1

Un formulaire n'a PAS besoin de deux adresses pour fonctionner.

Dans cette V6.1, chaque formulaire peut notifier deux boîtes en parallèle :

## Recrutement
Dans `mail_config.php` :
- `recrutement@bdj-consulting.net`
- `rh@bdj-consulting.net`

Le candidat remplit un seul formulaire et le CV est envoyé aux deux.

## Contact
- `contact@bdj-consulting.net`
- `commercial@bdj-consulting.net`

Une seule demande est envoyée aux deux.

## À remplacer
Modifiez uniquement `mail_config.php` lorsque vous aurez les adresses définitives.

## 1. Créer les boîtes sur IONOS
Panneau IONOS → **E-mail** → **Créer une adresse e-mail**. Créez :

| Boîte | Usage |
| --- | --- |
| `no-reply@bdj-consulting.net` | Expéditeur des formulaires (obligatoire) |
| `contact@bdj-consulting.net` | Adresse publique + destinataire contact |
| `commercial@bdj-consulting.net` | Destinataire contact (copie) |
| `recrutement@bdj-consulting.net` | Destinataire recrutement |
| `rh@bdj-consulting.net` | Destinataire recrutement (copie) |

`no-reply@` doit exister comme boîte (ou alias) : IONOS refuse d'envoyer avec un expéditeur qui n'appartient pas au domaine.

## 2. Brancher l'envoi SMTP
`config/mail_config.php` est réglé sur IONOS :

- Hôte : `smtp.ionos.com` — port `587` (STARTTLS) ou `465` (SSL)
- `username` : adresse complète de la boîte (ex. `no-reply@bdj-consulting.net`)
- `password` : mot de passe de la boîte ; préférer la variable d'environnement `IONOS_SMTP_PASSWORD`
- Consultation des boîtes : `imap.ionos.com:993` (SSL)

Prérequis : installer PHPMailer (`composer require phpmailer/phpmailer` ou dossier `PHPMailer/src/` dans `assets/lib/phpmailer/`). Sans PHPMailer ou sans identifiants, le code retombe sur `mail()`.

## 3. DNS — SPF, DKIM, DMARC (anti-spam)
Dans IONOS → **Domaines & SSL** → `bdj-consulting.net` → **DNS**, vérifiez/ajoutez :

- **SPF** (TXT sur `@`) — autorise les serveurs IONOS à envoyer pour le domaine :
  ```
  v=spf1 include:_spf.ionos.com ~all
  ```
  S'il existe déjà un SPF, n'en créez pas un second : ajoutez `include:_spf.ionos.com` à celui existant.

- **DKIM** — activé automatiquement par IONOS lors de la création des boîtes (sélecteur `default`/`s1`). Vérifiez la présence de l'enregistrement TXT `default._domainkey` (ou `s1._domainkey`) ; sinon activez la signature DKIM dans le panneau e-mail IONOS.

- **DMARC** (TXT sur `_dmarc`) — politique de départ, à durcir ensuite :
  ```
  v=DMARC1; p=none; rua=mailto:contact@bdj-consulting.net; adkim=s; aspf=s
  ```
  Après quelques semaines sans anomalie, passer à `p=quarantine` puis `p=reject`.

- **MX** : conserver les serveurs IONOS (`mx00.ionos.com`, `mx01.ionos.com`) pour recevoir le courrier.

Tester après propagation : envoyer un message via le formulaire de contact et vérifier les en-têtes `Authentication-Results` (SPF=pass, DKIM=pass, DMARC=pass).

## Important
Pour une mise en production professionnelle, surtout pour les CV avec pièces jointes, l'envoi SMTP authentifié IONOS est recommandé plutôt que de dépendre du `mail()` local de l'hébergement.
