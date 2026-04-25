# Plan — Transformation Lead Generation

> 2026-03-24

## Vue d'ensemble

Nouveau bounded context `Lead` à côté de `Estimation`. Un Lead = une personne intéressée, qui peut ou non avoir une estimation complète.

## Phases

### Phase 1 — Domaine Lead
- [x] `LeadId`, `ContactLead`, `SourceTracking`, `ScoreLead`, `StatutLead`, `TypeCapture`
- [x] Agrégat `Lead` (creer, marquerContacte, convertir, perdre, enregistrerRelance)
- [x] Service `ScoringLead` (HOT/WARM/COLD)
- [x] Event `LeadCree`
- [x] Repository interface `LeadRepository`
- [x] Tests unitaires (LeadTest, ScoringLeadTest) — 17 tests OK

### Phase 2 — Infrastructure + Application
- [x] `LeadEntity` Doctrine + migration `Version20260324000000`
- [x] `DoctrineLeadRepository`
- [x] `CreerLeadCommand` + `CreerLeadHandler`
- [x] `ListerLeadsQuery` / `ListerLeadsHandler` / `LeadResult`
- [x] `LeadNotifier` + templates email (lead_admin, relance_lead)
- [x] `CreerEstimationHandler` modifié pour créer un Lead automatiquement
- [x] Config messenger (LeadCree → async)
- [x] Config services (LeadRepository, LeadNotifier)

### Phase 3 — UI (formulaire simplifié + prix masqué)
- [x] Formulaire 4 étapes (type, superficie, encombrement, coordonnées)
- [x] Résultat : "À partir de X€" + CTA rappel
- [x] `UtmCaptureListener` (capture UTM en session)
- [x] UTM propagé dans `CreerEstimationCommand`

### Phase 4 — Points de capture
- [x] Modal "Rappel gratuit" (Stimulus controller, exit intent + timer 30s)
- [x] `LeadController` (POST /rappel-gratuit, POST /estimation-rapide)

### Phase 5 — Admin leads
- [x] Route admin/leads (liste avec filtres score/statut/recherche)
- [x] Route admin/lead/{id}/statut (transitions)
- [x] Navigation Estimations/Leads dans le dashboard

### Phase 6 — Relance automatique
- [x] Commande `app:leads:relancer` (relance après 24h sans réponse)
- [x] Template email relance
- [ ] Cron à configurer : `0 * * * * php /app/bin/console app:leads:relancer`

## Lead Scoring

| Critère | Score |
|---------|-------|
| Diogène | HOT |
| Superficie 200+ m² | HOT |
| Prix estimé > 2000€ | HOT |
| Très encombré + sale | HOT |
| Estimation complète | WARM |
| Superficie 100-200 m² | WARM |
| Prix estimé > 800€ | WARM |
| Très encombré | WARM |
| Reste | COLD |
