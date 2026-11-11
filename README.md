# site-ecom — Imma Shop

Boutique WooCommerce sur mesure, construite avec le skill **`boutique-woocommerce`** (installé sur le compte claude.ai) et développée en local avec **Local** (WP Engine).

Ce dépôt contient **le projet** : maquette, thème, contenus et description du déploiement. WordPress, ses extensions et sa base de données n'y sont pas : ils vivent dans le site Local, et tout ce qui compte pour les recréer est décrit dans `deploiement.json`.

## Organisation

```
site-ecom/
├── plan.md               notes de travail du skill (décisions de phase 1, tableaux des portes)
├── reference/            image de référence (originale.<ext>) et page isolée (page.png)
├── site/                 maquette HTML : accueil et pages vitrine
├── theme/<slug>/         le thème bloc WordPress — relié au site Local par un lien symbolique
├── contenus/             contenus des pages en blocs (accueil, contact, panier, commande, légales…)
├── deploiement.json      réglages de la boutique, catalogue, formulaires, pages
├── polices-ttf/          polices des titres (logo provisoire, favicon)
└── .atelier/             généré par l'atelier — ignoré par git
```

Ces dossiers apparaissent au fil des phases du skill ; au départ, le dépôt ne contient que ce README et le `.gitignore`.

| Versionné ici | Jamais versionné |
|---|---|
| Maquette, thème, contenus, `deploiement.json`, `plan.md` | Cœur de WordPress, extensions (WooCommerce s'installe tout seul) |
| | Base de données (produits, pages, réglages) : recréée depuis `deploiement.json` |
| | `wp-config.php`, `wp-content/uploads/`, sauvegardes |
| | **Secrets** : connecteur Novamira `.mcpb`, `acces.env`, `.atelier/` |

## Mise en place sur le Mac

Prérequis : [Local](https://localwp.com), Git, Node ≥ 20.18, Python 3, et le skill `boutique-woocommerce` sur le compte claude.ai.

1. **Le site Local** : « Imma Shop », domaine `imma-shop.local` (WordPress 7.1, PHP 8.2, MySQL 8.4). Il doit être démarré pour travailler.
2. **Le dépôt**, hors du dossier `Local Sites` :

   ```bash
   git clone https://github.com/wilnergraph92/site-ecom.git ~/projets/site-ecom
   ```

3. **Le thème relié au site** — une fois `theme/<slug>/` créé par le skill (phase 3b) :

   ```bash
   ln -s ~/projets/site-ecom/theme/<slug> ~/"Local Sites"/imma-shop/app/public/wp-content/themes/<slug>
   ```

   Toute modification du thème dans le dépôt est visible aussitôt sur `http://imma-shop.local`. Activer le thème dans WP Admin → Apparence → Thèmes.

4. **La session Claude Code**, sur le Mac, dans le dossier du dépôt : depuis l'app Claude Desktop (session locale), ou :

   ```bash
   cd ~/projets/site-ecom
   ```

   ```bash
   claude remote-control
   ```

   Puis lui envoyer le brief, qui commence par « Détail de la boutique : » (gabarit dans le skill).

## Travailler en local et sur GitHub

- **Session locale (Mac)** : elle voit le site Local, le thème et les fichiers ; c'est là que se font la maquette, l'atelier, les tests et la recette.
- **Session cloud (claude.ai/code)** : elle travaille sur le code du dépôt, mais **ne voit pas `imma-shop.local`** : rien n'y est testé sur le vrai site.
- **Une seule session à la fois par branche.** Chaque chantier a sa branche ; avant de reprendre le travail ailleurs :

  ```bash
  git pull
  ```

- **`main` est la branche principale** : on y fusionne ce qui est terminé et vérifié. (Si elle n'existe pas encore : renommer la branche par défaut en `main` dans GitHub → Settings → Branches.)

## Mise en production

Le skill installe la boutique sur le WordPress d'hébergement par l'extension **Novamira**, après un **feu vert explicite** du client, et referme l'accès à la fin (mot de passe d'application révoqué). Le connecteur `.mcpb` ne se met **jamais** dans ce dépôt : le `.gitignore` le bloque, mais ne pas le copier dans le dossier du projet.
