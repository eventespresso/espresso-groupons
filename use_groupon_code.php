<?php
function event_espresso_groupon_payment_page( $use_groupon_code = 'N', $event_id, $event_cost, $attendee_id, $mer = TRUE ){

	if ( $use_groupon_code == 'Y' ){
		if ( ! empty( $_REQUEST['groupon_code'] ) || ! empty( $_POST['event_espresso_groupon_code'] )){
			
			global $wpdb;
			$msg = '';

			$groupon_code = !empty($_POST['event_espresso_groupon_code']) ? sanitize_key( $_POST['event_espresso_groupon_code'] ) : sanitize_key( $_REQUEST['groupon_code'] );
			
			$SQL = "SELECT * FROM " . EVENTS_GROUPON_CODES_TABLE . " WHERE groupon_code = %s AND groupon_status > '0' ";

			if ( $groupon = $wpdb->get_row( $wpdb->prepare( $SQL, $groupon_code ))) {	

				$valid = 1;
				$groupon_id = $groupon->id;
				$groupon_code = $groupon->groupon_code;
				$groupon_status = $groupon->groupon_status;
				$groupon_holder = $groupon->groupon_holder;
				
				$msg = '<p id="event_espresso_valid_groupon"><strong>' . __('You are using voucher code','event_espresso') . ':</strong> '.$groupon_code . __(' purchased by ','event_espresso').$groupon_holder.'</p>';
				
				if ( ! $mer ) {
				
					$payment_status = 'Completed';
					$today = date(get_option('date_format'));
								
					$sql=array('coupon_code'=>$groupon_code, 'amount_pd'=>$event_cost, 'payment_status'=>$payment_status, 'payment_date' => $today);
					$sql_data = array('%s','%s','%s', '%s');
								
					$update_id = array('id'=> $attendee_id);
					//echo '<p>$attendee_id = '.$attendee_id.'</p>';	
					$wpdb->update(EVENTS_ATTENDEE_TABLE, $sql, $update_id, $sql_data, array( '%d' ) );
								
					$groupon_status = $groupon_status - 1;
				
					$groupon_used="UPDATE " . EVENTS_GROUPON_CODES_TABLE . " SET groupon_status='" . $groupon_status . "', date='" . $today . "' WHERE id = '" . $groupon_id . "' ";
					$wpdb->query($groupon_used);	
					
					echo $msg;
									
				} else {
				
					$groupon_details = array();					
					$groupon_details['id'] = $groupon->id;
					$groupon_details['code'] = $groupon->groupon_code;
					$groupon_details['status'] = $groupon->groupon_status;
					$groupon_details['holder'] = $groupon->groupon_holder;
					$groupon_details['discount'] = $event_cost;
					$_SESSION['espresso_session']['events_in_session'][ $event_id ]['groupon'] = $groupon_details;
					
				}			
				
				$event_cost = 0.00;

			} else {
			
				$msg = '<p id="event_espresso_invalid_coupon"><font color="red">'.__('Sorry, that voucher code is invalid or has already been used.','event_espresso'). '</font></p>';				
				$valid = 0;
				if ( ! $mer ) {
					echo $msg;
				}
			}
			
			return array( 'event_cost'=>$event_cost, 'valid'=>$valid, 'msg' => $msg );		
			
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
