---
name: claude-to-woocommerce
description: "Construit une boutique WooCommerce sur mesure et l'installe sur le WordPress du client : maquette HTML locale, pages marchandes dessinées dans un WordPress local (l'atelier), thème bloc 100 % éditable, déploiement via l'extension Novamira, recette. À utiliser seulement quand WordPress ou WooCommerce est en jeu : créer, refondre ou moderniser une boutique WooCommerce, vendre sur un site WordPress, remplacer Storefront, Astra, Elementor ou un thème WooCommerce du commerce, dessiner le catalogue, la fiche produit, le panier ou la commande d'une boutique WooCommerce. Se déclenche immédiatement quand un message commence par « Détail de la boutique : » avec une image et un connecteur Novamira .mcpb joints. Ne pas utiliser pour un e-commerce sur une autre plateforme ou en code (Shopify, PrestaShop, Wix, Magento, Next.js, React, Laravel, dépôt sans WordPress), ni pour une question générale sur le e-commerce. Site vitrine WordPress sans vente : claude-to-wordpress."
---

# Claude to WooCommerce

## Pourquoi cette méthode existe

La qualité d'un design est à peu près proportionnelle au nombre d'itérations. Dans WordPress, une itération prend trois à cinq minutes ; en local, cinq secondes. Une boutique ajoute une difficulté : **une partie des pages n'est pas à nous.** Catalogue, fiche produit, panier, commande et Mon compte sont générés par WooCommerce (le panier et la commande en React) : aucune maquette libre ne s'y reproduit.

D'où **l'atelier** : un vrai WordPress + WooCommerce local (WordPress Playground), thème du projet monté dedans. On y dessine les pages marchandes sur le DOM réel, et on y joue tout le déploiement à blanc avant de toucher au site du client. Et le site reste modifiable sans code : contenus, produits, couleurs, polices, logo, favicon, en-tête, pied de page.

> **En une ligne :** le design vit dans des fichiers, les produits vivent dans WooCommerce, et tout ce qui est global se règle dans Styles.

Rodée de bout en bout sur une boutique réelle (Hostinger, WordPress 7.1, WooCommerce 11.1.2). Chaque piège rencontré est consigné dans `references/pieges.md`.

## Deux familles de pages

| Famille | Pages | Structure | Où elles se dessinent |
|---|---|---|---|
| **Vitrine** | Accueil, pages listées au brief, pages légales | Nous | En HTML local (phases 2, 3a) |
| **Marchandes** | Catalogue (boutique, catégories, étiquettes, attributs), recherche, fiche, mini-panier, panier, commande, confirmation, Mon compte | WooCommerce | Dans l'atelier, sur le vrai DOM (phase 3b) |

Le brief ne liste que les pages vitrine ; les pages marchandes et les trames légales sont produites d'office.

## Les règles de design — en bref

**Texte complet : `references/regles.md`, à lire avant la phase 1** et à rouvrir à chaque porte de registre et du décalque.

- **Règle 0 — dessiner la phase 2 comme si la phase 4 n'existait pas.** Aucune contrainte de conversion en blocs dans la maquette ; un composant trop riche se tranche en phase 4, devant un design fini.
- **Règle 0′ — aucune liberté esthétique ne sort de la référence.** Les **sept invariants** (classe typographique, système de couleur, température du fond, découpe, géométrie, alignement, densité) se relèvent sur la référence et se reproduisent. **Sur la page seule, jamais sur son cadre de présentation** (`scripts/reference.py` → `reference/page.png`).
- **S'inspirer fortement, jamais décalquer.** Même famille, jamais la même page : ni l'ordre des sections, ni leurs dispositions, ni les textes (même traduits), ni le nom, le logo, les photos.
- **Règle 0″ — sur les pages marchandes, on n'imite pas WooCommerce : on l'habite.** Composition du template et surface sont à nous. La panne : « WooCommerce par défaut, repeint aux couleurs de la marque ».
- **Sobriété — ce qui n'est ni au brief, ni natif de WooCommerce, n'existe pas.** Aucune page, fonctionnalité (favoris, comparateur, newsletter, avis inventés…), service ou promesse inventés ; pas de carrousel ; pas de loupe dans l'en-tête. Ce qui est écarté de la référence se note dans `plan.md` et au compte rendu.
- **Le menu mène au catalogue** ; 2 à 4 catégories à plat, au-delà en sous-menu ; menu replié ≤ 1024 px, **contrôlé par `scripts/menu-mobile.js`, jamais à l'œil**.
- **Des pages de la longueur d'une vraie boutique.** L'accueil porte les sections de base : héros, catégories, produits phares, deuxième grille sous un autre angle, produit à la une, récit, FAQ factuelle, engagements. Les pages secondaires gagnent une ou deux sections.

