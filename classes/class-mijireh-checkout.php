<?php

/*
 *
 * This class is called during form processing to setup our Mijireh Checkout checkout using the NF_Mijireh_Process class.
 *
 * @since 1.0
 *
 */

class NF_Mijireh_Form_Process
{

	public $token = '';

	/*
	 *
	 * Function that handles putting together the request sent to Mijireh Checkout initially during form processing.
	 *
	 * @since 1.0
	 * @return void
	 */

	public function __construct() {
		global $ninja_forms_processing;
		// Bail if this form isn't setup to use Mijireh Checkout.
		if ( $ninja_forms_processing->get_form_setting( 'mijireh' ) != 1 )
    		return;
    	// Bail if the form's total calculation field is empty.
    	$purchase_total = floatval ( $this->get_purchase_total() );

    	if ( empty (  $purchase_total ) )
    		return;
 
    	$this->do_checkout();
    	return;
	} // function __construct
  

	/**
	 * init_mijireh function.
	 *
	 * @access public
	 */
	function init_mijireh() {
		if ( ! class_exists( 'Mijireh' ) ) {
	    	require_once 'includes/Mijireh.php';

			// Get Mijireh Checkout settings and return our API credentials.
			$plugin_settings = get_option( 'ninja_forms_mijireh', null );
		   	$key = ! empty( $plugin_settings['mijireh_access_key'] ) ? $plugin_settings['mijireh_access_key'] : '';
			
	    	Mijireh::$access_key = $key;
			
		}
	}

	/*
	 *
	 * Function that runs our checkout process.
	 *
	 * @since 1.0
	 * @return void
	 */

