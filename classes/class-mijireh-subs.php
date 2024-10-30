<?php

class NF_Mijireh_Subs
{
	/*
	 *
	 * Function that constructs our class.
	 *
	 * @since 1.0
	 * @return void
	 */

	public function __construct() {
		// Add our submission table actions
		add_action( 'ninja_forms_view_sub_table_header', array( $this, 'modify_header' ) );
		add_action( 'ninja_forms_view_sub_table_row', array( $this, 'modify_tr' ), 10, 2 );
		
		// Add our CSV filters
		add_filter( 'ninja_forms_export_subs_label_array', array( $this, 'filter_csv_labels' ), 10, 2 );
		add_filter( 'ninja_forms_export_subs_value_array', array( $this, 'filter_csv_values' ), 10, 2 );

		// Add our submission editor action / filter.
		add_action( 'ninja_forms_display_after_open_form_tag', array( $this, 'change_mijireh_status' ) );
		add_filter( 'ninja_forms_edit_sub_args', array( $this, 'save_mijireh_status' ) );

    	return;
	} // function __construct

	/*
	 *
	 * Function that modifies our view subs table header if the form has Mijireh Checkout enabled.
	 *
	 * @since 1.0
	 * @return void
	 */

	function modify_header( $form_id ) {
		$form = ninja_forms_get_form_by_id( $form_id );
		if ( isset ( $form['data']['mijireh'] ) AND $form['data']['mijireh'] == 1 ) {
			?>
			<th><?php _e( 'Mijireh Checkout Status', 'nf-mijireh-patsatech' );?></th>
			<th><?php _e( 'Transaction ID', 'nf-mijireh-patsatech' );?></th>
			<?php			
		}
	} // function modify_header

	/*
	 *
	 * Function that modifies our view subs table row with Mijireh Checkout information.
	 *
	 * @since 1.0
	 * @return void
	 */

	function modify_tr( $form_id, $sub_id ) {
		$form = ninja_forms_get_form_by_id( $form_id );
		if ( isset( $form['data']['mijireh'] ) AND $form['data']['mijireh'] == 1 ) {
			$sub_row = ninja_forms_get_sub_by_id( $sub_id );

			if ( isset ( $sub_row['mijireh_status'] ) ) {
				$mijireh_status = $sub_row['mijireh_status'];
			} else {
				$mijireh_status = '';
			}		

			if ( isset ( $sub_row['mijireh_transaction_id'] ) ) {
				$mijireh_transaction_id = $sub_row['mijireh_transaction_id'];
			} else {
				$mijireh_transaction_id = '';
			}

			if ( isset ( $form['data']['mijireh'] ) AND $form['data']['mijireh'] == 1 ) {
				?>
				<td><?php echo $mijireh_status;?></td>
				<td><?php echo $mijireh_transaction_id;?></td>
				<?php			
			}			
		}

	} // function modify_tr

	/*
	 *
	 * Function that modifies the header-row of the exported CSV file by adding 'Mijireh Checkout Status' and 'Transaction ID'.
	 *
	 * @since 1.0
	 * @return $label_array array
	 */

	function filter_csv_labels( $label_array, $sub_id_array ) {
		$form = ninja_forms_get_form_by_sub_id( $sub_id_array[0] );
		if ( isset ( $form['data']['mijireh'] ) AND $form['data']['mijireh'] == 1 ) {
			array_splice($label_array[0], 2, 0, __( 'Mijireh Checkout Status', 'nf-mijireh-patsatech' ) );
			array_splice($label_array[0], 3, 0, __( 'Transaction ID', 'nf-mijireh-patsatech' ) );			
		}
		return $label_array;	
	} // function filter_csv_labels

	/*
	 *
	 * Function that modifies each row of our CSV by adding Mijireh Checkout Status and Transaction ID if the form is set to use Mijireh Checkout.
	 *
	 * @since 1.0
	 * @return $values_array array
	 */

	function filter_csv_values( $values_array, $sub_id_array ) {
		$form = ninja_forms_get_form_by_sub_id( $sub_id_array[0] );
		if ( isset ( $form['data']['mijireh'] ) AND $form['data']['mijireh'] == 1 ) {
			if( is_array( $values_array ) AND !empty( $values_array ) ){
				for ($i=0; $i < count( $values_array ); $i++) {
					if( isset( $sub_id_array[$i] ) ){
						$sub_row = ninja_forms_get_sub_by_id( $sub_id_array[$i] );
						$mijireh_status = $sub_row['mijireh_status'];
						$transaction_id = $sub_row['mijireh_transaction_id'];

						array_splice($values_array[$i], 2, 0, $mijireh_status );
						array_splice($values_array[$i], 3, 0, $transaction_id );
					}
				}
			}			
		}
		return $values_array;
	} // function filter_csv_values

	/*
	 *
	 * Function that outputs a Select element allowing users to manually change the Mijireh Checkout status of a submission.
	 *
	 * @since 1.0
	 * @return void
	 */

	function change_mijireh_status() {
		if( isset( $_REQUEST['sub_id'] ) ){
			$sub_id = $_REQUEST['sub_id'];
		}else{
			$sub_id = '';
		}
		if( $sub_id != '' ){
			$form = ninja_forms_get_form_by_sub_id( $sub_id );
			if ( isset( $form['data']['mijireh'] ) AND $form['data']['mijireh'] == 1 ) {
				$sub_row = ninja_forms_get_sub_by_id( $sub_id );
				$mijireh_status = $sub_row['mijireh_status'];
				?>
				<div>
					<?php _e( 'Mijireh Checkout Status', 'nf-mijireh-patsatech' ); ?>	
					<select name="_mijireh_status" id="">
						<option value="cancelled" <?php selected( $mijireh_status, 'cancelled' );?>><?php _e( 'Cancelled', 'nf-mijireh-patsatech' );?></option>
						<option value="complete" <?php selected( $mijireh_status, 'complete' );?>><?php _e( 'Complete', 'nf-mijireh-patsatech' );?></option>
						<option value="error" <?php selected( $mijireh_status, 'error' );?>><?php _e( 'Error', 'nf-mijireh-patsatech' );?></option>
						<option value="refund" <?php selected( $mijireh_status, 'refund' );?>><?php _e( 'Refund', 'nf-mijireh-patsatech' );?></option>
					</select>
				</div>
				<div>
					<?php _e( 'Mijireh Checkout Transaction ID', 'nf-mijireh-patsatech' );?>: 
					<?php echo $sub_row['mijireh_transaction_id']; ?>
				</div>
				<?php
			}
		}
	} // function change_mijireh_status

	/*
	 *
	 * Function that saves our new mijireh status
	 *
	 * @since 1.0
	 * @return void
	 */

	function save_mijireh_status( $args ) {
		global $ninja_forms_processing;
		if( $ninja_forms_processing->get_extra_value( '_mijireh_status' ) !== false ){
			$args['mijireh_status'] = $ninja_forms_processing->get_extra_value( '_mijireh_status' );
		}

		return $args;
	} // function save_mijireh_status
}

// Initiate our sub settings class if we are on the admin.
function ninja_forms_mijireh_modify_sub(){
	if ( is_admin() ) {
		$NF_Mijireh_Subs = new NF_Mijireh_Subs();
	}	
}

add_action( 'init', 'ninja_forms_mijireh_modify_sub', 11 );