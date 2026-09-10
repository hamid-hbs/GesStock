# LeLabel — App de Gestion de Stock, Production, Ventes & Commissions

Application web de gestion de stock pour une structure de production/vente (ex : eau alcaline, eau de table).
L'**admin supervise tout** : il renseigne et modifie les types, produits, catégories, membres, clients,
saisit les productions journalières et les ventes, et suit les quantités **produites, vendues, restantes**
ainsi que les **commissions** reversées aux membres apporteurs.

## 1. Concept métier

### Hiérarchie produits (Type > Produit > Catégorie, les 2 niveaux optionnels)

```
Type (ex : Eau) — optionnel
 └─ Produit (ex : Eau alcaline, Eau de table)
      └─ Catégorie / Format (ex : 1L, 2L, 5L)
```

- Un produit peut exister **seul** (sans type ni catégorie) : son prix unitaire (PU) est alors sur le produit.
- S'il a des catégories, le PU est sur **chaque catégorie**.
- Exemple : `Eau > Eau alcaline > 1L`, `Eau > Eau alcaline > 2L`, ou simple `Gobelet` sans type ni catégorie.
- L'**article vendable** = soit le produit seul, soit la catégorie. Jamais les deux à la fois.

### Double tarif particulier / grossiste

Chaque article vendable porte 2 prix :

- `prix_particulier`
- `prix_grossiste`

À la vente, le prix est choisi automatiquement selon `client.type`.
Le prix appliqué est **figé (snapshot)** dans la ligne de vente pour garder l'historique
même si le tarif change plus tard.

### Production interne

- L'admin saisit la **production du jour** (ex : 200 x 1L + 100 x 2L).
- Chaque production **augmente le stock** de façon transactionnelle.
- Chaque vente **diminue le stock**. Vente impossible si stock insuffisant.
- Tout mouvement est tracé dans `mouvements_stock` (audit, lecture seule).

### Membres, clients & commissions

- La structure a des **membres** (vendeurs/apporteurs). Ce sont de simples fiches
  gérées par l'admin : nom, téléphone, adresse, `taux_commission` en % (ex : 10 %). **Pas de login membre en V1.**
- Chaque **client** est de type `particulier` ou `grossiste` et peut être rattaché
  à un membre apporteur (`client.membre_id`, renseigné par l'admin).
- Quand le client d'un membre effectue un achat, le membre reçoit automatiquement :
  `commission = total_vente × taux_membre / 100`, stockée en snapshot sur la vente.
- V1 : commission simplement **due / cumulée**. Statut payé/non-payé prévu en V2.

### Dashboard journalier admin

Filtre par date (défaut = aujourd'hui) avec :

- Quantité **produite** (jour), **vendue** (jour), **restante** (stock actuel), **totaux** CA.
- CA jour + CA mois, commissions dues du jour (global + par membre).
- Alertes stock bas / rupture (`stock <= seuil_alerte`).
- Dernières productions & ventes, top articles, répartition particulier vs grossiste,
  classement membres (CA apporté + commission due).

## 2. Fonctionnalités (la totale V1)

- [x] Plan : Types, Produits + Catégories, Productions, Ventes, Membres/Clients, Dashboard, Exports
- [ ] CRUD Types / Produits (+ catégories inline) / Membres / Clients
- [ ] Saisie production (réf `PROD-AAAA-0001`) → +stock auto
- [ ] Saisie vente (réf `VTE-AAAA-0001`) → prix auto + −stock + commission auto
- [ ] Historique mouvements (lecture seule)
- [ ] Dashboard journalier + alertes
- [ ] Exports PDF (bon de vente/production) + Excel/CSV (stock, ventes)
- [ ] Seeders démo + tests (stock jamais négatif, prix gros/part, commission correcte)

Hors périmètre V1 (prévu plus tard) : multi-dépôts, code-barres/QR, valorisation FIFO/CMP,
paiement des commissions, login membre.

## 3. Stack technique

- **Backend :** Laravel 12, PHP 8.2, Eloquent, `StockService` transactionnel
- **Frontend :** Blade + Livewire 3 + Tailwind CSS 4 + Vite + Chart.js
- **Auth :** Laravel Breeze (Blade) + Spatie Permission (rôle `admin` seul en V1)
- **Base :** PostgreSQL 16 (`pdo_pgsql`)
- **Exports :** `barryvdh/laravel-dompdf`, `maatwebsite/excel`

## 4. Schéma base de données

```text
types: id, nom [unique], description
produits: id, type_id [nullable FK], nom, description,
  prix_particulier [nullable si variantes], prix_grossiste [nullable si variantes],
  stock [si sans catégorie], seuil_alerte
categories: id, produit_id [FK], nom [unique par produit],
  prix_particulier, prix_grossiste, stock, seuil_alerte

membres: id, nom, telephone, adresse, taux_commission [%], actif
clients: id, membre_id [nullable FK apporteur], type [particulier|grossiste],
  nom, telephone, adresse

productions: id, reference, date_production, notes, user_id
production_lignes: id, production_id, produit_id [nullable], categorie_id [nullable], quantite

ventes: id, reference, client_id, membre_id [snapshot], type_client [snapshot],
  total, commission_membre [snapshot], date_vente, user_id
vente_lignes: id, vente_id, produit_id [nullable], categorie_id [nullable],
  quantite, prix_unitaire [snapshot], sous_total

mouvements_stock: id, produit_id [nullable], categorie_id [nullable],
  type [production|vente|ajustement], quantite,
  stock_avant, stock_apres, reference_doc, user_id
users + roles/permissions (Spatie)
```

Règles :
1. Stock modifié uniquement via productions/ventes/ajustements, en transaction.
2. Une ligne = soit `produit_id`, soit `categorie_id`, jamais les deux.
3. Annulation = mouvement inverse, jamais de DELETE sur l'historique.

## 5. Prérequis

- PHP 8.2+, Composer, Node 18+, PostgreSQL 16
- Extensions PHP : `pdo_pgsql`, `mbstring`, `gd` (PDF/images)

## 6. Installation

```bash
composer install
cp .env.example .env
# Éditer .env :
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1 DB_PORT=5432 DB_DATABASE=lelabel DB_USERNAME=postgres DB_PASSWORD=secret
php artisan key:generate
php artisan migrate --seed
npm install
npm run dev
php artisan serve
```

Compte admin démo (seed) : `admin@lelabel.local` / `password` (à changer).

Avec Docker (si Postgres non installé) : un `docker-compose.yml` postgres sera ajouté
en phase build.

## 7. Lancement

```bash
composer dev   # serve + queue + pail + vite
# ou séparé :
php artisan serve
npm run dev
php artisan test
```

## 8. Roadmap

- V1 : tout ci-dessus, admin seul.
- V2 : statut commission payée, login membre (voir ses ventes/commissions),
  multi-dépôts, code-barres, FIFO/CMP.