	function do_checkout() {
		global $ninja_forms_processing;

    	// Get the product info for our query.
		$purchase_total = $this->get_purchase_total();
		
		$this->init_mijireh();

		$mj_order = new Mijireh_Order();
			  
	  	// Get our items.
	  	// Get our form total. This can be returned as an array or a string value.
		$total = ninja_forms_mijireh_get_total();

	  	// Check to see if $total is an array. 
		if ( is_array ( $total ) ) { // If it is, then get the items from it.
			if ( is_array ( $total['fields'] ) ) {
				$item = array();
				$x = 0;
				foreach ( $total['fields'] as $field_id => $calc ) {
					$field = $ninja_forms_processing->get_field_settings( $field_id );
					$user_value = $ninja_forms_processing->get_field_value( $field_id );

					if ( isset ( $field['data']['label'] ) ) {
						$field_label = $field['data']['label'];
					} else {
						$field_label = '';
					}

					if ( isset ( $field['data']['desc_text'] ) ) {
						$field_desc = $field['data']['desc_text'];
					} else {
						$field_desc = '';
					}

					// Check to see if we're working with a checkbox. If we are, the calculation value will depend upon it's state.

					if ( $field['type'] == '_checkbox' ) {
						if ( $user_value == 'checked' ) { // This is a checkbox, so let's see if the value is checked or unchecked.
							$field_calc = $field['data']['calc_value']['checked'];
						} else {
							$field_calc = $field['data']['calc_value']['unchecked'];
						}
					} else { // We aren't working with a checkbox, so the value will be used as the calc value.
						$field_calc = $user_value;
					}

					// Check to see if we're working with a list item
					if ( $field['type'] == '_list' ) {
						// Get our list of options.
						$options = $field['data']['list']['options'];
						if ( is_array ( $user_value ) ) {
						  	foreach ( $user_value as $val ) {

						  		
								foreach ( $options as $opt ) {
									// Check to see if we're using option values.
									if ( isset ( $field['data']['list_show_value'] ) AND $field['data']['list_show_value'] == 1 ) {
										// If the val is equal to our option value, then assign the label.
										if ( $opt['value'] == $val ) {
											// Normally, we use the label field setting for the product name. For a list, however, the user may want to use the selected list item's label instead.
											// Check to see if the user wants to use the list label for the selected item.
											if ( isset ( $field['data']['list_label_product_name'] ) AND $field['data']['list_label_product_name'] == 1 ) {
												$field_label = $opt['label'];
											}
											$field_calc = $opt['calc'];
											break;
										}
									} else {
									// If the val is equal to our option label, then assign the label.
										if ( $opt['label'] == $val ) {
											// Normally, we use the label field setting for the product name. For a list, however, the user may want to use the selected list item's label instead.
											// Check to see if the user wants to use the list label for the selected item.
											if ( isset ( $field['data']['list_label_product_name'] ) AND $field['data']['list_label_product_name'] == 1 ) {
												$field_label = $opt['label'];
											}
											$field_calc = $opt['calc'];
											break;                
										}
									}
								}


								
								if ( isset ( $field['data']['desc_text'] ) AND $field['data']['desc_text'] != '' ) {
									$field_desc = $field['data']['desc_text'];
								} else {
									$field_desc = '';
								}

								$mj_order->add_item( apply_filters( 'ninja_forms_mijireh_product_name', $field_label, $field_id ), $field_calc, 1, '' );
								
								$x++;
						  	}
						} else {

							foreach ( $options as $opt ) {
								// Check to see if we're using option values.
								if ( isset ( $field['data']['list_show_value'] ) AND $field['data']['list_show_value'] == 1 ) {
									// If the val is equal to our option value, then assign the label.
									if ( $opt['value'] == $user_value ) {
										// Normally, we use the label field setting for the product name. For a list, however, the user may want to use the selected list item's label instead.
										// Check to see if the user wants to use the list label for the selected item.
										if ( isset ( $field['data']['list_label_product_name'] ) AND $field['data']['list_label_product_name'] == 1 ) {
											$field_label = $opt['label'];
										}
										$field_calc = $opt['calc'];
										break;
									}
								} else {
								// If the val is equal to our option label, then assign the label.
									if ( $opt['label'] == $user_value ) {
										// Normally, we use the label field setting for the product name. For a list, however, the user may want to use the selected list item's label instead.
										// Check to see if the user wants to use the list label for the selected item.
										if ( isset ( $field['data']['list_label_product_name'] ) AND $field['data']['list_label_product_name'] == 1 ) {
											$field_label = $opt['label'];
										}
										$field_calc = $opt['calc'];
										break;                
									}
								}
							}
						
							if ( isset ( $field['data']['desc_text'] ) AND $field['data']['desc_text'] != '' ) {
								$field_desc = $field['data']['desc_text'];
							} else {
								$field_desc = '';
							}
							
							$mj_order->add_item( apply_filters( 'ninja_forms_mijireh_product_name', $field_label, $field_id ), $field_calc, 1, '' );
							$x++;
						}

					} else { // This isn't a list element

						$mj_order->add_item( apply_filters( 'ninja_forms_mijireh_product_name', $field_label, $field_id ), $field_calc, 1, '' );
						$x++;
					}
				}
			}

			if ( isset ( $total['tax_total'] ) ) {
				// Get the Tax field label
				foreach ( $ninja_forms_processing->get_all_fields() as $field_id => $user_value ) {
					$field = ninja_forms_get_field_by_id( $field_id );
					if ( $field['type'] == '_tax' ) {
						$field_label = $field['data']['label'];
						$field_desc = $field['data']['default_value'];
						break;
					}
				}
				$mj_order->add_item( apply_filters( 'ninja_forms_mijireh_product_name', $field_label, $field_id ), $field_calc, 1, '' );
				$x++;
			}

		} else { // If it isn't, we are using the default product info.

			$product_name = $ninja_forms_processing->get_form_setting( 'mijireh_product_name' );
			$product_desc = $ninja_forms_processing->get_form_setting( 'mijireh_product_desc' );

			$mj_order->add_item( apply_filters( 'ninja_forms_mijireh_product_name', $product_name, false ), $purchase_total, 1, '' );
		}

				
		// set order totals
		$mj_order->total 			= $purchase_total;
			
		// Set URL for mijireh payment notificatoin - use WC API
		$mj_order->return_url 		= str_replace( 'https:', 'http:', add_query_arg( 'nfmc_checkout', 'success', home_url( '/' ) ) );
	
		// Identify woocommerce
		$mj_order->partner_id 		= 'patsatech';

		try {
		
		    // Update our 'landing_page' form setting with the new Mijireh Checkout checkout url.
			//$ninja_forms_processing->update_form_setting( 'landing_page', $url );
			// Update our 'mijireh_redirect' form setting to 1, indicating that this form has been redirected to Mijireh Checkout.
			$ninja_forms_processing->update_form_setting( 'mijireh_redirect', 1 );
		
			// Call our function to save the submission thus far.
			ninja_forms_save_sub();
	
			// Update our submission, setting the mijireh_status value to 'pending'.
			$sub_id = $ninja_forms_processing->get_form_setting( 'sub_id' );
			$sub_row = ninja_forms_get_sub_by_id( $sub_id );

			// add meta data to identify woocommerce order
			$mj_order->add_meta_data( 'nf_order_id', $sub_id );
	
		    if ( $sub_row AND is_array ( $sub_row ) ) {
				$sub_row['mijireh_status'] = 'pending';
				$sub_row['mijireh_total'] = $purchase_total;
				unset( $sub_row['id'] );
				$sub_row['sub_id'] = $sub_id;
				ninja_forms_update_sub( $sub_row );
		    }
	
		    // Set our transient variables
		    ninja_forms_set_transient();
		
			$mj_order->create();
	
		    // Redirect the user to Mijireh Checkout
		    wp_redirect( $mj_order->checkout_url );
	
		    die();
			
		} catch (Mijireh_Exception $e) {
			$ninja_forms_processing->add_error( 'mijireh-fail', __( 'Mijireh error:', 'nf-mijireh-patsatech' ) . $e->getMessage() );
		}
		return;
	}

