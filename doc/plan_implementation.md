# Plan d'implémentation — Site Débarras

> Généré le 2026-03-24 | Basé sur le cahier des charges et l'état actuel du code

## Légende

- [ ] À faire
- [x] Déjà implémenté
- [~] Partiellement fait (à compléter)

---

## Phase 1 — Fondations existantes (FAIT)

> Ce qui est déjà en place et fonctionnel.

- [x] Architecture DDD (Domain / Application / Infrastructure / UI)
- [x] Formulaire d'estimation multi-étapes (8 étapes)
- [x] Calcul de prix avec coefficients (encombrement, saleté, accessibilité, options)
- [x] Persistance des estimations (PostgreSQL + Doctrine)
- [x] Événements de domaine + traitement asynchrone (Messenger)
- [x] Notifications email (confirmation client + alerte admin)
- [x] Upload de photos (validation taille/type, stockage local)
- [x] Dashboard admin (liste paginée, filtres par statut, transitions de statut)
- [x] Page d'accueil avec processus en étapes
- [x] Pages services (Maison, Appartement, Diogène, Succession)
- [x] Page contact (formulaire, sans envoi email)
- [x] Aperçu AJAX du prix en temps réel dans le formulaire
- [x] Templates email (confirmation + notification admin)
- [x] Tests unitaires (domaine, application, infrastructure)

---

## Phase 2 — Corrections et finitions prioritaires

> Combler les lacunes critiques pour un site fonctionnel en production.

### 2.1 — Authentification admin
- [x] Déjà en place : HTTP Basic + memory provider dans `security.yaml`
- [x] Firewall `/admin` + `ROLE_ADMIN` configuré
- [x] `ADMIN_PASSWORD` en variable d'environnement

### 2.2 — Envoi email du formulaire de contact
- [x] `ContactNotifier` créé (`Infrastructure/Notification/ContactNotifier.php`)
- [x] Template email `email/contact_admin.html.twig`
- [x] Branché dans `ContactController`
- [x] Confirmation visuelle (flash message déjà en place)
- [x] Test unitaire `ContactNotifierTest`

### 2.3 — Affichage des photos uploadées
- [x] Déjà en place : photos servies depuis `public/uploads/estimations/`
- [x] Affichage dans le template admin détail (grille avec liens)

### 2.4 — Configuration Mailer production
- [ ] Remplacer `MAILER_DSN=null://null` par un vrai transport (config déploiement)

---

## Phase 3 — Amélioration de l'expérience utilisateur (UX)

> Optimisations de conversion issues du cahier des charges.

### 3.1 — Bouton sticky "Estimer mon débarras"
- [x] Présent dans `base.html.twig`, masqué sur formulaire et résultat
- [x] Visible sur toutes les pages (sauf formulaire/résultat)
- [x] Indication "2 min" ajoutée au bouton sticky

### 3.2 — Avis clients / Témoignages
- [x] Section témoignages sur la page d'accueil (3 avis statiques)
- [x] Note étoiles + texte + nom/ville

### 3.3 — Zone d'intervention
- [x] Section "Zone d'intervention" ajoutée sur l'accueil
- [x] Grille des régions desservies (8 régions)

### 3.4 — Résultat d'estimation amélioré
- [x] Fourchette de prix proéminente (déjà en place)
- [x] CTA "Appeler maintenant" + "Nous contacter" ajoutés
- [ ] Prise de rendez-vous directe (Phase 5 — Calendly ou solution maison)

### 3.5 — Progression visuelle du formulaire
- [x] Barre de progression (étape X/8)
- [x] Titre de l'étape affiché dans le label ("Étape 3/8 — Encombrement")

---

## Phase 4 — Design et responsive

> Alignement avec les directives design du cahier des charges.

### 4.1 — Charte graphique
- [x] Palette vert écologique (`--accent: #16a34a`) + variables CSS centralisées
- [x] Variable `--accent-light` ajoutée pour cohérence
- [x] Typographie system-ui, line-height 1.5

### 4.2 — Composants UI
- [x] Boutons larges (formulaire, CTA)
- [x] Icônes explicites par étape du formulaire
- [x] Cards avec hover sur services
- [x] Badges de statut colorés (admin)
- [x] Footer complet (navigation, contact, copyright)

