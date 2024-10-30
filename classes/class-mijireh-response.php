<?php

/*
 *
 * This class handles our responses to the user when they return from the Mijireh Checkout site.
 *
 * @since 1.0
 */

class NF_Mijireh_Response
{
  
  public $token = '';
  public $checkout = '';

  /*
   * Initialize the response class
   * 
   */
  public function __construct() {
    global $ninja_forms_processing;

    // Bail if we are in the wp_admin
    if ( is_admin() )
      return;
    
    // Bail if we don't have a $ninja_forms_processing variable.
    if ( !is_object ( $ninja_forms_processing ) )
      return;

    // Get our "nfmc_checkout" from the querystring.
    if ( isset ( $_GET['nfmc_checkout'] ) ) {
      $this->checkout = $_GET['nfmc_checkout'];
    } else {
      $this->checkout = '';
    }
 
    // Bail if $nfmc_checkout hasn't been set or is an empty string.
    if ( $ninja_forms_processing->get_form_setting( 'mijireh_redirect' ) != 1 AND ( empty( $this->checkout ) OR ( $this->checkout != 'cancel' AND $this->checkout != 'success' ) ) )
      return;
    
    // Check to see if we've cancelled using the Mijireh Checkout cancel link.
    if ( $ninja_forms_processing->get_form_setting( 'mijireh_redirect' ) == 1 AND $this->checkout == 'cancel' ) {
      $this->checkout_cancel();
      return;
    }

    // Check to see if our user pressed the "back" button on their browser after clicking the submit button.
    if ( $ninja_forms_processing->get_form_setting( 'mijireh_redirect' ) == 1 AND empty ( $this->checkout ) ) {
      $this->checkout_cancel();
      return;
    }

    // We have a successful transaction from Mijireh Checkout, run our complete function
    $this->checkout_complete();

  } // function __construct

  /*
   *
   * Function to handle a success response from Mijireh Checkout.
   * 1) Call do_checkout() to finalize our payment.
   * 2) If there is an error in the checkout, add that error to our $ninja_forms_processing variable and return to our form page.
   * 3) If payment was successful, update the submission mijireh_status to 'complete' and add the mijireh_transaction_id
   *
   * @since 1.0
   * @return void
   */

	/**
	 * init_mijireh function.
	 *
	 * @access public
	 */
	public function init_mijireh() {
		if ( ! class_exists( 'Mijireh' ) ) {
	    	require_once 'includes/Mijireh.php';

			// Get Mijireh Checkout settings and return our API credentials.
			$plugin_settings = get_option( 'ninja_forms_mijireh', null );
		   	$key = ! empty( $plugin_settings['mijireh_access_key'] ) ? $plugin_settings['mijireh_access_key'] : '';

	    	Mijireh::$access_key = $key;
		}
	}

  function checkout_complete() {
    global $ninja_forms_processing;
    
	$this->init_mijireh();
    
    if( isset( $_GET['order_number'] ) ) {
  		
  		try {
			$mj_order 	= new Mijireh_Order( esc_attr( $_GET['order_number'] ) );
					
			$plugin_settings = get_option( 'ninja_forms_mijireh' );

		    //$sub_id = $ninja_forms_processing->get_form_setting( 'sub_id' );
		    $sub_id = $mj_order->get_meta_value( 'nf_order_id' );
	    	$sub_row = ninja_forms_get_sub_by_id( $sub_id );
	    	
			$mijireh_transaction_id = $_GET['order_number'];
	    	$ninja_forms_processing->update_form_setting( 'mijireh_transaction_id', $mijireh_transaction_id );
	    	$ninja_forms_processing->update_form_setting( 'mijireh_redirect', 0 );
	      	if ( $sub_row AND is_array ( $sub_row ) ) {
				$sub_row['mijireh_status'] = 'complete';
	        	$sub_row['mijireh_transaction_id'] = $mijireh_transaction_id;
	        	unset( $sub_row['id'] );
	        	$sub_row['sub_id'] = $sub_id;
	        	ninja_forms_update_sub( $sub_row );
			}
	     
	      	do_action( 'ninja_forms_checkout_success', $mj_order );
	      	// Run our post_process functions.
	      	ninja_forms_post_process();
  			
  		} catch (Mijireh_Exception $e) {
		      // We need to add an error message to our $ninja_forms_processing.
			$ninja_forms_processing->add_error( 'mijireh-fail', __( 'Mijireh error:', 'nf-mijireh-patsatech' ) . $e->getMessage() );
			
			$error_message = __( 'Mijireh error:', 'nf-mijireh-patsatech' ) . $e->getMessage();
			
		      // Update our submission's mijireh_status and mijireh_error columns
		      if ( $sub_row AND is_array ( $sub_row ) ) {
		        $sub_row['mijireh_status'] = 'error';
		        $sub_row['mijireh_error'] = $error_message;
		        unset( $sub_row['id'] );
		        $sub_row['sub_id'] = $sub_id;
		        ninja_forms_update_sub( $sub_row );
		      }
		      do_action( 'ninja_forms_checkout_fail', $mj_order );
		      /*
		      ninja_forms_set_transient();
		      wp_redirect( $ninja_forms_processing->get_form_setting( 'form_url' ) );
		      die();
		      */
  		}
	}
    elseif( isset( $_POST['page_id'] ) ) {
      if( isset( $_POST['access_key'] ) && $_POST['access_key'] == Mijireh::$access_key ) {
        wp_update_post( array( 'ID' => $_POST['page_id'], 'post_status' => 'private' ) );
      }
    }
  } // function checkout_complete

  /*
   *
   * Function to handle the cancelling of payment (using the Mijireh Checkout cancel link )
   * 1) Remove success messages.
   * 2) Add error messages and set the processing_complete variable to 0 (incomplete)
   * 3) Update the submission row to reflect the fact that this was a cancelled transaction.
   * 4) Redirect the user to the form.
   *
   * @since 1.0
   * @return void
   */

  public function checkout_cancel() {
    global $ninja_forms_processing;

    // Remove all our success messages.
    $ninja_forms_processing->remove_all_success_msgs();
    // Add our error message.
    $ninja_forms_processing->add_error( 'mijireh-fail', __( 'Mijireh Checkout Transaction was cancelled. Please try again.', 'ninja-forms-mijireh-express' ) );
    // Set processing_complete to 0 so that the form on the other end doesn't think that this was a successful submission.
    $ninja_forms_processing->update_form_setting( 'processing_complete', 0 );
    
    // If this submission has been saved, update the "mijireh status" of that submission to fail.
    $sub_id = $ninja_forms_processing->get_form_setting( 'sub_id' );
    $sub_row = ninja_forms_get_sub_by_id( $sub_id );

    if ( $sub_row AND is_array ( $sub_row ) ) {
      $sub_row['mijireh_status'] = 'cancelled';
      unset( $sub_row['id'] );
      $sub_row['sub_id'] = $sub_id;
      ninja_forms_update_sub( $sub_row );
    }

    $ninja_forms_processing->update_form_setting( 'mijireh_redirect', 0 );

    /*
    ninja_forms_set_transient();

    wp_redirect( $ninja_forms_processing->get_form_setting( 'form_url' ) );
    die();
    */
  } // function checkout_cancel

} // Class


function ninja_forms_mijireh_response(){
  $NF_Mijireh_Response = new NF_Mijireh_Response();
}

add_action( 'init', 'ninja_forms_mijireh_response', 1001 );