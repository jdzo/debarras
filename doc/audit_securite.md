# Audit sécurité — Site Débarras

> Généré le 2026-03-24 | Mis à jour le 2026-03-25 (audit approfondi)

## Légende

- CRITIQUE : à corriger immédiatement
- HAUT : à corriger avant mise en production
- MOYEN : à planifier
- BAS : amélioration recommandée

---

## CRITIQUE

### 1. APP_SECRET vide dans `.env`
- **Statut : ✅ CORRIGÉ**
- **Fix appliqué :** valeur placeholder `change_me_in_production` (à remplacer en prod via `.env.local`)

### 2. APP_SECRET en clair dans `.env.dev`
- **Statut : ✅ CORRIGÉ**
- **Fix appliqué :** valeur explicitement marquée `dev_only_not_for_production`, commentaire ajouté

### 3. CSRF absent sur tous les formulaires POST
- **Statut : ✅ CORRIGÉ**
- **Fix appliqué :**
  - Token CSRF ajouté dans `formulaire.html.twig`, `contact/index.html.twig`, `estimation_detail.html.twig`, `leads.html.twig`
  - Validation `isCsrfTokenValid()` dans `EstimationController`, `ContactController`, `AdminController` (estimation statut + lead statut)
  - Tests fonctionnels vérifient que les POST sans token sont rejetés (401/403)

---

## HAUT

### 4. Injection d'en-têtes email
- **Statut : ✅ CORRIGÉ**
- **Fix appliqué :** `str_replace(["\r", "\n"], '', $nom)` avant insertion dans le sujet dans `ContactNotifier`, `EstimationNotifier` et `LeadNotifier`

