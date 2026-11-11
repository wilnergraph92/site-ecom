<?php
/**
 * Déploiement d'une boutique — UN SEUL fichier pour l'atelier et la production.
 *
 * Atelier (Playground) : chargé par le blueprint généré par atelier.sh.
 * Production (Novamira) : envoyé tel quel à novamira/execute-php, première
 * ligne « <?php » retirée, suivi d'un appel, par exemple :
 *
 *   return c2woo_deployer( json_decode( file_get_contents(
 *     ABSPATH . 'wp-content/uploads/paquet.json' ), true ), 'production', true );
 *
 * FEU VERT : en production, c2woo_preparer() et c2woo_deployer() refusent
 * d'écrire sans leur dernier argument à true. Ne le passer QU'APRÈS le « oui »
 * explicite du client au message de feu vert (phases.md, phase 5, étape 0).
 * La validation de la maquette ne vaut pas feu vert.
 *
 * Idempotent : chaque étape retrouve ce qu'elle a déjà créé (slug, SKU, nom de
 * zone…) et le met à jour au lieu de le dupliquer. On peut relancer à volonté.
 *
 * Étapes, dans l'ordre de la phase 5 :
 *   c2woo_etat_des_lieux() 0 · lecture seule : ce que contient le site, pour le message de feu vert
 *   c2woo_preparer()     0 · production : français, WooCommerce et extensions (dépôt officiel)
 *   c2woo_sauvegarder()  0 · sauvegarde protégée + garde-fous (boutique déjà en activité ?)
 *   c2woo_medias()       3 · placeholders et logo → médiathèque
 *   c2woo_reglages()     4 · WooCommerce : devise, TVA, livraison, paiement de test…
 *   c2woo_catalogue()    6 · catégories, attributs, produits (clé : SKU)
 *   c2woo_formulaires()  7 · formulaires Contact Form 7 (avant les pages)
 *   c2woo_pages()        7 · pages éditoriales, pages WooCommerce, pages légales
 *   c2woo_accueil()      8 · accueil statique + permaliens
 *
 *   c2woo_purger()       9 · caches : compositions, thème, theme.json, objet
 *
 * Outils de recette :
 *   c2woo_porte1()                      inventaire des blocs, pages ET templates
 *   c2woo_porte7()                      conformité de base d'une boutique française
 *   c2woo_blocs_disponibles()           blocs woocommerce/* réellement enregistrés
 *   c2woo_templates_en_base( $theme )   personnalisations de templates en base
 *   c2woo_annuler_commande_test( $id )  annulée (stock rendu) puis supprimée
 *
 * Clôture :
 *   c2woo_revoquer_acces()              révoque le mot de passe d'application de
 *                                       l'appel en cours — TOUJOURS le dernier appel
 *
 * @package claude-to-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'c2woo_deployer' ) ) :

	/**
	 * Toute la phase 5 côté base, dans l'ordre. $cible : 'atelier' ou 'production'.
	 */
	function c2woo_deployer( $p, $cible = 'production', $feu_vert_client = false ) {
		if ( 'atelier' !== $cible && true !== $feu_vert_client ) {
			return c2woo_sans_feu_vert();
		}
		if ( ! is_array( $p ) ) {
			return array( 'erreur' => 'Paquet illisible : vérifier paquet.json (paquet.py).' );
		}
		if ( ! class_exists( 'WooCommerce' ) ) {
			return array( 'erreur' => 'WooCommerce absent : installer et activer woocommerce d’abord.' );
		}
		$r = array( 'cible' => $cible, 'avertissements' => array() );

		$r['sauvegarde'] = c2woo_sauvegarder( $p, $cible );
		$r['medias']     = c2woo_medias( $p );
		$r['reglages']   = c2woo_reglages( $p, $cible, $r['medias'] );

		if ( ! empty( $r['sauvegarde']['boutique_existante'] ) && empty( $p['forcer_catalogue'] ) ) {
			$r['avertissements'][] = 'Des produits existent déjà : catalogue de démonstration NON importé (forcer_catalogue pour passer outre).';
		} else {
			$r['catalogue'] = c2woo_catalogue( $p );
		}

		$r['formulaires'] = c2woo_formulaires( $p );
		$r['pages']   = c2woo_pages( $p, $r['medias'] );
		$r['accueil'] = c2woo_accueil( $p );
		$r['purge']   = c2woo_purger();

		if ( ! empty( $r['sauvegarde']['commandes'] ) ) {
			$r['avertissements'][] = $r['sauvegarde']['commandes'] . ' commande(s) existante(s) : aucune n’a été touchée.';
		}
		if ( 'yes' === get_option( 'woocommerce_coming_soon' ) ) {
			$r['avertissements'][] = 'Mode « Bientôt disponible » actif : invisible déconnecté. Le lever = mise en ligne, à demander au client.';
		}
		return $r;
	}

	/**
	 * Refus commun aux écritures de production lancées sans le feu vert du client.
	 */
	function c2woo_sans_feu_vert() {
		return array(
			'erreur' => 'Feu vert du client absent : rien n’a été écrit. Envoyer d’abord le message de feu vert '
				. '(phases.md, phase 5, étape 0), attendre un « oui » explicite, puis relancer avec le dernier argument à true. '
				. 'La validation de la maquette ne vaut pas feu vert.',
		);
	}

	// ------------------------------------------------------------------ 0
	/**
	 * Phase 5, étape 0 — LECTURE SEULE. Ce que contient le site du client, pour
	 * rédiger le message de feu vert. N'écrit rien, n'installe rien.
	 */
	function c2woo_etat_des_lieux() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$compte = function ( $type ) {
			if ( ! post_type_exists( $type ) ) {
				return 0;
			}
			$n = wp_count_posts( $type );
			return (int) ( $n->publish ?? 0 ) + (int) ( $n->draft ?? 0 ) + (int) ( $n->private ?? 0 );
		};
		$commandes = null;
		if ( function_exists( 'wc_get_orders' ) ) {
			$res       = wc_get_orders( array( 'limit' => 1, 'return' => 'ids', 'paginate' => true, 'status' => array_keys( wc_get_order_statuses() ) ) );
			$commandes = (int) $res->total;
		}
		$actives = (array) get_option( 'active_plugins', array() );
		$ext     = array();
		foreach ( get_plugins() as $f => $d ) {
			$ext[ dirname( $f ) ] = $d['Version'] . ( in_array( $f, $actives, true ) ? '' : ' (inactive)' );
		}
		$produits = $compte( 'product' );
		$pages    = $compte( 'page' );
		$articles = $compte( 'post' );
		return array(
			'site'        => home_url( '/' ),
			'nom'         => get_option( 'blogname' ),
			'wordpress'   => get_bloginfo( 'version' ),
			'langue'      => get_locale(),
			'theme'       => get_stylesheet(),
			'woocommerce' => defined( 'WC_VERSION' ) ? WC_VERSION : null,
			'extensions'  => $ext,
			'pages'       => $pages,
			'articles'    => $articles,
			'produits'    => $produits,
			'commandes'   => $commandes,
			// Le cas du tutoriel : WordPress + Novamira fraîchement installés.
			'site_vierge' => 0 === $produits && empty( $commandes ) && $pages <= 2 && $articles <= 1,
		);
	}

	// ------------------------------------------------------------------ 0 bis
	/**
	 * Production, AVANT tout le reste (phase 5, étape 0) : site en français,
	 * puis WooCommerce et les extensions du paquet, installés depuis le dépôt
	 * officiel wordpress.org et activés. Dans cet ordre : WooCommerce activé sur
	 * un site encore en anglais crée /shop/, /cart/ et des textes anglais.
	 * Novamira n'expose pas d'installation d'extension : Plugin_Upgrader, le
	 * mécanisme natif de WordPress. Idempotent. Ne touche jamais à Novamira.
	 * $version_wc : aligner sur l'atelier (vide = dernière version stable).
	 */
	function c2woo_preparer( $p, $version_wc = '', $feu_vert_client = false ) {
		if ( true !== $feu_vert_client ) {
			return c2woo_sans_feu_vert();
		}
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/misc.php';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/translation-install.php';
		$out = array();

		if ( 'fr_FR' !== get_locale() ) {
			$pack = wp_download_language_pack( 'fr_FR' );
			if ( $pack ) {
				update_option( 'WPLANG', 'fr_FR' );
				switch_to_locale( 'fr_FR' );
			}
			$out['langue'] = $pack ? 'fr_FR installé et activé' : 'ÉCHEC du paquet fr_FR : le poser depuis Réglages → Général avant de continuer';
		} else {
			$out['langue'] = 'fr_FR';
		}

		$slugs = array_values( array_unique( array_merge( array( 'woocommerce' ), (array) ( $p['extensions'] ?? array() ) ) ) );
		foreach ( $slugs as $slug ) {
			$fichier = null;
			foreach ( array_keys( get_plugins() ) as $f ) {
				if ( 0 === strpos( $f, $slug . '/' ) ) {
					$fichier = $f;
				}
			}
			if ( ! $fichier ) {
				$api = plugins_api( 'plugin_information', array( 'slug' => $slug, 'fields' => array( 'sections' => false ) ) );
				if ( is_wp_error( $api ) ) {
					$out[ $slug ] = 'introuvable : ' . $api->get_error_message();
					continue;
				}
				$lien = $api->download_link;
				if ( 'woocommerce' === $slug && $version_wc ) {
					$lien = 'https://downloads.wordpress.org/plugin/woocommerce.' . $version_wc . '.zip';
				}
				$r = ( new Plugin_Upgrader( new Automatic_Upgrader_Skin() ) )->install( $lien );
				if ( is_wp_error( $r ) || ! $r ) {
					$out[ $slug ] = 'installation échouée' . ( is_wp_error( $r ) ? ' : ' . $r->get_error_message() : '' );
					continue;
				}
				wp_clean_plugins_cache();
				foreach ( array_keys( get_plugins() ) as $f ) {
					if ( 0 === strpos( $f, $slug . '/' ) ) {
						$fichier = $f;
					}
				}
			}
			$a            = $fichier ? activate_plugin( $fichier ) : new WP_Error( 'absent', 'fichier introuvable' );
			$versions     = get_plugins();
			$out[ $slug ] = is_wp_error( $a ) ? 'activation : ' . $a->get_error_message() : 'actif ' . ( $versions[ $fichier ]['Version'] ?? '' );
		}
		$out['wordpress'] = get_bloginfo( 'version' );
		return $out;
	}

	// ------------------------------------------------------------------ 0
	function c2woo_sauvegarder( $p, $cible ) {
		global $wpdb;
		$produits  = (int) wp_count_posts( 'product' )->publish + (int) wp_count_posts( 'product' )->draft;
		$commandes = 0;
		if ( function_exists( 'wc_get_orders' ) ) {
			$res       = wc_get_orders( array( 'limit' => 1, 'return' => 'ids', 'paginate' => true, 'status' => array_keys( wc_get_order_statuses() ) ) );
			$commandes = (int) $res->total;
		}
		$slugs_catalogue = wp_list_pluck( (array) ( $p['produits'] ?? array() ), 'sku' );
		$deja_a_nous     = 0;
		foreach ( $slugs_catalogue as $sku ) {
			if ( wc_get_product_id_by_sku( $sku ) ) {
				$deja_a_nous++;
			}
		}
		$out = array(
			'produits'           => $produits,
			'commandes'          => $commandes,
			// des produits qui ne viennent pas de notre catalogue = boutique réelle
			'boutique_existante' => $produits > $deja_a_nous,
		);
		if ( 'production' !== $cible ) {
			return $out;
		}
		$rows = $wpdb->get_results(
			"SELECT ID, post_name, post_title, post_status, post_type, post_content
			 FROM {$wpdb->posts} WHERE post_type IN ('page','post','wp_template','wp_template_part','wp_navigation')
			 AND post_status NOT IN ('auto-draft','revision')",
			ARRAY_A
		);
		// PIÈGE : dans uploads/, la sauvegarde est servie publiquement (contenus,
		// options). Dossier dédié, fermé au web (LiteSpeed et Apache lisent le
		// .htaccess ; un index vide coupe le listage partout ailleurs).
		$dossier = WP_CONTENT_DIR . '/c2woo-sauvegardes';
		if ( ! is_dir( $dossier ) ) {
			wp_mkdir_p( $dossier );
			file_put_contents( $dossier . '/.htaccess', "Require all denied\nDeny from all\n" );
			file_put_contents( $dossier . '/index.php', "<?php // Silence.\n" );
		}
		$fichier = $dossier . '/sauvegarde-avant-boutique-' . gmdate( 'Ymd-His' ) . '.json';
		file_put_contents(
			$fichier,
			wp_json_encode(
				array(
					'date'          => current_time( 'mysql' ),
					'theme_avant'   => get_stylesheet(),
					'show_on_front' => get_option( 'show_on_front' ),
					'page_on_front' => get_option( 'page_on_front' ),
					'titre_site'    => get_option( 'blogname' ),
					'woocommerce'   => array(
						'produits'  => $produits,
						'commandes' => $commandes,
						'pages'     => array(
							'boutique'   => get_option( 'woocommerce_shop_page_id' ),
							'panier'     => get_option( 'woocommerce_cart_page_id' ),
							'commande'   => get_option( 'woocommerce_checkout_page_id' ),
							'mon-compte' => get_option( 'woocommerce_myaccount_page_id' ),
						),
					),
					'contenus'      => $rows,
				),
				JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
			)
		);
		$out['fichier'] = str_replace( ABSPATH, '', $fichier );
		return $out;
	}

	// ------------------------------------------------------------------ 3
	/**
	 * Chaque visuel de theme/<slug>/assets/img/ listé dans $p['medias'] entre en
	 * médiathèque une seule fois (retrouvé par son slug). Retourne slug → [id, url].
	 */
	function c2woo_medias( $p ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$theme = $p['theme'] ?? get_stylesheet();
		$dir   = get_theme_root() . '/' . $theme . '/assets/img/';
		$table = array();
		foreach ( (array) ( $p['medias'] ?? array() ) as $slug => $alt ) {
			if ( is_int( $slug ) ) { // liste simple : ["placeholder-1x1", …]
				$slug = $alt;
				$alt  = '';
			}
			$exist = get_posts(
				array(
					'post_type'   => 'attachment',
					'name'        => $slug,
					'post_status' => 'inherit',
					'numberposts' => 1,
				)
			);
			if ( $exist ) {
				$id = $exist[0]->ID;
			} else {
				$fichier = null;
				foreach ( array( 'png', 'jpg', 'jpeg', 'webp' ) as $ext ) {
					if ( file_exists( $dir . $slug . '.' . $ext ) ) {
						$fichier = $dir . $slug . '.' . $ext;
						break;
					}
				}
				if ( ! $fichier ) {
					$table[ $slug ] = array( 'erreur' => 'fichier absent : assets/img/' . $slug . '.(png|jpg|webp)' );
					continue;
				}
				$tmp = wp_tempnam( $slug );
				copy( $fichier, $tmp );
				$id = media_handle_sideload(
					array(
						'name'     => basename( $fichier ),
						'tmp_name' => $tmp,
					),
					0,
					$alt
				);
				if ( is_wp_error( $id ) ) {
					$table[ $slug ] = array( 'erreur' => $id->get_error_message() );
					continue;
				}
				wp_update_post( array( 'ID' => $id, 'post_name' => $slug ) );
			}
			if ( $alt ) {
				update_post_meta( $id, '_wp_attachment_image_alt', $alt );
			}
			$table[ $slug ] = array( 'id' => $id, 'url' => wp_get_attachment_url( $id ) );
		}

		// Logo du site : le bloc core/site-logo le lit ici. Remplaçable en un clic.
		$logo = $p['logo'] ?? null;
		if ( $logo && ! empty( $table[ $logo ]['id'] ) ) {
			update_option( 'site_logo', $table[ $logo ]['id'] );
			set_theme_mod( 'custom_logo', $table[ $logo ]['id'] );
		}
		// Icône du site (favicon) : l'initiale de la marque (visuels.py favicon).
		// Remplaçable dans Réglages → Général → Icône du site.
		$icone = $p['favicon'] ?? null;
		if ( $icone && ! empty( $table[ $icone ]['id'] ) ) {
			update_option( 'site_icon', $table[ $icone ]['id'] );
		}
		return $table;
	}

	// ------------------------------------------------------------------ 4
	function c2woo_reglages( $p, $cible, $medias = array() ) {
		$g   = (array) ( $p['reglages'] ?? array() );
		$tva = (float) ( $g['tva'] ?? 20 );
		$out = array();

		// Nom et slogan du site : l'atelier les pose par son blueprint, la production
		// garde sinon ceux de l'hébergeur (« blanchedalmond-walrus-280784… » dans
		// les onglets, les e-mails de commande et les résultats de recherche).
		if ( ! empty( $p['marque'] ) ) {
			update_option( 'blogname', $p['marque'] );
		}
		if ( isset( $g['slogan'] ) ) {
			update_option( 'blogdescription', $g['slogan'] );
		}

		// Ne pas laisser WooCommerce détourner wp-admin vers son assistant.
		update_option( 'woocommerce_onboarding_profile', array( 'skipped' => true, 'completed' => true ) );
		update_option( 'woocommerce_task_list_hidden', 'yes' );
		update_option( 'woocommerce_show_marketplace_suggestions', 'no' );
		update_option( 'woocommerce_allow_tracking', 'no' );
		delete_transient( '_wc_activation_redirect' );

		// Dates, heures, semaine, fuseau : à la française (« 24 septembre 2026 »),
		// sinon WordPress installé en anglais affiche « septembre 24, 2026 ».
		if ( ! isset( $g['regional'] ) || $g['regional'] ) {
			update_option( 'date_format', 'j F Y' );
			update_option( 'time_format', 'H:i' );
			update_option( 'start_of_week', 1 );
			update_option( 'timezone_string', $g['fuseau'] ?? 'Europe/Paris' );
		}

		// Boutique, devise, format des prix à la française : 1 290,00 €
		$opts = array(
			'woocommerce_default_country'               => $g['pays'] ?? 'FR',
			'woocommerce_store_address'                 => $g['adresse'] ?? '',
			'woocommerce_store_city'                    => $g['ville'] ?? '',
			'woocommerce_store_postcode'                => $g['code_postal'] ?? '',
			'woocommerce_allowed_countries'             => 'specific',
			'woocommerce_specific_allowed_countries'    => (array) ( $g['pays_de_vente'] ?? array( $g['pays'] ?? 'FR' ) ),
			'woocommerce_ship_to_countries'             => '',
			'woocommerce_currency'                      => $g['devise'] ?? 'EUR',
			'woocommerce_currency_pos'                  => 'right_space',
			'woocommerce_price_thousand_sep'            => "\u{00A0}",
			'woocommerce_price_decimal_sep'             => ',',
			'woocommerce_price_num_decimals'            => 2,
			// TVA : prix saisis et affichés TTC
			'woocommerce_calc_taxes'                    => 'yes',
			'woocommerce_prices_include_tax'            => 'yes',
			'woocommerce_tax_display_shop'              => 'incl',
			'woocommerce_tax_display_cart'              => 'incl',
			'woocommerce_tax_total_display'             => 'single',
			'woocommerce_tax_round_at_subtotal'         => 'no',
			// Commande et compte client
			'woocommerce_enable_guest_checkout'         => 'yes',
			'woocommerce_enable_checkout_login_reminder' => 'yes',
			'woocommerce_enable_signup_and_login_from_checkout' => 'yes',
			'woocommerce_enable_myaccount_registration' => empty( $g['inscription_mon_compte'] ) ? 'no' : 'yes',
			'woocommerce_registration_generate_password' => 'yes',
			// Vignettes produit au ratio du design (placeholder compris)
			'woocommerce_thumbnail_image_width'         => (int) ( $g['largeur_vignette'] ?? 600 ),
			'woocommerce_single_image_width'            => (int) ( $g['largeur_fiche'] ?? 1200 ),
			// Produits par page du catalogue = colonnes × rangées. La grille hérite
			// de la requête de la page : à 4 × 4 (défaut), une grille de 3 colonnes
			// finit sur une carte orpheline.
			'woocommerce_catalog_columns'               => (int) ( $g['catalogue']['colonnes'] ?? 3 ),
			'woocommerce_catalog_rows'                  => (int) ( $g['catalogue']['rangees'] ?? 4 ),
		);
		if ( ! empty( $g['ratio_vignettes'] ) && preg_match( '/^(\d+)\s*[:x]\s*(\d+)$/', $g['ratio_vignettes'], $m ) ) {
			$opts['woocommerce_thumbnail_cropping']               = 'custom';
			$opts['woocommerce_thumbnail_cropping_custom_width']  = $m[1];
			$opts['woocommerce_thumbnail_cropping_custom_height'] = $m[2];
		}
		foreach ( $opts as $k => $v ) {
			update_option( $k, $v );
		}

		// Bases d'URL des produits. WooCommerce installé AVANT le passage en
		// français les crée en anglais (/product/, /product-category/) : on les
		// fixe explicitement. Les règles se régénèrent à la requête suivante.
		if ( ! empty( $g['permaliens'] ) ) {
			$pl = (array) get_option( 'woocommerce_permalinks', array() );
			$pl['product_base']  = $g['permaliens']['produit'] ?? ( $pl['product_base'] ?? 'produit' );
			$pl['category_base'] = $g['permaliens']['categorie'] ?? ( $pl['category_base'] ?? 'categorie-produit' );
			$pl['tag_base']      = $g['permaliens']['etiquette'] ?? ( $pl['tag_base'] ?? 'etiquette-produit' );
			update_option( 'woocommerce_permalinks', $pl );
			$out['permaliens'] = $pl;

			// Points de terminaison (commande reçue, Mon compte…) : WooCommerce les
			// garde en anglais quelle que soit la langue — la cliente arrive sur
			// /commande/order-received/72/. On les passe en français ;
			// « points » dans permaliens permet d'en changer un ou plusieurs.
			$points = array_merge(
				array(
					'checkout_pay'            => 'paiement',
					'checkout_order_received' => 'commande-recue',
					'myaccount_orders'        => 'commandes',
					'myaccount_view_order'    => 'voir-commande',
					'myaccount_downloads'     => 'telechargements',
					'myaccount_edit_account'  => 'modifier-compte',
					'myaccount_edit_address'  => 'adresses',
					'myaccount_payment_methods' => 'moyens-de-paiement',
					'myaccount_lost_password' => 'mot-de-passe-perdu',
					'logout'                  => 'deconnexion',
				),
				(array) ( $g['permaliens']['points'] ?? array() )
			);
			foreach ( $points as $cle => $slug ) {
				update_option( 'woocommerce_' . $cle . '_endpoint', sanitize_title( $slug ) );
			}
			$out['points_de_terminaison'] = $points;
			update_option( 'woocommerce_queue_flush_rewrite_rules', 'yes' );
		}

		// Textes de confidentialité créés en anglais quand WooCommerce a été installé
		// avant le passage du site en français : on les remplace par leur version
		// française (le client les ajuste dans Réglages → Comptes et confidentialité).
		$fr = array(
			'woocommerce_registration_privacy_policy_text' => 'Vos données personnelles seront utilisées pour vous accompagner au cours de votre visite du site web, gérer l’accès à votre compte, et pour d’autres raisons décrites dans notre [privacy_policy].',
			'woocommerce_checkout_privacy_policy_text'     => 'Vos données personnelles seront utilisées pour le traitement de votre commande, vous accompagner au cours de votre visite du site web, et pour d’autres raisons décrites dans notre [privacy_policy].',
		);
		foreach ( $fr as $cle => $texte ) {
			if ( 0 === strpos( (string) get_option( $cle, '' ), 'Your personal data' ) ) {
				update_option( $cle, $texte );
			}
		}

		// Placeholder produit : les produits n'ont PAS d'image, WooCommerce affiche
		// celui-ci partout (grilles, fiche, panier, mini-panier, e-mails).
		$ph = $g['placeholder_produit'] ?? 'placeholder-1x1';
		if ( ! empty( $medias[ $ph ]['id'] ) ) {
			update_option( 'woocommerce_placeholder_image', $medias[ $ph ]['id'] );
			$out['placeholder_produit'] = $medias[ $ph ]['id'];
		}

		// Taux de TVA standard du pays, créé une seule fois.
		global $wpdb;
		$pays   = $g['pays'] ?? 'FR';
		$existe = $wpdb->get_var( $wpdb->prepare( "SELECT tax_rate_id FROM {$wpdb->prefix}woocommerce_tax_rates WHERE tax_rate_country = %s AND tax_rate_class = ''", $pays ) );
		if ( ! $existe ) {
			$existe = WC_Tax::_insert_tax_rate(
				array(
					'tax_rate_country'  => $pays,
					'tax_rate_state'    => '',
					'tax_rate'          => number_format( $tva, 4, '.', '' ),
					'tax_rate_name'     => 'TVA',
					'tax_rate_priority' => 1,
					'tax_rate_compound' => 0,
					'tax_rate_shipping' => 1,
					'tax_rate_order'    => 0,
					'tax_rate_class'    => '',
				)
			);
		}
		$out['tva'] = (int) $existe;

		// Livraison : une zone, un forfait, une gratuité au-delà d'un seuil.
		// PIÈGE : WooCommerce saisit les frais de port HORS TAXE, même quand les
		// prix sont TTC. 5,90 € TTC se saisit 4,9167.
		$l = (array) ( $g['livraison'] ?? array() );
		if ( $l ) {
			$nom  = $l['zone'] ?? 'France';
			$zone = null;
			foreach ( WC_Shipping_Zones::get_zones() as $z ) {
				if ( $z['zone_name'] === $nom ) {
					$zone = new WC_Shipping_Zone( $z['id'] );
				}
			}
			if ( ! $zone ) {
				$zone = new WC_Shipping_Zone();
				$zone->set_zone_name( $nom );
				$zone->add_location( $pays, 'country' );
				$zone->save();
			}
			$methodes = array();
			foreach ( $zone->get_shipping_methods() as $m ) {
				$methodes[ $m->id ] = $m->instance_id;
			}
			if ( isset( $l['forfait'] ) ) {
				$iid = $methodes['flat_rate'] ?? $zone->add_shipping_method( 'flat_rate' );
				update_option(
					'woocommerce_flat_rate_' . $iid . '_settings',
					array(
						'title'      => $l['titre_forfait'] ?? 'Livraison standard',
						'tax_status' => 'taxable',
						'cost'       => (string) round( (float) $l['forfait'] / ( 1 + $tva / 100 ), 4 ),
					)
				);
			}
			if ( isset( $l['gratuite_des'] ) ) {
				$iid = $methodes['free_shipping'] ?? $zone->add_shipping_method( 'free_shipping' );
				update_option(
					'woocommerce_free_shipping_' . $iid . '_settings',
					array(
						'title'            => $l['titre_gratuite'] ?? 'Livraison offerte',
						'requires'         => 'min_amount',
						'min_amount'       => (string) $l['gratuite_des'],
						'ignore_discounts' => 'no',
					)
				);
			}
			// PIÈGE : WooCommerce présélectionne la PREMIÈRE méthode disponible de la
			// zone. Forfait en tête, un panier de 740 € affiche 7,90 € de port alors
			// que la livraison est offerte dès 300 € — la gratuité passe donc devant.
			global $wpdb;
			$ordre = 0;
			foreach ( array( 'free_shipping', 'flat_rate' ) as $id_methode ) {
				foreach ( $zone->get_shipping_methods() as $m ) {
					if ( $m->id === $id_methode ) {
						$wpdb->update( $wpdb->prefix . 'woocommerce_shipping_zone_methods', array( 'method_order' => ++$ordre ), array( 'instance_id' => $m->instance_id ) );
					}
				}
			}
			WC_Cache_Helper::get_transient_version( 'shipping', true );
			$out['zone_livraison'] = $zone->get_id();
		}

		// Paiement de test : hors ligne, aucune clé, aucun argent. Stripe/PayPal
		// se branchent par le client (l'agent ne saisit jamais de clé d'API).
		foreach ( (array) ( $g['paiements_test'] ?? array( 'bacs' ) ) as $gw ) {
			$cle = 'woocommerce_' . $gw . '_settings';
			$v   = (array) get_option( $cle, array() );
			$v['enabled'] = 'yes';
			if ( 'bacs' === $gw && empty( $v['title'] ) ) {
				$v['title'] = 'Virement bancaire';
			}
			update_option( $cle, $v );
		}

		// Mode « Bientôt disponible » (WooCommerce ≥ 9.1, actif à l'installation).
		// Atelier : toujours levé. Production : levé seulement sur mise_en_ligne.
		if ( 'atelier' === $cible || ! empty( $p['mise_en_ligne'] ) ) {
			update_option( 'woocommerce_coming_soon', 'no' );
		} else {
			// PIÈGE : WooCommerce l'installe en « pages de la boutique seulement »
			// (store_pages_only = yes) — l'accueil, À propos, Contact restent
			// publics. Avant la mise en ligne, c'est tout le site qui attend.
			update_option( 'woocommerce_coming_soon', 'yes' );
			update_option( 'woocommerce_store_pages_only', 'no' );
		}
		$out['bientot_disponible'] = 'yes' === get_option( 'woocommerce_coming_soon' ) ? ( 'yes' === get_option( 'woocommerce_store_pages_only' ) ? 'yes (boutique seulement)' : 'yes (tout le site)' ) : 'no';
		return $out;
	}

	// ------------------------------------------------------------------ 6
	function c2woo_catalogue( $p ) {
		$out = array( 'categories' => array(), 'produits' => array() );

		// Catégories — les parents d'abord.
		$cats = (array) ( $p['categories'] ?? array() );
		usort( $cats, fn( $a, $b ) => (int) ! empty( $a['parent'] ) <=> (int) ! empty( $b['parent'] ) );
		foreach ( $cats as $c ) {
			$parent = 0;
			if ( ! empty( $c['parent'] ) ) {
				$t      = get_term_by( 'slug', $c['parent'], 'product_cat' );
				$parent = $t ? $t->term_id : 0;
			}
			$t = get_term_by( 'slug', $c['slug'], 'product_cat' );
			if ( $t ) {
				wp_update_term( $t->term_id, 'product_cat', array( 'name' => $c['nom'], 'description' => $c['description'] ?? '', 'parent' => $parent ) );
				$id = $t->term_id;
			} else {
				$res = wp_insert_term( $c['nom'], 'product_cat', array( 'slug' => $c['slug'], 'description' => $c['description'] ?? '', 'parent' => $parent ) );
				$id  = is_wp_error( $res ) ? 0 : $res['term_id'];
			}
			$out['categories'][ $c['slug'] ] = $id;
		}

		// Attributs globaux (pa_*) et leurs termes.
		foreach ( (array) ( $p['attributs'] ?? array() ) as $a ) {
			$slug = wc_sanitize_taxonomy_name( $a['slug'] );
			if ( ! wc_attribute_taxonomy_id_by_name( $slug ) ) {
				// archives : une page par terme (/metal/or-jaune/), servie par archive-product.html
				wc_create_attribute( array( 'name' => $a['nom'], 'slug' => $slug, 'type' => 'select', 'order_by' => 'menu_order', 'has_archives' => ! empty( $a['archives'] ) ) );
			}
			$tax = wc_attribute_taxonomy_name( $slug );
			if ( ! taxonomy_exists( $tax ) ) {
				// créée dans cette requête : WooCommerce ne l'enregistrera qu'à la suivante
				register_taxonomy( $tax, array( 'product', 'product_variation' ), array( 'hierarchical' => false, 'show_ui' => false, 'query_var' => true, 'rewrite' => false ) );
			}
			foreach ( (array) $a['termes'] as $i => $terme ) {
				$t = term_exists( $terme, $tax ) ?: wp_insert_term( $terme, $tax );
				if ( ! is_wp_error( $t ) ) {
					// ordre du catalogue (Petite, Moyenne, Grande), pas l'alphabet
					update_term_meta( (int) $t['term_id'], 'order', $i );
				}
			}
		}

		// Produits — clé : SKU. Aucune image : le placeholder WooCommerce s'affiche.
		$ids = array();
		foreach ( array_values( (array) ( $p['produits'] ?? array() ) ) as $rang => $d ) {
			$id   = wc_get_product_id_by_sku( $d['sku'] );
			$type = $d['type'] ?? 'simple';
			$prod = $id ? wc_get_product( $id ) : null;
			if ( ! $prod || $prod->get_type() !== $type ) {
				$prod = 'variable' === $type ? new WC_Product_Variable( $id ?: 0 ) : new WC_Product_Simple( $id ?: 0 );
			}
			$prod->set_name( $d['nom'] );
			$prod->set_slug( $d['slug'] ?? sanitize_title( $d['nom'] ) );
			$prod->set_status( 'publish' );
			$prod->set_catalog_visibility( 'visible' );
			$prod->set_sku( $d['sku'] );
			$prod->set_short_description( $d['extrait'] ?? '' );
			$prod->set_description( $d['description'] ?? '' );
			$prod->set_featured( ! empty( $d['vedette'] ) );
			$prod->set_menu_order( (int) ( $d['ordre'] ?? 0 ) );
			// Dates échelonnées dans l'ordre du fichier (le dernier produit est le plus
			// récent) : créés dans la même seconde, les produits sortiraient dans un
			// ordre arbitraire des collections « nouveautés » et du tri par date.
			if ( ! $id ) {
				$prod->set_date_created( time() - 60 * ( count( (array) $p['produits'] ) - $rang ) );
			}
			$cat_ids = array();
			foreach ( (array) ( $d['categories'] ?? array() ) as $cs ) {
				$t = get_term_by( 'slug', $cs, 'product_cat' );
				if ( $t ) {
					$cat_ids[] = $t->term_id;
				}
			}
			$prod->set_category_ids( $cat_ids );
			$tag_ids = array();
			foreach ( (array) ( $d['etiquettes'] ?? array() ) as $tn ) {
				$t = term_exists( $tn, 'product_tag' ) ?: wp_insert_term( $tn, 'product_tag' );
				if ( ! is_wp_error( $t ) ) {
					$tag_ids[] = (int) $t['term_id'];
				}
			}
			$prod->set_tag_ids( $tag_ids );
			if ( isset( $d['stock'] ) && is_numeric( $d['stock'] ) ) {
				$prod->set_manage_stock( true );
				$prod->set_stock_quantity( (int) $d['stock'] );
			} else {
				$prod->set_manage_stock( false );
				$prod->set_stock_status( $d['stock'] ?? 'instock' ); // instock | outofstock | onbackorder
			}
			if ( 'variable' !== $type ) {
				$prod->set_regular_price( (string) ( $d['prix'] ?? '' ) );
				$prod->set_sale_price( (string) ( $d['promo'] ?? '' ) );
			}
			// « attributs » : attributs de variation (produits variables).
			// « caracteristiques » : attributs affichés sans variation (tout type),
			// par exemple le métal d'un produit simple, repris en pastille sur la carte.
			$attrs = array();
			$lots  = array();
			if ( 'variable' === $type ) {
				$lots[] = array( (array) ( $d['attributs'] ?? array() ), true );
			}
			$lots[] = array( (array) ( $d['caracteristiques'] ?? array() ), false );
			foreach ( $lots as list( $liste, $variation ) ) {
				foreach ( $liste as $slug => $termes ) {
					$tax = wc_attribute_taxonomy_name( $slug );
					$att = new WC_Product_Attribute();
					$att->set_id( wc_attribute_taxonomy_id_by_name( $slug ) );
					$att->set_name( $tax );
					$tids = array();
					foreach ( (array) $termes as $tn ) {
						$t = get_term_by( 'name', $tn, $tax );
						if ( $t ) {
							$tids[] = $t->term_id;
						}
					}
					$att->set_options( $tids );
					$att->set_visible( true );
					$att->set_variation( $variation );
					$attrs[] = $att;
				}
			}
			if ( $attrs ) {
				$prod->set_attributes( $attrs );
			}
			$pid = $prod->save();

			if ( 'variable' === $type ) {
				foreach ( (array) ( $d['variations'] ?? array() ) as $v ) {
					$vid = wc_get_product_id_by_sku( $v['sku'] );
					$var = $vid ? new WC_Product_Variation( $vid ) : new WC_Product_Variation();
					$var->set_parent_id( $pid );
					$va = array();
					foreach ( (array) $v['attributs'] as $slug => $tn ) {
						$tax = wc_attribute_taxonomy_name( $slug );
						$t   = get_term_by( 'name', $tn, $tax );
						$va[ $tax ] = $t ? $t->slug : sanitize_title( $tn );
					}
					$var->set_attributes( $va );
					$var->set_sku( $v['sku'] );
					$var->set_regular_price( (string) $v['prix'] );
					$var->set_sale_price( (string) ( $v['promo'] ?? '' ) );
					$var->set_stock_status( $v['stock'] ?? 'instock' );
					$var->save();
				}
				WC_Product_Variable::sync( $pid );
			}
			$ids[ $d['sku'] ] = $pid;
		}

		// Ventes croisées et incitatives : second passage, tous les SKU existent.
		foreach ( (array) ( $p['produits'] ?? array() ) as $d ) {
			if ( empty( $d['complementaires'] ) && empty( $d['montee_en_gamme'] ) ) {
				continue;
			}
			$prod = wc_get_product( $ids[ $d['sku'] ] );
			$prod->set_cross_sell_ids( array_values( array_filter( array_map( 'wc_get_product_id_by_sku', (array) ( $d['complementaires'] ?? array() ) ) ) ) );
			$prod->set_upsell_ids( array_values( array_filter( array_map( 'wc_get_product_id_by_sku', (array) ( $d['montee_en_gamme'] ?? array() ) ) ) ) );
			$prod->save();
		}
		$out['produits'] = $ids;
		return $out;
	}

	// ------------------------------------------------------------------ 7
	/**
	 * Jetons remplacés dans tout contenu de page, pour qu'un même fichier vaille
	 * dans l'atelier et en production (les identifiants y diffèrent) :
	 *   {{media:slug}}  {{media-url:slug}}  {{produit:SKU}}  {{lien-produit:SKU}}
	 *   {{categorie:slug}}  {{lien-categorie:slug}}  {{page:slug}}  {{lien-page:slug}}
	 */
	function c2woo_jetons( $html, $medias ) {
		return preg_replace_callback(
			'/\{\{(media|media-url|produit|lien-produit|categorie|lien-categorie|page|lien-page|formulaire):([^}]+)\}\}/',
			function ( $m ) use ( $medias ) {
				list( , $type, $cle ) = $m;
				switch ( $type ) {
					case 'media':
						return (string) ( $medias[ $cle ]['id'] ?? 0 );
					case 'media-url':
						return (string) ( $medias[ $cle ]['url'] ?? '' );
					case 'produit':
						return (string) wc_get_product_id_by_sku( $cle );
					case 'lien-produit':
						$id = wc_get_product_id_by_sku( $cle );
						return $id ? wp_make_link_relative( get_permalink( $id ) ) : '#';
					case 'categorie':
					case 'lien-categorie':
						$t = get_term_by( 'slug', $cle, 'product_cat' );
						if ( ! $t ) {
							return 'categorie' === $type ? '0' : '#';
						}
						return 'categorie' === $type ? (string) $t->term_id : wp_make_link_relative( get_term_link( $t ) );
					case 'formulaire':
						$f = function_exists( 'wpcf7_get_contact_form_by_title' ) ? wpcf7_get_contact_form_by_title( $cle ) : null;
						return $f ? (string) ( $f->hash() ?: $f->id() ) : '';
					case 'page':
					case 'lien-page':
						$pg = get_page_by_path( $cle );
						if ( ! $pg ) {
							return 'page' === $type ? '0' : '#';
						}
						return 'page' === $type ? (string) $pg->ID : wp_make_link_relative( get_permalink( $pg ) );
				}
				return $m[0];
			},
			$html
		);
	}

	// ------------------------------------------------------------------ 7 bis
	/**
	 * Formulaires Contact Form 7, AVANT les pages : leur contenu les appelle par
	 *   [contact-form-7 id="{{formulaire:<titre>}}" title="<titre>"]
	 * « formulaires » : [{ "titre", "gabarit": "@contenus/formulaire-contact.txt",
	 * "destinataire", "sujet" }]. Idempotent par titre. Le gabarit porte les
	 * classes de la maquette ; textarea en 40x6 (CF7 en met 10) ; Reply-To sur
	 * l'adresse du visiteur.
	 */
	function c2woo_formulaires( $p ) {
		$liste = (array) ( $p['formulaires'] ?? array() );
		if ( ! $liste ) {
			return array();
		}
		if ( ! class_exists( 'WPCF7_ContactForm' ) ) {
			return array( 'erreur' => 'Contact Form 7 absent : installer et activer contact-form-7 d’abord.' );
		}
		$out = array();
		foreach ( $liste as $f ) {
			$titre     = (string) $f['titre'];
			$existants = get_posts(
				array(
					'post_type'      => 'wpcf7_contact_form',
					'title'          => $titre,
					'posts_per_page' => 1,
					'post_status'    => 'any',
					'fields'         => 'ids',
				)
			);
			$form = $existants ? WPCF7_ContactForm::get_instance( $existants[0] ) : WPCF7_ContactForm::get_template( array( 'title' => $titre ) );
			$mail = (array) $form->prop( 'mail' );
			$mail['recipient']          = $f['destinataire'] ?? get_option( 'admin_email' );
			$mail['sender']             = '[_site_title] <wordpress@' . wp_parse_url( home_url(), PHP_URL_HOST ) . '>';
			$mail['subject']            = $f['sujet'] ?? '[_site_title] — [your-subject]';
			$mail['additional_headers'] = 'Reply-To: [your-email]';
			$mail['body']               = "De : [your-name] <[your-email]>\nSujet : [your-subject]\n\n[your-message]\n\n-- \nMessage envoyé depuis le formulaire de contact de [_site_title] ([_site_url]).";
			$form->set_properties(
				array(
					'form' => (string) ( $f['gabarit'] ?? '' ),
					'mail' => $mail,
				)
			);
			$form->save();
			$out[ $titre ] = $form->hash() ?: $form->id();
		}
		return $out;
	}

	function c2woo_pages( $p, $medias ) {
		$out = array();

		// Pages éditoriales et légales, par slug.
		foreach ( (array) ( $p['pages'] ?? array() ) as $pg ) {
			$existe = get_page_by_path( $pg['slug'] );
			$data   = array(
				'post_type'    => 'page',
				'post_title'   => $pg['titre'],
				'post_name'    => $pg['slug'],
				'post_status'  => $pg['statut'] ?? 'publish',
				'post_content' => c2woo_jetons( $pg['contenu'] ?? '', $medias ),
			);
			if ( $existe ) {
				$data['ID'] = $existe->ID;
				$id         = wp_update_post( wp_slash( $data ) );
			} else {
				$id = wp_insert_post( wp_slash( $data ) );
			}
			$out[ $pg['slug'] ] = $id;
		}

		// Pages WooCommerce : on réutilise celles que WooCommerce a créées (leurs
		// ID sont dans ses options) — jamais de doublon « panier-2 ».
		$options = array(
			'boutique'        => 'woocommerce_shop_page_id',
			'panier'          => 'woocommerce_cart_page_id',
			'commande'        => 'woocommerce_checkout_page_id',
			'mon-compte'      => 'woocommerce_myaccount_page_id',
			'cgv'             => 'woocommerce_terms_page_id',
			'confidentialite' => 'wp_page_for_privacy_policy',
		);
		foreach ( (array) ( $p['pages_woocommerce'] ?? array() ) as $cle => $pg ) {
			if ( ! isset( $options[ $cle ] ) ) {
				continue;
			}
			$id   = (int) get_option( $options[ $cle ] );
			$post = $id ? get_post( $id ) : null;
			// Option vide ou page à la corbeille : la page existe peut-être déjà
			// sous ce slug (créée juste avant par « pages ») — on la rattache.
			if ( ( ! $post || 'trash' === $post->post_status ) && ! empty( $pg['slug'] ) ) {
				$par_slug = get_page_by_path( $pg['slug'] );
				if ( $par_slug ) {
					$id   = $par_slug->ID;
					$post = $par_slug;
					update_option( $options[ $cle ], $id );
				}
			}
			$data = array( 'post_type' => 'page', 'post_status' => 'publish' );
			if ( ! empty( $pg['titre'] ) ) {
				$data['post_title'] = $pg['titre'];
			}
			if ( ! empty( $pg['slug'] ) ) {
				$data['post_name'] = $pg['slug'];
			}
			if ( array_key_exists( 'contenu', $pg ) && null !== $pg['contenu'] ) {
				$data['post_content'] = c2woo_jetons( $pg['contenu'], $medias );
			}
			if ( $post && 'trash' !== $post->post_status ) {
				$data['ID'] = $id;
				wp_update_post( wp_slash( $data ) );
			} else {
				$id = wp_insert_post( wp_slash( $data + array( 'post_title' => ucfirst( $cle ), 'post_content' => '' ) ) );
				update_option( $options[ $cle ], $id );
			}
			$out[ 'woocommerce:' . $cle ] = $id;
		}
		return $out;
	}

	// ------------------------------------------------------------------ 8
	function c2woo_accueil( $p ) {
		$slug = $p['accueil'] ?? 'accueil';
		$pg   = get_page_by_path( $slug );
		if ( $pg ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $pg->ID );
		}
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/%postname%/' );
		// Les bases produit viennent peut-être de changer dans CETTE requête, après
		// l'enregistrement des types de contenu : on laisse WordPress régénérer
		// les règles à la requête suivante plutôt que de figer les anciennes.
		delete_option( 'rewrite_rules' );
		return array( 'page_on_front' => $pg ? $pg->ID : null );
	}

	// ------------------------------------------------------------------ 9
	/**
	 * Caches qui font croire qu'un déploiement « ne prend pas » :
	 * - la liste des compositions du thème (WP ≥ 6.4) : un nouveau fichier de
	 *   patterns/ reste invisible tant qu'elle n'est pas vidée ;
	 * - les en-têtes de thème et theme.json ;
	 * - le cache objet.
	 * Le cache de page de l'hébergeur (LiteSpeed…) se purge À PART.
	 */
	function c2woo_purger() {
		$theme = wp_get_theme();
		if ( method_exists( $theme, 'delete_pattern_cache' ) ) {
			$theme->delete_pattern_cache();
		}
		wp_clean_themes_cache( true );
		if ( class_exists( 'WP_Theme_JSON_Resolver' ) ) {
			WP_Theme_JSON_Resolver::clean_cached_data();
		}
		wp_cache_flush();
		return 'compositions, thème, theme.json et cache objet purgés';
	}

	// ------------------------------------------------------------------ recette
	/**
	 * Porte 1 : inventaire des blocs de toutes les pages publiées et de tous les
	 * templates et parties du thème actif (fichiers ET personnalisations en base).
	 * Réception : html_brut = 0, legacy = 0, hors_liste = [] partout.
	 */
	function c2woo_porte1() {
		$compter = function ( $blocs, &$n ) use ( &$compter ) {
			foreach ( $blocs as $b ) {
				if ( empty( $b['blockName'] ) ) {
					continue;
				}
				$n[ $b['blockName'] ] = ( $n[ $b['blockName'] ] ?? 0 ) + 1;
				if ( ! empty( $b['innerBlocks'] ) ) {
					$compter( $b['innerBlocks'], $n );
				}
			}
		};
		$bilan = function ( $contenu ) use ( $compter ) {
			$n     = array();
			$blocs = parse_blocks( $contenu );
			if ( function_exists( 'resolve_pattern_blocks' ) ) {
				$blocs = resolve_pattern_blocks( $blocs ); // les compositions (patterns/) comptent aussi
			}
			$compter( $blocs, $n );
			$hors = array_values(
				array_filter(
					array_keys( $n ),
					fn( $k ) => 0 !== strpos( $k, 'core/' ) && 0 !== strpos( $k, 'woocommerce/' )
				)
			);
			return array(
				'total'     => array_sum( $n ),
				'html_brut' => $n['core/html'] ?? 0,
				'legacy'    => ( $n['woocommerce/legacy-template'] ?? 0 ) + ( $n['woocommerce/classic-template'] ?? 0 ),
				'shortcode' => $n['core/shortcode'] ?? 0,
				'hors_liste' => $hors,
			);
		};
		$out = array( 'pages' => array(), 'templates' => array() );
		foreach ( get_posts( array( 'post_type' => 'page', 'post_status' => 'publish', 'numberposts' => -1 ) ) as $pg ) {
			$out['pages'][ $pg->post_name ] = $bilan( $pg->post_content );
		}
		foreach ( array( 'wp_template', 'wp_template_part' ) as $type ) {
			foreach ( get_block_templates( array(), $type ) as $t ) {
				if ( $t->theme !== get_stylesheet() ) {
					continue; // modèles fournis par WooCommerce que le thème ne remplace pas
				}
				$out['templates'][ $t->slug . ( 'custom' === $t->source ? ' (en base)' : '' ) ] = $bilan( $t->content );
			}
		}
		return $out;
	}

	/**
	 * Porte 7 : conformité de base d'une boutique française. Une liste de
	 * contrôles, pas un avis juridique — le fond des textes reste au client.
	 */
	function c2woo_porte7() {
		$page_ok = function ( $id ) {
			$pg = $id ? get_post( $id ) : null;
			if ( ! $pg || 'publish' !== $pg->post_status ) {
				return 'absente ou non publiée';
			}
			return false === strpos( $pg->post_content, '[À compléter' ) ? 'ok' : 'trame non complétée';
		};
		$commande = get_post( (int) get_option( 'woocommerce_checkout_page_id' ) );
		$blocs    = $commande ? $commande->post_content : '';
		preg_match( '/"placeOrderButtonLabel":"([^"]*)"/', $blocs, $m );
		$libelle = $m[1] ?? 'Commander';
		$mentions = get_page_by_path( 'mentions-legales' );
		return array(
			'cgv'                     => $page_ok( (int) get_option( 'woocommerce_terms_page_id' ) ),
			'confidentialite'         => $page_ok( (int) get_option( 'wp_page_for_privacy_policy' ) ),
			'mentions_legales'        => $page_ok( $mentions ? $mentions->ID : 0 ),
			'case_cgv_a_cocher'       => false !== strpos( $blocs, 'checkout-terms-block {"checkbox":true' ) ? 'ok' : 'non',
			'bouton_de_commande'      => $libelle . ( preg_match( '/paiement|payer/i', $libelle ) ? ' (ok)' : ' (doit mentionner l’obligation de payer)' ),
			'prix_ttc'                => 'yes' === get_option( 'woocommerce_prices_include_tax' ) && 'incl' === get_option( 'woocommerce_tax_display_shop' ) ? 'ok' : 'non',
			'bientot_disponible'      => get_option( 'woocommerce_coming_soon' ),
		);
	}

	/** Blocs WooCommerce réellement enregistrés sur CE site (la liste change avec les versions). */
	function c2woo_blocs_disponibles() {
		$noms = array_keys( WP_Block_Type_Registry::get_instance()->get_all_registered() );
		sort( $noms );
		return array(
			'woocommerce' => defined( 'WC_VERSION' ) ? WC_VERSION : null,
			'wordpress'   => get_bloginfo( 'version' ),
			'blocs'       => array_values( array_filter( $noms, fn( $n ) => 0 === strpos( $n, 'woocommerce/' ) ) ),
		);
	}

	/**
	 * Templates du thème personnalisés en base : ils MASQUENT les fichiers du
	 * thème. Un correctif de template « sans effet » vient presque toujours de là.
	 */
	function c2woo_templates_en_base( $theme = null, $supprimer = false ) {
		$theme = $theme ?: get_stylesheet();
		$posts = get_posts(
			array(
				'post_type'   => array( 'wp_template', 'wp_template_part' ),
				'post_status' => 'any',
				'numberposts' => -1,
				'tax_query'   => array( array( 'taxonomy' => 'wp_theme', 'field' => 'name', 'terms' => $theme ) ),
			)
		);
		$out = array();
		foreach ( $posts as $t ) {
			$out[] = $t->post_type . ':' . $t->post_name . ' (modifié le ' . $t->post_modified . ')';
			if ( $supprimer ) {
				wp_delete_post( $t->ID, true );
			}
		}
		return array( 'theme' => $theme, 'en_base' => $out, 'supprimes' => $supprimer );
	}

	/**
	 * Clôture — TOUJOURS le dernier appel au site : révoque le mot de passe
	 * d'application avec lequel CET appel s'est authentifié (celui du connecteur).
	 * Ne touche ni aux autres mots de passe d'application, ni aux sessions, ni à
	 * l'extension Novamira. Après lui, le connecteur ne répond plus : c'est voulu.
	 */
	function c2woo_revoquer_acces() {
		$a_faire = 'à révoquer par le client : Utilisateurs → Profil → Mots de passe d’application.';
		if ( ! class_exists( 'WP_Application_Passwords' ) || ! function_exists( 'rest_get_authenticated_app_password' ) ) {
			return array( 'revoque' => false, 'raison' => 'WordPress sans mots de passe d’application ; ' . $a_faire );
		}
		$utilisateur = get_current_user_id();
		$uuid        = rest_get_authenticated_app_password();
		if ( ! $utilisateur || ! $uuid ) {
			return array( 'revoque' => false, 'raison' => 'mot de passe d’application de cet appel introuvable ; ' . $a_faire );
		}
		$item = WP_Application_Passwords::get_user_application_password( $utilisateur, $uuid );
		$r    = WP_Application_Passwords::delete_application_password( $utilisateur, $uuid );
		if ( is_wp_error( $r ) ) {
			return array( 'revoque' => false, 'raison' => $r->get_error_message() . ' ; ' . $a_faire );
		}
		return array(
			'revoque'     => true,
			'nom'         => $item['name'] ?? '',
			'utilisateur' => wp_get_current_user()->user_login,
		);
	}

	/** Commande de recette : annulée d'abord (le stock revient), puis supprimée. */
	function c2woo_annuler_commande_test( $id ) {
		$o = wc_get_order( $id );
		if ( ! $o ) {
			return 'commande introuvable : ' . $id;
		}
		$o->update_status( 'cancelled', 'Commande de recette — annulée puis supprimée.' );
		$o->delete( true );
		return 'commande ' . $id . ' annulée puis supprimée';
	}

endif;
