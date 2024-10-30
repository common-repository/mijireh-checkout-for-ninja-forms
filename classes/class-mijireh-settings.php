<?php

/*
 *
 * This class sets up all of our Mijireh Checkout settings in the wp-admin.
 *
 * @since 1.0
 */

class NF_Mijireh_Settings 
{
    /**
   * Initialize the plugin
   */
  public function __construct() { 
    // load scripts
    //add_action( 'ninja_forms_display_js', array( &$this, "load_scripts" ) );

    // load settings
    add_action( 'admin_menu', array( $this, 'load_mijireh_settings' ) );
    add_action( 'admin_init', array( $this, 'load_mijireh_form_settings' ) );
    add_action( 'ninja_forms_edit_field_after_registered', array( $this, 'load_mijireh_field_settings' ), 12 );
  }

  public function load_mijireh_settings() {
    // Add a submenu to Ninja Forms for Mijireh Checkout settings.
    $mijireh = add_submenu_page( 'ninja-forms', __( 'Mijireh Checkout Settings', 'nf-mijireh-patsatech' ), __( 'Mijireh Checkout', 'nf-mijireh-patsatech' ), 'administrator', 'nf-mijireh-patsatech', 'ninja_forms_admin' );

    // Enqueue default Ninja Forms admin styles and JS.
    add_action('admin_print_styles-' . $mijireh, 'ninja_forms_admin_css');
    add_action('admin_print_styles-' . $mijireh, 'ninja_forms_admin_js');

    // Register a tab to our new page for Mijireh Checkout settings.
    $args = array(
      'name' => __( 'Mijireh Checkout Settings', 'nf-mijireh-patsatech' ),
      'page' => 'nf-mijireh-patsatech',
      'display_function' => '',
      'save_function' => array( $this, 'save_mijireh_settings' ),
      'tab_reload' => true,
    );
    if ( function_exists( 'ninja_Forms_register_tab' ) ) {
      ninja_forms_register_tab( 'general_settings', $args);
    }

    // Grab our current settings.
    $plugin_settings = get_option( 'ninja_forms_mijireh' );
    
    if ( isset ( $plugin_settings['currency'] ) ) {
      $selected_currency = $plugin_settings['currency'];
    } else { 
      $selected_currency = 'USD';
    }    

    if ( isset ( $plugin_settings['mijireh_access_key'] ) ) {
      $mijireh_access_key = $plugin_settings['mijireh_access_key'];
    } else { 
      $mijireh_access_key = '';
    }

    // Register our Genearl Settings metabox.
    $mijireh_currencies = array(
      array( 'name' => __( 'Australian Dollars', 'nf-mijireh-patsatech' ),   'value' => 'AUD' ),
      array( 'name' => __( 'Canadian Dollars', 'nf-mijireh-patsatech' ),     'value' => 'CAD' ),
      array( 'name' => __( 'Czech Koruna', 'nf-mijireh-patsatech' ),         'value' => 'CZK' ),
      array( 'name' => __( 'Danish Krone', 'nf-mijireh-patsatech' ),         'value' => 'DKK' ),
      array( 'name' => __( 'Euros', 'nf-mijireh-patsatech' ),                'value' => 'EUR' ),
      array( 'name' => __( 'Hong Kong Dollars', 'nf-mijireh-patsatech' ),    'value' => 'HKD' ),
      array( 'name' => __( 'Hungarian Forints', 'nf-mijireh-patsatech' ),    'value' => 'HUF' ),
      array( 'name' => __( 'Israeli New Sheqels', 'nf-mijireh-patsatech' ),  'value' => 'ILS' ),
      array( 'name' => __( 'Japanese Yen', 'nf-mijireh-patsatech' ),         'value' => 'JPY' ),
      array( 'name' => __( 'Mexican Pesos', 'nf-mijireh-patsatech' ),        'value' => 'MXN' ),
      array( 'name' => __( 'Norwegian Krone', 'nf-mijireh-patsatech' ),      'value' => 'NOK' ),
      array( 'name' => __( 'New Zealand Dollars', 'nf-mijireh-patsatech' ),  'value' => 'NZD' ),
      array( 'name' => __( 'Philippine Pesos', 'nf-mijireh-patsatech' ),     'value' => 'PHP' ),
      array( 'name' => __( 'Polish Zloty', 'nf-mijireh-patsatech' ),         'value' => 'PLN' ),
      array( 'name' => __( 'Pound Sterling', 'nf-mijireh-patsatech' ),       'value' => 'GBP' ),
      array( 'name' => __( 'Singapore Dollars', 'nf-mijireh-patsatech' ),    'value' => 'SGD' ),
      array( 'name' => __( 'Swedish Krona', 'nf-mijireh-patsatech' ),        'value' => 'SEK' ),
      array( 'name' => __( 'Swiss Franc', 'nf-mijireh-patsatech' ),          'value' => 'CHF' ),
      array( 'name' => __( 'Taiwan New Dollars', 'nf-mijireh-patsatech' ),   'value' => 'TWD' ),
      array( 'name' => __( 'Thai Baht', 'nf-mijireh-patsatech' ),            'value' => 'THB' ),
      array( 'name' => __( 'U.S. Dollars', 'nf-mijireh-patsatech' ),         'value' => 'USD' ),
    );

    $args = array(
      'page' => 'nf-mijireh-patsatech',
      'tab' => 'general_settings',
      'slug' => 'general',
      'title' => __( 'Basic Settings', 'nf-mijireh-patsatech' ),
      'display_function' => '',
      'state' => 'closed',
      'settings' => array(    
        array(
          'name' => 'currency',
          'type' => 'select',
          'options' => $mijireh_currencies,
          'label' => __( 'Transaction Currency', 'nf-mijireh-patsatech'),
          'default_value' => $selected_currency,
        ),
      ),
    );
    if ( function_exists( 'ninja_forms_register_tab_metabox' ) ) {
      ninja_forms_register_tab_metabox($args);
    }

    // Register our API Settings metabox.
    $args = array(
      'page' => 'nf-mijireh-patsatech',
      'tab' => 'general_settings',
      'slug' => 'mijireh_credentials',
      'title' => __( 'Mijireh Credentials', 'nf-mijireh-patsatech' ),
      'display_function' => '',
      'state' => 'open',
      'settings' => array(
        array(
          'name' => 'mijireh_access_key',
          'type' => 'text',
          'label' => __( 'Access Key', 'nf-mijireh-patsatech' ),
          'default_value' => $mijireh_access_key,
        ),
      ),
    );
    if ( function_exists( 'ninja_forms_register_tab_metabox' ) ) {
      ninja_forms_register_tab_metabox($args);
    }

  }