## Les quatorze règles

Chacune vient d'une erreur réellement commise. Les enfreindre produit un livrable que le client ne peut pas utiliser.

1. **Exiger une référence visuelle** : sans elle, le modèle échantillonne le centre statistique de ses données.
2. **Images produit : le placeholder** (`assets/placeholders/`, image par défaut de WooCommerce partout). **Toutes les autres : vraies photos Unsplash**, dans le registre de la référence, recadrées au format de l'emplacement, jamais Unsplash+. Texte sur photo : un voile.
3. **Deux accords du client, ni plus ni moins.** **La maquette de l'accueil**, seule validation du design : pas de plan à valider, pas de seconde validation des pages. Puis, la répétition générale passée, **le feu vert d'installation** : un « oui » explicite avant la moindre écriture sur son site, vierge ou non. Le « go » sur la maquette ne vaut pas feu vert.
4. **L'accueil est une page, jamais un template.** Un seul `archive-product.html` sert boutique, catégories, étiquettes, attributs (grille en `inherit: true`). Panier, commande, Mon compte sont des pages.
5. **Zéro `core/html`, zéro rendu hérité.** Liste blanche : `core/*` et `woocommerce/*`. `core/shortcode` seulement pour `[woocommerce_my_account]` et Contact Form 7.
6. **Images en médiathèque, avec identifiant** — placeholders, logo et favicon compris. Les produits n'ont pas d'image.
7. **Les variantes interactives vivent dans la page** : toutes présentes, le script bascule une classe. FAQ : `core/details`.
8. **Pas de thème tiers sous le design** (ni Storefront, ni Astra…).
9. **Recette dans l'éditeur, pages ET templates.** Le balisage des blocs WooCommerce composites se génère dans l'éditeur de l'atelier ; celui des pages vitrine sort de `scripts/blocs.py`.
10. **Le pixel perfect se mesure** : ±2 px par section, ordinateur et 390 px (maquette ou atelier contre site).
11. **Tout ce qui est global se règle dans Styles** : couleurs par rôle, deux polices hébergées, en-tête et pied en parties de modèle, logo et favicon natifs. `style.css` sans aucune couleur ni police en dur.
12. **Aucune donnée produit en dur** : tout vient des blocs dynamiques de WooCommerce.
13. **Le paiement réel se branche par le client.** Jamais de clé Stripe, PayPal ou autre ; recette par virement.
14. **Le connecteur reste au chaud jusqu'à la phase 5, et se détruit à la clôture.** Rangé dès réception hors du projet (`connecteur.py`) ; site non contacté avant la phase 5 ; secrets jamais affichés ; à la fin, mot de passe d'application révoqué sur le site et identifiants effacés en local (phase 7).

## Déroulé

Chaque phase a une porte de sortie : tant qu'elle n'est pas franchie, ne pas passer à la suivante. **Le détail et les commandes exactes : `references/phases.md`, à lire avant la phase 1.**

| Phase | Durée | Sortie |
|---|---|---|
| 0 · Préparation | 5 min | Dossier de travail, Node ≥ 20.18, Python 3, **connecteur rangé** |
| 1 · Brief | 5 min | Les huit décisions dans `plan.md` — rien à valider, on enchaîne |
| 2 · Maquette | 30-60 min | Accueil HTML montré au client : **accord n° 1, le design** |
| 3 · Déclinaison | 60-90 min | 3a pages vitrine en HTML · 3b pages marchandes dans l'atelier, porte de registre marchande |
| 4 · Thème bloc | 45-60 min | Thème complet, **déploiement joué à blanc dans l'atelier**, portes 1, 2, 3, 4 et 7 |
| 5 · Déploiement | 20 min | État des lieux → **accord n° 2, le feu vert** → boutique installée, tout le site « Bientôt disponible » |
| 6 · Recette | 20 min | Sept portes passées sur le site |
| 7 · Clôture | 2 min | Accès refermé (révocation, effacement local), puis compte rendu de fin |

## Ce que le client voit

La méthode est pour l'agent. Le client envoie un brief et reçoit un site ; il ne relit ni plan, ni tableau, ni palette.

| Moment | Ce que l'agent écrit |
|---|---|
| Brief reçu | Une ligne : « Brief reçu : je construis l'accueil de <Marque>. » — puis il travaille |
| Maquette prête | Capture pleine page (ordinateur et mobile), adresse locale, trois lignes au plus, puis « Je continue sur toutes les pages et l'installation, ou tu veux changer quelque chose ? » |
| Retours | Il corrige et remontre ; au « go », il enchaîne jusqu'à la répétition générale, sans autre validation du design |
| Feu vert | Ce qui va être installé, ce que contient déjà le site (état des lieux), « Je lance l'installation ? » — modèle dans `phases.md`, phase 5, étape 0 |
| Fin | Le compte rendu de fin, accès déjà refermé |

