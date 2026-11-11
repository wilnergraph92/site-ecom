# Les pages marchandes en blocs WooCommerce

Relevé sur WooCommerce 11.1.2 et WordPress 7.1, dans l'atelier puis sur un site en production (Hostinger). Les noms de blocs et de classes bougent d'une version à l'autre : **toujours vérifier sur la version installée** (`c2woo_blocs_disponibles()`, et l'inventaire du DOM en fin de document).

## Les trois faits qui commandent tout

1. **WooCommerce possède le DOM, le thème possède la composition et la surface.** Un template marchand est une suite de blocs WooCommerce et de groupes natifs ; ce qu'il y a *dans* chaque bloc WooCommerce, c'est WooCommerce qui l'écrit. On le style, on ne le réécrit pas.
2. **Le balisage des blocs WooCommerce composites ne s'écrit pas à la main.** Beaucoup ont une fonction `save()` qui dépend d'attributs de contexte (`isDescendentOfQueryLoop`, `isDescendentOfSingleProductTemplate`) : oubliés, le bloc est invalide. Le balisage **se génère dans l'éditeur de l'atelier** (procédure plus bas), puis se valide (porte 2). Exception tolérée : un attribut simple d'un bloc dynamique sans balisage enregistré (`aspectRatio`, `miniCartIcon`, `perPage`…) se pose dans le délimiteur — puis porte 2.
3. **Le CSS de WooCommerce est spécifique et chargé tard.** Surcharger par une spécificité supérieure (qualifier par la classe du composant ou du bloc parent), pas par l'ordre. `!important` seulement là où WooCommerce écrit des styles en ligne ou des sélecteurs à trois classes. Chaque cas rencontré est commenté « PIÈGE » dans `style.css` du squelette.

## Les templates du thème

| Fichier | Sert | Remarques |
|---|---|---|
| `archive-product.html` | Boutique, **catégories, étiquettes, attributs, marques** | Repli natif : WooCommerce l'utilise pour `taxonomy-product_cat`, `_tag`, `_attribute`, `_brand` tant que le thème ne les fournit pas. **Ne pas créer ces fichiers.** Sa grille **hérite de la requête** (`inherit: true`). |
| `product-search-results.html` | Recherche de produits | Pas de repli : fichier propre, même corps (`patterns/catalogue.php`). La recherche reste accessible (URL, formulaire d'une page) — mais pas de loupe dans l'en-tête. |
| `single-product.html` | Toutes les fiches produit | L'ajout au panier (`add-to-cart-with-options`) choisit seul son rendu selon le type de produit. |
| `page-cart.html` | Cadre de la page Panier | Le bloc Panier est dans le **contenu** de la page (`contenus/panier.html`). |
| `page-checkout.html` | Cadre de la page Commande | En-tête épuré `checkout-header`, bloc Commande dans le contenu (`contenus/commande.html`). |
| `order-confirmation.html` | Confirmation de commande | Blocs `order-confirmation-*`, titres par compositions WooCommerce traduites. |
| `page.html` | Mon compte, pages vitrine et légales | Mon compte = `core/shortcode` `[woocommerce_my_account]` : il n'existe pas de version en blocs. |

Parties : `header`, `footer`, `checkout-header`, `mini-cart` (le tiroir). Déclarées dans `theme.json` → `templateParts`.

> **Le piège du repli manquant.** Un thème bloc sans `archive-product.html` ni `single-product.html` n'échoue pas : WooCommerce sert **ses** templates, parfois en `woocommerce/legacy-template` (l'ancien rendu PHP). La porte 1 compte ces blocs : 0.

## La carte produit — un composant, partout

Même structure dans le catalogue, la recherche, les produits associés, les ventes croisées du panier, le panier vide et les sections de l'accueil. Elle se dessine d'après la carte de la maquette, se génère une fois dans l'éditeur, et s'enregistre dans `contenus/_carte.html` pour être réutilisée par `scripts/blocs.py`. Structure du squelette (à adapter à la maquette) :