	/*
	 *
	 * Function that gets the $purchase_total of our form.
	 *
	 * @since 1.0
	 * @return $purchase_total string
	 */

	public function get_purchase_total() {
		// Get our form total. This can be returned as an array or a string value.
		$total = ninja_forms_mijireh_get_total();

		if ( is_array ( $total ) ) { // If this is an array, grab the string total.
			if ( isset ( $total['total'] ) ) {
			  $purchase_total = $total['total'];
			} else {
			  $purchase_total = '';
			}
		} else { // This isn't an array, so $purchase_total can just be set to the string value.
			$purchase_total = $total;
		}
		return $purchase_total;
	}

	/*
	 *
	 * Function that handles successfully sending an mijireh checkout.
	 * 1) Set the landing_page form setting to the Mijireh Checkout url.
	 * 2) Set the mijireh_redirect form setting to 1, indicating that the form has been redirected.
	 * 3) Update our form submission, setting mijireh_status to 'pending'
	 *
	 * @since 1.0
	 * @return void
	 */

	function checkout_success($url) {
		global $ninja_forms_processing;

		$purchase_total = $this->get_purchase_total();
		
	    // Update our 'landing_page' form setting with the new Mijireh Checkout checkout url.
		//$ninja_forms_processing->update_form_setting( 'landing_page', $url );
		// Update our 'mijireh_redirect' form setting to 1, indicating that this form has been redirected to Mijireh Checkout.
		$ninja_forms_processing->update_form_setting( 'mijireh_redirect', 1 );
	
		// Call our function to save the submission thus far.
		ninja_forms_save_sub();

		// Update our submission, setting the mijireh_status value to 'pending'.
		$sub_id = $ninja_forms_processing->get_form_setting( 'sub_id' );
		$sub_row = ninja_forms_get_sub_by_id( $sub_id );

	    if ( $sub_row AND is_array ( $sub_row ) ) {
			$sub_row['mijireh_status'] = 'pending';
			$sub_row['mijireh_total'] = $purchase_total;
			unset( $sub_row['id'] );
			$sub_row['sub_id'] = $sub_id;
			ninja_forms_update_sub( $sub_row );
	    }

	    // Set our transient variables
	    ninja_forms_set_transient();
		
	    // Redirect the user to Mijireh Checkout
	    wp_redirect( $url );
	    exit();
	} // function checkout_success


} // Class

/*
 *
 * Function that initialized our Mijireh Checkout processing
 *
 * @since 1.0
 * @return void
 */

function ninja_forms_mijireh_process(){
	$NF_Mijireh_Form_Process = new NF_Mijireh_Form_Process();
}

add_action( 'ninja_forms_process', 'ninja_forms_mijireh_process', 9999 );