### 4.3 — Responsive / Mobile
- [x] Menu hamburger mobile (nav-toggle + nav-links.open)
- [x] Navbar sticky (position: sticky, top: 0)
- [x] Bouton sticky repositionné sur mobile
- [x] Footer responsive (1 colonne sur mobile)
- [x] Formulaire déjà adapté (options-grid 1 colonne sur mobile)

---

## Phase 5 — Fonctionnalités avancées

> Idées différenciantes du cahier des charges.

### 5.1 — Prix adapté selon la ville
- [x] `ZoneTarifaire` value object (IDF +30%, grande ville +15%, province base)
- [x] Coefficient intégré dans `CalculateurPrix`
- [x] Zone déduite automatiquement du code postal (champ déjà existant)
- [x] Propagé dans `Estimation::creer()`, `recalculerFourchette()`, aperçu AJAX
- [x] Tests unitaires (ZoneTarifaireTest, CalculateurPrixTest)

### 5.2 — Prise de rendez-vous
- [ ] Intégrer un système de créneaux (Calendly embed ou solution maison)
- [ ] Proposer après l'estimation

### 5.3 — WhatsApp
- [x] Bouton flottant en bas à gauche (toutes les pages)
- [x] Lien `wa.me/` avec message pré-rempli

### 5.4 — Dashboard admin avancé
- [x] Statistiques en haut du dashboard (total, nouvelles, acceptées, CA potentiel)
- [x] Export CSV des leads (`/admin/export-csv`)
- [x] Recherche par nom/email/téléphone
- [ ] Notes internes sur chaque estimation (Phase ultérieure)

---

## Phase 6 — SEO et performance

### 6.1 — SEO
- [x] Meta title + description par page (accueil, estimation, services, contact, detail service)
- [x] URLs propres et descriptives (déjà en place)
- [x] `sitemap.xml` dynamique (`SitemapController`, cache 1h)
- [x] `robots.txt` (Allow /, Disallow /admin/)
- [x] Schema.org `LocalBusiness` dans `base.html.twig`
- [x] Open Graph (og:title, og:description, og:url, og:locale)
- [x] Balise `<link rel="canonical">`

### 6.2 — Performance
- [x] `loading="lazy"` sur toutes les images (photos estimations)
- [ ] Minification CSS/JS (AssetMapper — config déploiement)
- [ ] Cache HTTP headers (config serveur FrankenPHP/Caddy)

---

## Phase 7 — Mise en production

### 7.1 — Infrastructure
- [ ] Configurer le domaine et SSL
- [ ] Variables d'environnement production (.env.local)
- [ ] Base de données PostgreSQL production
- [ ] Service d'envoi d'emails (Mailgun / SendGrid / Amazon SES)

### 7.2 — Supervision
- [ ] Logs centralisés
- [ ] Monitoring uptime
- [ ] Alertes en cas d'erreur

### 7.3 — Lancement
- [ ] Tests de bout en bout (formulaire → email → admin)
- [ ] Test de charge basique
- [ ] Analytics (Google Analytics / Matomo)
- [ ] Cookie banner RGPD

---

## Ordre de priorité recommandé

| Priorité | Phase | Effort estimé | Impact |
|----------|-------|--------------|--------|
| 1 | Phase 2 — Corrections critiques | Faible | Élevé |
| 2 | Phase 3 — UX / Conversion | Moyen | Élevé |
| 3 | Phase 4 — Design / Responsive | Moyen | Élevé |
| 4 | Phase 7 — Mise en production | Moyen | Bloquant |
| 5 | Phase 6 — SEO / Performance | Faible | Moyen |
| 6 | Phase 5 — Fonctionnalités avancées | Élevé | Moyen |

---

## Notes

- Le cahier des charges mentionne React/Node.js comme stack, mais le projet utilise déjà Symfony 7.3 avec Twig + Stimulus/Turbo — c'est une stack parfaitement adaptée et plus avancée que prévu.
- L'agrégat `Chantier` existe mais n'est pas utilisé activement. Il pourra servir plus tard pour la gestion opérationnelle interne (suivi des équipes sur le terrain).
- Le système de calcul de prix est déjà conforme aux coefficients du cahier des charges.