```
woocommerce/product-collection          (className: collection-…)
└── woocommerce/product-template        (className: grille-produits) → ul.wc-block-product-template.grille-produits
    ├── woocommerce/product-image       (className: carte-image, imageSizing: thumbnail, isDescendentOfQueryLoop)
    │   └── woocommerce/product-sale-badge
    ├── core/group                      (className: carte-infos)
    │   ├── core/post-title             (className: carte-titre, level 3, __woocommerceNamespace: …/product-title)
    │   └── woocommerce/product-price   (className: carte-prix)
    └── woocommerce/product-button      (className: carte-bouton)
```

Variante vécue (étiquette et bouton rond posés sur l'image, sous-titre, pastilles d'attribut) : un `core/group` `carte-visuel` qui contient l'image, un `core/post-terms` (`term: product_cat`, étiquette), le bouton ; puis `carte-infos` avec titre, `woocommerce/product-summary` (`summaryLength: 8` — l'extrait court du produit comme sous-titre), et un `core/post-terms` sur l'attribut (`term: pa_metal`) dont les liens deviennent des pastilles de couleur en CSS (`a[href*="or-rose"]`). Aucune donnée en dur (règle 12).

- Chaque produit est un `li.wc-block-product`. **WooCommerce ajoute 12 px sous chaque enfant et 12 px sous le résumé** : la carte mesurait 18 px de plus que la maquette. Le squelette les neutralise.
- Le ratio de l'image vient du **réglage WooCommerce** (`ratio_vignettes` → recadrage des vignettes) ; ne pas poser d'`aspectRatio` sur l'image **de la carte**.
- Pas de cœur « favoris » : aucune liste d'envies native (règle de sobriété).

### Les collections

Variations de `woocommerce/product-collection`, relevées dans l'éditeur (`wp.blocks.getBlockVariations('woocommerce/product-collection')`) — `scripts/blocs.py` les porte :

| Collection | Requête | `hideControls` |
|---|---|---|
| `product-catalog` | celle de la page si `inherit: true` (archives) | `["inherit"]` |
| `featured` | `featured: true` (Produits → étoile) | `["inherit","featured","filterable"]` |
| `on-sale` | `woocommerceOnSale: true` | `["inherit","on-sale","filterable"]` |
| `best-sellers` | `orderBy: popularity` | `["inherit","order","filterable"]` |
| `by-category` | `taxQuery: {"product_cat": [ID]}` → jeton `{{categorie:slug}}` | `["inherit","hand-picked","filterable"]` |
| `hand-picked` | `woocommerceHandPickedProducts: ["ID",…]` → jetons `{{produit:SKU}}`, `orderBy: post__in` | `["inherit","hand-picked","filterable","order"]` |
| `related`, `upsells`, `cross-sells` | contexte du produit / du panier | `["inherit"]`, `["inherit","filterable"]` |

> **Le piège des nouveautés.** `new-arrivals` porte `"timeFrame":{"operator":"in","value":"-7 days"}` : sept jours après le lancement, la section est vide. Pour « le plus récent d'une catégorie », `by-category` trié `order: desc`, `orderBy: date` — `boutique.php` échelonne les dates des produits dans l'ordre du fichier, pour que ce tri soit stable.

Chaque `product-collection` d'une même page a son `queryId`. Sections produits de l'accueil : **en grille**, jamais en carrousel (règle de sobriété).

## Catalogue — `patterns/catalogue.php`

```
group.wrap.catalogue                         grille : filtres | résultats
├── group.catalogue-filtres
│   └── woocommerce/product-filters (filtres)   → panneau latéral ; bouton « Filtrer les produits » + surcouche en mobile
│       ├── product-filter-active        (puces retirables + « Effacer les filtres »)
│       ├── product-filter-price         (curseur)
│       ├── product-filter-attribute     (cases) — attributeId : l'ID de l'attribut, relevé dans l'atelier
│       ├── product-filter-taxonomy      (catégories)
│       └── product-filter-status        (disponibilité)
└── group.catalogue-resultats
    ├── woocommerce/store-notices
    ├── group.catalogue-barre → product-results-count + catalog-sorting
    └── product-collection (product-catalog, **inherit: true**) → product-template.grille-produits (3 colonnes)
        ├── core/query-pagination.pagination
        └── product-collection-no-results.aucun-resultat → paragraphe + bouton
```

