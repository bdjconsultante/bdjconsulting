# BDJ Consulting — Site officiel (V6.1.5)

Site vitrine statique du centre de relation client **BDJ Consulting**, basé à Abidjan (Côte d'Ivoire).
9 pages HTML + 2 handlers PHP (`send_contact.php`, `send_recruitment.php`), sans framework.

Visuel : https://www.bdj-consulting.ci/

---

## Fonctionnalités

- **7 axes de contenu** : Accueil, Nos solutions, Nos secteurs, À propos, Notre savoir-faire, Carrières, Contact + mentions légales et politique de confidentialité
- **Widget WhatsApp** : bouton flottant, badge, réponses rapides, horaires, message pré-rempli (`wa.me/2250102440707`)
- **Réseaux sociaux** en pied de page : Facebook, Instagram, X, TikTok, LinkedIn, YouTube (`bjconsulting`)
- **Formulaires opérationnels** :
  - Contact → 2 destinataires
  - Recrutement → 2 destinataires + CV (PDF/DOC/DOCX, max 5 Mo)
- **SEO complet** : titles/descriptions/canonicals uniques, Open Graph + Twitter Card, JSON-LD (`ProfessionalService`, `WebSite`, `ContactPage`, `BreadcrumbList` ×6), sitemap.xml, robots.txt
- **SEO local** : NAP unifié (`+225 01 02 44 07 07`), géolocalisation, zone de service Abidjan / Côte d'Ivoire
- **Bandeau cookies** + politique de confidentialité

## Structure

```
├── *.html                  # 9 pages du site
├── assets/
│   ├── css/style.css
│   ├── js/main.js          # nav, carrousel, widgets (WhatsApp, cookies, anti-spam ts)
│   ├── images/             # logos
│   └── favicon/
├── config/                 # PROTÉGÉ (.htaccess = Require all denied)
│   ├── config.php          # config centrale (non versionnée — secrets)
│   ├── mail_config.php     # destinataires + SMTP (non versionnée — secrets)
│   └── mail_helper.php     # envoi : PHPMailer/Postmark sinon mail()
├── send_contact.php        # handler formulaire contact
├── send_recruitment.php    # handler formulaire recrutement
├── .htaccess               # headers de sécurité, CSP, blocage /config/
├── sitemap.xml
└── robots.txt
```

## Configuration e-mail (à faire sur le serveur)

`config/config.php` et `config/mail_config.php` **ne sont pas versionnés** (secrets). Ils doivent être
recréés sur l'hébergement. Le dossier `config/` référence le modèle dans `CONFIGURATION_EMAIL.md`.

Envoi intelligent via `config/mail_helper.php` :

1. **PHPMailer présent + token configuré** → envoi SMTP (Postmark) avec pièces jointes fiables.
2. **Sinon** → repli sur la fonction PHP `mail()` (comportement historique, pas de blocage).

Brancher Postmark :
1. Créer un serveur sur postmarkapp.com → copier le **Server API Token**
2. Ajouter `no-reply@bdj-consulting.ci` dans *Sender Signatures* + records SPF/DKIM chez l'hébergeur
3. Installer PHPMailer : `composer require phpmailer/phpmailer` **ou** déposer `PHPMailer/src/` dans `assets/lib/phpmailer/`
4. Renseigner `'token'` dans `config/mail_config.php` ou définir `POSTMARK_TOKEN` (env var, recommandé)

## Sécurité

- `.htaccess` : `Options -Indexes`, CSP (`script-src 'self'`), `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`, blocage `/config/`
- Dossier `config/` verrouillé (accès direct 403)
- Formulaires : honeypot + champ horodaté `ts` (anti-bot), suppression CRLF (anti header injection), `FILTER_VALIDATE_EMAIL`
- Upload CV : extension + MIME (finfo) + taille max, jamais écrit sur disque (envoyé en pièce jointe uniquement)
- Aucun secret dans le dépôt (configs dans `.gitignore`)

## Mise en ligne (checklist)

1. Hébergement → docroot du domaine `bdj-consulting.ci` (HTTPS)
2. Recréer `config/config.php` + `config/mail_config.php` (modèles dans `CONFIGURATION_*.md`)
3. Renseigner l'adresse définitive (remplace `[Adresse à compléter]`) dans `config.php` + `index.html` (commentaire HTML présent)
4. Brancher Postmark + PHPMailer (voir ci-dessus)
5. Vérifier : réception des 2 destinataires pour chaque formulaire
6. Valider HTTPS + headers (avec CSP) + Google Business Profile (NAP identique)
7. Soumettre sitemap.xml dans Google Search Console

## Commandes de vérification

```bash
php -l config/mail_helper.php send_contact.php send_recruitment.php
```

---
© 2026 BDJ Consulting. Tous droits réservés.