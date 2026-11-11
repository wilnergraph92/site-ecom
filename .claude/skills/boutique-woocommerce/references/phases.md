# Déroulé détaillé des phases

Table des matières
- [Phase 0 — Préparation](#phase-0--préparation)
- [Phase 1 — Brief](#phase-1--brief)
- [Phase 2 — Maquette](#phase-2--maquette)
- [Phase 3 — Déclinaison](#phase-3--déclinaison)
- [Phase 4 — Thème bloc](#phase-4--thème-bloc)
- [Phase 5 — Déploiement](#phase-5--déploiement)
- [Phase 6 — Recette](#phase-6--recette)
- [Phase 7 — Clôture de l'accès](#phase-7--clôture-de-laccès)
- [Le compte rendu de fin](#le-compte-rendu-de-fin)
- [Hygiène de mesure](#hygiène-de-mesure)

Dans tout ce document, `SKILL` désigne le dossier du skill, `PROJET` le dossier du projet et `ACCES` le dossier privé du connecteur (`~/.config/boutique-woocommerce/<projet>`).

---

## Phase 0 — Préparation

### Outillage

Trois vérifications, rien à installer à la main :

```bash
node -v      # ≥ 20.18 : l'atelier (WordPress Playground) le demande
python3 -V   # scripts paquet.py, polices.py, visuels.py, blocs.py (Pillow s'installe tout seul si besoin)
curl -sI https://downloads.wordpress.org | head -1   # réseau : WordPress, WooCommerce, polices
```

Des avertissements `npm warn EBADENGINE` au lancement de l'atelier sur Node 20 sont attendus et sans effet : l'atelier a été validé avec Node 20.20.

Aucun plugin de design à installer, aucun thème tiers.

### Espace de travail

**Créer le projet dans le dossier auquel l'utilisateur a donné accès** — le répertoire de travail de la session. Sur macOS, `~/Documents` et `~/Desktop` sont protégés par TCC, **mais seulement si l'autorisation n'a pas été accordée**. Ça se teste en trois secondes :

```bash
mkdir -p "$PWD/.essai-tcc" \
  && node -e "process.chdir('$PWD/.essai-tcc'); require('fs').writeFileSync('t','x'); console.log('acces ok')" \
  ; rm -rf "$PWD/.essai-tcc"
```

- **`acces ok`** — le cas courant. Créer le projet dans le dossier de travail.
- **`EPERM: uv_cwd`** — alors seulement, créer le projet dans `~/projets-wp/<nom>`, **le dire à l'utilisateur**, et poser un lien : `ln -sfn ~/projets-wp/<nom> <dossier-de-travail>/<nom>`.

> Le serveur de maquette et l'atelier se lancent tous deux depuis `/tmp` : c'est le `cwd` du processus qui doit être accessible, jamais les dossiers qu'ils servent.

### Le connecteur, rangé et oublié

Le `.mcpb` de Novamira arrive avec le brief. On le range **tout de suite**, hors du projet, et on n'y touche plus jusqu'à la phase 5 :

```bash
python3 SKILL/scripts/connecteur.py "<chemin du .mcpb>" <slug-du-projet>
# → ~/.config/boutique-woocommerce/<slug>/ (700) : bundle/, acces.env (600)
# n'affiche que l'adresse du site et l'identifiant — jamais le mot de passe applicatif
```

Aucun appel au site ici. Le connecteur ne part ni dans le projet, ni dans un zip, ni dans un message : il porte un mot de passe applicatif en clair. S'il manque au brief, rien ne bloque — on le demande en arrivant à la phase 5. Il vit jusqu'à la **clôture** (phase 7) : mot de passe révoqué sur le site, dossier effacé ici.

### Arborescence du projet

```
PROJET/
├── plan.md                   les huit décisions de phase 1 et les tableaux des portes — notes de travail de
│                             l'agent, jamais soumises au client
├── reference/                originale.<ext> (l'image du brief) et page.png (la page seule, cadre de présentation
│                             retiré par reference.py) — page.png est LA référence de toutes les portes
├── site/                     maquette HTML (phases 2 et 3a) : _gabarit.html, _partials/, _pages/, _assembler.py,
│                             index.html…, styles.css, app.js, img/
├── theme/<slug>/             le thème bloc (dès la phase 3b) — monté dans l'atelier
├── contenus/                 contenus de pages en blocs : accueil.html, a-propos.html, contact.html,
│                             panier.html, commande.html…, formulaire-contact.txt,
│                             _carte.html (la carte produit générée dans l'éditeur), _generer.py
├── deploiement.json          réglages, catalogue, formulaires, pages — rejoué par l'atelier ET envoyé en production
├── polices-ttf/              TTF de la police des titres (logo provisoire, favicon)
└── .atelier/                 généré par atelier.sh : jeton, paquet, blueprint, rapport
```

---

## Phase 1 — Brief

### Gabarit

```
Détail de la boutique :
[Nom]
[Ce que vous vendez]
[Description]
[Contact]

Catalogue :
[Catégories]
[Quelques exemples de produits et une fourchette de prix]

Pages du site :
Accueil
[Pages vitrine : À propos, Notre histoire, Contact…]

Référence :
Image en pièce jointe

Connecteur :
Fichier .mcpb de Novamira en pièce jointe
```

Le client a installé WordPress et l'extension Novamira, téléchargé le connecteur, et envoie tout en une fois.

### Ce que le gabarit ne dit pas, la méthode le décide

Aucune de ces questions ne se pose à l'utilisateur :

- **Référence.** L'image jointe est la référence visuelle — **une inspiration forte, jamais un modèle à décalquer** (regles.md, « S'inspirer fortement, jamais décalquer ») — et seule la page compte, pas sa mise en scène (ci-dessous). Sans image, la demander avant d'écrire du code.
- **Pages.** « Pages du site » ne liste que la vitrine. Les pages marchandes (boutique, catégories, fiche, panier, commande, confirmation, Mon compte) et les quatre trames légales sont produites **quoi qu'il arrive**. Aucune page n'est ajoutée au-delà.
- **Contenu.** Rédigé à partir de l'activité et de la description ; tout élément réel fourni est repris tel quel. [Contact] alimente le pied de page, la page contact, les mentions légales et l'adresse de la boutique. **Rien d'inventé qui engage le client** : ni service, ni garantie, ni mode de livraison, ni argument de fabrication ou de provenance absent du brief, ni avis, ni personne nommée (règle de sobriété, regles.md).
- **Catalogue de démonstration.** Les catégories fournies, 3 ou 4 produits chacune, noms et prix crédibles dans la fourchette. **Au moins un produit à variations** (un attribut global, trois termes ou plus), **un en promotion**, **un en rupture** : trois états à dessiner. Trois à six produits « mis en avant » pour l'accueil.
- **Menu.** La page catalogue (page Boutique de WooCommerce) nommée selon la niche (« Nos bijoux », « Catalogue », « Tous les produits »…), toujours au menu ; 2 à 4 catégories qui tiennent : à plat ; au-delà : en sous-menu du catalogue ; puis les pages vitrine. **Pas de loupe de recherche dans l'en-tête.**
- **Images.** Le placeholder de la méthode pour toute image produit ; des photos Unsplash pour toutes les autres (règle 2), choisies dans le registre photo de la référence. Logo provisoire : le nom en texte, dans la police des titres. Favicon : l'initiale.
- **Vente.** Euros, TTC, TVA 20 %, France métropolitaine, **un forfait de livraison et un seuil de gratuité** cohérents avec les prix (par exemple 5,90 € et 60 € pour un panier moyen de 40 €), virement bancaire comme paiement de test. Aucun autre mode de livraison.
- **WordPress.** Rien n'y part avant la validation de l'accueil, la répétition générale **et le feu vert du client** (règle 3, phase 5 étape 0). **Recette.** Les sept portes se passent d'office.

**Aucune question, aucun plan à valider.** Le brief reçu, l'agent écrit une ligne au client (« Brief reçu : je construis l'accueil de <Marque>. ») et enchaîne jusqu'à la maquette. Un invariant qui semble mal convenir à la boutique se reproduit quand même et se signale en une ligne avec la maquette. Seule l'absence d'image de référence arrête le travail avant de coder.

### Isoler la page de sa présentation

Première chose faite avec l'image, avant tout relevé :

```bash
python3 SKILL/scripts/reference.py "<image jointe>" PROJET/reference
```

Il copie l'image (`reference/originale.<ext>`), retire le cadre de présentation s'il en trouve un, écrit **`reference/page.png`** et rend un rapport. Exemple, une page de marque de soins en crème présentée sur Pinterest dans un liseré brun foncé :

```json
{"originale": [349, 2000],
 "cadre": {"couleur": "#2A231A", "gauche": 4, "droite": 4, "haut": 4, "bas": 7, "fond_de_la_page": "#FBF8F2"},
 "avertissements": ["page en basse définition (341 px de large) : relever les invariants sur ce qui se lit, ne pas inventer les détails ; une mesure × 4.223 donne son équivalent à 1440 px"],
 "page": [341, 1989], "vers_1440": 4.223, "page_isolee": "PROJET/reference/page.png"}
```

Puis **regarder `page.png`** : le script ne décide pas seul. Ce qui est de la présentation, et ce que ça fausserait si on le relevait :

| Mise en scène | Se reconnaît à | Ce qu'elle fausserait |
|---|---|---|
| Cadre uni (sombre, coloré, gris) | Une bande de même couleur sur les quatre côtés ; les bandes pleine largeur de la page (héros, sections sombres, pied) s'arrêtent net à son bord | Palette et fond (2, 3) ; découpe — la page paraît « encartée » (4) |
| Coins arrondis, ombre portée | Arrondi aux quatre coins de la page entière, pas sur ses sections | Rayon du site (5) ; profondeur |
| Appareil, fenêtre de navigateur | Cadre de téléphone ou d'ordinateur, barre d'adresse, boutons de fenêtre | Découpe, en-tête, échelle |
| Collage, perspective | Plusieurs écrans côte à côte, inclinés, coupés par le bord de l'image, sur un fond commun | Rotation et diagonales (5) ; densité (7) ; le fond commun passe pour celui du site |
| Légende, filigrane, interface | Nom de l'auteur, logo du service, boutons « Enregistrer », badge « Made in … » | Contenu et typographie |

Les cas que le script laisse à l'œil :

- **Bande proche du fond de la page** (il ne retire rien et le signale) : cadre clair — un gris de Dribbble autour d'une page blanche — ou vraies marges d'une page à blocs encartés ? Un cadre entoure la page **entière**, et tout ce qui est pleine largeur dans la page s'arrête net à son bord ; des marges, elles, laissent passer au moins une bande pleine largeur (en-tête, héros, pied) ou reviennent entre les blocs. Cadre : relancer avec `--forcer`. Marges : c'est l'invariant 4, et il se relève.
- **Collage, appareil, perspective** (« coins de couleurs différentes ») : relever sur l'écran le plus grand et le plus lisible ; ignorer l'inclinaison, la perspective, les ombres, le fond commun et la coupure des autres écrans. Si besoin, recadrer cet écran à la main (Pillow) dans `reference/page.png`.
- **Basse définition** : relever ce qui se lit ; ce qui ne se lit pas (texte fin, icônes) ne s'invente pas, il se décide dans le registre.

Le relevé commence par une ligne sur ce qui a été écarté : « Cadre brun foncé de présentation (Pinterest), ignoré — page crème, bandes bord à bord, angles droits. » Au moindre doute persistant sur un élément, **il est de la présentation** : on n'hérite jamais d'un effet de mise en scène.

### Ce que l'agent décide avant de coder — pour lui seul

Ces huit décisions sont des **consignes de travail, pas un livrable** : elles s'écrivent dans `PROJET/plan.md`, dans cet ordre et avec ces intitulés, et **ne s'affichent pas dans la conversation**. La porte de registre, la porte du décalque, la phase 3 et le compte rendu de fin viennent les y relire.

1. Le **relevé de la référence** : ce qui a été écarté comme présentation, en une ligne ; puis les sept invariants, lus sur `reference/page.png`. Observation pure.
2. Le **sujet concret** : boutique, clientèle, mission unique de l'accueil.
3. Le **plan de l'accueil** — **les sections de base d'un site e-commerce** (regles.md, « Des pages de la longueur d'une vraie boutique »), décidé à partir de la mission et du catalogue, **pas** de la liste des sections de la référence :

| Section (dans l'ordre) | Rôle pour cette boutique | Ce qu'elle doit à la référence |
|---|---|---|
| Héros | Dire en une phrase ce qu'on vend, mener au catalogue | Procédé : photo plein cadre sous le titre — ici titre à gauche sur une photo d'atelier, sans carte produit posée dessus |
| Catégories | Entrer par famille de produits | Composition propre |
| Nos produits phares | Montrer la carte produit en situation | Procédé : grille de quatre sur fond clair — ici trois, avec un titre à gauche et un lien vers le catalogue |
| … | … | … |

   Chaque procédé repris dit **ce qui change** (place, disposition, proportions, contenu). « Identique » n'est pas une réponse.
4. La **palette par rôle**, prélevée sur la référence :

| Rôle (slug) | Sert à | Si la référence ne le donne pas |
|---|---|---|
| `fond` | Fond des pages | — (toujours relevé) |
| `surface` | Cartes, champs, récapitulatifs, fond des images | Le fond, assombri de 3 à 5 % |
| `texte` | Texte courant et titres | — (toujours relevé) |
| `sourdine` | Texte secondaire, fil d'Ariane, mentions | Le texte à 55-65 % |
| `trait` | Bordures, séparateurs | Le texte à 12-18 % |
| `accent` | Boutons, pastilles choisies, curseur de prix, favicon | **Le texte**, si la référence n'a aucun accent |
| `sur-accent` | Texte posé sur l'accent, lettre du favicon | Le fond |
| `promo` | Prix promotionnels, badge « Économisez » | Un rouge ou un bordeaux lisible sur le fond |
| `alerte` | Erreurs de formulaire | Un rouge sombre lisible sur le fond |

5. Les **typographies** : la classe est imposée par le relevé ; seule la famille exacte est un choix. Deux rôles, titres et texte.
6. Le **plan de catalogue et le menu** : catégories, produits (nom, prix, promo, stock, variations), réglages de vente, libellé de la page catalogue, catégories à plat ou en sous-menu.
7. **Ce que j'ajoute** : la liste exhaustive de ce qui, **dans le registre**, ne figure pas dans la référence (couleur, police, effet, forme, découpe). Sa bonne valeur est souvent zéro. La composition n'y figure pas : elle est déclarée au point 3.
8. **Ce que je ne reprends pas** : les fonctionnalités de la référence écartées par la règle de sobriété, une ligne chacune.

Quatre points de contrôle, pour soi : s'il manque au plan de l'accueil une des sections de base d'un site e-commerce, il est trop court ; si ce plan ressemble à ce qu'on produirait pour n'importe quelle boutique du même secteur, le refaire ; si « Ce que j'ajoute » dépasse deux lignes, c'est un autre site qui se prépare ; si le plan de l'accueil suit celui de la référence section après section, c'est un décalque — repartir de la mission de l'accueil.

---

## Phase 2 — Maquette

Une seule page, l'accueil, en HTML et CSS, en local.

### La porte d'entrée : règle 0

**Rien de la phase 4 n'a le droit d'entrer ici.** Le test, à chaque décision de composition : *est-ce que j'écarte cette idée parce qu'elle est mauvaise, ou parce que je me demande comment elle passera en blocs ?* Si c'est la seconde réponse, l'idée reste.

### La seconde porte d'entrée : règle 0′

Les sept invariants appartiennent à la référence. Le test, à chaque décision de surface : *est-ce que je choisis ça parce que la référence le fait, ou parce que je le trouve plus beau ?* Si c'est la seconde réponse, c'est une dérive : revenir à la référence.

### La troisième : la sobriété

Le test, à chaque composant : *le brief le demande-t-il, ou WooCommerce le fait-il nativement ?* Sinon, il n'existe pas. Ce qui disparaît le plus souvent au passage : le cœur des cartes, le bouton « prendre rendez-vous », le retrait en magasin, le carrousel, le diaporama du héros, la loupe de l'en-tête, les promesses non sourcées.

### La quatrième : s'inspirer, pas décalquer

Le test, à chaque section : *est-ce que je reprends un procédé de la référence pour ce qu'il apporte à cette boutique, ou est-ce que je recopie sa section ?* Un procédé se transpose — autre place, autre disposition, autres proportions, autre contenu. Une section recopiée, même repeinte et traduite, reste celle d'un autre site. Aucun texte de la référence n'est repris, même traduit.

### Le parti pris se décide, il ne se déduit pas

> **Avec une référence, ce tableau se lit à l'envers** : chaque ligne est une question posée à la référence, et sa réponse est la consigne.

| Exigence | Ce qu'on regarde sur la capture | Ce qu'on demande d'abord à la référence |
|---|---|---|
| Un élément domine | On peut nommer en un mot ce qui porte la page | A-t-elle une dominante, ou est-elle uniforme ? |
| La couleur forte occupe du terrain | Elle couvre une surface réelle, pas deux bandeaux | Combien de couleurs non neutres ? Zéro est une réponse. |
| Les sections n'ont pas le même poids | Deux sections voisines ne partagent pas le même gabarit | Ses sections varient-elles, ou sont-elles calibrées pareil ? |
| Il y a de la profondeur | Au moins un élément sort de son conteneur | Combien de débordements réels chez elle ? |
| La typo de caractère travaille | Elle porte du contenu, pas une figuration | Combien de familles, et de quelle classe ? |
| Le concept est visible sans légende | Il se lit en échelle et en composition | A-t-elle un concept, ou est-ce un gabarit sobre ? |

### Spécificités boutique

- **Images produit : le placeholder.** Copier `SKILL/assets/placeholders/` dans `site/img/`. Toute image de produit prend le fichier de **son** format (`placeholder-4x5.png` pour une carte…) avec `object-fit: cover`.
- **Toutes les autres images : Unsplash** — voir « Sourcer les photos ». Elles se règlent **avant la première capture**.
- **Texte sur image : voile obligatoire**, en pseudo-élément, réglé sur la photo la plus claire.
- **En-tête** : logo (le nom de la marque en texte, dans la police des titres — il deviendra une image en phase 3b), le menu du catalogue (phase 1), le compte, le panier avec compteur. Pas de loupe.
- **Héros à un seul message.** Pas de diaporama, pas d'onglets qui font défiler des messages.
- **Carte produit** : image au ratio choisi (4:5 par défaut), étiquette de catégorie ou badge promo, titre, prix normal et prix barré, bouton d'ajout. Pas de cœur. Une section « produits » de l'accueil la montre en situation, **en grille** (pas de carrousel).
- **Pied de page** : coordonnées du brief, colonnes de liens (boutique, aide, légal). Réseaux sociaux seulement s'ils sont au brief.
- **Variables par rôle dès la première ligne** : `--fond --surface --texte --sourdine --trait --accent --sur-accent --promo --alerte --police-titres --police-texte`. Aucune couleur en dur hors de `:root`.
- **Les engagements affichés** (bandeau, réassurance) : seulement des faits — le seuil de livraison offerte, la rétractation de 14 jours, le paiement sécurisé.

### Sourcer les photos (Unsplash)

Toutes les images qui ne sont pas des produits. Quatre temps :

1. **Relever les identifiants sur une page de résultats**, jamais deviner une URL. Chercher en anglais par emplacement (`goldsmith workshop`, `woman wearing gold necklace`…), puis sur la page de résultats :

```js
[...new Set([...document.querySelectorAll('img[src*="images.unsplash.com/photo-"]')]
  .map(i => (i.src.match(/photo-[\w-]+/) || [''])[0] + ' | ' + (i.alt || '').slice(0, 70)))].slice(0, 24)
```

   Le sélecteur écarte d'office les images **Unsplash+** (payantes, `plus.unsplash.com/premium_photo-…`). Refuser une bannière de cookies en gardant le choix le plus restrictif.

2. **Planche contact** : télécharger un aperçu léger de chaque candidate (`https://images.unsplash.com/<id>?w=360&q=60&fm=jpg`), les assembler numérotées sur une image, et **choisir à l'œil** dans le registre photographique de la référence — lumière, tonalité, type de sujet — **jamais une photo qui refait l'une des siennes** (même sujet, même cadrage). Pour une page (À propos), préférer une **série cohérente** (même lieu, même personne).

3. **Recadrer au format de l'emplacement** par les paramètres de l'URL, pas en CSS :

```bash
curl -s -o site/img/hero.jpg "https://images.unsplash.com/<id>?w=2400&h=1100&fit=crop&crop=entropy&q=80&fm=jpg"   # héros large
curl -s -o site/img/portrait.jpg "https://images.unsplash.com/<id>?w=1200&h=1500&fit=crop&crop=entropy&q=80&fm=jpg" # 4:5
```

   `crop=faces` quand le sujet est un visage ; `crop=focalpoint&fp-x=…&fp-y=…` pour décentrer. **Un visage coupé en haut d'un héros de page se recadre avant l'intégration** : contrôler chaque recadrage sur une planche.

4. **Nommer par emplacement** (`hero.jpg`, `bandeau-atelier.jpg`) : ce nom devient le slug du média (`{{media:hero}}`). Texte alternatif descriptif dans le HTML, repris dans `deploiement.json`.

La licence Unsplash autorise l'usage commercial sans attribution obligatoire.

### Serveur de maquette

```bash
cd /tmp && RACINE=PROJET/site nohup node SKILL/scripts/serve.js > /tmp/serve.log 2>&1 &
sleep 1 && tail -1 /tmp/serve.log   # le serveur affiche le port réellement utilisé
```

Si 4321 est occupé, le serveur essaie les ports suivants — lire le journal, et ne jamais tuer un processus qui l'occupe. Donner l'adresse locale à l'utilisateur quand il la demande.

### Protocole d'itération

1. Écrire la page complète, avec du contenu réel, jamais de lorem.
2. Prendre **une** capture, corriger ce qu'elle montre.
3. **Un tour de design avant de montrer au client** : pousser la page *vers le registre de la référence*, jamais vers sa mise en page.
4. **Passer la porte de registre et la porte du décalque**, puis relire la page contre la règle de sobriété.
5. **Montrer au client** — capture pleine page en ordinateur et en mobile, adresse locale, trois lignes au plus (la direction prise ; ce qui mérite un regard), puis : « Je continue sur toutes les pages et l'installation, ou tu veux changer quelque chose ? ». Aucun tableau, aucune décision détaillée : les portes restent dans `plan.md`.
6. Deux à trois tours maximum. Au « go », la suite s'enchaîne **jusqu'à la répétition générale**, sans autre validation du design. La seule question suivante est le **feu vert d'installation** (phase 5, étape 0) : le « go » sur la maquette n'autorise aucune écriture sur le site du client.

### La porte de registre

**D'abord, rouvrir `reference/page.png` dans le même message que la capture** — la page isolée, jamais l'originale dans son cadre, jamais la référence de mémoire. Ensuite, relever la maquette telle qu'elle est :

```js
const cs = e => getComputedStyle(e);
const titres = [...document.querySelectorAll('h1,h2,h3')];
const blocs  = [...document.querySelectorAll('section,article,figure,aside')];
const teinte = c => { const [r,g,b] = c.match(/\d+/g).map(Number);
  return Math.max(r,g,b) - Math.min(r,g,b) > 18 ? c : null; };   // non neutre
({
  famillesTypo : [...new Set([cs(document.body).fontFamily, ...titres.map(t => cs(t).fontFamily)])],
  italiques    : titres.filter(t => cs(t).fontStyle === 'italic').length,
  fondClair    : cs(document.body).backgroundColor,
  nonNeutres   : [...new Set(blocs.flatMap(e => [cs(e).backgroundColor, cs(e).color]).map(teinte).filter(Boolean))],
  rayons       : [...new Set(blocs.map(e => cs(e).borderRadius))],
  centres      : titres.filter(t => cs(t).textAlign === 'center').length + ' / ' + titres.length,
  corpsVsTitre : cs(document.body).fontSize + ' · ' + (titres[0] && cs(titres[0]).fontSize),
})
```

Puis remplir ce tableau dans `plan.md`, **avant** de montrer la maquette :

| Invariant | Référence | Maquette (relevé) | Verdict |
|---|---|---|---|
| 1 · Classe typographique | | | |
| 2 · Système de couleur | | | |
| 3 · Température du fond clair | | | |
| 4 · Découpe de la page | | | |
| 5 · Géométrie | | | |
| 6 · Alignement dominant | | | |
| 7 · Densité et échelle | | | |

Un verdict ne prend que deux valeurs : **conforme**, ou **autorisé par le client** avec la citation de son accord.

### La porte du décalque

La porte de registre vérifie que la maquette est **de la même famille** que la référence ; celle-ci vérifie qu'elle n'est **pas la même page**. Même message, même image (`reference/page.png`), même capture pleine page de la maquette. Relever la liste des sections de la maquette :

```js
[...document.querySelectorAll('main > section, main > div, body > section')].map(s =>
  (s.className.split(' ')[0] || s.tagName) + ' · ' + (s.querySelector('h1,h2')?.textContent.trim().slice(0, 40) || '—'))
```

Puis remplir, dans `plan.md`, **avant** de montrer la maquette :

| Section de la maquette | Équivalent dans la référence | Ce qui diffère |
|---|---|---|
| | | |

Réception :

- **l'ordre** des sections n'est pas celui de la référence ;
- chaque section qui a un équivalent **diffère par sa disposition ou ses proportions** — pas seulement par ses couleurs, ses textes ou ses photos ;
- **aucun texte** de la référence, même traduit ; aucun nom, logo, icône ou illustration dessinée de la référence ;
- les procédés repris servent un rôle déclaré au plan de l'accueil (phase 1, point 3).

Une ligne qui échoue se corrige avant de montrer : on change la disposition de la section, pas sa couleur. Et la porte de registre se repasse ensuite — s'éloigner de la page de la référence ne donne jamais le droit de s'éloigner de son registre.

### Points de contrôle avant validation

- Aucune décision de composition prise en pensant à la conversion (règle 0).
- Porte de registre passée : sept invariants, sept verdicts (règle 0′). `reference/page.png` rouverte à côté de la capture, dans ce tour-ci ; aucun élément du cadre de présentation relevé comme du design.
- **Porte du décalque passée** : même famille que la référence, jamais la même page — ordre des sections différent, aucune section à la même disposition, aucun texte repris.
- **Sobriété** : aucune page, fonctionnalité, service ni promesse hors brief ; pas de carrousel, pas de diaporama, pas de loupe ; le menu mène au catalogue.
- Un élément domine la page ; deux sections voisines ne partagent pas le même gabarit.
- **Longueur** : l'accueil porte les sections de base d'un site e-commerce (héros, catégories, produits phares, une deuxième grille sous un autre angle, produit à la une, récit, FAQ factuelle, engagements) ; s'il en manque, la page est trop courte — la compléter par des sections légitimes, jamais par du remplissage.
- Les cartes produit tiennent avec le placeholder ; les autres images sont de vraies photos, au format exact.
- La carte produit montre un prix barré et un badge promo quelque part.
- Aucune couleur en dur hors de `:root`. Le titre principal tient sur deux lignes au plus.
- Rendu à 390 px sans défilement horizontal ; le menu mobile s'ouvre, se lit, se ferme (croix et Échap), et donne accès à tout ce que l'en-tête masque. Le bouton du menu est à gauche du logo ou à droite des actions, jamais seul au milieu de l'en-tête.

---

## Phase 3 — Déclinaison

L'accueil validé, le système de design l'est aussi. Aucune page ne rouvre le débat esthétique.

### 3a · Pages vitrine, en HTML

Les pages listées au brief, et elles seules. Générées par script depuis une ossature commune, pour que l'en-tête, le pied de page et la carte produit soient strictement identiques partout :

```
site/_gabarit.html        squelette ({{titre}}, {{description}}, {{classe}}, {{entete}}, {{contenu}}, {{pied}})
site/_partials/entete.html, pied.html
site/_pages/<page>.html   le contenu de <main>
site/_assembler.py        assemble index.html, a-propos.html… (relancer après chaque retouche)
```

**Longueur** : une page vitrine n'a pas besoin d'être longue, mais elle gagne **une ou deux sections** de plus que tête + photo + récit : un bloc photo et texte, une galerie, les matières, une petite sélection de produits (`hand-picked`), un appel final. Contact : formulaire et coordonnées, plus une FAQ factuelle et les engagements.

Les pages légales n'ont pas de maquette : elles utilisent `assets/contenus/` et les styles de texte courant.

### 3b · Pages marchandes, dans l'atelier

#### 1. Thème minimal

```bash
cp -R SKILL/assets/theme-squelette PROJET/theme/<slug>
cd PROJET/theme/<slug>
grep -rl 'mon-theme\|mon_theme\|Mon Thème\|Mon thème' . | xargs sed -i '' \
  's/mon-theme/<slug>/g; s/mon_theme/<slug_en_underscores>/g; s/Mon Thème/<Marque>/g; s/Mon thème/<Marque>/g'
mkdir -p assets/img && cp SKILL/assets/placeholders/*.png assets/img/ && cp PROJET/site/img/*.jpg assets/img/
```

(`sed -i ''` est la syntaxe macOS ; sous Linux, `sed -i`.) Puis :

- **Palette** : les valeurs de la phase 1 dans `theme.json` → `settings.color.palette`, slugs inchangés (`promo` compris).
- **Typographie et boutons de theme.json d'après la maquette** : `styles.elements.heading` (graisse, interlettre, hauteur de ligne) et `styles.elements.button` (corps, graisse, marges) — les valeurs du squelette ne correspondent à aucune maquette, et elles règlent l'éditeur et tous les boutons de WooCommerce.
- **Rayons** : `settings.custom.rayon` (cartes) et `settings.custom.rayon-bouton` (boutons, champs de la maquette).
- **Polices** — l'URL Google Fonts exacte du `<link>` de la maquette :

```bash
python3 SKILL/scripts/polices.py --theme PROJET/theme/<slug> \
  --url "https://fonts.googleapis.com/css2?family=…&display=swap" \
  --titres "Famille des titres:serif" --texte "Famille du texte:sans-serif" \
  --ttf PROJET/polices-ttf
```

- **Logo provisoire, favicon, vignette** :

```bash
python3 SKILL/scripts/visuels.py logo --marque "<Marque>" --police PROJET/polices-ttf/titres.ttf \
  --couleur "<hex du rôle texte>" --sortie PROJET/theme/<slug>/assets/img/logo.png
python3 SKILL/scripts/visuels.py favicon --marque "<Marque>" --police PROJET/polices-ttf/titres.ttf \
  --fond "<hex du rôle accent>" --lettre "<hex du rôle sur-accent>" --sortie PROJET/theme/<slug>/assets/img/favicon.png
python3 SKILL/scripts/visuels.py vignette --marque "<Marque>" --sortie PROJET/theme/<slug>/screenshot.png
```

   Le favicon prend la paire **accent / sur-accent** : charte claire à accent sombre → fond sombre et lettre claire ; accent clair → l'inverse. `--arrondi` (0 à 0,5) suit la géométrie de la référence.

- **En-tête et pied de page** : ceux de la maquette dans `parts/header.html` et `parts/footer.html` — mêmes classes, blocs natifs. Le logo est un bloc **Logo du site** (`core/site-logo`), jamais une image. Le menu : lien du catalogue, catégories (à plat ou en `core/navigation-submenu`), pages vitrine. Ce que l'en-tête masque en mobile (un lien Contact posé dans les actions, un bouton) entre dans le menu avec `"className":"menu-mobile-seul"` : visible dans le panneau seulement. Le panneau du menu mobile est déjà réglé dans le squelette (marges, croix de 44 px, repli dès 1024 px, bouton du menu à gauche du logo) : ne pas retirer ces règles. Le mini-panier porte `"miniCartIcon":"bag"` (ou l'icône de la maquette) et `"addToCartBehaviour":"open_drawer"` : le tiroir s'ouvre à chaque ajout.
- **Largeur du logo** : celle du logo texte de la maquette, **mesurée** (en ordinateur et en mobile), posée en attribut `width` du bloc et en CSS.

#### 2. deploiement.json

Partir de `SKILL/assets/deploiement-exemple.json`. Clés :

| Clé | Contenu |
|---|---|
| `marque`, `theme` | Nom affiché (devient le nom du site), slug du thème |
| `reglages` | `slogan` (devient le slogan du site), adresse, devise, TVA, livraison (`forfait` et `gratuite_des` **TTC**, titres), paiements de test, `ratio_vignettes`, `placeholder_produit`, `catalogue` (`colonnes` × `rangees` = produits par page, défaut 3 × 4), permaliens |
| `medias` | Fichiers de `theme/<slug>/assets/img/` à importer (slug → texte alternatif) |
| `logo`, `favicon` | Les slugs des médias logo et favicon |
| `categories`, `attributs`, `produits` | Le catalogue de démonstration. Clé des produits : le SKU. `attributs` d'un produit variable = ses attributs de variation ; `caracteristiques` = attributs affichés sans variation (ex. le métal d'une bague qui varie par taille). `archives: true` sur un attribut = une page par terme (`/metal/or-rose/`), servie par `archive-product.html` |
| `formulaires` | Contact Form 7 : `titre`, `gabarit` (« @contenus/formulaire-contact.txt »), `destinataire` |
| `pages` | **Les pages vitrine du brief** et les trames légales : slug, titre, `contenu` (« @contenus/fichier.html ») |
| `pages_woocommerce` | Boutique (titre selon la niche : c'est le libellé du menu), panier, commande, mon-compte, cgv, confidentialite |
| `accueil` | Slug de la page d'accueil |
| `extensions` | Extensions wordpress.org (ex. `contact-form-7`) : installées dans l'atelier, et en production par `c2woo_preparer()` |
| `mise_en_ligne` | `false` tant que le client n'a pas dit oui |

Les contenus de page utilisent des **jetons**, remplacés au déploiement parce que les identifiants diffèrent entre l'atelier et la production : `{{media:slug}}`, `{{media-url:slug}}`, `{{produit:SKU}}`, `{{lien-produit:SKU}}`, `{{categorie:slug}}`, `{{lien-categorie:slug}}`, `{{page:slug}}`, `{{lien-page:slug}}`, `{{formulaire:<titre>}}`.

Copier `SKILL/assets/contenus/*` dans `PROJET/contenus/`.

#### 3. Lancer l'atelier

```bash
cd /tmp && nohup bash SKILL/scripts/atelier.sh PROJET 9400 > /tmp/atelier.log 2>&1 &
# attendre « Ready! » dans le journal (« Error downloading translations for theme » est attendu), puis :
cat PROJET/.atelier/rapport.json          # le déploiement à blanc : avertissements, identifiants
```

Le journal affiche le **lien de connexion sans mot de passe** à ouvrir dans le navigateur. PHP dans l'atelier :

```bash
SKILL/scripts/atelier-php.sh PROJET 'return c2woo_blocs_disponibles();'
```

Mode développement de thème actif (`WP_DEVELOPMENT_MODE=theme`) : un fichier du thème modifié se voit au rechargement. Une modification de `deploiement.json` ou de `contenus/` demande de rejouer le déploiement :

```bash
python3 SKILL/scripts/paquet.py PROJET/deploiement.json > PROJET/.atelier/paquet.json
SKILL/scripts/atelier-php.sh PROJET 'return c2woo_deployer(json_decode(file_get_contents("/atelier/projet/.atelier/paquet.json"), true), "atelier");'
```

Pour une seule page : `wp_update_post(wp_slash(["ID" => wc_get_page_id("cart"), "post_content" => file_get_contents("/atelier/projet/contenus/panier.html")]))`. Un réglage de livraison modifié ne se voit qu'après `WC_Cache_Helper::get_transient_version("shipping", true)` (les tarifs sont en cache de session).

#### Le contrôle du menu mobile, dès que l'en-tête est posé

Le menu mobile est la première chose qu'un client touche sur son téléphone, et sa panne est invisible en ordinateur : liens collés au bord gauche, croix trop petite ou cachée sous la barre d'admin, menu impossible à fermer. Il se contrôle **par script, jamais à l'œil** : `scripts/menu-mobile.js`.

Pour chaque largeur — **390 × 844, 768 × 1024, puis 1440 × 900** — régler le viewport, recharger la page, puis passer **le contenu du fichier, tel quel**, à l'outil JavaScript du navigateur. Il ouvre le menu, le mesure, le ferme à Échap puis à la croix, et rend un verdict :

```json
{"ok":true,"largeur":390,"connecte":true,"defauts":[],
 "mesures":{"sw":390,"bouton":[5,62,44,44],"croix":[341,67,44,44],"fond":"rgb(255, 255, 255)",
            "liens":["Tous les produits @16","Bracelets @16","À propos @16","Contact @16"],"focus":true}}
```

`ok: false` bloque la suite : chaque entrée de `defauts` dit ce qui cloche et où corriger. Ce qu'il contrôle :

| Largeur | Contrôles |
|---|---|
| ≤ 1024 px | menu replié (bouton visible, aucun lien en ligne) ; bouton ≥ 40 px, **à gauche du logo ou à droite des actions, jamais au milieu** ; panier visible ; rien ne se chevauche dans l'en-tête ; le menu s'ouvre ; panneau opaque, pleine largeur ; **liens décalés du bord (≥ 12 px)**, lisibles, ≥ 16 px, cibles ≥ 40 px de haut ; derniers liens atteignables ; **croix ≥ 40 px, sous la barre d'admin, recouverte par rien** (barre d'admin, widget d'hébergeur) ; **fermeture à Échap et à la croix** ; page de nouveau défilable ; tout lien que l'en-tête masque à cette largeur présent dans le panneau (sinon : un lien `menu-mobile-seul`) |
| > 1024 px | bouton du menu masqué ; liens `menu-mobile-seul` invisibles ; **menu sur une seule ligne** (sinon : catégories en sous-menu du catalogue) ; rien ne se chevauche dans l'en-tête |

À repasser, sans exception : ici en phase 3b, à la répétition générale (phase 4), **sur le site juste après le déploiement** (phase 5, connecté), à la porte 3 (déconnecté et connecté), et **après toute modification de l'en-tête ou de `style.css`**. Chaque passage se fait sur deux pages au moins — l'accueil et une page marchande (boutique ou panier).

Dans l'atelier, un raccourci évite de recoller le fichier à chaque rechargement : le copier dans le thème (`cp SKILL/scripts/menu-mobile.js PROJET/theme/<slug>/menu-mobile-test.js`), puis
`const src = await (await fetch('/wp-content/themes/<slug>/menu-mobile-test.js?' + Date.now())).text(); await eval(src.replace(/^await \(async/m, '(async'))`.
Supprimer `menu-mobile-test.js` avant d'empaqueter le thème.

#### 4. Dessiner les pages marchandes

Pour chaque page, dans cet ordre, la boucle de la phase 2 — écrire, capturer, corriger — sur l'URL de l'atelier. Le squelette fournit la **composition** éprouvée (détail dans `blocs-woocommerce.md`) ; la maquette donne la **surface** : tailles, espacements, boutons, pastilles, icônes.

| Page | URL de l'atelier | États à dessiner et à vérifier |
|---|---|---|
| Carte produit | `/boutique/` | normale, promo, rupture, à variations ; **mesurée identique** à celle de la maquette |
| Catalogue | `/boutique/`, `/categorie-produit/<slug>/` | **une catégorie ne montre que ses produits** ; filtres (prix, attribut, disponibilité) ; aucun résultat ; 2 colonnes à 390 px |
| Recherche | `/?s=<mot>&post_type=product` | résultats, aucun résultat |
| Fiche produit | `/produit/<slug>/` | simple ; à variations — pastille choisie, variation en rupture, prix qui suit la variation ; promo ; rupture ; **un produit avec trois photos** (galerie réelle : vignettes et grande image — voir `pieges.md`) |
| Mini-panier | ajout depuis une fiche | s'ouvre à l'ajout, une seule vignette par article, vide |
| Panier | `/panier/` | rempli (quantités, code promo) — **livraison offerte présélectionnée au-dessus du seuil** ; vide ; 390 px sans débordement |
| Commande | `/commande/` | validation à vide (erreurs à la couleur « alerte »), rempli, **une seule option de livraison au-dessus du seuil**, virement, CGV |
| Confirmation | après une commande de test | numéro, total TTC, adresses ; adresse en français (`/commande/commande-recue/<n>/`) |
| Mon compte | `/mon-compte/` | déconnecté (connexion + inscription), connecté (tableau de bord, commandes, adresses) |
| Messages | ajout au panier, erreur | bandeaux à la palette, pas le bleu de WooCommerce |

Remplir un panier sans cliquer — depuis une page qui porte le bloc Panier (le jeton de l'API n'est chargé que là) :

```js
const nonce = wcBlocksMiddlewareConfig.storeApiNonce;
const ajouter = (id, quantity, variation) => fetch('/wp-json/wc/store/v1/cart/add-item', { method: 'POST', credentials: 'include',
  headers: { 'Content-Type': 'application/json', Nonce: nonce }, body: JSON.stringify({ id, quantity, ...(variation ? { variation } : {}) }) }).then(r => r.status);
const produits = await fetch('/wp-json/wc/store/v1/products?per_page=50').then(r => r.json());
await ajouter(produits.find(p => p.name === 'Nom du produit').id, 1, [{ attribute: 'Taille', value: 'Moyenne' }]);
```

Déconnecter pour la connexion et l'inscription de Mon compte : le lien « Se déconnecter » de Mon compte ; se reconnecter : le lien de l'atelier (`/atelier/connexion.php?jeton=…&vers=/…`).

Où vit chaque décision :

- **Composition** → les templates (`templates/*.html`, `patterns/catalogue.php`) et les contenus (`contenus/panier.html`, `contenus/commande.html`).
- **Surface** → `style.css`, sections 4 à 10, sur les classes listées dans `blocs-woocommerce.md`.
- **Libellés et options** → attributs des blocs, réglés dans l'éditeur de l'atelier.

Tout balisage de bloc WooCommerce composite **se génère dans l'éditeur de l'atelier** (procédure dans `blocs-woocommerce.md`). Un attribut simple ajouté à un bloc dynamique sans balisage enregistré (`aspectRatio` d'une image, `miniCartIcon`) se pose dans le délimiteur, puis se valide (porte 2).

#### 5. Porte de registre marchande

Capture de chaque page marchande **dans le même message** que la capture de l'accueil validé. Même tableau des sept invariants. Plus :

- La carte produit du catalogue et celle de l'accueil sont **mesurées identiques** (hauteur totale, écart image/infos, corps et graisse du titre).
- Aucun composant WooCommerce dans son habit par défaut (bouton gris, champ à bordure bleue, badge « Promo ! », onglets, bandeau bleu, smiley du panier vide).
- Si la page ressemble à la démo de WooCommerce avec la palette du client, reprendre la composition.

#### 6. Pas de seconde validation du design

L'accueil validé fait foi : les pages marchandes en reprennent le système, et la porte de registre marchande en répond. On enchaîne sur la phase 4 sans rien soumettre au client. Les captures de toutes les pages, en ordinateur et à 390 px, accompagnent le compte rendu de fin. (Le feu vert de la phase 5 n'est pas une validation du design : il autorise l'écriture sur le site.)

---

## Phase 4 — Thème bloc

Voir `blocs-gutenberg.md` (pages vitrine) et `blocs-woocommerce.md` (pages marchandes).

Arborescence cible :

```
<slug>/                    le nom du dossier EST le slug passé à switch_theme()
├── style.css              en-tête + alias de la palette + CSS de la maquette + WooCommerce
├── theme.json             palette par rôle, deux polices (fontFace locales), rayons, blockGap 0
├── functions.php          styles, script, pont des polices, livraison offerte seule, CF7 sans <p>
├── screenshot.png         la vignette « Marque / Ecommerce » (visuels.py)
├── parts/                 header (menu du catalogue), footer, checkout-header, mini-cart
├── templates/             front-page, page, index, 404, archive-product, product-search-results,
│                          single-product, page-cart, page-checkout, order-confirmation
├── patterns/              catalogue.php (corps commun du catalogue et de la recherche), produits-vedettes.php
└── assets/
    ├── app.js, editor.css
    ├── fonts/             woff2 (polices.py)
    └── img/               placeholders, photos Unsplash, logo, favicon — importés en médiathèque au déploiement
```

### Pages vitrine

1. **La carte produit du thème**, générée dans l'éditeur (phase 3b), enregistrée dans `contenus/_carte.html` : les sections produits de l'accueil la réutilisent telle quelle.
2. **Un script de projet**, `contenus/_generer.py`, qui écrit `contenus/accueil.html` et les autres pages avec `SKILL/scripts/blocs.py` : chaque section de la maquette devient des blocs natifs, classes via `className`, images liées (`image('hero', 'alt', 'hero-photo')`), sections produits en `collection(...)` (featured, by-category, hand-picked, on-sale — jamais new-arrivals et son `timeFrame`), identifiants par jetons. Relancer après chaque retouche : c'est la source, le HTML généré n'est jamais retouché à la main.
3. **Adapter le CSS de la maquette au DOM des blocs** — liste de contrôle dans `blocs-gutenberg.md` (image → `figure.classe img`, lien → paragraphe, `span`/`b` → `strong`, `dl` → groupes, `ul` → `core/list`, `details` → `core/details`, formulaire → Contact Form 7).
4. **Contact** : `contenus/formulaire-contact.txt` (le gabarit, aux classes de la maquette), déclaré dans `formulaires` ; la page contient `[contact-form-7 id="{{formulaire:<titre>}}" title="<titre>"]` dans un `core/shortcode`, dans le groupe du formulaire.
5. **`assets/editor.css`** : couches sorties du flux rétablies, variantes toutes visibles, liens étendus coupés, pour que chaque bloc reste sélectionnable dans le canevas.
6. **Déclarer les pages** dans `deploiement.json` (`pages`), rejouer le déploiement, puis la porte 2.

### Styles globaux — la porte avant la porte

C'est ici que la maquette se branche sur Apparence → Éditeur → Styles :

1. Dans `style.css`, la palette de la maquette devient des alias : `--fond: var(--wp--preset--color--fond);` etc.
2. **Supprimer** toute définition de `--police-titres` et `--police-texte` de `style.css` : c'est le pont de `functions.php` qui les pose.
3. Les rayons deviennent `var(--rayon)` et `var(--rayon-bouton)`.

Contrôle — les deux commandes doivent ne rien afficher :

```bash
# couleurs en dur hors du bloc :root des alias (commentaires exclus) — y compris dans un masque en dégradé
python3 - PROJET/theme/<slug>/style.css <<'EOF'
import re,sys; s=re.sub(r'/\*.*?\*/','',open(sys.argv[1]).read(),flags=re.S)
s=re.sub(r':root\s*{[^}]*}','',s,count=1)
print('\n'.join(sorted(set(re.findall(r'#[0-9a-fA-F]{3,8}\b|rgba?\([^)]*\)|hsla?\([^)]*\)',s)))))
EOF
# familles de polices en dur
grep -n "font-family" PROJET/theme/<slug>/style.css | grep -v "var(--police-\|inherit"
```

Un masque (`mask-image: linear-gradient(…, #000 …)`) ne lit que l'opacité : y mettre `var(--texte)`.

### La mesure, section par section

Maquette (serveur local) et atelier, **même viewport** (1440 × 900 puis 390 × 844), même relevé — la position de chaque section depuis le haut du `body` (moins la barre d'admin, 32 px, ou 46 px en mobile, si l'on est connecté) et sa hauteur :

```js
const barre = document.body.classList.contains('admin-bar') ? (innerWidth > 782 ? 32 : 46) : 0;
const m = s => { const e = document.querySelector(s); if (!e) return null; const r = e.getBoundingClientRect();
  return [Math.round(r.top + scrollY - barre), Math.round(r.height)]; };
JSON.stringify({ hero: m('.hero'), produits: m('.grille-produits'), bandeau: m('.bandeau'), pied: m('.pied'),
  fin: Math.round(document.querySelector('.pied').getBoundingClientRect().bottom + scrollY - barre) });
```

Adapter la liste de sélecteurs (maquette : `.grille-4` ; site : `.collection-x ul`). Mesurer la **fin du pied de page**, pas `scrollHeight` : le tiroir du mini-panier, fixe et haut comme la fenêtre, gonfle `scrollHeight`. Un écart se descend jusqu'au composant (hauteurs des enfants, marges calculées) — les causes relevées sont dans `pieges.md` (« Le site mesure quelques pixels de plus que la maquette »).

### Répétition générale

Relancer l'atelier **depuis zéro** (arrêter le processus, puis `atelier.sh`) : il rejoue tout `deploiement.json` sur une base vierge, exactement comme la production le fera. Rapport sans avertissement ni erreur de média, puis portes 1, 2, 3, 4 et 7 dans l'atelier — dont `menu-mobile.js` à 390, 768 et 1440 px, déconnecté et connecté.

---

## Phase 5 — Déploiement

### Prérequis · L'accès au site

Le connecteur rangé en phase 0 (sinon : le demander maintenant, puis `connecteur.py`). Quatre commandes, toutes avec `ACCES=~/.config/boutique-woocommerce/<slug>` ; aucune n'affiche un secret :

| Commande | Rôle |
|---|---|
| `SKILL/scripts/wp.sh <ability> '<json>'` | Appel direct d'une ability Novamira |
| `SKILL/scripts/envoyer.sh '<php>'` | `novamira/execute-php` avec `boutique.php` chargé |
| `SKILL/scripts/televerser.sh <local> <distant>` | `novamira/create-upload-link` + PUT |
| `SKILL/scripts/lien-admin.sh` | Session wp-admin sans mot de passe : n'affiche que l'URL, **à usage unique, valable 60 s** — l'ouvrir aussitôt |

Novamira n'expose pas d'installation d'extension ; `c2woo_preparer()` passe par le mécanisme natif de WordPress. Ne jamais modifier, désactiver ni mettre à jour l'extension Novamira : c'est la connexion en cours. (Seule exception, en toute fin : `c2woo_revoquer_acces()` à la clôture, phase 7.)

Neuf opérations, de 0 à 8, dans cet ordre.

### 0 · État des lieux, feu vert du client, puis préparation

**a) État des lieux — lecture seule.** C'est le seul appel autorisé avant le feu vert :

```bash
export ACCES=~/.config/boutique-woocommerce/<slug>
SKILL/scripts/envoyer.sh 'return c2woo_etat_des_lieux();'
# → {"site":"https://…/","nom":"…","wordpress":"7.1.2","langue":"en_US","theme":"twentytwentyfive","woocommerce":null,
#    "extensions":{"novamira":"1.4.0"},"pages":2,"articles":1,"produits":0,"commandes":0,"site_vierge":true}
```

**b) Le feu vert — un arrêt obligatoire, sur tout site, vierge ou non.** Rien n'est écrit chez le client tant qu'il n'a pas répondu « oui » à ce message. La validation de la maquette ne vaut pas feu vert : elle portait sur le design, celui-ci porte sur son site. Le message, court, rempli avec l'état des lieux :

> Tout est prêt et joué à blanc dans l'atelier. Je peux maintenant installer la boutique sur **<adresse du site>** :
> - site passé en français, WooCommerce et Contact Form 7 installés ;
> - thème « <Marque> » activé (il remplace « <thème actuel> ») ;
> - réglages de vente, catalogue de démonstration, pages et pages légales à compléter ;
> - **tout le site reste invisible du public** (« Bientôt disponible ») : la mise en ligne restera ta décision ;
> - à la fin, je referme l'accès que tu m'as donné (mot de passe d'application révoqué).
>
> Je lance l'installation ?

Si `site_vierge` est `false`, ajouter avant la question, chiffres à l'appui :

> Ton site n'est pas vierge : <n> pages, <n> produits, <n> commandes. Je fais d'abord une sauvegarde ; tes produits et commandes ne sont pas touchés, et le catalogue de démonstration n'est pas importé. En revanche, le thème change et l'accueil devient la page de la boutique.

Seul un accord explicite (« oui », « vas-y », « go », « lance ») vaut feu vert. Une question, une demande de changement ou un silence : aucune écriture — répondre, corriger dans l'atelier, refaire la répétition générale si besoin, puis redemander. Le feu vert couvre **ce déploiement-ci** jusqu'à la clôture (opérations 1 à 8, recette, commande de test). Il ne couvre pas, et chacun se redemande à part : la suppression de templates personnalisés en base lors d'un redéploiement, `forcer_catalogue` sur un site qui a déjà des produits, la mise en ligne.

`c2woo_preparer()` et `c2woo_deployer()` refusent d'écrire en production sans leur dernier argument à `true` : ne le passer qu'après ce « oui ».

**c) Préparation**, **avant tout le reste** — le français d'abord, WooCommerce et les extensions ensuite, aux versions de l'atelier :

```bash
SKILL/scripts/envoyer.sh 'return c2woo_preparer(["extensions" => ["contact-form-7"]], "11.1.2", true);'
# → {"langue":"fr_FR","woocommerce":"actif 11.1.2","contact-form-7":"actif 6.1.7","wordpress":"7.1.2"}
```

Si les versions diffèrent de l'atelier (WordPress surtout), relancer l'atelier avec `WC_VERSION`/`WP_VERSION` alignés et repasser la porte 2 : l'atelier et la production doivent servir le même DOM.

### 1 · Empaqueter et téléverser le thème

```bash
cd PROJET/theme && zip -rq /tmp/<slug>.zip <slug> -x '*.DS_Store' '*/menu-mobile-test.js'
SKILL/scripts/televerser.sh /tmp/<slug>.zip wp-content/uploads/theme.zip
```

L'archive contient un dossier portant **exactement le slug**.

### 2 · Décompresser et activer

```bash
SKILL/scripts/envoyer.sh 'require_once ABSPATH . "wp-admin/includes/file.php"; WP_Filesystem();
foreach (["patterns","templates","parts"] as $d) { foreach ((array) glob(get_theme_root() . "/<slug>/$d/*") as $f) { @unlink($f); } }
$r = unzip_file(ABSPATH . "wp-content/uploads/theme.zip", get_theme_root()); if (is_wp_error($r)) { return $r->get_error_message(); }
wp_clean_themes_cache(); switch_theme("<slug>");
return ["theme" => get_stylesheet(), "version" => wp_get_theme()->get("Version"), "en_base" => c2woo_templates_en_base("<slug>")];'
```

Les dossiers versionnés sont vidés d'abord : d'anciens templates ou compositions survivraient. Si `en_base` liste des templates **au premier déploiement**, ce sont des restes : les supprimer (`c2woo_templates_en_base('<slug>', true)`). Lors d'un redéploiement, ce sont peut-être les retouches du client : **demander avant de supprimer**.

### 3 · Téléverser le paquet

```bash
python3 SKILL/scripts/paquet.py PROJET/deploiement.json > /tmp/paquet.json
cmp -s /tmp/paquet.json PROJET/.atelier/paquet.json && echo "identique à la répétition générale"
SKILL/scripts/televerser.sh /tmp/paquet.json wp-content/uploads/paquet.json
```

Le paquet envoyé doit être **identique à l'octet** à celui de la répétition générale.

### 4 · Déployer

```bash
SKILL/scripts/envoyer.sh 'return c2woo_deployer(json_decode(file_get_contents(ABSPATH . "wp-content/uploads/paquet.json"), true), "production", true);'
```

Dans l'ordre : sauvegarde (dans `wp-content/c2woo-sauvegardes/`, fermé au web), médias (placeholders, photos, logo → Logo du site, favicon → Icône du site), réglages (nom et slogan du site, formats français, devise, TVA, livraison — gratuité en tête —, paiement de test, vignettes, catalogue 3 × 4, placeholder produit, permaliens et adresses en français, **« Bientôt disponible » sur tout le site**), catalogue, formulaires Contact Form 7, pages, accueil statique, purge. Lire les **avertissements** du rapport avant toute autre chose ; le seul attendu : « Mode Bientôt disponible actif ». Une trentaine de secondes pour une vingtaine de médias.

### 5 · Accueil et permaliens

Faits par l'étape 4 (`c2woo_accueil`). Vérifier que la page d'accueil est bien déclarée (`accueil.page_on_front` dans le rapport).

### 6 · Purger, vérifier ce qui est réellement servi

```bash
SKILL/scripts/envoyer.sh 'do_action("litespeed_purge_all"); return c2woo_purger();'
curl -s "https://SITE/wp-content/themes/<slug>/style.css?frais=1" | cmp -s - PROJET/theme/<slug>/style.css && echo "style.css identique"
for u in panier commande mon-compte; do curl -sI "https://SITE/$u/" | grep -i "x-litespeed-cache-control\|cache-control"; done   # no-cache attendu
for u in "" boutique/ contact/; do curl -s "https://SITE/$u?frais=2" | grep -o "coming-soon" | head -1; done                # coming-soon partout
```

Puis, **sur le site, connecté** (lien de `lien-admin.sh`), `scripts/menu-mobile.js` à 390 × 844 et 768 × 1024, sur l'accueil et la boutique : l'hébergeur ajoute ses propres couches (barre d'admin, widget d'assistance, bannière) qui n'existent pas dans l'atelier. `ok: true` attendu avant de passer à la recette. Constaté sur un premier déploiement : liens collés à gauche et croix sous la barre d'admin — le menu ne se fermait plus.

Après **chaque** modification ultérieure de `style.css` ou `theme.json` : incrémenter `Version:` dans `style.css`, purger (LiteSpeed + `c2woo_purger()`), vérifier par `curl`, contrôler avec `?frais=<n>` — jamais `?m=`, `?p=`, `?s=`, `?cat=`, `?tag=`, ni `?add-to-cart=`.

### 7 · Nettoyer

```bash
SKILL/scripts/envoyer.sh 'foreach (["theme.zip","paquet.json"] as $f) { @unlink(WP_CONTENT_DIR . "/uploads/" . $f); } return "ok";'
```

`theme.zip` et `paquet.json` sont servis publiquement tant qu'ils sont dans `uploads/`.

### 8 · État de la boutique

Tout le site reste en mode « Bientôt disponible » (`mise_en_ligne: false`) : déconnecté, chaque page — accueil compris — montre la page d'attente ; connecté en administrateur, on voit le site. Le lever est une **mise en ligne** : sur l'accord explicite du client, après la recette, en rejouant `c2woo_reglages()` avec `mise_en_ligne: true`, ou depuis WooCommerce → Réglages → Visibilité du site.

---

## Phase 6 — Recette

Chaque porte se passe **dans l'atelier en phase 4**, puis **sur le site du client** ici.

### Porte 1 · Intégrité des blocs, côté serveur

```bash
SKILL/scripts/envoyer.sh 'return c2woo_porte1();'                     # production
SKILL/scripts/atelier-php.sh PROJET 'return c2woo_porte1();'          # atelier
```

Pour chaque page publiée et chaque template ou partie du thème, compositions résolues : `html_brut` = 0, `legacy` = 0, `hors_liste` = []. `shortcode` = 1 seulement sur Mon compte et la page contact.

### Porte 2 · Validation dans l'éditeur

Ouvrir une session : le lien de l'atelier, ou `SKILL/scripts/lien-admin.sh` en production (URL ouverte **dans les 60 s**).

**a) Tous les fichiers, en une fois.** Ouvrir `wp-admin/post-new.php?post_type=page`, puis dans la console :

```js
const aplatir = bs => bs.reduce((a, b) => a.concat([b], aplatir(b.innerBlocks || [])), []);
const theme = (await wp.apiFetch({ path: '/wp/v2/themes?status=active' }))[0].stylesheet;
const modeles = [ ...(await wp.apiFetch({ path: '/wp/v2/templates?context=edit&per_page=100' })),
                  ...(await wp.apiFetch({ path: '/wp/v2/template-parts?context=edit&per_page=100' })) ].filter(t => t.theme === theme);
const compositions = Object.fromEntries((await wp.apiFetch({ path: '/wp/v2/block-patterns/patterns' })).map(p => [p.name, p.content]));
const resoudre = bs => bs.flatMap(b => b.name === 'core/pattern' && compositions[b.attributes.slug]
  ? resoudre(wp.blocks.parse(compositions[b.attributes.slug])) : [{ ...b, innerBlocks: resoudre(b.innerBlocks || []) }]);
const pages = await wp.apiFetch({ path: '/wp/v2/pages?context=edit&per_page=100&status=publish,draft' });
const bilan = html => { const bs = aplatir(resoudre(wp.blocks.parse(html)));
  const inv = bs.filter(b => b.isValid === false);
  const images = bs.filter(b => b.name === 'core/image');
  return (inv.length ? 'INVALIDES ' + [...new Set(inv.map(b => b.name))].join(',') : 'ok') + ' · ' + bs.length + ' blocs'
    + (images.length ? ' · images liées ' + images.filter(b => b.attributes.id).length + '/' + images.length : '')
    + (bs.some(b => b.name === 'core/pattern') ? ' · COMPOSITION NON RÉSOLUE' : ''); };
const rapport = {};
for (const t of modeles) rapport[(t.type === 'wp_template' ? 'modele:' : 'partie:') + t.slug + (t.source === 'custom' ? ' (en base)' : '')] = bilan(t.content.raw);
for (const p of pages) rapport['page:' + p.slug] = bilan(p.content.raw);
JSON.stringify({ ko: Object.entries(rapport).filter(([k, v]) => !v.startsWith('ok') || v.includes('NON R')), n: Object.keys(rapport).length });
```

Réception : `ko` vide, `images liées n/n`. Un bloc invalide se diagnostique ainsi :

```js
const b = aplatir(wp.blocks.parse(html)).find(x => x.isValid === false);
({ attendu: wp.blocks.getSaveContent(b.name, b.attributes, b.innerBlocks).slice(0, 400), trouve: b.originalContent.slice(0, 400) });
```

(Vécu : une balise `</div>` perdue en réécrivant le panier vide — « Expected EndTag div, instead saw end of content ».)

**b) L'ouverture réelle.** L'accueil (`post.php?post=ID&action=edit`) et la fiche produit dans l'éditeur de site (`site-editor.php?p=%2Fwp_template%2F<slug>%2F%2Fsingle-product&canvas=edit`), attendre les blocs (20 s), puis :

```js
const aplatir = bs => bs.reduce((a, b) => a.concat([b], aplatir(b.innerBlocks || [])), []);
const tous = aplatir(wp.data.select('core/block-editor').getBlocks());
({ total: tous.length, invalides: tous.filter(b => b.isValid === false).map(b => b.name), imagesSansId: tous.filter(b => b.name === 'core/image' && !b.attributes.id).length })
```

`total` non nul (sinon l'éditeur n'a pas fini de charger — réessayer par appels de moins de 40 s), `invalides` vide.

### Porte 3 · Rendu et responsive — déconnecté

Dans l'atelier (en production, le site est encore masqué ; la porte se repasse après la mise en ligne). Se déconnecter, puis à 390 × 844, sur chaque page (accueil, pages vitrine, boutique, une catégorie, une archive d'attribut, recherche, fiches simple/variable/promo/rupture, panier vide et rempli, commande, Mon compte, une page légale, une 404) :

```js
await new Promise(r => setTimeout(r, 1500)); await document.fonts.ready;
const urls = [...new Set([...document.images].map(i => i.currentSrc || i.src).filter(Boolean))];
const st = await Promise.all(urls.map(u => fetch(u, { method: 'HEAD' }).then(r => r.status).catch(() => 0)));
JSON.stringify({ page: location.pathname, sw: document.documentElement.scrollWidth, ko: urls.filter((u, i) => st[i] !== 200),
  corps: getComputedStyle(document.body).fontFamily, titres: getComputedStyle(document.querySelector('h1,h2')).fontFamily });
```

Réception : `sw` ≤ 390, `ko` vide, les deux familles de la charte. Le formulaire de contact : envoi inactif tant que la case de consentement n'est pas cochée, puis erreurs lisibles sur les champs vides.

**Le menu mobile** — c'est la panne qu'un client voit en premier : `scripts/menu-mobile.js` (mode d'emploi et liste des contrôles en phase 3b), à **390 × 844, 768 × 1024 et 1440 × 900**, **déconnecté et connecté** (la barre d'admin se superpose au haut du panneau), sur l'accueil, la boutique et le panier.

Se déconnecter dans l'atelier : `/wp-login.php?action=logout`, puis le lien « vous déconnecter » ; se reconnecter par le lien à jeton de l'atelier. En production, déconnecté, le site montre la page d'attente tant qu'il est « Bientôt disponible » : le passage déconnecté se fait dans l'atelier, puis sur le site **à la mise en ligne**.

Réception : `ok: true` partout, `defauts` vide. Le script ne remplace pas un regard : une **capture du panneau ouvert** à 390 px, jointe au compte rendu, confirme que le menu est lisible et à la charte (police des titres, couleurs, traits).

### Porte 4 · Fidélité au pixel

La mesure de la phase 4, section par section, maquette contre site (pages vitrine) et atelier contre site (pages marchandes), 1440 × 900 et 390 × 844. Réception : chaque section à ±2 px en position et en hauteur, la fin du pied de page à ±2 px. En production, connecté : retrancher la barre d'admin.

### Porte 5 · Fidélité de l'éditeur — pages ET modèles

Le client ne voit pas que l'éditeur de pages : il clique sur **« Modifier le site »**, ouvre le modèle « Fiche produit » ou « Catalogue ». Vécu : pages parfaites, mais fiche produit en vrac dans l'éditeur de site (grille des produits associés dans une colonne, titres lettre par lettre, boutons ronds, galerie de vignettes géantes, « Termes introuvables ») — voir `pieges.md`. La porte se passe donc sur **six vues**, dans l'atelier puis sur le site :

| Vue | Adresse |
|---|---|
| « Modifier le site » (l'accueil) | `site-editor.php?canvas=edit` |
| Accueil (page) et une page vitrine | `post.php?post=<ID>&action=edit` |
| Fiche produit | `site-editor.php?p=%2Fwp_template%2F<slug>%2F%2Fsingle-product&canvas=edit` |
| Catalogue | `…%2F%2Farchive-product&canvas=edit` |
| Confirmation | `…%2F%2Forder-confirmation&canvas=edit` |
| Panier (page) | `post.php?post=<ID du panier>&action=edit` |

Pour chacune, fermer la fenêtre de bienvenue (Échap), attendre les blocs (20 s), puis dans la console :

```js
const doc = document.querySelector('iframe[name="editor-canvas"]').contentDocument;
const d = [], r = e => e.getBoundingClientRect();
const m = s => { const e = doc.querySelector(s); if (!e) return null; return { pos: getComputedStyle(e).position, l: Math.round(r(e).width), h: Math.round(r(e).height) }; };
// textes écrasés : un titre ou un bouton plus haut que trois lignes et plus étroit que 120 px
// (les éléments masqués — largeur 0, titres réservés aux lecteurs d'écran — sont ignorés)
doc.querySelectorAll('h1,h2,h3,.wp-block-post-title,.wp-block-button__link,.wc-block-components-product-button__button').forEach(e => {
  const lh = parseFloat(getComputedStyle(e).lineHeight) || 20;
  if (r(e).width > 10 && r(e).width < 120 && r(e).height > 3 * lh) d.push('écrasé : « ' + e.textContent.trim().slice(0, 24) + ' » ' + Math.round(r(e).width) + ' px'); });
doc.querySelectorAll('.wc-block-product').forEach(e => { if (r(e).width > 10 && r(e).width < 150) d.push('carte produit de ' + Math.round(r(e).width) + ' px'); });
doc.querySelectorAll('.wc-block-product-gallery-thumbnails').forEach(e => { if (r(e).width > 140) d.push('colonne de vignettes de ' + Math.round(r(e).width) + ' px'); });
if (/Termes introuvables/.test(doc.body.textContent)) d.push('« Termes introuvables » (attribut non exposé à REST)');
({ defauts: d, canvas: doc.documentElement.clientWidth, entete: m('.entete'), hero: m('.hero-photo'), badge: m('.wc-block-components-product-sale-badge'), bouton: m('.wp-block-button') })
```

Réception : `defauts` vide ; les couches sorties du flux le sont restées (photo du héros, badges en `absolute`) ; rien de collant ; chaque variante visible et sélectionnable ; **une capture de chaque vue** regardée à côté du site — la même page, pas une approximation. Sinon : `assets/editor.css` (sections 1 à 7 du squelette) et `pieges.md`.

### Porte 6 · Parcours d'achat

Dans l'atelier d'abord, sur ordinateur puis à 390 px :

1. Accueil → une catégorie (ses produits seulement) → un filtre (prix ou attribut) → le retirer.
2. Fiche d'un produit simple → « Ajouter au panier » → le tiroir du mini-panier s'ouvre, compteur à jour.
3. Fiche du produit à variations → bouton inactif tant qu'aucune pastille n'est choisie → variation en rupture signalée → choisir → le prix suit → ajouter.
4. Panier : quantité (total recalculé), code promo invalide (message lisible), suppression ; au-dessus du seuil, livraison offerte présélectionnée.
5. Commande : valider vide (erreurs sur chaque champ), remplir, une seule option de livraison au-dessus du seuil, virement, case CGV → « Commander avec obligation de paiement ».
6. Confirmation : numéro, total TTC, TVA incluse, adresses.
7. Mon compte : la commande apparaît.

Puis **sur le site du client**, connecté par `lien-admin.sh` : une seule commande de recette, par virement, avec une adresse **`@example.com`** (domaine réservé, rien n'est livré ; l'e-mail « Nouvelle commande » part vers l'administrateur du site). Numéro relevé, puis :

```bash
SKILL/scripts/envoyer.sh '$o = wc_get_order(<id>); if (!$o || $o->get_billing_email() !== "test@example.com") { return "pas une commande de test"; }
return [c2woo_annuler_commande_test(<id>), (int) wc_get_orders(["limit" => 1, "return" => "ids", "paginate" => true, "status" => array_keys(wc_get_order_statuses())])->total];'
```

Annulée d'abord (le stock revient), supprimée ensuite ; 0 commande restante.

### Porte 7 · Conformité de base

```bash
SKILL/scripts/envoyer.sh 'return c2woo_porte7();'
```

Réception : `case_cgv_a_cocher` à `ok`, bouton de commande `(ok)`, `prix_ttc` à `ok`. `cgv`, `confidentialite`, `mentions_legales` restent à « trame non complétée » tant que le client ne les a pas remplies : la porte 7 bloque la **mise en ligne**, pas la livraison — le dire dans le compte rendu.

---

## Phase 7 — Clôture de l'accès

La recette passée, l'accès au site ne sert plus. Il se referme **avant** d'écrire le compte rendu de fin, dans cet ordre — la révocation en dernier côté site, puisqu'après elle le connecteur ne répond plus :

1. **Fichiers temporaires** : l'étape 7 de la phase 5 est faite (`theme.zip` et `paquet.json` retirés de `uploads/`) — sinon la refaire maintenant.
2. **Session d'administration du navigateur** : ouvrir `https://SITE/wp-login.php?action=logout` et confirmer la déconnexion. La session ouverte par `lien-admin.sh` est détruite ; celles du client ne sont pas touchées.
3. **Révoquer le mot de passe d'application** du connecteur — **le tout dernier appel au site** :

```bash
SKILL/scripts/envoyer.sh 'return c2woo_revoquer_acces();'
# → {"revoque":true,"nom":"<nom du mot de passe>","utilisateur":"<identifiant>"}
```

   Il ne révoque que le mot de passe avec lequel cet appel s'authentifie — ni les autres mots de passe d'application, ni l'extension Novamira. `revoque: false` : le compte rendu demande au client de le révoquer lui-même (Utilisateurs → Profil → Mots de passe d'application) et dit pourquoi.

4. **Effacer les identifiants locaux** :

```bash
python3 SKILL/scripts/connecteur.py --oublier <slug>
# → supprime ~/.config/boutique-woocommerce/<slug>/ (acces.env, bundle, session MCP)
```

   Le `.mcpb` reçu au brief n'est pas supprimé (c'est un fichier de l'utilisateur) : le script en rappelle le chemin, et le compte rendu le signale.

Après la clôture, toute nouvelle intervention sur le site (mise en ligne faite par l'agent, retouche) demande **un nouveau connecteur** : le client le télécharge depuis Novamira en une minute. C'est voulu.

Si la conversation doit s'arrêter avant la fin de la recette (blocage, attente du client), la clôture se fait quand même — sauf si le client demande expressément de garder l'accès ouvert, ce que le compte rendu mentionne alors.

---

## Le compte rendu de fin

En français, court, dans cet ordre :

1. **Le site** : son adresse, son état (« Bientôt disponible » sur tout le site ; visible en étant connecté à WordPress).
2. **Ce qui a été fait et vérifié** : les pages — **avec leurs captures** en ordinateur et à 390 px, puisqu'elles n'ont pas été soumises une à une —, les portes passées, **le menu mobile contrôlé sur le site** (390, 768 et 1440 px, capture du panneau ouvert), la commande de recette annulée et supprimée.
3. **Ce que le client fait avant la mise en ligne** :
   - les photos de chaque produit (placeholder partout pour l'instant) ;
   - les trois trames légales et la page livraison et retours ;
   - les coordonnées bancaires du virement (WooCommerce → Réglages → Paiements), ou le branchement de Stripe/PayPal ;
   - les contenus provisoires à confirmer (réseaux sociaux, textes de présentation, adresse) ;
   - la réception des e-mails du formulaire de contact (envoi SMTP recommandé).
4. **Ce qui n'a pas été repris de la référence**, et pourquoi (règle de sobriété).
5. **La mise en ligne** : sa décision, en un geste (WooCommerce → Réglages → Visibilité du site) ; ou par l'agent, sur sa demande, avec un nouveau connecteur.
6. **L'accès refermé** (phase 7) : le mot de passe d'application du connecteur est révoqué (ou, si `revoque: false`, à révoquer par le client : Utilisateurs → Profil → Mots de passe d'application), les identifiants sont effacés de la machine de l'agent, et le `.mcpb` reçu peut être supprimé de son côté. L'extension Novamira peut être désactivée si elle ne sert à rien d'autre.

---

## Hygiène de mesure

Artefacts de test qui ont fait diagnostiquer des pannes imaginaires :

- **`scroll-behavior: smooth`** anime les `scrollTo()` programmés : captures noires, mesures en plein défilement. Forcer `scrollBehavior = 'auto'` et `behavior: 'instant'`.
- **JPEG non décodés, fondus d'entrée** : une photo peut paraître absente pendant ~1 s (transition d'opacité) ou ~5 s (décodage). Vérifier `img.complete && img.naturalWidth > 0` et reprendre la capture.
- **Paramètres réservés** : `?m=`, `?p=`, `?s=`, `?cat=`, `?tag=`, `?add-to-cart=`, `?orderby=`, `?product_cat=`. Casse-cache : `?frais=<n>`.
- **Volet du navigateur masqué** (`document.visibilityState === 'hidden'`) : le navigateur ne repeint plus après un défilement — captures blanches. Remèdes, du plus simple au plus sûr : un redimensionnement d'un pixel (`resize_window`) ; **masquer en JS les sections au-dessus** de celle à capturer puis revenir en haut de page (`document.querySelector('.fiche').style.display = 'none'; scrollTo(0, 0)`) ; pour une vue d'ensemble, une fenêtre aussi haute que la page (1440 × hauteur du `body`) — la capture sort réduite mais complète.
- **`scrollHeight` gonflé** par le tiroir du mini-panier (fixe, haut comme la fenêtre) : mesurer la fin du pied de page.
- **Barre d'admin** : connecté, tout est décalé de 32 px (46 px sous 782 px de large).
- **Mobile** : un viewport de 390 px peut s'élargir tout seul si un élément déborde (`innerWidth` ≠ 390) — c'est le symptôme à chercher, pas un réglage.
