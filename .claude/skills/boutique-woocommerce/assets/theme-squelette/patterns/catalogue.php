<?php
/**
 * Title: Catalogue — filtres et grille de produits
 * Slug: mon-theme/catalogue
 * Categories: mon-theme
 * Inserter: false
 *
 * Corps commun à archive-product.html (boutique, catégories, étiquettes,
 * attributs, marques) et à product-search-results.html. Généré dans l'éditeur
 * de l'atelier — ne pas réécrire le balisage à la main (porte 2).
 */
?>
<!-- wp:group {"className":"wrap catalogue","layout":{"type":"default"}} -->
<div class="wp-block-group wrap catalogue"><!-- wp:group {"className":"catalogue-filtres","layout":{"type":"default"}} -->
<div class="wp-block-group catalogue-filtres"><!-- wp:woocommerce/product-filters {"className":"filtres"} -->
<div class="wp-block-woocommerce-product-filters wc-block-product-filters filtres"><!-- wp:heading {"style":{"margin":{"top":"0","bottom":"0"},"spacing":{"margin":{"top":"0","bottom":"0"}}}} -->
<h2 class="wp-block-heading" style="margin-top:0;margin-bottom:0">Filtres</h2>
<!-- /wp:heading -->

<!-- wp:woocommerce/product-filter-active -->
<div class="wp-block-woocommerce-product-filter-active"><!-- wp:woocommerce/product-filter-removable-chips -->
<div class="wp-block-woocommerce-product-filter-removable-chips wc-block-product-filter-removable-chips"></div>
<!-- /wp:woocommerce/product-filter-removable-chips -->

<!-- wp:woocommerce/product-filter-clear-button -->
<!-- wp:buttons {"layout":{"type":"flex","verticalAlignment":"stretched"}} -->
<div class="wp-block-buttons"><!-- wp:button {"className":"wc-block-product-filter-clear-button is-style-outline","style":{"border":{"width":"1px"},"typography":{"textDecoration":"none"},"outline":"none","fontSize":"medium","spacing":{"padding":{"left":"8px","right":"8px","top":"5px","bottom":"5px"}}}} -->
<div class="wp-block-button wc-block-product-filter-clear-button is-style-outline"><a class="wp-block-button__link wp-element-button" style="border-width:1px;padding-top:5px;padding-right:8px;padding-bottom:5px;padding-left:8px;text-decoration:none">Effacer les filtres</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->
<!-- /wp:woocommerce/product-filter-clear-button --></div>
<!-- /wp:woocommerce/product-filter-active -->

<!-- wp:woocommerce/product-filter-price -->
<div class="wp-block-woocommerce-product-filter-price"><!-- wp:heading {"level":3,"style":{"spacing":{"margin":{"bottom":"0.625rem","top":"0"}}}} -->
<h3 class="wp-block-heading" style="margin-top:0;margin-bottom:0.625rem">Prix</h3>
<!-- /wp:heading -->

<!-- wp:woocommerce/product-filter-price-slider -->
<div class="wp-block-woocommerce-product-filter-price-slider wc-block-product-filter-price-slider"></div>
<!-- /wp:woocommerce/product-filter-price-slider --></div>
<!-- /wp:woocommerce/product-filter-price -->

<!-- wp:woocommerce/product-filter-attribute -->
<div class="wp-block-woocommerce-product-filter-attribute"><!-- wp:heading {"level":3,"style":{"spacing":{"margin":{"bottom":"0.625rem","top":"0"}}}} -->
<h3 class="wp-block-heading" style="margin-top:0;margin-bottom:0.625rem">Taille</h3>
<!-- /wp:heading -->

<!-- wp:woocommerce/product-filter-checkbox-list -->
<div class="wp-block-woocommerce-product-filter-checkbox-list wc-block-product-filter-checkbox-list"></div>
<!-- /wp:woocommerce/product-filter-checkbox-list --></div>
<!-- /wp:woocommerce/product-filter-attribute -->

<!-- wp:woocommerce/product-filter-taxonomy -->
<div class="wp-block-woocommerce-product-filter-taxonomy"><!-- wp:heading {"level":3,"style":{"spacing":{"margin":{"bottom":"0.625rem","top":"0"}}}} -->
<h3 class="wp-block-heading" style="margin-top:0;margin-bottom:0.625rem">Catégorie</h3>
<!-- /wp:heading -->

