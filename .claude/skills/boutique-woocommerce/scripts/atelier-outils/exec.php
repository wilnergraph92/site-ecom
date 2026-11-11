<?php
/**
 * Atelier seulement — l'équivalent local de novamira/execute-php.
 * Exécute le PHP reçu en corps de requête (POST), en administrateur, avec
 * scripts/boutique.php déjà chargé. Protégé par le jeton que atelier.sh écrit
 * dans <projet>/.atelier/jeton à chaque lancement.
 *
 * Ne s'appelle pas à la main : scripts/atelier-php.sh 'return c2woo_porte1();'
 */
$jeton = trim( (string) @file_get_contents( '/atelier/projet/.atelier/jeton' ) );
if ( '' === $jeton || ! hash_equals( $jeton, (string) ( $_SERVER['HTTP_X_ATELIER_JETON'] ?? '' ) ) ) {
	http_response_code( 403 );
	exit( 'jeton absent ou invalide' );
}
require dirname( __DIR__ ) . '/wp-load.php';
require_once '/atelier/scripts/boutique.php';
wp_set_current_user( 1 );
header( 'Content-Type: application/json; charset=utf-8' );
$code = (string) file_get_contents( 'php://input' );
try {
	$r = ( function () use ( $code ) {
		return eval( $code );
	} )();
	echo wp_json_encode( $r, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
} catch ( \Throwable $e ) {
	http_response_code( 500 );
	echo wp_json_encode(
		array(
			'erreur'  => get_class( $e ) . ' : ' . $e->getMessage(),
			'fichier' => str_replace( ABSPATH, '', $e->getFile() ) . ':' . $e->getLine(),
		),
		JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
	);
}
