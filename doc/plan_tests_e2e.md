# Plan : Tests E2E — Formulaires, Navigation, Balises

## Contexte

Seuls des tests unitaires existent (`tests/Unit/`). Aucun test fonctionnel ne vérifie que les pages s'affichent, que les formulaires soumettent correctement, que la navigation fonctionne et que les balises SEO sont présentes. On utilise **WebTestCase** (browser-kit + css-selector, déjà installés).

## Structure

```
tests/Functional/
├── Navigation/
│   └── NavigationTest.php        # Liens navbar, footer, pages accessibles
├── Estimation/
│   └── EstimationFormTest.php    # POST formulaire estimation, résultat
├── Contact/
│   └── ContactFormTest.php       # POST formulaire contact, validation
├── Seo/
│   └── MetaTagsTest.php          # Title, description, OG, canonical, JSON-LD
└── Lead/
    └── LeadCaptureTest.php       # POST /rappel-gratuit, /estimation-rapide (JSON)
```

## Tests détaillés

### 1. NavigationTest (`tests/Functional/Navigation/NavigationTest.php`)
- **Toutes les pages publiques retournent 200** : `/`, `/estimation`, `/contact`, `/services`, `/services/maison`, `/debarras/rennes`, `/sitemap.xml`
- **Navbar** : liens présents (Services, Contact, Estimation gratuite)
- **Footer** : liens navigation, zones, contact
- **Sticky CTA** : lien "Estimer mon débarras" présent
- **Page 404** : URL inexistante retourne 404

### 2. EstimationFormTest (`tests/Functional/Estimation/EstimationFormTest.php`)
- **GET /estimation** : page s'affiche (200), formulaire présent
- **POST /estimation valide** : soumission directe avec tous les champs (type_de_bien, superficie, encombrement, nom, telephone, email) → redirection 302 vers `/estimation/{id}`
- **Page résultat** : affiche fourchette de prix, récapitulatif, coordonnées
- **POST /estimation invalide** : champs manquants → erreur/re-affichage
- **POST /estimation/apercu** : endpoint JSON retourne un aperçu prix

### 3. ContactFormTest (`tests/Functional/Contact/ContactFormTest.php`)
- **GET /contact** : page s'affiche (200), formulaire présent
- **POST /contact valide** : nom + telephone → message succès
- **POST /contact invalide** : nom vide → erreur validation
- **POST /contact email invalide** : format email incorrect → erreur

### 4. MetaTagsTest (`tests/Functional/Seo/MetaTagsTest.php`)
Pour chaque page publique (`/`, `/estimation`, `/contact`, `/services`, `/services/maison`, `/debarras/rennes`) :
- **`<title>`** : présent et non vide, contient "ClearWay"
- **`<meta name="description">`** : présent et non vide
- **`<meta property="og:title">`** : présent
- **`<meta property="og:description">`** : présent
- **`<meta property="og:url">`** : présent, correspond à l'URL
- **`<meta property="og:locale">`** : `fr_FR`
- **`<link rel="canonical">`** : présent
- **JSON-LD** : `<script type="application/ld+json">` présent sur accueil, contient `LocalBusiness`

### 5. LeadCaptureTest (`tests/Functional/Lead/LeadCaptureTest.php`)
- **POST /rappel-gratuit** : nom + telephone → JSON `{success: true}`
- **POST /rappel-gratuit invalide** : champs manquants → erreur JSON
- **POST /estimation-rapide** : données valides → JSON succès

## Fichiers à créer

| Fichier | Contenu |
|---------|---------|
| `tests/Functional/Navigation/NavigationTest.php` | ~8 tests |
| `tests/Functional/Estimation/EstimationFormTest.php` | ~5 tests |
| `tests/Functional/Contact/ContactFormTest.php` | ~4 tests |
| `tests/Functional/Seo/MetaTagsTest.php` | ~6 tests (dataProvider pour les pages) |
| `tests/Functional/Lead/LeadCaptureTest.php` | ~3 tests |

## Fichiers existants à consulter (pas de modification)

- `src/UI/Http/Controller/EstimationController.php` — champs attendus et redirection
- `src/UI/Http/Controller/ContactController.php` — validation serveur
- `src/UI/Http/Controller/LeadController.php` — endpoints JSON
- `templates/base.html.twig` — structure balises meta

## Vérification

```bash
make bash
./vendor/bin/phpunit tests/Functional/ --testdox
```

Total estimé : **~26 tests** dans 5 fichiers.
