<?php
function event_espresso_groupon_payment_page( $use_groupon_code = 'N', $event_id = FALSE, $event_cost = 0.00, $attendee_id = FALSE, $mer = TRUE ){

	if ( $use_groupon_code == 'Y' ){
		if ( ! empty( $_REQUEST['groupon_code'] ) || ! empty( $_POST['event_espresso_groupon_code'] )){
			
			global $wpdb;
			$msg = '';
			$error = '';

			$groupon_code = !empty($_POST['event_espresso_groupon_code']) ? wp_strip_all_tags( $_POST['event_espresso_groupon_code'] ) : wp_strip_all_tags( $_REQUEST['groupon_code'] );
			
			$SQL = "SELECT * FROM " . EVENTS_GROUPON_CODES_TABLE ;
			$SQL .= " WHERE  groupon_code = %s";
			$SQL .= " AND ( event_id = 0 OR event_id = %d )";

			if ( $groupon = $wpdb->get_row( $wpdb->prepare( $SQL, $groupon_code, $event_id ))) {	

				$valid = TRUE;
				$groupon_id = $groupon->id;
				$groupon_code = $groupon->groupon_code;
				$groupon_status = $groupon->groupon_status;
				$groupon_holder = $groupon->groupon_holder;
				
				if ( $groupon_status ) {
					$msg = '<p id="event_espresso_valid_groupon" style="margin:0;"><strong>' . __('Voucher code ','event_espresso') . $groupon_code . '</strong>' . __(' purchased by ','event_espresso').$groupon_holder.'<br/>';
				} else {
					$error = '<p id="event_espresso_invalid_groupon" style="margin:0;"><font color="red">'.__('Sorry, voucher code ', 'event_espresso') . '<strong>' . $groupon_code . '</strong>' . __(' has already been used.','event_espresso'). '</font></p>';
				}
				

				if ( $mer ) {

					$groupon_details = array();					
					$groupon_details['id'] = $groupon->id;
					$groupon_details['code'] = $groupon->groupon_code;
					$groupon_details['status'] = $groupon->groupon_status;
					$groupon_details['holder'] = $groupon->groupon_holder;
					$groupon_details['discount'] = $event_cost;
					$_SESSION['espresso_session']['events_in_session'][ $event_id ]['groupon'] = $groupon_details;
					
					$msg .= 'has being successfully applied to the following events:<br/>';
					
				} else {
				
					$payment_status = 'Completed';
					$today = date(get_option('date_format'));
					
                    if ( $attendee_id ) {
						// update attendee			
						$set_cols_and_values = array( 'coupon_code'=>$groupon_code, 'amount_pd'=>$event_cost, 'payment_status'=>$payment_status, 'payment_date' => $today );
						$set_format = array( '%s', '%f', '%s', '%s' );
						$where_cols_and_values = array( 'id'=> $attendee_id );
						$where_format = array( '%d' );						
						$wpdb->update( EVENTS_ATTENDEE_TABLE, $set_cols_and_values, $where_cols_and_values, $set_format, $where_format  );
					}
								
					$groupon_status--;
					
					// update groupon
					$SQL="UPDATE " . EVENTS_GROUPON_CODES_TABLE . " SET groupon_status = %d, date = %s WHERE id = %s";
					$wpdb->query( $wpdb->prepare( $SQL, $groupon_status, $today, $groupon_id ));	
									
				} 
				
				$event_cost = 0.00;

			} else {
			
				$valid = FALSE;
				if ( $mer ) {
					$error = '<p id="event_espresso_invalid_groupon" style="margin:0;"><font color="red">'.__('Sorry, voucher code ', 'event_espresso') . '<strong>' . $groupon_code . '</strong>' . __(' could not be found, it may be invalid.','event_espresso'). '</font></p>';				
				}
				
			}
			
			return array( 'event_cost'=>$event_cost, 'valid'=>$valid, 'msg' => $msg, 'error' => $error );		
			
		}
	}
	
	return FALSE;

}

function event_espresso_groupon_registration_page($use_groupon_code, $event_id){
	if ($use_groupon_code == "Y"){ ?>
		<p class="event_form_field" id="groupon_code-<?php echo $event_id ?>"><label for="groupon_code"><?php _e('Do you have a voucher code?','event_espresso'); ?></label> <input tabIndex="9" maxLength="25" size="35" type="text" name="groupon_code" id="groupon_code-<?php echo $event_id;?>">
		</p>
<?php
	}
}
