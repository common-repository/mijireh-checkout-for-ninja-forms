<?php
/**
 * Plugin Name: Mijireh Checkout for Ninja Forms
 * Plugin URI: http://www.patsatech.com/
 * Description: Allows for integration with the Mijireh Checkout payment gateway.
 * Version: 1.0.0
 * Author: PatSaTECH
 * Author URI: http://www.patsatech.com
 * Contributors: patsatech
 * Requires at least: 3.5
 * Tested up to: 3.9
 *
 * Text Domain: nf-mijireh-patsatech
 * Domain Path: /lang/
 *
 * @package Mijireh Checkout for Ninja Forms
 * @author PatSaTECH
 */


define("NINJA_FORMS_MIJIREH_DIR", WP_PLUGIN_DIR."/".basename( dirname( __FILE__ ) ) );
define("NINJA_FORMS_MIJIREH_URL", plugins_url()."/".basename( dirname( __FILE__ ) ) );
define("NINJA_FORMS_MIJIREH_VERSION", "1.0.2");

function ninja_forms_mijireh_load_lang() {

  /** Set our unique textdomain string */
  $textdomain = 'nf-mijireh-patsatech';

  /** The 'plugin_locale' filter is also used by default in load_plugin_textdomain() */
  $locale = apply_filters( 'plugin_locale', get_locale(), $textdomain );

  /** Set filter for WordPress languages directory */
  $wp_lang_dir = apply_filters(
    'ninja_forms_wp_lang_dir',
    WP_LANG_DIR . '/'.basename( dirname( __FILE__ ) ).'/' . $textdomain . '-' . $locale . '.mo'
  );

  /** Translations: First, look in WordPress' "languages" folder = custom & update-secure! */
  load_textdomain( $textdomain, $wp_lang_dir );

  /** Translations: Secondly, look in plugin's "lang" folder = default */
  $plugin_dir = basename( dirname( __FILE__ ) );
  $lang_dir = apply_filters( 'ninja_forms_mijireh_lang_dir', $plugin_dir . '/lang/' );
  load_plugin_textdomain( $textdomain, FALSE, $lang_dir );

}
add_action('plugins_loaded', 'ninja_forms_mijireh_load_lang');

register_activation_hook( __FILE__, 'ninja_forms_mijireh_activation' );

require_once( NINJA_FORMS_MIJIREH_DIR.'/includes/functions.php' );
require_once( NINJA_FORMS_MIJIREH_DIR.'/includes/activation.php' );
require_once( NINJA_FORMS_MIJIREH_DIR.'/includes/shortcodes.php' );

require_once( NINJA_FORMS_MIJIREH_DIR.'/classes/class-mijireh-settings.php' );
require_once( NINJA_FORMS_MIJIREH_DIR.'/classes/class-mijireh-checkout.php' );
require_once( NINJA_FORMS_MIJIREH_DIR.'/classes/class-mijireh-response.php' );
require_once( NINJA_FORMS_MIJIREH_DIR.'/classes/class-mijireh-subs.php' );