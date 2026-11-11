# Pièges recensés

Pannes rencontrées en conditions réelles — sur des sites vitrines pour la méthode d'origine, dans l'atelier WooCommerce 11.1.2 / WordPress 7.1 et sur une boutique déployée chez Hostinger pour les pièges marchands — avec leur symptôme exact et leur correctif.

Quatre sont à part : ce sont les seules qu'aucune porte de la phase 6 ne détecte. Les deux premières sont jumelles (la même section du skill produit l'une quand on l'ignore, l'autre quand on l'applique à la lettre en présence d'une référence) ; la troisième est l'excès inverse de la première — la référence recopiée au lieu d'être suivie ; la quatrième est leur équivalent sur les pages marchandes.

---

## « Ça n'a rien à voir avec ma référence »

*Règle 0′ — le registre appartient à la référence (regles.md).*

**Symptôme.** Le client, qui a fourni une image, ne reconnaît rien. Il ne dit pas « c'est moche » : il dit que ça n'a **aucun rapport** avec ce qu'il a envoyé. À l'inventaire, l'ordre des sections est pourtant respecté presque à l'identique, et souvent la couleur dominante aussi. Ce sont les seules choses qui ont survécu.

**Cause.** Les six exigences de parti pris ont été lues comme des consignes à appliquer à la page, alors qu'une référence était présente. Chacune pousse dans la même direction — ajouter une dominante, une couleur forte, de la profondeur, une typographie de caractère, un concept — et rien dans le skill ne tirait en sens inverse. Quand la référence est un gabarit calme, dense, centré, sans accent et sans dominante, le manifeste et la référence se contredisent point par point. L'agent tranche alors en faveur du manifeste, sans jamais énoncer la contradiction : le manifeste est du texte dans le contexte et se lit comme une instruction, la référence est une image et se lit comme une donnée. Le texte gagne.

Une version antérieure de la méthode aggravait la panne en exigeant « deux ou trois écarts volontaires » : un quota de déviation, qui mettait sur le même plan une section retirée et un changement de classe typographique.

**Diagnostic.** Un seul test, et il est brutal : **rouvrir l'image de référence et la capture dans le même message**, puis reprendre les sept invariants de registre un par un. Tant que la comparaison se fait de mémoire ou sur une description, la panne reste invisible — on se souvient de l'ordre des sections, jamais de la surface. Deuxième tell, en amont : le plan de phase 1 contenait un argumentaire de plusieurs paragraphes expliquant pourquoi on s'éloignait de la référence.

**Correctif.** Reprendre la surface, pas la composition — c'est l'inverse exact de la panne suivante. Classe typographique, système de couleur, température du fond, découpe de la page, géométrie, alignement, densité : les sept se réalignent sur la référence. Ce qui semble ne pas convenir à la boutique se reproduit quand même et se signale en une ligne avec la maquette — jamais une question posée avant de coder. Puis passer la porte de registre avant tout nouvel envoi.

**Attention.** Cette panne ne vient pas d'un oubli mais d'un goût, et le goût revient à chaque projet en se présentant comme du jugement professionnel. Le résultat est en général *plus joli* que le centre statistique — c'est pour ça qu'il franchit toutes les relectures internes. Seule la comparaison côte à côte l'arrête.

---

## Maquette plate, validée sans enthousiasme

**Symptôme.** La page est propre, adaptative, cohérente — et ne provoque aucune réaction. Le client valide « c'est bien » sans rien demander. Les cinq portes de la phase 6 passent toutes. C'est précisément ce qui rend cette panne coûteuse : elle est la seule que le protocole de vérification ne voit pas.

**Cause.** Le design a été dessiné avec les contraintes de la phase 4 déjà appliquées. À chaque décision de composition, la question « comment est-ce que ça passera en blocs natifs ? » a écarté une idée : la superposition, le débordement, l'objet central, le mouvement, le champ de couleur dominant. Aucun de ces renoncements n'est visible isolément ; leur somme est une page sans intention.

**Diagnostic.** Trois questions, posées sur la capture et non sur l'intention :

- Peut-on nommer en un mot ce qui porte la page ? Si non, il n'y a pas de dominante.
- Sur toute la page, deux sections voisines partagent-elles exactement le même gabarit — largeur, marge, alignement ? Si oui, il n'y a pas de rythme.
- Le concept annoncé au client se lit-il sans qu'on l'explique ? S'il lui faut une légende, il n'est pas dans le design.

**Correctif.** Reprendre la composition, pas la retoucher : une maquette plate ne se sauve pas par ajustements successifs, parce que le défaut est dans la structure des décisions, pas dans leur exécution. Puis respecter l'ordre : dessiner librement, convertir ensuite. Règle 0, et phase 2.

**Attention.** Le réflexe revient à chaque projet, parce que l'agent connaît déjà les contraintes de la phase 4 en écrivant sa première ligne de CSS. C'est un piège de méthode, pas une erreur ponctuelle — il se reprend à chaque fois, pas une fois pour toutes.

**Ne pas la corriger en sortant du registre.** Le correctif est « reprendre la composition » — l'échelle, le rythme, les débordements, la hiérarchie. Pas « ajouter une police à caractère et une couleur d'accent » : ça soigne la platitude en provoquant la panne précédente. Si la référence est elle-même un gabarit calme et uniforme, la page doit l'être, et il n'y a pas de platitude à corriger.

---

## « C'est le site de la référence »

*S'inspirer fortement, jamais décalquer (regles.md).*

**Symptôme.** Le client reconnaît sa référence au premier coup d'œil — trop bien. Même suite de sections, même héros avec la même carte posée dans le même angle, mêmes trois cartes sombres, même bandeau, même nom géant dans le pied de page, et des titres qui sont ceux de la référence traduits. « Ce n'est pas mon site, c'est celui de la capture. » Au pire, le client publie la page d'une autre marque.

**Cause.** Règle 0′ et la porte de registre tirent vers la référence, et rien ne disait où s'arrêter : recopier la page entière est le chemin le plus court vers sept verdicts « conforme ». Le plan de l'accueil avait été déduit de la liste des sections de la référence, pas de la mission de la boutique.

**Diagnostic.** La porte du décalque (phases.md) : la liste des sections de la maquette face à `reference/page.png`. Même ordre ? Même disposition, section par section ? Un texte reconnaissable, même traduit ?

