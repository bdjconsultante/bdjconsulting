# BDJ Consulting — V6.1 technique

Base : V6.

### Visuels
- Remplacement des visuels génériques par des photographies Pexels orientées équipes africaines, multiculturelles et professionnelles.
- Hero et cartes solutions adaptés à l'identité africaine/internationale de BDJ Consulting.
- Les pages citent les sources Pexels dans les métadonnées du projet ; vérifier les licences et conserver les attributions requises selon l'usage final.

### Formulaires
- Formulaire recrutement opérationnel avec CV.
- Formulaire contact opérationnel.
- Deux destinataires configurables pour chaque formulaire via `mail_config.php`.
- Protection basique anti-spam (honeypot).
- Validation e-mail.
- Validation MIME/extension et taille du CV.
- Réponse utilisateur après envoi.

### SEO / technique
- robots.txt
- sitemap.xml
- données structurées
- lazy-loading
- responsive
- réduction des mouvements si le navigateur le demande
- politique de confidentialité
- mentions légales
- bandeau cookies

### À faire avant production
1. Remplacer les adresses de `mail_config.php`.
2. Configurer un SMTP authentifié.
3. Tester réception des 2 destinataires de chaque formulaire.
4. Remplacer les coordonnées et placeholders.
5. Vérifier les licences/conditions d'utilisation des photos sélectionnées.
6. Tester le site sur le domaine réel et HTTPS.
