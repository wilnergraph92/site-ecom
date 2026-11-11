<?php
/**
 * Atelier seulement — ouvre une session administrateur SANS mot de passe dans
 * le navigateur qui visite ce lien, puis redirige (par défaut vers wp-admin).
 * L'équivalent local de novamira/create-admin-access-link.
 *
 *   http://127.0.0.1:9400/atelier/connexion.php?jeton=<.atelier/jeton>&vers=/wp-admin/
 */
$jeton = trim( (string) @file_get_contents( '/atelier/projet/.atelier/jeton' ) );
if ( '' === $jeton || ! hash_equals( $jeton, (string) ( $_GET['jeton'] ?? '' ) ) ) {
	http_response_code( 403 );
	exit( 'jeton absent ou invalide' );
}
require dirname( __DIR__ ) . '/wp-load.php';
wp_set_current_user( 1 );
wp_set_auth_cookie( 1, true );
$vers = (string) ( $_GET['vers'] ?? '/wp-admin/' );
if ( '/' !== substr( $vers, 0, 1 ) || '//' === substr( $vers, 0, 2 ) ) {
	$vers = '/wp-admin/'; // chemins locaux uniquement
}
wp_safe_redirect( home_url( $vers ) );
exit;
