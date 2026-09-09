# BDJ Consulting — V6.1.5 finale

Cette version reprend volontairement la V6.1.5 comme base visuelle et fonctionnelle.

## Conservé
- Design V6.1.5
- Images de la V6.1.5
- Logo et favicon
- Navigation
- Pages et contenus
- Bandeaux et cartes
- Cookies
- Formulaires
- Carte de localisation
- Fonctionnalités existantes

## Ajout
- `config.php` pour centraliser :
  - adresse
  - latitude / longitude
  - téléphone
  - e-mail
  - destinataires du formulaire Contact
  - destinataires du recrutement
  - paramètres Postmark
  - règles de téléchargement des CV

## Prochaine étape
Le fichier `config.php` est prêt à être utilisé par les scripts PHP lors du branchement final de PHPMailer + Postmark.