**Correctif.** Repartir du plan de l'accueil — la mission, le catalogue, le rôle de chaque section — puis habiller chaque section dans le registre de la référence. Un procédé se garde en changeant de place, de disposition, de proportions ou de contenu ; une section recopiée se recompose, elle ne se repeint pas. Les textes se réécrivent à partir du brief.

**Ne pas la corriger en sortant du registre.** C'est la jumelle inversée de la première panne : s'éloigner de la page de la référence ne donne aucun droit sur son registre. Après correction, la porte de registre se repasse — sept verdicts, comme avant.

---

## Un plan à valider au lieu d'une maquette

*Ce que le client voit (SKILL.md).*

**Symptôme.** Le brief envoyé, l'agent répond par un long document — relevé des sept invariants, plan de l'accueil, palette, polices, catalogue, ce qu'il ajoute, ce qu'il ne reprend pas — et conclut « Si tu valides, je lance la maquette ». Rien n'est construit ; le client doit relire et répondre.

**Cause.** Les décisions de phase 1 étaient rédigées comme une annonce (« Avant de coder, annoncer dans cet ordre… »), et une question « obligatoire » sur les invariants ouvrait la porte à une validation. L'agent a pris ses consignes de travail pour un livrable.

**Correctif.** Les huit décisions s'écrivent dans `PROJET/plan.md` et ne s'affichent pas. Au client : « Brief reçu : je construis l'accueil de <Marque>. », puis la maquette — la seule validation du design. Un invariant qui semble mal convenir se reproduit et se signale en une ligne avec la maquette. Au « go », tout s'enchaîne jusqu'à la répétition générale ; puis un seul message de feu vert avant d'écrire sur le site (phases.md, phase 5, étape 0).

---

## « Ça ressemble à WooCommerce »

*Règle 0″ — la structure est à WooCommerce, la surface est à nous (regles.md).*

**Symptôme.** L'accueil est à la hauteur de la référence ; le catalogue, la fiche produit et le panier ressemblent à la démonstration de WooCommerce avec la palette du client. Grille de stock, boutons pleins arrondis par défaut, onglets soulignés, panier en deux colonnes standard. Toutes les portes techniques passent.

**Cause.** Les pages marchandes ont été traitées comme une formalité de branchement : jetons de couleur posés, polices chargées, terminé. La liberté que laisse WooCommerce — composition des templates, sections ajoutées en blocs natifs, surface de chaque composant, états — n'a pas été prise.

**Diagnostic.** Mettre côte à côte la capture de l'accueil validé et celle de la fiche produit : la fiche doit paraître dessinée par la même main. Relever la carte produit sur l'accueil et dans le catalogue : elles doivent être identiques au pixel. Chercher un composant dans son habit d'origine (badge « Promo ! », champ à bordure grise, onglet violet).

**Correctif.** Reprendre la composition des templates (ordre, regroupements, largeurs, sections d'accompagnement), puis la surface composant par composant, sur les classes de `blocs-woocommerce.md`. Jamais en recopiant le DOM dans une maquette : dans l'atelier, sur le DOM réel.

---

## Une couleur, une marge ou un arrondi sortis de nulle part

*La page, pas sa présentation (regles.md, règle 0′).*

**Symptôme.** La maquette porte un brun, un gris ou un bleu que le client n'a jamais vu sur son site de référence — pourtant « prélevé » sur l'image ; ou des sections encartées avec une marge autour alors que la référence est bord à bord ; ou des coins arrondis et une ombre sur toute la page ; ou une page légèrement inclinée.

**Cause.** La référence venait de Pinterest, Dribbble, Behance ou d'une galerie de sites, où chaque capture est mise en scène : liseré ou fond uni autour de la page, coins arrondis, ombre, appareil, collage d'écrans en perspective. Le relevé a été fait sur l'image entière : le cadre a été lu comme le fond (invariant 3), une couleur de la charte (2), une découpe encartée (4), un rayon ou une rotation (5).

**Diagnostic.** Rouvrir l'originale : la bande est-elle de même couleur sur les quatre côtés ? Les sections pleine largeur de la page (héros, bandes sombres, pied) s'arrêtent-elles net à son bord ? Si oui, c'est un cadre.

**Correctif.** `scripts/reference.py` dès réception de l'image ; tout le relevé, toutes les portes de registre et du décalque sur `reference/page.png`. Une bande claire proche du fond (le script ne la retire pas d'office) se tranche à l'œil : cadre → `--forcer` ; vraies marges → invariant 4. Un collage ou un appareil : relever sur l'écran le plus lisible, ignorer perspective, ombres et fond commun. Au moindre doute, l'élément est de la présentation.

---

## « Bientôt disponible » : trop, ou pas assez

**Symptôme.** Dans l'atelier, déconnecté, chaque page de la boutique montre « Bientôt disponible ». En production, l'inverse : la boutique est masquée, mais **l'accueil, À propos et Contact sont publics** — avec leurs sections produits.

**Cause.** WooCommerce ≥ 9.1 crée `woocommerce_coming_soon = yes` à l'installation, **et** `woocommerce_store_pages_only = yes` : seules ses propres pages sont masquées.

**Correctif.** L'atelier lève le mode. En production, tant que `mise_en_ligne` est à `false`, `boutique.php` pose les deux options (`coming_soon = yes`, `store_pages_only = no`) : **tout le site** attend. Contrôle déconnecté : `curl -s https://SITE/?frais=1 | grep -o coming-soon` sur l'accueil et une page vitrine. La porte 3 déconnectée se passe dans l'atelier ; la mise en ligne se décide avec le client.

---

## L'ancien rendu PHP à la place des blocs

**Symptôme.** Le catalogue ou la fiche produit s'affiche, mais le template ne contient qu'un bloc « Modèle WooCommerce classique » : rien de modifiable, aucune classe du thème.

**Cause.** Le thème ne fournit pas le template ; WooCommerce sert le sien, en `woocommerce/legacy-template` quand l'option `wc_blocks_use_blockified_product_grid_block_as_template` vaut `no` — typiquement quand WooCommerce a été installé sous un thème classique.

**Correctif.** Le thème fournit `archive-product.html`, `product-search-results.html`, `single-product.html`, `page-cart.html`, `page-checkout.html`, `order-confirmation.html`. La porte 1 compte les `legacy` : 0.

---

## Blocs invalides dans un template « officiel »

**Symptôme.** « Ce bloc contient du contenu inattendu ou non valide » sur le prix et la note d'un template pourtant copié depuis WooCommerce.