<!-- wp:woocommerce/product-filter-checkbox-list -->
<div class="wp-block-woocommerce-product-filter-checkbox-list wc-block-product-filter-checkbox-list"></div>
<!-- /wp:woocommerce/product-filter-checkbox-list --></div>
<!-- /wp:woocommerce/product-filter-taxonomy -->

<!-- wp:woocommerce/product-filter-status -->
<div class="wp-block-woocommerce-product-filter-status"><!-- wp:heading {"level":3,"style":{"spacing":{"margin":{"bottom":"0.625rem","top":"0"}}}} -->
<h3 class="wp-block-heading" style="margin-top:0;margin-bottom:0.625rem">Disponibilité</h3>
<!-- /wp:heading -->

<!-- wp:woocommerce/product-filter-checkbox-list -->
<div class="wp-block-woocommerce-product-filter-checkbox-list wc-block-product-filter-checkbox-list"></div>
<!-- /wp:woocommerce/product-filter-checkbox-list --></div>
<!-- /wp:woocommerce/product-filter-status --></div>
<!-- /wp:woocommerce/product-filters --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"catalogue-resultats","layout":{"type":"default"}} -->
<div class="wp-block-group catalogue-resultats"><!-- wp:woocommerce/store-notices /-->

<!-- wp:group {"className":"catalogue-barre","layout":{"type":"default"}} -->
<div class="wp-block-group catalogue-barre"><!-- wp:woocommerce/product-results-count {"className":"catalogue-compte"} /-->

<!-- wp:woocommerce/catalog-sorting {"className":"catalogue-tri"} /--></div>
<!-- /wp:group -->

<!-- wp:woocommerce/product-collection {"query":{"perPage":12,"pages":0,"offset":0,"postType":"product","order":"asc","orderBy":"title","search":"","exclude":[],"inherit":true,"taxQuery":{},"isProductCollectionBlock":true,"featured":false,"woocommerceOnSale":false,"woocommerceStockStatus":["instock","outofstock","onbackorder"],"woocommerceAttributes":[],"woocommerceHandPickedProducts":[],"filterable":false,"relatedBy":{"categories":true,"tags":true}},"tagName":"div","displayLayout":{"type":"flex","columns":3,"shrinkColumns":true},"dimensions":{"widthType":"fill"},"collection":"woocommerce/product-collection/product-catalog","queryContextIncludes":["collection"],"className":"collection-catalogue"} -->
<div class="wp-block-woocommerce-product-collection collection-catalogue"><!-- wp:woocommerce/product-template {"className":"grille-produits"} -->
<!-- wp:woocommerce/product-image {"showSaleBadge":false,"imageSizing":"thumbnail","isDescendentOfQueryLoop":true,"className":"carte-image"} -->
<!-- wp:woocommerce/product-sale-badge {"align":"right","className":"carte-badge"} /-->
<!-- /wp:woocommerce/product-image -->

<!-- wp:group {"className":"carte-infos","layout":{"type":"default"}} -->
<div class="wp-block-group carte-infos"><!-- wp:post-title {"level":3,"isLink":true,"className":"carte-titre","__woocommerceNamespace":"woocommerce/product-collection/product-title"} /-->

<!-- wp:woocommerce/product-price {"isDescendentOfQueryLoop":true,"className":"carte-prix"} /--></div>
<!-- /wp:group -->

<!-- wp:woocommerce/product-button {"isDescendentOfQueryLoop":true,"className":"carte-bouton"} /-->
<!-- /wp:woocommerce/product-template -->

<!-- wp:query-pagination {"className":"pagination","layout":{"type":"flex","justifyContent":"center"}} -->
<!-- wp:query-pagination-previous /-->

<!-- wp:query-pagination-numbers /-->

<!-- wp:query-pagination-next /-->
<!-- /wp:query-pagination -->

<!-- wp:woocommerce/product-collection-no-results {"className":"aucun-resultat"} -->
<!-- wp:paragraph {"className":"aucun-resultat-texte"} -->
<p class="aucun-resultat-texte">Aucun produit ne correspond à votre sélection.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"b-plein"} -->
<div class="wp-block-button b-plein"><a class="wp-block-button__link wp-element-button" href="/boutique/">Voir toute la boutique</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->
<!-- /wp:woocommerce/product-collection-no-results --></div>
<!-- /wp:woocommerce/product-collection --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