**Trois arrêts légitimes, pas un de plus** : l'image de référence manque (la demander avant de coder) ; le connecteur manque en arrivant à la phase 5 ; le **feu vert d'installation**. Tout le reste se décide. Jamais : le relevé, la palette ou le plan soumis au client ; « Si tu valides, je lance… » après le brief.

## Phase 0 et 1 : à la réception du brief

Le brief tient en un message — gabarit dans `phases.md` (« Détail de la boutique : … ») — avec l'image de référence et le connecteur `.mcpb` en pièces jointes. Aussitôt :

```bash
python3 SKILL/scripts/connecteur.py "<chemin du .mcpb>" <slug-du-projet>   # rangé hors du projet, on n'y touche plus avant la phase 5
python3 SKILL/scripts/reference.py "<image jointe>" PROJET/reference       # reference/page.png devient LA référence
```

Tout ce que le gabarit ne dit pas est un défaut de la méthode, jamais une question (`phases.md`, « Ce que le gabarit ne dit pas ») : catalogue de démonstration (dont un produit à variations, un en promo, un en rupture), euros TTC à 20 %, livraison en France avec forfait et seuil de gratuité, virement de test, pages légales en trames à compléter.

Puis les **huit décisions**, écrites dans `PROJET/plan.md` et jamais affichées : relevé de la référence, sujet concret, plan de l'accueil, palette par rôle, typographies, catalogue et menu, ce que j'ajoute, ce que je ne reprends pas (détail et points de contrôle : `phases.md`).

## Phases 2 à 4 : maquette, atelier, thème

- **Phase 2** — l'accueil seul, en HTML local (`scripts/serve.js`). Variables par rôle dès la première ligne (`--fond --surface --texte --sourdine --trait --accent --sur-accent --promo --alerte --police-titres --police-texte`). La carte produit se dessine ici. Porte de registre et porte du décalque dans `plan.md` avant de montrer ; deux à trois tours.
- **Phase 3b** — l'atelier : `cd /tmp && nohup bash SKILL/scripts/atelier.sh PROJET 9400 > /tmp/atelier.log 2>&1 &`. Thème depuis `assets/theme-squelette/`, `deploiement.json` d'après `assets/deploiement-exemple.json`, puis chaque page marchande dessinée sur le DOM réel (composition du squelette, surface de la maquette). `menu-mobile.js` à 390, 768 et 1440 px dès que l'en-tête est posé. Compositions : `references/blocs-woocommerce.md`.
- **Phase 4** — pages vitrine en blocs natifs (`scripts/blocs.py`, `references/blocs-gutenberg.md`), Styles globaux branchés, mesure section par section, puis **répétition générale** : atelier relancé à zéro, portes 1, 2, 3, 4 et 7.

## Phase 5 : déployer, après le feu vert

Quatre commandes, qui n'affichent jamais un secret : `wp.sh`, `envoyer.sh` (PHP + `boutique.php`), `televerser.sh`, `lien-admin.sh`. Dans l'ordre (`phases.md`) :

1. **État des lieux, lecture seule** : `envoyer.sh 'return c2woo_etat_des_lieux();'` — le seul appel avant le feu vert.
2. **Feu vert** : le message de `phases.md` (phase 5, étape 0), puis **attendre un « oui » explicite**. Site non vierge : le dire, chiffres à l'appui. Sans « oui », rien n'est écrit. `c2woo_preparer()` et `c2woo_deployer()` refusent d'écrire en production sans leur dernier argument à `true`.
3. Préparation (français, WooCommerce, Contact Form 7), thème téléversé et activé, paquet téléversé, `c2woo_deployer()`, purge des caches, `menu-mobile.js` sur le site (connecté, 390 et 768 px), nettoyage de `uploads/`.

**Tout le site** reste « Bientôt disponible » : **la mise en ligne est une décision du client.** Le feu vert couvre ce déploiement ; se redemandent à part : supprimer des templates personnalisés en base, `forcer_catalogue`, la mise en ligne.

## Phase 6 : la recette, sept portes

Dans l'atelier en phase 4, puis sur le site. Code exact : `phases.md`.

1. **Serveur** — `c2woo_porte1()` : 0 `core/html`, 0 rendu hérité, rien hors `core/` et `woocommerce/`.
2. **Éditeur** — 0 bloc invalide, images liées ; ouverture réelle de l'accueil et de la fiche produit.
3. **Rendu** — images en 200, polices, `scrollWidth` ≤ 390, **menu mobile** `ok: true` à 390, 768, 1440 px, déconnecté et connecté.
4. **Pixel** — ±2 px, maquette contre site (vitrine), atelier contre site (marchandes).
5. **Éditeur fidèle** — « Modifier le site », fiche produit, catalogue, confirmation : rien d'effondré.
6. **Parcours d'achat** — du catalogue à Mon compte ; sur le site, une commande `@example.com` par virement, annulée puis supprimée.
7. **Conformité** — `c2woo_porte7()` : trames légales, case CGV, « Commander avec obligation de paiement », prix TTC.