**Cause.** Constaté : les templates de la branche principale de WooCommerce contiennent `<!-- wp:woocommerce/product-price {"fontSize":"large"} /-->` ; dans l'éditeur 11.1, sans l'attribut `isDescendentOfSingleProductTemplate`, la fonction `save()` attend `<div class="is-loading"></div>`. Le balisage d'une autre version, ou écrit à la main, ne correspond pas.

**Correctif.** Générer le balisage dans l'éditeur de l'atelier, dans le bon contexte (éditeur de site pour la fiche produit), puis le valider (porte 2). Procédure dans `blocs-woocommerce.md`.

---

## Une nouvelle composition reste invisible

**Symptôme.** Un fichier ajouté à `patterns/` n'apparaît nulle part ; le template qui l'appelle rend un vide. `WP_Block_Patterns_Registry` ne la connaît pas.

**Cause.** WordPress ≥ 6.4 met en cache la liste des fichiers de compositions du thème (transitoire par thème). Un fichier ajouté après coup n'est pas relu.

**Correctif.** Atelier : `WP_DEVELOPMENT_MODE=theme` (posé par `atelier.sh`) désactive ce cache. Production : `c2woo_purger()` après chaque déploiement — elle appelle `wp_get_theme()->delete_pattern_cache()`.

---

## « Mon compte » en désordre

**Symptôme.** Le menu de Mon compte s'affiche en deuxième colonne, le contenu tombe dessous sur 150 px de large ; déconnecté, le formulaire de connexion est décalé à droite.

**Cause.** La feuille classique de WooCommerce pose des clearfix (`::before`/`::after` en `display: table`) sur `.woocommerce` et `.col2-set`, plus des flottants et des largeurs en %. Dans une grille, les pseudo-éléments deviennent des cellules.

**Correctif.** Les neutraliser — `content: none` — avec une spécificité **supérieure** à celle de WooCommerce (`.woocommerce-account .woocommerce .u-columns::before`) : à spécificité égale, sa feuille gagne. `float: none` et `width: auto` sur les colonnes. C'est dans le squelette.

---

## Pages, adresses et textes en anglais

**Symptôme.** `/shop/`, `/cart/`, `/product/…` au lieu de `/boutique/`, `/panier/`, `/produit/…` ; le panier par défaut affiche « Your cart is currently empty! » ; les textes de confidentialité des formulaires sont en anglais ; les dates s'écrivent « septembre 24, 2026 ».

**Cause.** WooCommerce a été activé **avant** le passage du site en français : ses pages, ses bases d'URL et ses options de texte sont nés en anglais, et le format de date est resté celui d'une installation anglaise.

**Correctif.** `boutique.php` renomme les pages WooCommerce (slugs de `pages_woocommerce`), fixe les bases d'URL (`reglages.permaliens`), remplace les textes de confidentialité, pose `date_format`, `time_format`, `start_of_week` et le fuseau. `contenus/panier.html` remplace le contenu par défaut du panier. En production, installer la langue française avant WooCommerce quand c'est possible.

---

## Frais de port faux de 20 %

**Symptôme.** Le forfait annoncé à 5,90 € s'affiche 7,08 € dans le panier.

**Cause.** WooCommerce saisit les frais de livraison **hors taxe**, même quand les prix des produits sont saisis TTC.

**Correctif.** `deploiement.json` exprime la livraison en TTC ; `boutique.php` divise par (1 + TVA) à la saisie. Vérifié dans l'atelier : 5,90 € TTC affichés, 6,31 € de TVA incluse sur un panier de 37,90 €.

---

## La section « Nouveautés » se vide après une semaine

**Symptôme.** Au lancement, la section montre quatre produits ; huit jours plus tard, plus rien — ou le panier vide ne propose plus rien.

**Cause.** La collection `new-arrivals` de WooCommerce est livrée avec `"timeFrame":{"operator":"in","value":"-7 days"}`.

**Correctif.** Retirer `timeFrame` de la requête à la génération du bloc. Le squelette l'a fait pour le panier vide.

---

## Les filtres du catalogue restent vides

**Symptôme.** Inséré par script dans un groupe, le bloc Filtres ne contient rien — ni prix, ni attributs.

**Cause.** Son gabarit de blocs internes ne s'applique qu'au montage du bloc dans l'éditeur ; imbriqué et hors écran, il ne se monte pas, et remplacer le bloc par une version remplie ne tient pas davantage.

**Correctif.** Générer le bloc seul, au premier niveau, le sérialiser, puis assembler le template par chaîne et le **valider** par la porte 2. C'est ainsi que `patterns/catalogue.php` a été produit.

---

## Un correctif de template « sans effet »

**Symptôme.** Le fichier `templates/…html` est corrigé, déployé, purgé — le site affiche toujours l'ancienne version.

**Cause.** Le template a été modifié dans l'éditeur de site : la version **en base** (`wp_template`) masque le fichier du thème, indéfiniment.

**Correctif.** `c2woo_templates_en_base('<slug>')` les liste. Au premier déploiement, ce sont des restes : les supprimer. Ensuite, ce sont peut-être les retouches du client : demander avant de supprimer.

---

## Les options d'un produit à variations dans le désordre

**Symptôme.** « Grande, Moyenne, Petite » au lieu de « Petite, Moyenne, Grande ».

**Cause.** Les termes d'un attribut global sont triés par leur méta `order` ; sans elle, par nom.

**Correctif.** `boutique.php` pose `order` dans l'ordre du catalogue.

---

## L'éditeur de site ne finit jamais de charger

**Symptôme.** Dans l'atelier, `site-editor.php` reste blanc une minute ou plus ; le store de l'éditeur renvoie zéro bloc.

**Cause.** Le PHP de Playground est plus lent qu'un serveur, et un onglet dont le volet est masqué voit son rendu ralenti par le navigateur.

**Correctif.** Pour valider, le validateur de fichiers de la porte 2 (éditeur d'articles, quelques secondes, indépendant de l'affichage). Pour l'ouverture réelle, garder le volet visible et interroger le store par appels courts (moins de 40 s chacun).

---

## Se connecter à l'atelier

**Symptôme.** Le navigateur arrive sur la page de connexion de l'atelier.

**Cause.** La connexion automatique de Playground ne vaut que pour le premier client ; le navigateur de l'agent n'en profite pas.

**Correctif.** Le lien de connexion **sans mot de passe** affiché par `atelier.sh` (`/atelier/connexion.php?jeton=…`). Ne jamais saisir de mot de passe, même celui de l'atelier.

