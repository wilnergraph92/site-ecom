<?php
/**
 * Title: Produits mis en avant
 * Slug: mon-theme/produits-vedettes
 * Categories: mon-theme
 * Inserter: true
 *
 * Section d'accueil : les produits cochés « mis en avant » dans WooCommerce
 * (Produits → étoile). Même carte produit que le catalogue. Généré dans
 * l'éditeur de l'atelier — ne pas réécrire le balisage à la main (porte 2).
 */
?>
<!-- wp:group {"className":"wrap section-produits","layout":{"type":"default"}} -->
<div class="wp-block-group wrap section-produits"><!-- wp:group {"className":"section-produits-tete","layout":{"type":"default"}} -->
<div class="wp-block-group section-produits-tete"><!-- wp:heading {"className":"section-titre"} -->
<h2 class="wp-block-heading section-titre">Nos incontournables</h2>
<!-- /wp:heading -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/boutique/">Voir toute la boutique</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->

<!-- wp:woocommerce/product-collection {"queryId":21,"query":{"perPage":4,"pages":1,"offset":0,"postType":"product","order":"asc","orderBy":"title","search":"","exclude":[],"inherit":false,"taxQuery":{},"isProductCollectionBlock":true,"featured":true,"woocommerceOnSale":false,"woocommerceStockStatus":["instock","outofstock","onbackorder"],"woocommerceAttributes":[],"woocommerceHandPickedProducts":[],"filterable":false,"relatedBy":{"categories":true,"tags":true}},"tagName":"div","displayLayout":{"type":"flex","columns":4,"shrinkColumns":true},"dimensions":{"widthType":"fill"},"collection":"woocommerce/product-collection/featured","hideControls":["inherit","featured","filterable"],"queryContextIncludes":["collection"],"className":"collection-vedettes"} -->
<div class="wp-block-woocommerce-product-collection collection-vedettes"><!-- wp:woocommerce/product-template {"className":"grille-produits"} -->
<!-- wp:woocommerce/product-image {"showSaleBadge":false,"imageSizing":"thumbnail","isDescendentOfQueryLoop":true,"className":"carte-image"} -->
<!-- wp:woocommerce/product-sale-badge {"align":"right","className":"carte-badge"} /-->
<!-- /wp:woocommerce/product-image -->

<!-- wp:group {"className":"carte-infos","layout":{"type":"default"}} -->
<div class="wp-block-group carte-infos"><!-- wp:post-title {"level":3,"isLink":true,"className":"carte-titre","__woocommerceNamespace":"woocommerce/product-collection/product-title"} /-->

<!-- wp:woocommerce/product-price {"isDescendentOfQueryLoop":true,"className":"carte-prix"} /--></div>
<!-- /wp:group -->

<!-- wp:woocommerce/product-button {"isDescendentOfQueryLoop":true,"className":"carte-bouton"} /-->
<!-- /wp:woocommerce/product-template --></div>
<!-- /wp:woocommerce/product-collection --></div>
<!-- /wp:group -->
