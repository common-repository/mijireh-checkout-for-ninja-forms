<?php

function ninja_forms_mijireh_transaction_id_shortcode( $atts ){
	global $ninja_forms_processing;
	
	return $ninja_forms_processing->get_form_setting( 'mijireh_transaction_id' );

}
add_shortcode( 'ninja_forms_mijireh_transaction_id', 'ninja_forms_mijireh_transaction_id_shortcode' );