---

## Stock faussé après la recette

**Symptôme.** Après la commande de recette sur le site du client, un produit affiche un stock de moins, ou « Rupture ».

**Cause.** Supprimer une commande ne rend pas le stock ; l'annuler, si.

**Correctif.** `c2woo_annuler_commande_test($id)` : annulation d'abord, suppression ensuite.

---

## Panier figé par le cache de page

**Risque.** Le compteur du mini-panier ne bouge pas, ou le panier paraît vide après un ajout, sur un hébergement avec cache de page.

**Constat au premier déploiement (Hostinger, LiteSpeed Cache 7.9).** L'intégration WooCommerce de LiteSpeed exclut bien panier, commande et Mon compte : `x-litespeed-cache-control: no-cache` sur les trois. Le contrôle reste obligatoire à chaque déploiement : `curl -sI https://SITE/panier/ | grep -i cache`.

---

## Captures blanches pendant la maquette

**Symptôme.** La première capture après un chargement est bonne ; après un défilement programmé, les suivantes sont entièrement blanches — alors que le DOM est là (`elementFromPoint` renvoie bien des éléments).

**Cause.** Le volet du navigateur est masqué (l'utilisateur a ouvert autre chose dans le panneau latéral) : `document.visibilityState === 'hidden'`, et le navigateur ne repeint plus après un défilement. Un `scroll-behavior: smooth` aggrave : le défilement vers une ancre ne progresse même pas.

**Correctif.** Forcer un rendu : une navigation, ou un redimensionnement d'un pixel (`resize_window`) après le défilement. Pour une section précise d'une page WordPress, **masquer en JS les sections au-dessus** et revenir en haut (`document.querySelector('.fiche').style.display='none'; scrollTo(0,0)`) puis redimensionner d'un pixel ; dans la maquette, un paramètre `?seul=<id>` (outil de maquette, jamais dans le thème). Pour une vue d'ensemble, une fenêtre aussi haute que la page. Ou demander à l'utilisateur de réafficher le volet. Une photo qui paraît absente juste après le chargement est souvent en plein fondu d'entrée : reprendre la capture.

---

## Une photo « Unsplash » payante dans la maquette

**Symptôme.** Une photo retenue sur Unsplash porte un filigrane, ou son URL ne se télécharge pas en pleine taille.

**Cause.** Les pages de résultats d'Unsplash mêlent les photos gratuites et les photos **Unsplash+** (payantes), servies depuis `plus.unsplash.com/premium_photo-…`.

**Correctif.** Ne relever que les identifiants `images.unsplash.com/photo-…` (le script de `phases.md` le fait) ; télécharger par `https://images.unsplash.com/<id>?…`.

---

## Page blanche après écriture d'une option de thème

**Symptôme.** « Il y a eu une erreur critique sur ce site ». Le journal d'erreurs ne contient rien d'utile.

**Cause.** Une option typée a reçu un tableau responsive là où le thème attendait une chaîne. Exemple vécu : `hb-primary-footer-height` dont le défaut est une chaîne vide, et qui provoque `strtolower(): Argument #1 must be of type string, array given`.

**Diagnostic.** Reproduire le rendu en PHP plutôt que chercher dans les logs :

```php
ob_start();
try { wp_head(); }
catch (\Throwable $e) {
  return get_class($e) . ': ' . $e->getMessage()
       . ' @ ' . str_replace(ABSPATH, '', $e->getFile()) . ':' . $e->getLine();
}
ob_end_clean();
```

La trace donne le fichier et la ligne exacts.

**Correctif.** Lire le défaut avant d'écrire, et respecter son type. Plus généralement : ne pas empiler un thème tiers sous un design sur mesure (règle 8).

**Attention.** Le cache statique du thème garde l'ancienne valeur dans la même requête PHP. Retester dans une requête neuve, sinon le correctif paraît inopérant.

---

## Toutes les images cassées après déploiement

**Symptôme.** Le texte alternatif s'affiche à la place de chaque image. Les URL se terminent sans extension.

**Cause.** Extension oubliée dans le générateur de patterns : `get_theme_file_uri('assets/img/nom')` au lieu de `'assets/img/nom.jpg'`.

**Correctif.** Corriger, régénérer, et ne renvoyer qu'une archive des patterns — 11 Ko au lieu de 2,3 Mo.

---

## Page d'accueil impossible à modifier

**Symptôme.** Aucune page « Accueil » dans la liste des pages. Le contenu n'apparaît que dans l'éditeur de site.

**Cause.** `front-page.html` appelle directement des `wp:pattern`. Le contenu appartient au template, pas à une page.

**Correctif.** Créer une page réelle, y placer le contenu, réduire `front-page.html` à `header + post-content + footer`, puis `show_on_front = page`. C'est la règle 4, et elle coûte une refonte complète quand on la découvre après coup.

---

## Le serveur de développement refuse de démarrer

**Symptôme.** `Error: EPERM: operation not permitted, uv_cwd` pour Node, `PermissionError: [Errno 1] Operation not permitted` pour Python.

**Cause.** Le processus est lancé avec un **répertoire courant** protégé par macOS sans que l'autorisation ait été accordée — typiquement sous `~/Documents` ou `~/Desktop`. C'est le `cwd` qui est en cause, pas l'emplacement des fichiers servis.

**Correctif.** Lancer depuis `/tmp` en passant `RACINE` en chemin absolu : le serveur lit sans difficulté un dossier que son `cwd` ne pourrait pas occuper. Ce n'est qu'en cas d'échec de **lecture** qu'il faut déplacer le projet — et ça se teste, voir phase 0.

---

## « Pourquoi mon dossier est vide ? »

**Symptôme.** Le site est en ligne, tout fonctionne, et l'utilisateur ne trouve aucun fichier là où il les attendait. Il le découvre seul, souvent longtemps après.

**Cause.** Le projet a été créé hors du dossier de travail « par précaution TCC », sans avoir vérifié que la protection s'appliquait vraiment, et sans le redire ensuite.

**Correctif.** Tester l'accès en phase 0, et ne sortir du dossier de travail que sur un échec réel. C'est la panne jumelle de la précédente, et la plus coûteuse des deux : une consigne de prudence appliquée sans vérification ne casse rien, donc rien ne la signale — seul l'utilisateur finit par buter dessus, et il a perdu confiance avant de poser la question.

---

## Composants sans style après passage en blocs natifs

**Symptôme.** Les grilles s'empilent, les cartes perdent leurs marges intérieures.

**Cause.** Le DOM a changé : une image est devenue `figure.wp-block-image`, un conteneur `div.wp-block-group`. Les sélecteurs écrits pour du HTML brut ne matchent plus.

**Correctif.** Poser les classes du composant directement sur le bloc via `className`. Voir `blocs-gutenberg.md`.

---

## Une itération de mise en page coûte quatre minutes

**Symptôme.** Chaque retouche exige de composer, mettre en file, sérialiser dans un onglet d'administration ouvert, purger le cache, recharger.

**Cause.** Le design est poussé dans le `post_content` par l'API de blocs au lieu d'être écrit en fichiers.

**Correctif.** C'est le principe même de la méthode. La file de blocs sert au contenu, jamais à la mise en forme.

---

## Le rythme vertical disparaît après le passage en blocs

**Symptôme.** Le site fonctionne, rien ne semble cassé — mais la page est ~300 px plus courte que la maquette, les titres collent aux paragraphes, une carte volontairement décalée est revenue dans l'alignement. L'œil ne le voit pas ; seul le relevé de la porte 4 le montre.

**Cause.** La neutralisation naïve de l'espacement automatique : `.wp-block-group > * { margin-block: 0 }`. Spécificité (0,1,0) en fin de feuille, elle écrase toutes les marges déclarées par les composants. Vécu : quatorze marges à zéro d'un coup.

**Correctif.** `blockGap: "0px"` dans `theme.json` + reset à spécificité nulle (`:where()`). La méthode complète est dans `blocs-gutenberg.md`, section « Neutraliser l'espacement automatique — sans détruire le vôtre ».

---

## Un correctif CSS ne « prend » pas

**Symptôme.** Le fichier est corrigé sur le serveur — `curl` le prouve — mais le navigateur affiche toujours l'ancien rendu, purge après purge.

**Cause.** Trois caches superposés : la page LiteSpeed, le navigateur (Hostinger sert le HTML avec `cache-control: max-age=604800` — sept jours), et le cache objet des en-têtes de thème qui fige le `?ver=` des assets.

**Diagnostic.** `curl -D -` sur l'URL nue : comparer le `?ver=` du HTML servi à la version réelle du thème, et le fichier servi au fichier local, à l'octet près.

**Correctif.** Incrémenter `Version:` dans `style.css`, purger (LiteSpeed + `c2woo_purger()`), contrôler avec `?frais=<n>`. Le détail est en phase 5, étape 7. Si c'est un template qui ne prend pas : voir « Un correctif de template sans effet ».

---

## 404 sur tout le site pendant les tests

**Symptôme.** Chaque page testée renvoie « Page non trouvée » alors qu'elles existent et que les permaliens sont bons.

**Cause.** Le paramètre choisi comme casse-cache est une variable de requête réservée de WordPress : `?m=1` demande l'archive du mois « 1 », qui n'existe pas. Idem `?p=`, `?s=`, `?cat=`, `?tag=` — et côté WooCommerce `?add-to-cart=` (ajoute un produit au panier), `?orderby=`, `?product_cat=`.

**Correctif.** Un nom neutre : `?frais=<n>`.

---

## Menu mobile : liens collés à gauche, impossible à fermer

**Symptôme (signalé par le client sur le site en production).** Le burger ouvre un panneau où les liens touchent le bord gauche de l'écran ; la croix est minuscule, collée dans l'angle, et connecté elle passe sous la barre d'administration : « on ne peut pas le fermer ». En mobile, le lien Contact de l'en-tête (masqué avec les actions) n'existe plus nulle part ; en tablette, le menu en ligne touche le logo.

**Cause.** WordPress calcule la marge du panneau à partir du padding racine de `theme.json` (`var(--wp--style--root--padding-*)` dans un `clamp()`) : sans padding racine, la valeur est invalide et vaut 0. La croix (24 px) se cale alors dans l'angle du panneau. Et `core/navigation` ne replie le menu qu'en dessous de 600 px.

**Correctif.** Dans le squelette : panneau à la gouttière du site, croix de 44 px, liens pleine largeur à filets, sous-menus en retrait ; menu replié dès 1024 px ; les liens que l'en-tête masque en mobile ajoutés au menu avec la classe `menu-mobile-seul` (visibles dans le panneau seulement). **Détection.** `scripts/menu-mobile.js` rend exactement ces défauts (« liens collés au bord gauche (x = 0 px) », « croix trop petite (24 × 24 px) », « croix recouverte par img.avatar », « croix sous la barre d'admin ») : il se passe dès la phase 3b, à la répétition générale, sur le site juste après le déploiement, et à la porte 3.