  public function save_mijireh_settings( $data ) {
    $plugin_settings = get_option( 'ninja_forms_mijireh' );
    if ( is_array( $data ) ) {
      foreach ( $data as $key => $val ) {
        $plugin_settings[$key] = $val;
      }
    }
    update_option( 'ninja_forms_mijireh', $plugin_settings );

    return __( 'Settings Updated', 'nf-mijireh-patsatech' );
  }

  public function load_mijireh_form_settings() {
    // Register our Mijireh Checkout Settings metabox.
    $args = array(
      'page' => 'ninja-forms',
      'tab' => 'form_settings',
      'slug' => 'mijireh',
      'title' => __( 'Mijireh Checkout Settings', 'nf-mijireh-patsatech' ),
      'display_function' => '',
      'state' => 'closed',
      'settings' => array(
        array(
          'name' => 'mijireh',
          'type' => 'checkbox',
          'label' => __( 'Use Mijireh Checkout', 'nf-mijireh-patsatech' ),
        ),
        array(
          'name' => 'mijireh_product_name',
          'type' => 'text',
          'label' => __( 'Default Product Name', 'nf-mijireh-patsatech' ),
          'desc' => __( 'If you do not plan on adding any calculation fields to your form, enter a product name here.', 'nf-mijireh-patsatech' ),
        ),        
        array(
          'name' => 'mijireh_product_desc',
          'type' => 'text',
          'label' => __( 'Default Product Description', 'nf-mijireh-patsatech' ),
          'desc' => __( 'If you do not plan on adding any calculation fields to your form, enter a product description here.', 'nf-mijireh-patsatech' ),
        ),
        array(
          'name' => 'mijireh_default_total',
          'type' => 'text',
          'label' => __( 'Default Total', 'nf-mijireh-patsatech' ),
          'desc' => __( 'If you do not want to use a Total Field in your form, you can use this setting. Please leave out any currency markers.', 'nf-mijireh-patsatech' ),
        ),
      ),
    );
    if ( function_exists( 'ninja_forms_register_tab_metabox' ) ) {
      ninja_forms_register_tab_metabox($args);
    }
  }

  public function load_mijireh_field_settings( $field_id ) {
    // Output our edit field settings
    $field = ninja_forms_get_field_by_id( $field_id );
    // If we're working with a list, add the checkbox option to use the List Item Label for the Mijireh Checkout Product Name.
    if ( $field['type'] == '_list' ) {
      ?>
      <div id="mijireh_settings">
        <h4>Mijireh Checkout Settings</h4>
        <?php
        if ( isset ( $field['data']['list_label_product_name'] ) ) {
          $list_label_product_name = $field['data']['list_label_product_name'];
        } else {
          $list_label_product_name = 0;
        }
        
        ninja_forms_edit_field_el_output( $field_id, 'checkbox', __( 'Use List Label For Mijireh Checkout Product Name', 'ninja-forms' ), 'list_label_product_name', $list_label_product_name, 'wide', '', '' );
        ?>

      </div>
      <?php
    }
  }

} // Class

function ninja_forms_mijireh_initiate(){
  if ( is_admin() ) {
    $NF_Mijireh_Settings = new NF_Mijireh_Settings();     
  }
}

add_action( 'init', 'ninja_forms_mijireh_initiate' );