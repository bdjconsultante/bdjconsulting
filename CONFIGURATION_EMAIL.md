# Configuration des e-mails — BDJ Consulting V6.1

Un formulaire n'a PAS besoin de deux adresses pour fonctionner.

Dans cette V6.1, chaque formulaire peut notifier deux boîtes en parallèle :

## Recrutement
Dans `mail_config.php` :
- `recrutement@bdj-consulting.ci`
- `rh@bdj-consulting.ci`

Le candidat remplit un seul formulaire et le CV est envoyé aux deux.

## Contact
- `contact@bdj-consulting.ci`
- `commercial@bdj-consulting.ci`

Une seule demande est envoyée aux deux.

## À remplacer
Modifiez uniquement `mail_config.php` lorsque vous aurez les adresses définitives.

## Important
Le code utilise actuellement PHP `mail()`. Pour une mise en production professionnelle, surtout pour les CV avec pièces jointes, il est recommandé de brancher un SMTP authentifié (domaine BDJ ou prestataire SMTP) plutôt que de dépendre du `mail()` local de l'hébergement.
