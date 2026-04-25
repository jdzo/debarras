# 🧾 Cahier des charges — Site société de débarras

## 1. 🎯 Objectif du site
- Générer des leads qualifiés
- Permettre une estimation rapide et automatisée
- Rassurer les utilisateurs (avis clients, transparence, simplicité)

---

## 2. 🧩 Pages principales

### 🏠 Accueil
- Accroche claire : “Estimez votre débarras en 2 minutes”
- CTA principal : **Faire une estimation**
- Explication du service (3 étapes)
- Avis clients
- Zone d’intervention

### 📋 Page “Estimation / Devis”
- Formulaire multi-étapes (élément central du site)

### 📄 Page services
- Débarras maison
- Débarras appartement
- Débarras Diogène
- Débarras succession

### 📞 Contact
- Formulaire simple
- Téléphone
- WhatsApp (optionnel)

---

## 3. 🧠 Formulaire d’estimation

### Étape 1 — Type de bien
- Maison
- Appartement
- Local

### Étape 2 — Superficie
- 0–50 m²
- 50–100 m²
- 100–200 m²
- 200+ m²

### Étape 3 — Niveau d’encombrement
- 🟢 Vide
- 🟡 Meublé normal
- 🔴 Très encombré

### Étape 4 — Niveau de saleté
- Propre
- Sale
- Très sale
- ⚠️ Syndrome de Diogène

### Étape 5 — Accessibilité
- Rez-de-chaussée
- Étage avec ascenseur
- Étage sans ascenseur

### Étape 6 — Options
- Nettoyage après débarras
- Désinfection
- Démontage de meubles

### Étape 7 — Photos
- Upload de photos

### Étape 8 — Coordonnées
- Nom
- Téléphone
- Email

---

## 4. ⚙️ Logique de calcul

```js
prix = base_m2 * superficie
  + coefficient_encombrement
  + coefficient_salete
  + coefficient_accessibilite
  + options
```

### Exemple de coefficients

- Prix de base : 15€/m²

#### Encombrement
- Meublé : +30%
- Très encombré : +60%

#### Saleté
- Sale : +20%
- Diogène : +100%

#### Accessibilité
- Sans ascenseur : +25%

---

## 5. 🎨 Design

### Style général
- Fond blanc / gris clair
- Couleur principale :
  - Vert (écologique)
  - ou Bleu (confiance)

### UI formulaire
- Progression visuelle (ex: Étape 2/8)
- Boutons larges
- Icônes explicites
- Interface épurée

---

## 6. 🧲 Optimisations conversion

- Bouton sticky : “Estimer mon débarras”
- Indication du temps : “⏱️ 2 minutes”
- Résultat immédiat : “Estimation entre 800€ et 1200€”

---

## 7. 🔧 Stack technique

### Frontend
- React ou Next.js
- Formulaire multi-step

### Backend
- Node.js
- API REST
- Base de données (ex: MySQL avec Sequelize)

### Bonus
- Envoi d’email automatique
- Dashboard admin

---

## 8. 💡 Idées différenciantes

- Estimation instantanée
- Upload de photos
- Prix adapté selon la ville
- Prise de rendez-vous directe

---

## 9. 🧱 Structure technique

```
/client
  /pages
  /components
    - FormStepper
    - PriceEstimator

/server
  /routes
  /controllers
  /services
```

---

## 10. 📊 Facteurs clés de succès

- Simplicité du formulaire
- Rapidité
- Confiance utilisateur