### 5. Données personnelles dans les logs
- **Statut : ✅ CORRIGÉ**
- **Fix appliqué :**
  - `OnEstimationCreee` : suppression de `client_email` du contexte de log
  - `ContactController` : suppression de toutes les données perso du log (ne log plus que l'événement)

### 6. Paramètres UTM non sanitisés (XSS stored potentiel)
- **Statut : ✅ CORRIGÉ**
- **Fichier :** `UtmCaptureListener.php`
- **Risque :** les paramètres UTM, le referrer HTTP et la landing page étaient stockés en session/BDD sans validation. Un attaquant pouvait injecter des données malveillantes.
- **Fix appliqué :**
  - `strip_tags()` + suppression `\r\n\t` + `mb_substr(200)` sur les UTM params
  - Validation du schéma (`http://` ou `https://`) pour referrer et landing_page
  - Tronqué à 500 caractères max pour les URLs

### 7. Envoi d'email à des adresses non validées
- **Statut : ✅ CORRIGÉ**
- **Fichiers :** `EstimationNotifier.php`, `LeadNotifier.php`
- **Risque :** l'email client fourni dans le formulaire est utilisé comme destinataire sans validation. Un attaquant pouvait utiliser le service comme relais de spam.
- **Fix appliqué :** `filter_var($email, FILTER_VALIDATE_EMAIL)` avant tout envoi au client

---

## MOYEN

### 8. Mot de passe admin par défaut
- **Fichier :** `.env` — commentaire indique "admin" comme mot de passe par défaut
- **Fix :** forcer le changement au premier déploiement, documenter

### 9. HTTP Basic Auth sans protection brute force
- **Fichier :** `config/packages/security.yaml`
- **Risque :** pas de rate limiting, pas de verrouillage après X tentatives
- **Fix :** ajouter `symfony/rate-limiter` sur le firewall admin ou migrer vers form-based auth

### 10. Pas de rate limiting sur les formulaires
- **Statut : ✅ CORRIGÉ**
- **Fix appliqué :**
  - `symfony/rate-limiter` installé
  - Config `rate_limiter.yaml` : contact (5/15min), estimation (10/15min), lead (10/15min)
  - Rate limiting appliqué par IP dans `ContactController`, `EstimationController`, `LeadController`

### 11. Validation d'entrée insuffisante
- **Statut : ✅ CORRIGÉ**
- **Fix appliqué :**
  - `ContactController` : `mb_substr` sur tous les champs (nom 100, tel 20, email 180, message 2000), regex validation téléphone
  - `EstimationController` : `mb_substr` sur nom (100), telephone (20), email (180), adresse (255), code_postal (10), ville (100), commentaire (2000). Limite de 5 photos max.
  - `PhotoUploader` : vérification `getimagesize()` pour bloquer les fichiers polyglots

### 12. Session cookies non configurés
- **Statut : ✅ CORRIGÉ**
- **Fix appliqué :** `framework.yaml` → `cookie_secure: auto`, `cookie_httponly: true`, `cookie_samesite: lax`

### 13. Headers de sécurité absents
- **Statut : ✅ CORRIGÉ**
- **Fix appliqué :** `SecurityHeadersListener` ajoute :
  - `X-Frame-Options: DENY`
  - `X-Content-Type-Options: nosniff`
  - `X-XSS-Protection: 1; mode=block`
  - `Referrer-Policy: strict-origin-when-cross-origin`
  - `Permissions-Policy: camera=(), microphone=(), geolocation=()`
  - `Strict-Transport-Security: max-age=31536000; includeSubDomains`
  - `Content-Security-Policy` (self, inline styles/scripts, Google Fonts, data: images)

### 14. Pagination sans borne (DOS potentiel)
- **Statut : ✅ CORRIGÉ**
- **Fichiers :** `ListerEstimationsHandler.php`, `ListerLeadsHandler.php`
- **Risque :** un attaquant pouvait demander `?page=999999999`, causant un offset SQL énorme
- **Fix appliqué :** `$page = max(1, min($query->page, $pages))` — la page est bornée au nombre de pages réel

---

## BAS

### 15. Upload de fichiers — MIME spoofing
- **Statut : ✅ CORRIGÉ**
- **Fix appliqué :** `getimagesize()` ajouté dans `PhotoUploader`
- **Restant :** désactiver l'exécution PHP dans le dossier uploads (config serveur)

### 16. Directory listing sur `/uploads/`
- **Risque :** les fichiers sont accessibles si l'URL est devinée
- **Atténuation existante :** noms aléatoires (16 hex chars)
- **Fix :** désactiver le directory listing côté serveur (config FrankenPHP/Caddy)

### 17. Photos d'estimation publiquement accessibles (IDOR)
- **Risque :** les photos uploadées sont servies en statique depuis `/uploads/estimations/`
- **Atténuation existante :** les chemins contiennent un UUID hex aléatoire (32 chars), rendant l'énumération impossible en pratique
- **Fix possible (si sensibilité élevée) :** servir les photos via un contrôleur avec vérification d'accès

---

## Dépendances

### 18. CVE dans les dépendances Composer
- **Statut : ✅ CORRIGÉ**
- **Fix appliqué :** `composer update` des packages vulnérables
  - `phpunit/phpunit` — CVE-2026-24765 (unsafe deserialization)
  - `symfony/http-foundation` — CVE-2025-64500 (authorization bypass via PATH_INFO)
  - `symfony/process` — CVE-2026-24739 (argument escaping Windows)
- **`composer audit` = 0 vulnérabilités**

---

## Résumé

| Sévérité | Total | Corrigés | Restant |
|----------|-------|----------|---------|
| CRITIQUE | 3 | 3 | 0 |
| HAUT | 4 | 4 | 0 |
| MOYEN | 7 | 5 | 2 |
| BAS | 3 | 1 | 2 |
| Dépendances | 3 | 3 | 0 |

### Points restants (config serveur / déploiement)
- **#8** — Mot de passe admin : à changer au déploiement
- **#9** — Rate limiting HTTP Basic : à configurer au niveau serveur/reverse proxy
- **#16** — Directory listing uploads : à désactiver dans la config FrankenPHP/Caddy
- **#17** — Photos IDOR : acceptable avec les noms aléatoires, à revoir si données sensibles

## Points positifs

- Doctrine ORM + paramètres nommés partout (pas de SQL injection)
- Twig autoescape activé, aucun `|raw` dans les templates (XSS web maîtrisé)
- Noms de fichiers uploadés aléatoires + validation contenu image
- Value objects avec validation dans les constructeurs
- Séparation des couches DDD respectée
- CSRF sur tous les formulaires POST
- Rate limiting sur tous les endpoints publics
- Headers de sécurité complets (CSP, HSTS, X-Frame, etc.) sur toutes les réponses
- Session cookies sécurisés (httponly, secure, samesite)
- Paramètres UTM sanitisés en entrée
- Emails clients validés avant envoi
- Toutes les entrées utilisateur tronquées à des longueurs max
- Pagination bornée pour prévenir les attaques DOS
- Injection de headers email bloquée sur tous les notifiers
- Dépendances à jour, 0 CVE connue