> **Le piège de la grille qui n'hérite pas.** Avec `"inherit": false` (ce que donne la variation `product-catalog` insérée hors d'un template d'archive), la grille ignore la page : **chaque catégorie affiche tout le catalogue**, et les filtres n'agissent pas — alors que le compteur, lui, dit « 4 résultats ». `inherit: true`, et pas de `"inherit"` dans `hideControls`. Contrôle : `/categorie-produit/<slug>/` ne liste que ses produits ; `?filter_<attribut>=<terme>` et `?min_price=` filtrent.

Produits par page : `woocommerce_catalog_columns` × `woocommerce_catalog_rows` (réglage `catalogue` de `deploiement.json`, 3 × 4 par défaut) — à 4 × 4, une grille de 3 colonnes finit sur une carte orpheline.

L'en-tête du catalogue (fil d'Ariane, `core/query-title` en type `archive` ou `search`, `core/term-description`) est dans chaque template. Titre et description d'une catégorie viennent de **Produits → Catégories** (règle 12).

> **Le piège des filtres imbriqués.** Inséré par script à l'intérieur d'un groupe, `product-filters` peut rester vide : son gabarit interne ne se monte que lorsque le bloc s'affiche. Générer le bloc seul, au premier niveau, puis l'insérer par sa sérialisation.

## Fiche produit — `single-product.html`

```
main.fiche-produit
├── group.wrap.fiche-haut → breadcrumbs + store-notices
├── group.wrap.fiche                                  grille 7/5 : galerie | achat
│   ├── group.fiche-galerie → product-gallery
│   │     └── product-gallery-large-image → product-image {"aspectRatio":"4/5"} + sale-badge + suivant/précédent
│   └── group.fiche-achat                             collant en ordinateur
│       ├── core/post-terms (product_cat, fiche-surtitre)   la catégorie au-dessus du titre
│       ├── core/post-title (h1, fiche-titre)
│       ├── product-rating      {isDescendentOfSingleProductTemplate: true}   (vide sans avis)
│       ├── product-price       {isDescendentOfSingleProductTemplate: true}
│       ├── product-summary     {isDescendentOfSingleProductTemplate: true}
│       ├── add-to-cart-with-options (fiche-ajout)   pastilles de variation, stock, quantité + bouton sur une ligne
│       ├── group.fiche-reassurance → paragraphes .promesse (livraison, retours, paiement) — des faits seulement
│       └── product-meta (SKU, catégorie, étiquettes)
├── group.wrap.fiche-details → heading « Détails » | group (product-description + product-specifications)
└── group.wrap.fiche-associes → product-collection « related » (3 produits)
      ├── group.associes-tete → heading + lien vers le catalogue      première colonne d'une grille de 4
      └── product-template.grille-produits → la carte                 trois colonnes à droite
```

