#!/usr/bin/env python3
"""
Constructeurs de balisage de blocs Gutenberg — phase 4, pages éditoriales.

Sortie identique au sérialiseur de l'éditeur (délimiteurs, classes, frères
séparés par « \\n\\n ») : ce qui sort d'ici passe la porte 2 sans retouche.
Validé dans l'éditeur de WordPress 7.1 / WooCommerce 11.1 (0 bloc invalide sur
trois pages, 260 blocs).

Usage — un script de projet, PROJET/contenus/_generer.py, qui écrit
contenus/accueil.html, contenus/a-propos.html… à partir de la maquette :

    import sys; sys.path.insert(0, '<SKILL>/scripts')
    from blocs import *
    CARTE = open('<PROJET>/contenus/_carte.html').read()   # la carte du thème, générée dans l'éditeur
    accueil = suite(
        groupe('hero', image('hero-1', 'Texte alternatif', 'hero-photo'),
               groupe('wrap hero-contenu', titre("Titre<br>sur deux lignes", 'hero-titre', 1),
                      para('Chapeau.', 'hero-texte'), boutons(bouton('Voir les produits', '/boutique/', 'b-sombre')))),
        groupe('section wrap', titre('Nos produits phares', 'section-titre'),
               collection('featured', 'collection-vedettes', 31, 4, CARTE, perPage=4, featured=True),
               tag='section'),
    )

Jetons remplacés au déploiement (boutique.php) : {{media:slug}}, {{media-url:slug}},
{{produit:SKU}}, {{lien-produit:SKU}}, {{categorie:slug}}, {{lien-categorie:slug}},
{{page:slug}}, {{lien-page:slug}}, {{formulaire:<titre>}}.

Règles tenues par ces fonctions (blocs-gutenberg.md) : classes via className,
groupes en layout default, image liée à la médiathèque (id + wp-image-<id>),
bouton = conteneur .wp-block-button + lien .wp-block-button__link.wp-element-button,
liste = core/list + core/list-item (classe par élément possible), accordéon
= core/details. Jamais de core/html.
"""
import json

__all__ = ['suite', 'groupe', 'titre', 'para', 'image', 'boutons', 'bouton', 'liste', 'item',
           'details', 'shortcode', 'separateur', 'collection', 'VARIATIONS']


def _j(d):
    return json.dumps(d, ensure_ascii=False, separators=(',', ':'))


def _classe(base, classe):
    return (base + ' ' + classe).strip() if classe else base


def suite(*blocs):
    """Frères, séparés comme par le sérialiseur."""
    return '\n\n'.join(b for b in blocs if b)


def groupe(classe, *enfants, tag='div', ancre=None):
    """core/group, layout default. tag : 'section', 'main'… ancre : id (#ancre)."""
    a = {}
    if tag != 'div':
        a['tagName'] = tag
    if ancre:
        a['anchor'] = ancre
    if classe:
        a['className'] = classe
    a['layout'] = {'type': 'default'}
    idattr = f' id="{ancre}"' if ancre else ''
    return (f'<!-- wp:group {_j(a)} -->\n<{tag}{idattr} class="{_classe("wp-block-group", classe)}">'
            + suite(*enfants) + f'</{tag}>\n<!-- /wp:group -->')


def titre(texte, classe='', niveau=2):
    """core/heading. <br> permis ; <em> nu seulement (jamais <em class>)."""
    a = {}
    if niveau != 2:
        a['level'] = niveau
    if classe:
        a['className'] = classe
    ja = (' ' + _j(a)) if a else ''
    return (f'<!-- wp:heading{ja} -->\n<h{niveau} class="{_classe("wp-block-heading", classe)}">{texte}</h{niveau}>'
            f'\n<!-- /wp:heading -->')


def para(texte, classe=''):
    """core/paragraph. Formats permis : <strong>, <em>, <a href>, <br>.
    Un <span> ou un <b> de la maquette devient <strong> (styler par le contexte)."""
    if classe:
        return f'<!-- wp:paragraph {_j({"className": classe})} -->\n<p class="{classe}">{texte}</p>\n<!-- /wp:paragraph -->'
    return f'<!-- wp:paragraph -->\n<p>{texte}</p>\n<!-- /wp:paragraph -->'


def image(media, alt, classe=''):
    """core/image liée à la médiathèque. media : slug de deploiement.json → medias.
    La classe va sur la FIGURE : le CSS vise « .classe img »."""
    a = '{"id":{{media:%s}},"sizeSlug":"full","linkDestination":"none"%s}' % (
        media, (',"className":' + json.dumps(classe, ensure_ascii=False)) if classe else '')
    return (f'<!-- wp:image {a} -->\n<figure class="{_classe("wp-block-image size-full", classe)}">'
            f'<img src="{{{{media-url:{media}}}}}" alt="{alt}" class="wp-image-{{{{media:{media}}}}}"/></figure>'
            f'\n<!-- /wp:image -->')


def boutons(*items, classe=''):
    a = (' ' + _j({'className': classe})) if classe else ''
    return (f'<!-- wp:buttons{a} -->\n<div class="{_classe("wp-block-buttons", classe)}">' + suite(*items)
            + '</div>\n<!-- /wp:buttons -->')


