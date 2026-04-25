---
name: Coding style preferences
description: User wants SOLID/DRY/DDD, concise responses (diffs only), no over-engineering, no useless comments, explicit naming, design patterns when relevant
type: feedback
---

Réponses concises, ne montrer que les diffs. Pas de reformulation.

**Why:** L'utilisateur veut économiser des tokens et aller droit au but.

**How to apply:** Ne pas reformuler ce qui a été fait. Montrer uniquement les modifications. Pas de résumé superflu.

---

SOLID, DRY, DDD stricts. Design patterns quand pertinent. Pas d'over-engineering.

**Why:** Code maintenable, simple, sans abstraction prématurée.

**How to apply:** Responsabilité unique par classe. Pas de helpers/utils pour un seul usage. Noms de variables explicites. Supprimer les commentaires évidents. Tester les classes importantes.