- **Galerie** : son format vient de l'attribut `aspectRatio` du bloc Image du produit **imbriqué** (sinon 1:1, image en `contain` sur un fond) ; le CSS la recadre en `cover`.
- **Détails** : `product-description` + `product-specifications` (attributs, poids, dimensions en tableau) remplacent les onglets de `product-details` et leur « Avis (0) », qui font « WooCommerce par défaut ». Le tableau est un `figure.wp-block-table` : qualifier par `table` pour passer devant ses bordures.
- **Pastilles de variation** : `add-to-cart-with-options` rend les options en `button.wc-block-product-filter-chips__item[aria-checked]` (`value` = slug du terme) ; une pastille de couleur se pose sur `[value="<slug>"] .wc-block-product-filter-chips__label::before`. Le nom de l'attribut : `…-variation-selector-attribute-name`. Styles en ligne de WooCommerce sur les groupes internes (`margin: 1rem`) → `!important`.
- **Stock** : pour une variation en rupture, l'indicateur affiche « En rupture de stock » mais **garde la classe `--in-stock` du parent** — ne pas colorer par la classe sur un produit variable. Le bouton passe en `is-invalid` (inactif) tant qu'aucune option n'est choisie.
- **Bouton** : `button.single_add_to_cart_button.wp-block-button__link` dans `.wp-block-button.wp-block-woocommerce-product-button` ; WooCommerce règle largeur et alignement à 3 classes — qualifier. Après l'ajout, le libellé devient « 1 dans le panier ». Aucun pseudo-élément de WooCommerce sur ce bouton : `::before`/`::after` sont libres pour le décor de la maquette.

Ordre des options d'un attribut : l'ordre du catalogue (méta `order` des termes, posée par `boutique.php`).

## Mini-panier — `woocommerce/mini-cart` + partie `mini-cart`

Dans l'en-tête : `{"miniCartIcon":"bag","addToCartBehaviour":"open_drawer","className":"entete-panier"}` — l'icône de la maquette (`cart`, `bag`, `bag-alt`), et **le tiroir s'ouvre à chaque ajout** (sinon seul le compteur bouge). Masqué par défaut sur le panier et la commande (`cartAndCheckoutRenderStyle: hidden`). Le tiroir : titre + compteur, articles, pied (sous-total, « Voir mon panier », « Valider la commande »).

> **Le piège des deux vignettes.** Chaque article porte une image de secours `<img hidden>` ; une base `img{display:block}` l'affiche. `[hidden]{display:none!important}` dans la base du squelette.

## Panier et commande — le bloc est dans la page

Les templates `page-cart.html` et `page-checkout.html` enveloppent `core/post-content` dans `woocommerce/page-content-wrapper`. **Le bloc Panier et le bloc Commande vivent dans le contenu des pages** — `contenus/panier.html` et `contenus/commande.html`. Le client les modifie dans l'éditeur de page.

