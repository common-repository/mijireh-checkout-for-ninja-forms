<?php

function ninja_forms_mijireh_activation(){
	global $wpdb;
	if($wpdb->get_var("SHOW COLUMNS FROM ".NINJA_FORMS_SUBS_TABLE_NAME." LIKE 'mijireh_status'") != 'mijireh_status') {
		$sql = "ALTER TABLE ".NINJA_FORMS_SUBS_TABLE_NAME." ADD `mijireh_status` VARCHAR(50) NOT NULL";
		$wpdb->query($sql);		
		$sql = "ALTER TABLE ".NINJA_FORMS_SUBS_TABLE_NAME." ADD `mijireh_transaction_id` VARCHAR(255) NOT NULL";
		$wpdb->query($sql);		
		$sql = "ALTER TABLE ".NINJA_FORMS_SUBS_TABLE_NAME." ADD `mijireh_total` VARCHAR(255) NOT NULL";
		$wpdb->query($sql);
		$sql = "ALTER TABLE ".NINJA_FORMS_SUBS_TABLE_NAME." ADD `mijireh_error` VARCHAR(255) NOT NULL";
		$wpdb->query($sql);
	}
}