def bouton(texte, lien, classe=''):
    """La classe atterrit sur le CONTENEUR : CSS « .classe>.wp-block-button__link »."""
    a = (' ' + _j({'className': classe})) if classe else ''
    return (f'<!-- wp:button{a} -->\n<div class="{_classe("wp-block-button", classe)}">'
            f'<a class="wp-block-button__link wp-element-button" href="{lien}">{texte}</a></div>\n<!-- /wp:button -->')


def liste(classe, *items):
    return (f'<!-- wp:list {_j({"className": classe})} -->\n<ul class="wp-block-list {classe}">'
            + suite(*items) + '</ul>\n<!-- /wp:list -->')


def item(texte, classe=''):
    """core/list-item ; une classe par élément sert, par exemple, à choisir son icône (::before)."""
    if classe:
        return f'<!-- wp:list-item {_j({"className": classe})} -->\n<li class="{classe}">{texte}</li>\n<!-- /wp:list-item -->'
    return f'<!-- wp:list-item -->\n<li>{texte}</li>\n<!-- /wp:list-item -->'


def details(resume, *enfants, classe=''):
    """core/details (<details><summary>) : FAQ et dépliants, éditables, sans script."""
    a = (' ' + _j({'className': classe})) if classe else ''
    return (f'<!-- wp:details{a} -->\n<details class="{_classe("wp-block-details", classe)}"><summary>{resume}</summary>'
            + suite(*enfants) + '</details>\n<!-- /wp:details -->')


def shortcode(code):
    """core/shortcode — deux usages seulement : [woocommerce_my_account] et Contact Form 7."""
    return f'<!-- wp:shortcode -->\n{code}\n<!-- /wp:shortcode -->'


def separateur(classe=''):
    a = (' ' + _j({'className': classe})) if classe else ''
    return (f'<!-- wp:separator{a} -->\n<hr class="{_classe("wp-block-separator has-alpha-channel-opacity", classe)}"/>'
            f'\n<!-- /wp:separator -->')


# Attributs des variations de woocommerce/product-collection, relevés dans l'éditeur
# de l'atelier : wp.blocks.getBlockVariations('woocommerce/product-collection').
# new-arrivals est volontairement absent : sa variation porte un timeFrame de 7 jours
# (section vide une semaine après la mise en ligne) — utiliser by-category ou
# product-catalog triés par date décroissante.
REQUETE = {"perPage": 4, "pages": 1, "offset": 0, "postType": "product", "order": "asc", "orderBy": "title",
           "search": "", "exclude": [], "inherit": False, "taxQuery": {}, "isProductCollectionBlock": True,
           "featured": False, "woocommerceOnSale": False,
           "woocommerceStockStatus": ["instock", "outofstock", "onbackorder"], "woocommerceAttributes": [],
           "woocommerceHandPickedProducts": [], "filterable": False, "relatedBy": {"categories": True, "tags": True}}
VARIATIONS = {
    'product-catalog': ["inherit"],
    'featured': ["inherit", "featured", "filterable"],
    'on-sale': ["inherit", "on-sale", "filterable"],
    'best-sellers': ["inherit", "order", "filterable"],
    'hand-picked': ["inherit", "hand-picked", "filterable", "order"],
    'by-category': ["inherit", "hand-picked", "filterable"],
    'by-tag': ["inherit", "hand-picked", "filterable"],
}


def collection(nom, classe, query_id, colonnes, carte, grille='grille-produits', jetons=None, **requete):
    """woocommerce/product-collection + product-template + la carte du thème.

    nom      : clé de VARIATIONS (featured, by-category, hand-picked…)
    query_id : UNIQUE par page (pagination, filtres)
    carte    : balisage des blocs internes de la carte, généré dans l'éditeur
    requete  : surcharges de la requête. Identifiants qui changent entre l'atelier
               et la production → jetons, passés par `jetons` :
      taxQuery={'product_cat': ['__CAT__']}, jetons={'__CAT__': '{{categorie:bagues}}'}
      woocommerceHandPickedProducts=['{{produit:SKU-1}}', '{{produit:SKU-2}}'], orderBy='post__in'
    Ne jamais poser inherit=True ici : réservé à la grille des templates d'archive."""
    if nom not in VARIATIONS:
        raise ValueError('collection inconnue : %s (voir VARIATIONS)' % nom)
    brut = _j(dict(REQUETE, **requete))
    for cle, jeton in (jetons or {}).items():          # valeurs numériques : sans guillemets
        brut = brut.replace(json.dumps(cle), jeton)
    attrs = ('{"queryId":%d,"query":%s,"tagName":"div","displayLayout":{"type":"flex","columns":%d,"shrinkColumns":true},'
             '"dimensions":{"widthType":"fill"},"collection":"woocommerce/product-collection/%s","hideControls":%s,'
             '"queryContextIncludes":["collection"],"className":%s}') % (
        query_id, brut, colonnes, nom, _j(VARIATIONS[nom]), json.dumps(classe, ensure_ascii=False))
    return (f'<!-- wp:woocommerce/product-collection {attrs} -->\n'
            f'<div class="wp-block-woocommerce-product-collection {classe}">'
            f'<!-- wp:woocommerce/product-template {_j({"className": grille})} -->\n'
            + carte.strip() + '\n<!-- /wp:woocommerce/product-template --></div>\n<!-- /wp:woocommerce/product-collection -->')
