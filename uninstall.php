<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Uninstallation actions here

//remove the taxonomy that we created

$ddd_idObj = get_term_by( 'slug', 'dis_ddd_bookings', 'product_cat'); 
$ddd_tt_id = $ddd_idObj->term_id;
wp_delete_term( $ddd_tt_id, 'product_cat' );
