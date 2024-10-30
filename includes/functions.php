<?php

function ninja_forms_mijireh_get_currency(){
  $plugin_settings = get_option( 'ninja_forms_mijireh' );

  if ( isset ( $plugin_settings['currency'] ) ) {
    $currency = $plugin_settings['currency'];
  } else {
    $currency = 'USD';
  }

  return $currency;
}

function ninja_forms_mijireh_get_total(){
  global $ninja_forms_processing;
  $total = $ninja_forms_processing->get_calc_total();

  if ( !$total ) {
    $total = $ninja_forms_processing->get_form_setting( 'mijireh_default_total' );
  }
  
  return $total;
}