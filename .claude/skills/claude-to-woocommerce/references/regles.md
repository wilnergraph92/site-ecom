# Règles de design — le texte complet

Le résumé est dans SKILL.md. Ce document en est la version complète : **le lire avant la phase 1**, et le rouvrir à chaque porte de registre, porte du décalque et porte de registre marchande.

Table des matières
- [Deux familles de pages](#deux-familles-de-pages)
- [La règle 0](#la-règle-0)
- [La règle 0′ — le registre appartient à la référence](#la-règle-0--le-registre-appartient-à-la-référence)
- [S'inspirer fortement, jamais décalquer](#sinspirer-fortement-jamais-décalquer)
- [La règle 0″ — la structure est à WooCommerce, la surface est à nous](#la-règle-0--la-structure-est-à-woocommerce-la-surface-est-à-nous)
- [La règle de sobriété — rien d'inventé, rien de superflu](#la-règle-de-sobriété--rien-dinventé-rien-de-superflu)

## Deux familles de pages

| Famille | Pages | Qui possède la structure | Où elles se dessinent |
|---|---|---|---|
| **Vitrine** | Accueil + les pages listées au brief (À propos, Notre histoire, Contact…), pages légales | Nous | En HTML local, librement (phases 2, 3a) |
| **Marchandes** | Catalogue (boutique, catégories, étiquettes, attributs), recherche, fiche produit, mini-panier, panier, commande, confirmation, Mon compte | WooCommerce | Dans l'atelier, sur le vrai DOM (phase 3b) |

**Le brief ne liste que les pages vitrine.** Les pages marchandes et les trames légales sont produites d'office, quoi qu'il arrive — elles ne se demandent pas et ne se discutent pas.

Les règles 0 et 0′ s'appliquent aux pages vitrine exactement comme dans la méthode vitrine. Les pages marchandes ont leur propre règle, la 0″. La règle de sobriété et « s'inspirer, jamais décalquer » valent pour toutes.

## La règle 0

> **Règle 0 — Dessiner la phase 2 comme si la phase 4 n'existait pas.**
>
> Aucune contrainte de conversion n'entre dans la maquette. « Pas de `core/html` », « blocs natifs », « éditable par le client » sont des problèmes de phase 4. Les anticiper en phase 2 fait disparaître, une par une et sans qu'on s'en aperçoive, la profondeur, les superpositions, les débordements, l'objet central. Le résultat se convertit parfaitement et ne plaît à personne.
>
> Si un composant s'avère trop riche à convertir, c'est un arbitrage de phase 4, pris devant un design fini et, s'il le faut, devant le client. Jamais un renoncement de phase 2.

Elle ne produit pas de panne visible : elle produit un site fade — et un site fade passe toutes les portes. Elle ne couvre pas les **fonctionnalités** : ce qu'on ajoute relève de la règle de sobriété, pas de la liberté de composition.

## La règle 0′ — le registre appartient à la référence

Règle 0 libère la main sur la **technique**. Elle ne la libère pas sur le **registre**.

> **Règle 0′ — Aucune liberté esthétique ne sort de la référence.**
>
> Sans référence, un modèle dérive vers le centre statistique de ses données : police neutre, dégradé violet, fond blanc. **Avec une référence, il dérive vers sa propre maison** : beige chaud là où la référence est en blanc froid, serif à caractère là où elle n'a qu'une sans-serif, un accent terreux là où elle n'a aucun accent. Le résultat est souvent *plus joli* — et c'est exactement pour ça qu'il passe. Le client, lui, le voit en une seconde.

### Les sept invariants de registre

Ils se **relèvent** sur la référence — **sur la page seule, jamais sur son cadre de présentation** (voir plus bas) — et se reproduisent. Ils ne s'interprètent pas et ne comptent jamais comme un écart volontaire.

| Invariant | Ce qu'on relève sur la référence |
|---|---|
| 1 · Classe typographique | Serif ou sans-serif ? Combien de familles ? Des italiques, ou aucune ? |
| 2 · Système de couleur | Combien de couleurs non neutres ? **« Aucun accent » est une réponse fréquente, et c'est une contrainte, pas un manque.** |
| 3 · Température du fond clair | Blanc froid, gris, blanc cassé, beige ? |
| 4 · Découpe de la page | Bandes pleine largeur bord à bord, ou blocs arrondis encartés avec de la marge autour ? |
| 5 · Géométrie | Rayon des angles. Présence ou absence de rotation, de diagonale, de forme non rectangulaire. |
| 6 · Alignement dominant | Titres de section centrés ou à gauche ? Grilles symétriques ou asymétriques ? |
| 7 · Densité et échelle | Beaucoup de petits éléments, ou peu de grands ? Rapport entre le corps de texte et les titres. |

Changer l'un des sept, c'est livrer un autre site. Si un invariant paraît mal convenir à la boutique, on le reproduit quand même, et on le signale **en une ligne en montrant la maquette** : le client tranche sur pièce, jamais sur un plan.

La référence donne le **registre**, pas la **liste des fonctionnalités** : un cœur « favoris », un carrousel ou un bouton « prendre rendez-vous » visibles sur l'image ne se reproduisent pas pour autant (règle de sobriété). On en reprend la surface — typographie, couleurs, géométrie — sur des composants sobres.

Les six exigences de parti pris de la phase 2 (dominante, couleur, poids des sections, profondeur, typographie, concept) **ne sont pas des consignes quand une référence existe : ce sont six questions posées à la référence, et sa réponse est la consigne.**

### La page, pas sa présentation

Une capture trouvée sur Pinterest, Dribbble, Behance ou une galerie de sites est souvent **mise en scène** pour être jolie à regarder : une bande unie autour de la page (sombre, colorée ou grise), des coins arrondis, une ombre, un appareil (téléphone, ordinateur), une fenêtre de navigateur, plusieurs écrans inclinés en perspective, un fond derrière, une légende ou un filigrane. **Rien de tout cela n'appartient au design.** Relevé par erreur, le cadre fausse quatre invariants d'un coup : sa couleur entre dans la palette (2, 3), sa marge fait croire à des blocs encartés (4), ses coins arrondis deviennent le rayon du site et son inclinaison une rotation (5).

La référence s'isole dès réception, avant tout relevé :

```bash
python3 SKILL/scripts/reference.py "<image jointe>" PROJET/reference
# → reference/originale.<ext> et reference/page.png (la page seule), plus un rapport : cadre retiré, coins, échelle
```

**`reference/page.png` devient LA référence** : c'est elle qu'on relève, qu'on rouvre à chaque porte de registre et qu'on pose à côté des captures. Le script ne retire qu'un cadre uni qui tranche nettement avec la page ; il signale sans rien retirer une bande proche du fond de la page (cadre clair ou vraies marges ?) et une mise en scène qu'il ne sait pas découper (collage, appareil, perspective). Ces cas se tranchent à l'œil, selon `references/phases.md`, et le relevé dit en une ligne ce qui a été écarté (« cadre brun foncé de présentation, ignoré »).

## S'inspirer fortement, jamais décalquer

> **La référence donne une famille, pas une page.** On en reprend le registre — les sept invariants, le registre photographique, le niveau de finition — et on dessine **la page de cette boutique-ci**. Quelqu'un qui connaît la référence doit reconnaître la même famille ; jamais la même page.

Règle 0′ protège contre la dérive vers « plus joli ». Cette règle protège contre l'excès inverse : le copier-coller, qui livre au client le site d'un autre — et parfois une contrefaçon.

| Se reprend | Se réinterprète | Ne se reprend jamais |
|---|---|---|
| Les sept invariants (classe typographique, système de couleur, température du fond, découpe, géométrie, alignement, densité) · le registre des photos (lumière, tonalité, sujets) · le soin des détails | Un **procédé** de la référence — photo plein cadre sous le titre, alternance de bandes claires et sombres, nom de la marque en très grand dans le pied de page, section image et texte en deux colonnes — **au service de cette boutique** : autre place dans la page, autre disposition, autres proportions, autre contenu | **L'ordre de ses sections** · **la disposition exacte d'une section** (même grille, même nombre d'éléments, mêmes positions du titre, du texte, de l'image) · ses textes, titres, slogans et libellés, **même traduits** · son nom, son logo, ses icônes et illustrations dessinées · ses photos, ou une photo au même sujet et au même cadrage · ses chiffres, ses avis, ses noms |

Le plan de l'accueil vient de **la mission de l'accueil et du catalogue** (phase 1), pas de la liste des sections de la référence : on décide d'abord ce que la page doit accomplir pour cette boutique, puis on habille chaque section dans le registre de la référence. La **porte du décalque** (phase 2) le vérifie section par section.

## La règle 0″ — la structure est à WooCommerce, la surface est à nous

> **Règle 0″ — Sur les pages marchandes, on ne redessine pas WooCommerce : on l'habite.**
>
> Le DOM du panier, de la commande, de la galerie, du sélecteur de variations appartient à WooCommerce. On ne le recopie pas dans une maquette et on ne l'imite pas : on le prend tel qu'il est rendu dans l'atelier, et on dessine **par-dessus** — composition du template (ordre, regroupements, colonnes, sections ajoutées en blocs natifs), surface (typo, couleurs, rayons, espacements, états), et tout ce que WooCommerce laisse régler (libellés, blocs facultatifs, réagencement).
>
> La liberté qui reste est grande, et elle doit être prise entièrement. La panne de cette famille de pages a un nom : **WooCommerce par défaut, repeint aux couleurs de la marque.** Une grille de stock avec des boutons de la bonne teinte. Elle passe toutes les portes techniques. Le client la reconnaît pour ce qu'elle est.

Les sept invariants s'appliquent aux pages marchandes, mesurés **contre l'accueil validé** : la carte produit du catalogue est celle de l'accueil, le bouton « Ajouter au panier » est le bouton de la maquette, le panier parle la même langue typographique que le héros. Le squelette fournit une composition éprouvée pour chaque page marchande (`references/blocs-woocommerce.md`) : on la prend, et on la dessine aux couleurs, à l'échelle et aux boutons de la maquette.

## La règle de sobriété — rien d'inventé, rien de superflu

> **Ce qui n'est ni demandé au brief, ni natif de WooCommerce, n'existe pas.**
>
> Une boutique chargée de fonctionnalités que le client n'a pas demandées lui coûte : il doit les comprendre, les maintenir, ou les démentir auprès de ses clients. Et une promesse inventée (un service, une garantie) devient un engagement commercial qu'il n'a jamais pris.

Concrètement, sauf demande explicite du brief :

- **Aucune page inventée.** Les pages vitrine sont celles du brief, pas une de plus (ni « Sur-mesure », ni « FAQ », ni « Blog ») ; les pages marchandes et légales sont celles de la méthode. Une **section** FAQ factuelle dans une page, oui (voir « Des pages de la longueur d'une vraie boutique ») ; une **page** FAQ, non.
- **Aucune fonctionnalité hors WooCommerce natif** : pas de cœur « favoris » (liste d'envies), de comparateur, de compte à rebours, de badge de rareté, de fenêtre newsletter, de chat, de programme de fidélité, d'avis ou de témoignages inventés, de personne nommée.
- **Aucun service ni mode de livraison inventé** : pas de retrait en magasin (click & collect, retrait à l'atelier), de prise de rendez-vous ni de bouton « prendre rendez-vous », d'emballage cadeau, de gravure, de sur-mesure, d'entretien à vie.
- **Les engagements affichés sont des faits** : le seuil de livraison offerte des réglages, le droit de rétractation légal de 14 jours, le paiement sécurisé. Rien d'autre sans source dans le brief. Pour la même raison, jamais d'argument de fabrication ou de provenance (« fait main », « recyclé », « local ») qui ne figure pas au brief.
- **Pas de carrousel ni de diaporama sur ordinateur.** Gutenberg les gère mal (variantes invisibles dans l'éditeur, glisser inutilisable) : héros à un seul message, sections produits en grilles statiques. En mobile, une grille de deux colonnes — au plus un défilement horizontal en CSS pur (`scroll-snap`), sans script.
- **Jamais la loupe de recherche par défaut dans l'en-tête** (`core/search`). L'en-tête porte le compte et le mini-panier.

Ce que la référence montre et qui n'est pas repris pour ces raisons se consigne dans `plan.md` (« Ce que je ne reprends pas ») et figure au **compte rendu de fin**, une ligne par élément.

### Le menu mène au catalogue

- Le menu principal contient **toujours** le lien vers la page catalogue (la page Boutique de WooCommerce), nommée selon la niche : « Nos bijoux », « Catalogue », « Tous les produits »… Le même libellé titre la page.
- **2 à 4 catégories** qui tiennent dans l'en-tête : à plat dans le menu, à côté du catalogue. **Au-delà**, ou si la place manque, elles passent en **sous-menu du catalogue** (`core/navigation-submenu`).
- Puis les pages vitrine du brief (À propos, Contact…). Rien d'autre.
- **En mobile et en tablette (≤ 1024 px), le menu est replié** : bouton à gauche du logo (ou à droite des actions, jamais seul au milieu), panneau opaque aux marges de la page, liens lisibles, croix de 44 px dégagée de la barre d'admin, fermeture à la croix et à Échap, et tout ce que l'en-tête masque y figure. **Il se contrôle par `scripts/menu-mobile.js`, jamais à l'œil** : c'est la première chose que le client touche sur son téléphone, et sa panne ne se voit pas en ordinateur.

### Des pages de la longueur d'une vraie boutique

> **Une boutique en ligne se parcourt.** La pente naturelle de l'agent est la page trop courte — héros, une grille, un bandeau, le pied — qui passe toutes les portes et paraît vide au client (vécu : « tu as tendance à les faire vraiment très très courtes »). L'accueil porte **les sections de base d'un site e-commerce** ; les pages secondaires gagnent **une ou deux sections** de plus que le strict minimum. Pas de page-fleuve pour autant : chaque section a un rôle.

**L'accueil — les sections de base d'un site e-commerce**, dans l'ordre que dicte la mission (jamais celui de la référence) :

- le **héros** (un message, un bouton vers le catalogue) ;
- l'**entrée par catégories** ;
- les **produits phares** (vedettes) ;
- une **deuxième grille avec un autre angle** : une catégorie mise en avant (`by-category`), ou par matière / attribut (tuiles vers les archives `/metal/or/`) ;
- un **produit à la une** (un vrai produit du brief, prix et ajout dynamiques) ;
- le **récit de marque** (photo et texte, vers À propos ou L'atelier) ;
- une **FAQ factuelle** (`core/details`) : livraison et seuil, rétractation de 14 jours, tailles disponibles, lieu de fabrication donné au brief, paiement sécurisé — des faits des réglages, rien d'autre ;
- les **engagements** (faits seulement), puis le pied de page.

Selon le registre de la référence : une bande typographique géante, un appel final vers le catalogue.

**Les pages secondaires** (À propos, Notre histoire, L'atelier…) : la tête de page, la grande photo, le récit, **plus une ou deux sections** prises dans : un bloc photo et texte, une galerie, les matières travaillées, une petite sélection de produits (`hand-picked`), un appel final. **Contact** : formulaire et coordonnées, plus une FAQ factuelle et les engagements.

Ne rallongent jamais une page : avis ou témoignages, compteurs, logos de presse, Instagram (hors brief), newsletter, « nos valeurs » inventées, personnes nommées. Deux sections voisines ne partagent pas le même gabarit.