---

## Le bouton du menu flotte au milieu de l'en-tête mobile

**Symptôme.** En mobile, l'en-tête montre le logo à gauche, le burger seul au milieu, puis le compte et le panier à droite.

**Cause.** L'en-tête d'ordinateur est une rangée `logo · menu · actions` en `justify-content: space-between` : une fois le menu replié, son bouton reste au centre de la rangée.

**Correctif.** Sous 1024 px : `.entete-rangee{justify-content:flex-start}`, le menu en `order:-1` (bouton à gauche du logo), les actions en `margin-left:auto` — déjà dans le squelette. Avec une rangée en grille (`1fr auto 1fr`, logo centré), le menu reste dans la première colonne. `scripts/menu-mobile.js` signale le cas (« bouton du menu isolé au milieu de l'en-tête »).

---

## Le panneau de navigation mobile s'ouvre blanc sur blanc

**Symptôme.** Le burger fonctionne, le panneau s'ouvre — fond blanc, liens crème, illisible. La règle du thème qui pose le fond sombre est bien dans la feuille servie.

**Cause.** WordPress imprime le CSS d'un bloc au moment où il le rend, donc après `style.css`. À spécificité égale, le style du bloc gagne — quelle que soit la feuille du thème. Et la feuille qui pose le blanc n'est pas inspectable depuis la page.

**Correctif.** Qualifier le nav (`.wp-block-navigation.ma-nav …`) et forcer `background-color` avec `!important` — le seul endroit du thème où il est justifié. Voir `blocs-gutenberg.md`, section « Navigation ».

---

## L'éditeur affiche des colonnes effondrées et des boutons en texte vertical

**Symptôme.** Le site est parfait, mais dans Gutenberg la photo du héros prend la moitié du canevas, l'enveloppe est écrasée, un bouton s'affiche lettre par lettre sur 100 px de large.

**Cause.** Gutenberg impose `position: relative` à chaque bloc pour ancrer ses barres d'outils : les couches que la maquette sort du flux y retombent et volent la place au contenu.

**Correctif.** Une feuille `assets/editor.css` chargée par `add_editor_style()`, qui rétablit les `position: absolute` (avec `!important`). Voir `blocs-gutenberg.md`, section « L'éditeur doit prévisualiser le site », et la porte 5 qui le vérifie.

---

## « Modifier le site » : la fiche produit s'affiche en vrac dans l'éditeur

**Symptôme (vécu, SilverRock, 24/09/2026).** Le site est parfait, les pages s'éditent bien, mais le client clique sur « Modifier le site » → modèle « Fiche produit » : « Vous aimerez aussi » tient dans une colonne de 290 px, chaque carte fait 87 px, les titres s'écrivent lettre par lettre, les boutons « Ajouter au panier » deviennent des ronds ; la galerie empile une dizaine de vignettes de 318 px ; « Termes introuvables » sous chaque carte ; le nom de marque géant du pied passe sur deux lignes. Aucune porte ne le voyait : la porte 5 ne regardait que l'accueil.

**Causes.**
1. Dans l'éditeur, les blocs internes des blocs WooCommerce **composites** (Collection de produits, Galerie) sont emballés dans un conteneur `.block-editor-block-list__layout`. Si le thème pose sa grille sur le bloc lui-même (`.collection-associes{display:grid}`), ce conteneur devient son seul enfant : tout s'entasse dans la première colonne. (Les groupes n'ont pas ce problème : leur `div` EST le conteneur.)
2. La galerie de l'aperçu affiche des vignettes fictives, sans largeur posée par le thème.
3. `core/post-terms` sur un attribut (`pa_metal`) : les taxonomies d'attributs ne sont pas exposées à l'API REST, l'éditeur ne trouve aucun terme.
4. Le texte enrichi de l'éditeur impose `white-space: pre-wrap` : un `nowrap` du thème saute.
5. Le bouton d'ajout de l'aperçu n'est pas `.single_add_to_cart_button` mais `.wc-block-components-product-button__button`, dans deux conteneurs de plus.

**Correctifs (dans le squelette).** `editor.css` §5 (la grille passe sur `> .block-editor-block-list__layout`), §6 (colonne de vignettes bornée), §7 (`nowrap` rétabli) ; `functions.php` : `register_taxonomy_args` → `show_in_rest` pour `pa_*` ; `style.css` : le bouton de la fiche vise les deux DOM. Contrôle : porte 5, **sur les modèles** (fiche produit, catalogue, confirmation) et sur l'accueil ouvert par « Modifier le site ».

---

## La grande image de la fiche est écrasée dès qu'un produit a plusieurs photos

**Symptôme.** Avec le placeholder seul, la fiche est parfaite. Le client ajoute trois photos à un produit : la colonne des vignettes prend 332 px (45 % de la galerie à 1440), la grande image rétrécit à 411 px, les vignettes sont centrées verticalement.

**Cause.** Aucune largeur n'est posée sur `.wc-block-product-gallery-thumbnails` : la colonne flexible prend sa taille naturelle. Invisible pendant toute la recette, parce que le catalogue de démonstration n'a pas de photos.

**Correctif.** Colonne fixe (76 px, `align-self: flex-start`) en ordinateur, rangée de 64 px sous l'image en mobile — dans le squelette. **Et en phase 3b, donner trois photos à un produit de démonstration** (médias déjà importés) pour dessiner la galerie réelle, puis les retirer avant la répétition générale (elle repart de zéro, donc d'elle-même).

---

## Un fichier PHP du thème refusé au téléversement

**Symptôme.** `televerser.sh theme/…/functions.php wp-content/themes/<slug>/functions.php` : « PHP files … can only be written to the sandbox directory ».

**Cause.** Novamira n'autorise l'écriture de fichiers PHP que dans `wp-content/novamira-sandbox/`.

**Correctif.** Un correctif CSS ou JS se téléverse seul ; dès qu'un `.php` change (functions.php, patterns/), repasser par l'archive du thème (phase 5, étapes 1 et 2). Vérifier ensuite par `md5_file()` côté serveur.

---

## L'atelier ne démarre pas : port 9400 occupé

**Symptôme.** `listen EADDRINUSE :::9400` dans le journal de l'atelier.

**Cause.** L'atelier d'un **autre projet** tourne encore (autre session). Ne jamais le tuer : ce n'est pas le nôtre.

**Correctif.** Lancer sur un autre port (`atelier.sh PROJET 9410`) et le passer aux outils : `ATELIER_PORT=9410 scripts/atelier-php.sh …` (sinon le jeton est refusé — ou, pire, la commande vise l'autre atelier).

---

## Captures noires ou mesures fausses pendant la recette

**Symptôme.** Des captures d'écran entièrement noires, des éléments mesurés à 0 px de haut alors qu'ils s'affichent, un défilement programmé qui ne semble pas s'appliquer.

**Cause.** `scroll-behavior: smooth` dans le CSS du site : chaque `scrollTo()` programmé devient une animation, et la mesure ou la capture part en plein mouvement.

**Correctif.** Avant toute mesure : `document.documentElement.style.scrollBehavior = 'auto'` et `scrollTo({top, behavior: 'instant'})`. Attendre ensuite le décodage des images (`img.complete && img.naturalWidth > 0`).

---

## Chaque catégorie affiche tout le catalogue

**Symptôme.** `/categorie-produit/bagues/` annonce « 4 résultats affichés » et montre les douze produits de la boutique. Les filtres (prix, attribut) ne changent rien.

**Cause.** La grille de `patterns/catalogue.php` était une collection `product-catalog` avec `"inherit": false` : elle ignore la requête de la page. Le compteur, lui, lit la requête. (C'était le cas du squelette de la V1.)

**Correctif.** `"inherit": true`, et `"inherit"` retiré de `hideControls`. Contrôle dans l'atelier : une catégorie ne liste que ses produits ; `?filter_<attribut>=<terme>` et `?min_price=` filtrent ; `/<attribut>/<terme>/` (archives d'attribut) aussi.

---

## Frais de port affichés au-dessus du seuil de gratuité

**Symptôme.** « Livraison offerte dès 300 € » partout, et un panier de 740 € affiche 7,90 € de port. À la commande, deux options : « Livraison offerte » et le forfait payant.

**Cause.** WooCommerce présélectionne la **première** méthode disponible de la zone ; le forfait avait été créé en premier. Et au-dessus du seuil, les deux méthodes sont proposées.

**Correctif.** `boutique.php` place la gratuité en tête (`method_order`) ; `functions.php` du squelette retire le forfait quand la gratuité est disponible (`woocommerce_package_rates`). Après un changement de réglage, les tarifs sont en cache de session : `WC_Cache_Helper::get_transient_version('shipping', true)`.

---

## Le nom de l'hébergeur dans l'onglet

**Symptôme.** En production, les onglets affichent « Contact – blanchedalmond-walrus-280784.hostingersite.com » ; l'atelier affichait « Contact – Marque ».

**Cause.** Le blueprint de l'atelier pose le nom du site ; en production, rien ne le faisait.

**Correctif.** `c2woo_reglages()` pose `blogname` (depuis `marque`) et `blogdescription` (depuis `reglages.slogan`).

---

## Adresses de commande en anglais

**Symptôme.** Après une commande : `/commande/order-received/72/` ; dans Mon compte : `/mon-compte/orders/`.

**Cause.** Les points de terminaison de WooCommerce restent en anglais quelle que soit la langue du site.

**Correctif.** `boutique.php` les passe en français (`commande-recue`, `commandes`, `voir-commande`, `adresses`, `modifier-compte`, `mot-de-passe-perdu`, `deconnexion`…) et vide les règles de réécriture ; `reglages.permaliens.points` permet d'en changer.

---

## Deux vignettes par article dans le mini-panier

**Symptôme.** Chaque article du tiroir (et du panier) montre deux images empilées.

**Cause.** WooCommerce place une image de secours `<img hidden>` ; la base `img { display: block }` l'emporte sur l'attribut `hidden` (style de l'agent utilisateur).

**Correctif.** `[hidden] { display: none !important }` dans la base (squelette).

---

## Le site mesure quelques pixels de plus que la maquette

**Symptôme.** À la porte 4, les sections décalées de quelques pixels, de plus en plus bas dans la page.

**Causes relevées, par ordre de fréquence :**

- **Cartes produit** : WooCommerce ajoute 12 px sous chaque enfant de `li.wc-block-product` et 12 px sous le résumé — +18 px par rangée de cartes. Neutralisés dans le squelette.
- **Paragraphe-lien** : le lien de la maquette est devenu un paragraphe, attrapé par la règle `.composant p` du texte courant (corps, interligne) — `p:not(.lien-fleche)`.
- **Hauteur de ligne des titres** : `theme.json` impose 1.1 à tous les titres ; la maquette laissait hériter 1.55 — poser l'interligne sur le composant.
- **Logo** : texte de 37 px de ligne dans la maquette, image de 20 px dans le site — largeur mesurée, `min-height` sur le bloc.
- **Gouttière** : la valeur du squelette n'est pas celle de la maquette — elle change les sections pleine largeur.
- **Champs** : un `textarea` en ligne laisse 5 px sous lui — `display: block`.
- **Un artefact de la maquette** (une marge héritée par erreur) : le reproduire à l'identique, commentaire à l'appui, plutôt que de corriger la maquette validée.

**Diagnostic.** Relever les hauteurs des enfants et leurs marges calculées sur les deux, composant par composant (phase 4, « La mesure »).

---

## La galerie produit est carrée, l'image flotte dans des bandes

**Symptôme.** Sur la fiche, le placeholder 4:5 est centré dans un carré, avec deux bandes de fond de part et d'autre.

**Cause.** Le format de la galerie vient de l'attribut `aspectRatio` du bloc Image du produit **imbriqué** dans la galerie ; sans lui, 1:1, et l'image en `object-fit: contain`.

**Correctif.** `{"aspectRatio":"4/5"}` (le format de la maquette) sur ce bloc, et `object-fit: cover` en CSS. C'est dans le squelette.

---

## Composants WooCommerce qui ne prennent pas le style

Tous vécus, tous à spécificité insuffisante — WooCommerce écrit à 2 ou 3 classes, ou en ligne :

| Symptôme | Correctif (squelette) |
|---|---|
| Bouton « Ajouter au panier » pas pleine largeur | `.fiche-ajout .wp-block-button .single_add_to_cart_button.wp-block-button__link` |
| Bouton rond d'une carte à 0 px de large | qualifier `.wc-block-product .carte-… .carte-bouton …` et forcer la largeur |
| Tableau des caractéristiques à bordures noires | c'est un `figure.wp-block-table` : `.fiche-caracteristiques table th` |
| Pastille de quantité du récapitulatif blanche sur blanc | `.wc-block-components-order-summary .wc-block-components-order-summary-item__quantity` |
| Liens de Mon compte très espacés, bloc borné à 1000 px | `.woocommerce-account .woocommerce-MyAccount-navigation li`, `.woocommerce-account main .woocommerce` |
| Bandeaux de message bleus ou verts | `.wc-block-components-notice-banner.is-info` (qualifié par le type) |
| Marges internes des pastilles de variation | `!important` (styles en ligne de WooCommerce) |

---

## Une cellule vide décale la grille des produits associés

**Symptôme.** « Vous aimerez aussi » : le titre tombe dans la deuxième colonne, les cartes passent dessous.

**Cause.** `product-collection` place un conteneur de messages (vide) avant la grille : il occupe la première cellule quand la collection est elle-même une grille CSS.

**Correctif.** `.collection-x > .wc-block-components-notices { grid-column: 1 / -1 }` et masqué s'il est vide (`:not(:has(*))`).

---

## Une variation en rupture colorée comme en stock

**Symptôme.** « En rupture de stock » s'affiche à la couleur normale pour une taille épuisée d'un produit variable.

**Cause.** L'indicateur met à jour son texte pour la variation, mais garde la classe `--in-stock` du produit parent.

**Correctif.** Ne pas colorer l'état par sa classe sur un produit variable ; le bouton inactif et le texte suffisent.

---

## Le panier déborde en mobile

**Symptôme.** À 390 px, `scrollWidth` vaut 415 : le total des lignes (avec le badge « Économisez… ») sort de l'écran.

**Cause.** En mobile, WooCommerce fige la colonne produit du panier à 132 px et place le total dans une colonne implicite.

**Correctif.** `grid-template-columns: 80px minmax(0,1fr) auto` sur `.wc-block-cart.is-mobile … .wc-block-cart-items__row`, badge sur deux lignes. Dans le squelette.

---

## L'en-tête recouvre le fil d'Ariane, connecté

**Symptôme.** En administrateur (et surtout en mobile), l'en-tête descend de 32 ou 46 px et masque le haut du contenu ; déconnecté, tout va bien.

**Cause.** `.admin-bar .entete { top: 32px }` appliqué à un en-tête **en flux** (ni collant ni posé sur le héros), aggravé par `body { position: relative }`.

**Correctif.** Le décalage seulement pour un en-tête collant, ou posé sur le héros (`body.admin-bar:has(.hero) .entete`) ; pas de `position: relative` sur `body`. Dans le squelette.

---

## Un bouton de l'en-tête reste affiché en mobile

**Symptôme.** Le bouton de l'en-tête déborde à 390 px malgré `.entete-bouton { display: none }`.

**Cause.** Le CSS du bloc Boutons (`.wp-block-buttons { display: flex }`) est imprimé après la feuille du thème, à spécificité égale.

**Correctif.** Qualifier : `.entete .entete-bouton { display: none }`.

---

## WooCommerce ou Contact Form 7 absent du site

**Symptôme.** Le site du tutoriel est vierge : WordPress et Novamira seulement. `c2woo_deployer()` répond « WooCommerce absent ».

**Cause.** Rien n'installe les extensions implicitement, et Novamira n'expose pas d'ability d'installation.

**Correctif.** `c2woo_preparer()` : paquet de langue `fr_FR` d'abord (sinon WooCommerce crée `/shop/`, `/cart/` et des textes anglais), puis WooCommerce et les extensions du paquet par `Plugin_Upgrader` depuis le dépôt officiel, aux versions de l'atelier. Idempotent ; ne touche jamais à Novamira.

---

## « Invalid or expired admin access nonce »

**Symptôme.** Le lien de connexion obtenu par `novamira/create-admin-access-link` affiche une erreur 401. Ou l'appel lui-même est refusé : « redirect_to n'est pas une propriété valide ».

**Cause.** L'ability n'accepte aucun paramètre ; sa réponse est un échange (jeton + nonce à poster sur `exchange_url`) qui rend une URL de connexion **à usage unique, valable 60 secondes** — trop tard si l'on enchaîne d'autres étapes entre les deux.

**Correctif.** `scripts/lien-admin.sh` : création, échange et affichage de la seule URL, d'un geste ; l'ouvrir aussitôt.

---

## Un secret affiché dans le journal

**Symptôme.** Un jeton d'accès administrateur, un jeton de téléversement ou le mot de passe applicatif apparaît en clair dans une sortie de commande.

**Cause.** Une réponse d'ability imprimée telle quelle, avec un masquage qui ne visait que les URL.

**Correctif.** Passer par les scripts (`connecteur.py`, `envoyer.sh`, `televerser.sh`, `lien-admin.sh`) : aucun n'affiche un secret. Si c'est arrivé : un jeton à usage unique déjà consommé ne vaut plus rien — le dire ; un mot de passe applicatif se révoque et se remplace (WordPress → Profil → Mots de passe d'application, puis nouveau connecteur). En fin de projet, la révocation est systématique : phase 7 (`c2woo_revoquer_acces()`, puis `connecteur.py --oublier`).

---

## La sauvegarde est téléchargeable par n'importe qui

**Symptôme.** `https://SITE/wp-content/uploads/sauvegarde-avant-boutique-….json` répond 200 : contenus et réglages du site.

**Cause.** La sauvegarde était écrite dans `uploads/`, servi publiquement.

**Correctif.** `wp-content/c2woo-sauvegardes/`, fermé par `.htaccess` (LiteSpeed, Apache) et un `index.php` vide.

---

## Le formulaire de contact « ne répond pas »

**Symptôme.** Un clic sur « Envoyer » ne fait rien ; aucune erreur.

**Cause.** Contact Form 7 **désactive** le bouton tant que la case de consentement (`[acceptance]`) n'est pas cochée.

**Correctif.** C'est voulu : dessiner l'état inactif (`.envoi:has(input:disabled) { opacity: .45 }`). Pour tester, cocher la case puis envoyer à vide : les erreurs doivent s'afficher sous chaque champ, à la couleur `alerte`.

---

## Remplir un panier de test renvoie 403

**Symptôme.** `fetch('/wp-json/wc/store/v1/cart/add-item', …)` répond 403.

**Cause.** Le jeton de l'API Store (`wcBlocksMiddlewareConfig.storeApiNonce`) n'est chargé que sur les pages qui portent un bloc Panier ou Commande — pas sur l'accueil.

**Correctif.** Lancer le script depuis `/panier/`.

---

## Divers, à connaître

- **Sessions MCP en HTTP.** L'endpoint exige un en-tête `Mcp-Session-Id`. Ouvrir une session par `initialize`, récupérer l'identifiant dans les en-têtes de réponse, envoyer `notifications/initialized`, puis réutiliser l'identifiant. `scripts/wp.sh` le gère.
- **Réponses en flux.** Une réponse peut arriver en `text/event-stream` : lire la ligne préfixée `data:` avant de parser le JSON.
- **Captures trop précoces.** Les JPEG volumineux ne sont pas décodés avant cinq secondes environ. Vérifier `img.complete && img.naturalWidth > 0`.
- **Cache LiteSpeed.** Purger après chaque déploiement, et contrôler avec `?frais=<n>` — jamais `?m=` ni un autre paramètre réservé. Le protocole complet (version, purge, contrôle par `curl`) est en phase 5, étape 7.
- **Écriture PHP verrouillée.** Novamira n'écrit les fichiers `.php` que dans son bac à sable : `functions.php` et les patterns passent obligatoirement par l'archive du thème, les fichiers non-PHP (`style.css`, `theme.json`, `editor.css`) peuvent s'éditer directement. `boutique.php` ne s'écrit pas non plus sur le serveur : son code part dans chaque appel à `execute-php` (phase 5).
- **Node 20 et Playground.** `npm warn EBADENGINE … node >= 24` au lancement de l'atelier : sans effet constaté avec Node 20.20. Si l'atelier refuse un jour de démarrer, épingler une version antérieure de `@wp-playground/cli` dans `atelier.sh`.
- **Traductions du thème.** « Error downloading translations for theme » au lancement de l'atelier : le thème n'est pas sur wordpress.org, c'est attendu.
- **Site cible jamais vide.** Sauvegarder l'existant avant d'écrire (phase 5, étape 0) : les slugs en collision sont écrasés.
