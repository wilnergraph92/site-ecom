<?php
/**
 * Fonctions du thème.
 *
 * @package mon-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'after_setup_theme',
	function () {
		add_theme_support( 'wp-block-styles' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'editor-styles' );
		// editor.css corrige ce que le canevas de Gutenberg casse (position:relative
		// forcé sur chaque bloc) : sans lui, l'aperçu s'effondre. Porte 5 de la recette.
		add_editor_style( array( 'style.css', 'assets/editor.css' ) );
	}
);

/**
 * Pont « Styles globaux → CSS du thème » pour les polices.
 *
 * Les composants n'emploient que deux variables : --police-titres et
 * --police-texte. Elles valent ce que le client choisit dans Apparence →
 * Éditeur → Styles → Typographie (Titres, Texte) — y compris une police
 * installée depuis la bibliothèque de polices. Sans ce pont, seuls les h1-h6 et
 * le corps suivraient ; prix, boutons, menus et badges garderaient l'ancienne.
 *
 * Ne JAMAIS redéfinir --police-titres / --police-texte dans style.css : la
 * définition de la maquette est supprimée en phase 4, c'est ce pont qui la pose.
 */
function mon_theme_police( $chemin, $repli ) {
	$valeur = wp_get_global_styles( $chemin );
	if ( ! is_string( $valeur ) || '' === $valeur ) {
		return $repli;
	}
	if ( 0 === strpos( $valeur, 'var:' ) ) { // var:preset|font-family|inter
		$valeur = 'var(--wp--' . str_replace( '|', '--', substr( $valeur, 4 ) ) . ')';
	}
	return preg_replace( '/[^A-Za-z0-9\s\-_,\'"().]/', '', $valeur );
}

function mon_theme_pont_css() {
	return sprintf(
		':root{--police-titres:%s;--police-texte:%s}',
		mon_theme_police( array( 'elements', 'heading', 'typography', 'fontFamily' ), 'var(--wp--preset--font-family--titres)' ),
		mon_theme_police( array( 'typography', 'fontFamily' ), 'var(--wp--preset--font-family--texte)' )
	);
}

add_action(
	'wp_enqueue_scripts',
	function () {
		$version = wp_get_theme()->get( 'Version' );

		// Les polices ne passent PAS par un <link> Google Fonts : elles sont
		// déclarées dans theme.json (fontFace, fichiers dans assets/fonts/), ce
		// qui les charge aussi dans l'éditeur et les rend modifiables dans Styles.
		wp_enqueue_style( 'mon-theme', get_stylesheet_uri(), array(), $version );
		wp_add_inline_style( 'mon-theme', mon_theme_pont_css() );

		// Ne jamais enqueue un fichier absent : 404 sur chaque page sinon.
		if ( file_exists( get_theme_file_path( 'assets/app.js' ) ) ) {
			wp_enqueue_script(
				'mon-theme',
				get_theme_file_uri( 'assets/app.js' ),
				array(),
				$version,
				array( 'strategy' => 'defer' )
			);
		}
	}
);

// Le même pont dans le canevas de l'éditeur (iframe) : l'aperçu reste fidèle.
add_action(
	'enqueue_block_assets',
	function () {
		if ( ! is_admin() ) {
			return;
		}
		wp_register_style( 'mon-theme-pont', false, array(), null );
		wp_enqueue_style( 'mon-theme-pont' );
		wp_add_inline_style( 'mon-theme-pont', mon_theme_pont_css() );
	}
);

add_action(
	'init',
	function () {
		register_block_pattern_category(
			'mon-theme',
			array( 'label' => __( 'Mon thème', 'mon-theme' ) )
		);
	}
);

/**
 * Livraison : le seuil de gratuité atteint, le forfait payant n'est plus
 * proposé. Sans ce filtre, une commande au-dessus du seuil affiche « Livraison
 * offerte » ET le forfait payant, et le client se demande ce que la gratuite
 * n'inclut pas. (boutique.php place déjà la gratuité en tête : présélectionnée.)
 */
add_filter(
	'woocommerce_package_rates',
	function ( $tarifs ) {
		foreach ( $tarifs as $tarif ) {
			if ( 'free_shipping' === $tarif->method_id ) {
				return array_filter(
					$tarifs,
					function ( $t ) {
						return 'flat_rate' !== $t->method_id;
					}
				);
			}
		}
		return $tarifs;
	},
	100
);

/**
 * Contact Form 7 : pas de <p> ni de <br> ajoutés autour des champs — le gabarit
 * du formulaire (contenus/formulaire-contact.txt) porte déjà la mise en page.
 */
add_filter( 'wpcf7_autop_or_not', '__return_false' );

/**
 * Attributs produits (pa_metal, pa_taille…) exposés à l'API REST : sans cela,
 * un bloc « Termes » (core/post-terms) sur un attribut — pastilles de métal,
 * matière sous le titre d'une carte — affiche « Termes introuvables » dans
 * l'éditeur de site, alors que le site les affiche bien.
 */
add_filter(
	'register_taxonomy_args',
	function ( $args, $taxonomie ) {
		if ( 0 === strpos( $taxonomie, 'pa_' ) ) {
			$args['show_in_rest'] = true;
		}
		return $args;
	},
	10,
	2
);