## Phase 7 : clôture, puis compte rendu

Avant d'écrire le compte rendu (`phases.md`, phase 7) : déconnexion de wp-admin dans le navigateur ; **`envoyer.sh 'return c2woo_revoquer_acces();'` en tout dernier appel au site** (révoque le mot de passe d'application du connecteur, et lui seul) ; puis `python3 SKILL/scripts/connecteur.py --oublier <slug>`. Une intervention ultérieure demande un nouveau connecteur.

**Le compte rendu de fin**, court, dans cet ordre : l'adresse du site et son état ; ce qui a été fait et vérifié, **avec les captures** et le contrôle du menu mobile ; ce que le client fait avant la mise en ligne (photos produits, trames légales, coordonnées du virement ou prestataire de paiement, contenus provisoires) ; ce qui n'a pas été repris de la référence ; la mise en ligne, qui est sa décision ; l'accès refermé.

## Quand ça part de travers

`references/pieges.md` : symptôme, cause, correctif. Les plus coûteux :

- **« Ça ressemble à WooCommerce »** — surface repeinte, composition de stock (règle 0″).
- **Des pages de trois écrans** — l'accueil manque des sections de base.
- **Un plan à valider au lieu d'une maquette** — les décisions sont pour `plan.md`.
- **« C'est le site de la référence »** — même ordre, mêmes dispositions, textes traduits.
- **Une couleur ou une marge sorties de nulle part** — le cadre de présentation relevé comme du design.
- **Chaque catégorie affiche tout le catalogue** — grille d'archive en `inherit: false`.
- **Frais de port au-dessus du seuil de gratuité**, **nom de l'hébergeur dans l'onglet**, **accueil public en « Bientôt disponible »**.
- **Menu mobile impossible à fermer** — vu seulement sur le site, en mobile : `menu-mobile.js` à chaque étape.
- **Blocs invalides dans un template**, **« Modifier le site » en vrac**, **Mon compte en désordre**, **galerie écrasée avec de vraies photos**.

## Ce que la méthode ne couvre pas

Le dire au client plutôt que de le laisser le découvrir : paiement réel (règle 13) ; délivrabilité des e-mails (SMTP recommandé) ; fond des textes légaux ; photos des produits ; catalogue volumineux (import CSV natif) ; fonctionnalités hors WooCommerce natif (favoris, retrait en magasin, réservations, multilingue, abonnements) — des extensions à trancher avec le client, jamais ajoutées d'office.

## Fichiers fournis

| Fichier | Quand le lire ou s'en servir |
|---|---|
| `references/regles.md` | Avant la phase 1, puis à chaque porte de registre et du décalque |
| `references/phases.md` | Avant la phase 1, puis à chaque phase |
| `references/blocs-gutenberg.md` | Phase 4, pages vitrine |
| `references/blocs-woocommerce.md` | Phases 3b et 4, pages marchandes |
| `references/pieges.md` | Dès qu'un symptôme inattendu apparaît |
| `scripts/connecteur.py` | Phase 0 : ranger le connecteur · phase 7 : `--oublier` |
| `scripts/reference.py` | Phase 1 : isoler la page de la référence |
| `scripts/serve.js` | Phase 2 : serveur de la maquette |
| `scripts/atelier.sh`, `scripts/atelier-php.sh` | Phases 3b à 6 : l'atelier local |
| `scripts/boutique.php` | Préparation, déploiement (atelier et production), recette, révocation |
| `scripts/paquet.py` | Assembler `deploiement.json` en un seul paquet |
| `scripts/blocs.py` | Phase 4 : balisage des pages vitrine |
| `scripts/polices.py`, `scripts/visuels.py` | Phase 3b : polices, logo provisoire, favicon, vignette |
| `scripts/menu-mobile.js` | Phases 3b à 6 : contrôle du menu mobile |
| `scripts/wp.sh`, `envoyer.sh`, `televerser.sh`, `lien-admin.sh` | Phases 5 à 7 : Novamira, sans jamais afficher un secret |
| `assets/theme-squelette/` | Phase 3b : point de départ du thème |
| `assets/contenus/` | Panier, commande, Mon compte, formulaire de contact, trames légales |
| `assets/deploiement-exemple.json` | Modèle de `deploiement.json` |
| `assets/placeholders/`, `assets/vignette/` | Placeholder produit (huit formats) ; Inter Display (OFL) |
| `assets/methode.html` | Document lisible à transmettre au client |