Blocs insérables par zone (relevé `canInsertBlockType` dans l'éditeur) :

| Zone du panier | Blocs natifs acceptés |
|---|---|
| `cart-items-block` (colonne des articles) | `core/paragraph`, `core/separator`, `woocommerce/product-collection` |
| `cart-totals-block` (récapitulatif) | `core/paragraph`, `core/separator` — c'est là que vont les **promesses** |
| `empty-cart-block` (panier vide) | tout : titre, texte, boutons, groupe, collection |

Composition du squelette :

- **Rempli** : articles | récapitulatif sur la surface (« Récapitulatif », code promo, sous-total, livraison, total, bouton, moyens de paiement acceptés, trois `.promesse`).
- **Vide** : un groupe `panier-vide` (titre sans l'icône « triste » de WooCommerce — classe `with-empty-cart-icon` retirée —, bouton vers le catalogue), puis un titre et une collection `featured` avec la carte du thème.

> **La balise perdue.** En réécrivant le panier vide par script, la fermeture `</div>` de `empty-cart-block` disparaît facilement : la page s'affiche, mais la porte 2 le marque invalide. Le balisage attendu : `…<!-- /wp:woocommerce/product-collection --></div>\n<!-- /wp:woocommerce/empty-cart-block -->`.

Ce qui se règle, et où :

| Réglage | Bloc | Attribut |
|---|---|---|
| Libellé du bouton de paiement | `checkout-actions-block` | `placeOrderButtonLabel` — **« Commander avec obligation de paiement »** |
| Case CGV obligatoire | `checkout-terms-block` | `checkbox: true` |
| Champs téléphone, société, complément | `checkout` | `showPhoneField`, `requirePhoneField`, `showCompanyField`, `showApartmentField` |
| Note de commande | `checkout` + `checkout-order-note-block` | `showOrderNotes` |
| Titre du récapitulatif du panier | `cart-order-summary-heading-block` | `content` |
| Panier vide | `empty-cart-block` | contenu libre en blocs |

Blocs facultatifs retirables : code promo, paiement express, note, moyens de paiement acceptés. Blocs verrouillés : on les restyle.

**Livraison** (`boutique.php` + `functions.php` du squelette) : la gratuité est placée **en tête** de la zone — WooCommerce présélectionne la première méthode disponible, et un panier au-dessus du seuil affichait le forfait ; et le forfait **disparaît** quand la gratuité est disponible (filtre `woocommerce_package_rates`). Aucun retrait en magasin sans demande.

Champs de la commande : **libellés flottants** de WooCommerce — on règle leur peau (sélecteurs à 3 classes → `!important`), pas leur mécanique ; erreurs à la couleur `alerte`. Récapitulatif sur la surface ; la pastille de quantité est posée par WooCommerce à 2 classes (blanc sur blanc si l'on ne qualifie pas). En mobile, WooCommerce fige la colonne produit du panier à 132 px et le total déborde : le squelette rend la colonne souple.

## Confirmation de commande

`order-confirmation.html` : `order-confirmation-status`, `-summary`, `-totals-wrapper` + `-totals`, `-downloads-wrapper`, adresses, `-additional-fields`, `-additional-information` (les coordonnées du virement, quand le client les a saisies). Titres de section : compositions WooCommerce traduites, à garder. Mise en page du squelette : un **reçu** (colonne centrée, résumé sur la surface, tableau à filets). Adresse : `/commande/commande-recue/<n>/` — `boutique.php` passe les points de terminaison en français (sinon `order-received`).

## Mon compte — balisage classique

Pas de version en blocs : `[woocommerce_my_account]` (`core/shortcode`). Connecté : `.woocommerce > nav.woocommerce-MyAccount-navigation + div.woocommerce-MyAccount-content` ; déconnecté : `.u-columns > .u-column1 (connexion) + .u-column2 (inscription)` — deux cartes sur la surface dans le squelette ; formulaires `.form-row`, `.input-text`, `.woocommerce-Button` ; tableaux `.shop_table`, adresses `.woocommerce-Address`.

> **Deux pièges de spécificité.** La feuille classique pose des clearfix (`::before`/`::after` en `display: table`) qui deviennent des cellules de grille — neutralisés à spécificité supérieure. Et la feuille « blocktheme » **espace les liens de navigation** (`.woocommerce-account .woocommerce-MyAccount-navigation li { padding }`) et **borne le bloc à 1000 px** (`.woocommerce-account main .woocommerce`), à 3 classes : qualifier pareil.

Adresses : `/mon-compte/commandes/`, `/mon-compte/adresses/`… (points de terminaison en français, `boutique.php`). L'inscription suit `inscription_mon_compte`.

## Messages

Bandeaux de blocs `.wc-block-components-notice-banner.is-info|is-success|is-warning|is-error` : WooCommerce les colore en bleu, vert, rouge **à 2 classes** — qualifier par le type pour les passer sur la surface et la palette. Balisage classique : `.woocommerce-message`, `.woocommerce-info`, `.woocommerce-error`.

## En-tête, pied de page : ce que le client modifie

| Élément | Bloc | Où le client le change |
|---|---|---|
| Logo | `core/site-logo` (média `logo`) — largeur mesurée sur le logo texte de la maquette | Clic sur le logo → Remplacer ; change partout (en-tête, pied, commande) |
| Favicon | option `site_icon` (média `favicon`) | Réglages → Général → Icône du site |
| Menu | `core/navigation` + `core/navigation-link`, catégories en `core/navigation-submenu` au-delà de 4 | Éditeur → Navigation, ou dans la partie |
| Compte | `woocommerce/customer-account` (`displayStyle: icon_only`) | — |
| Mini-panier | `woocommerce/mini-cart` + partie `mini-cart` | Éditeur → Compositions → Mini-panier |
| Textes, colonnes, coordonnées | `core/heading`, `core/paragraph`, `core/navigation` | Éditeur → Compositions → Pied de page |
| Réseaux | `core/social-links` — **seulement s'ils sont au brief** | URL de chaque icône |

**Pas de `core/search` dans l'en-tête** (règle de sobriété). Le menu : le catalogue d'abord (libellé de la niche), puis 2 à 4 catégories à plat — ou toutes en sous-menu du catalogue :

```html
<!-- wp:navigation-submenu {"label":"Nos bijoux","url":"/boutique/","kind":"custom"} -->
<!-- wp:navigation-link {"label":"Bagues","url":"/categorie-produit/bagues/","kind":"custom"} /-->

<!-- wp:navigation-link {"label":"Colliers","url":"/categorie-produit/colliers/","kind":"custom"} /-->
<!-- /wp:navigation-submenu -->
```

## Styles globaux — ce qui rend couleurs et polices modifiables

- **Palette par rôle** dans `theme.json` (`fond`, `surface`, `texte`, `sourdine`, `trait`, `accent`, `sur-accent`, `promo`, `alerte`) ; `style.css` n'emploie que les alias. Styles → Couleurs → Palette : le client change « Accent », et boutons, pastilles, curseur de prix, compteur du mini-panier suivent.
- **Variables de WooCommerce** branchées sur les rôles, dans `:root` : `--wc-separator-color`, `--wc-form-color-text`, `--wc-product-filter-chips-*`, `--wc-product-filter-price-slider*`, `--wc-product-filter-checkbox-list-*`, `--wc-product-filter-removable-chips-*`, `--wc-customer-account-dropdown-*`.
- **Boutons** : `theme.json` → `styles.elements.button`, **réglé d'après la maquette** (corps, graisse, marges) : tous les boutons WooCommerce portent `.wp-element-button`.
- **Titres** : `styles.elements.heading` d'après la maquette (graisse, interlettre, hauteur de ligne). Un titre de composant qui héritait de la hauteur de ligne du corps dans la maquette la pose explicitement (le 1.1 de `theme.json` l'emporterait).
- **Polices** : deux familles déclarées dans `theme.json` (`scripts/polices.py`) et le **pont** de `functions.php` qui pose `--police-titres` et `--police-texte` d'après Styles → Typographie.
- **Rayons** : `settings.custom.rayon` et `rayon-bouton`.

## Générer le balisage dans l'éditeur de l'atelier

Dans l'atelier, connecté, ouvrir **l'éditeur du bon contexte** :

| Balisage à produire | Où l'ouvrir |
|---|---|
| Fiche produit | `site-editor.php?p=%2Fwp_template%2F<slug>%2F%2Fsingle-product&canvas=edit` |
| Catalogue | `…%2F%2Farchive-product&canvas=edit` |
| Contenus de page, carte produit, compositions, parties | `post-new.php?post_type=page` |

Puis, dans la console — construire, laisser les blocs se monter, sérialiser :

```js
const { createBlock: B, serialize, parse, getBlockVariations } = wp.blocks;
const d = wp.data.dispatch('core/block-editor'), s = wp.data.select('core/block-editor');
const collection = v => getBlockVariations('woocommerce/product-collection').find(x => x.name === 'woocommerce/product-collection/' + v);
const vedettes = collection('featured');
const bloc = B('woocommerce/product-collection', { ...vedettes.attributes, queryId: 31, className: 'collection-vedettes' }, [
  B('woocommerce/product-template', { className: 'grille-produits' }, [ /* la carte */ ]) ]);
d.resetBlocks([bloc]);
await new Promise(r => setTimeout(r, 6000));          // gabarits internes et attributs de contexte
const aplatir = bs => bs.reduce((a, b) => a.concat([b], aplatir(b.innerBlocks || [])), []);
const html = serialize(s.getBlocks());
({ invalides: aplatir(parse(html)).filter(b => b.isValid === false).map(b => b.name), html });
```

Tester ce qu'une zone accepte : `s.canInsertBlockType('core/paragraph', clientIdDeLaZone)`.

Enregistrer depuis la même console, par l'outil PHP de l'atelier (thème monté sous `/wordpress/wp-content/themes/<slug>/`, projet sous `/atelier/projet/`) :

```js
const ecrire = async (chemin, contenu) => {
  const jeton = '<contenu de PROJET/.atelier/jeton>';
  const b64 = btoa(unescape(encodeURIComponent(contenu)));
  const r = await fetch('/atelier/exec.php', { method: 'POST', headers: { 'X-Atelier-Jeton': jeton },
    body: 'return file_put_contents(' + JSON.stringify(chemin) + ', base64_decode("' + b64 + '"));' });
  return r.status + ' ' + await r.text();
};
await ecrire('/atelier/projet/contenus/_carte.html', html + '\n');
```

## Inventaire du DOM (WooCommerce 11.1)

Pour une autre version, refaire le relevé :

```js
const cls = {}; document.querySelector('main').querySelectorAll('[class]').forEach(e => e.classList.forEach(c => {
  if (/^(wc-block|wp-block-woocommerce|woocommerce)/.test(c)) cls[c] = 1; })); Object.keys(cls).sort().join(' ');
```

| Composant | Classes |
|---|---|
| Grille, carte | `ul.wc-block-product-template` · `li.wc-block-product` · `.wc-block-components-product-image` · `.wc-block-components-product-price` · `del` / `ins` · `.wc-block-components-product-sale-badge` · `.wc-block-components-product-button__button` · `.wc-block-components-product-summary` |
| Barre du catalogue | `.woocommerce-result-count` · `.woocommerce-ordering select` · `.wp-block-query-pagination` · `.page-numbers` · `.woocommerce-breadcrumb` |
| Filtres | `.wc-block-product-filters` · `.wc-block-product-filter-price-slider` · `.wc-block-product-filter-checkbox-list__item` · `.wc-block-product-filter-chips__item` · `.wc-block-product-filter-removable-chips` |
| Fiche | `.wc-block-product-gallery` · `.wc-block-product-gallery-large-image` · `.wc-block-add-to-cart-with-options` (`.is-invalid`) · `.wp-block-woocommerce-add-to-cart-with-options-variation-selector-attribute(-name)` · `.wc-block-product-filter-chips__item[aria-checked]` · `.wc-block-components-quantity-selector` · `.single_add_to_cart_button` · `.wc-block-components-product-stock-indicator` · `.wp-block-woocommerce-product-description` · `.wp-block-product-specifications-item__label/__value` |
| Mini-panier | `.wc-block-mini-cart__button` · `.wc-block-mini-cart__badge` · `.wc-block-components-drawer__content` · `.wc-block-mini-cart__title` · `.wc-block-mini-cart__footer(-cart, -checkout)` |
| Panier | `.wc-block-cart` (`.is-mobile/.is-small/.is-medium`) · `.wc-block-cart-items__row` · `.wc-block-cart-item__image/__product/__quantity/__total` · `.wc-block-cart__sidebar` · `.wc-block-components-totals-item` · `.wc-block-cart__submit-button` |
| Commande | `.wc-block-checkout` · `.wc-block-components-checkout-step(__title)` · `.wc-block-components-text-input` (`.has-error`) · `.wc-block-components-validation-error` · `.wc-block-components-radio-control` · `.wc-block-components-order-summary(-item__quantity)` · `.wc-block-components-checkout-place-order-button` |
| Confirmation | `.wc-block-order-confirmation-status/-summary(-list-item__key/__value)/-totals__table` · `.wc-block-order-confirmation-shipping-address/-billing-address` |
| Messages | `.wc-block-components-notice-banner.is-info/-success/-warning/-error` · `.woocommerce-message/-info/-error` |
| Mon compte | voir plus haut |
