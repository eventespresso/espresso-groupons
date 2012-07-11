<?php
//Groupon event management functions
function event_espresso_add_new_event_groupon(){
	_e('Allow GROUPON codes?','event_espresso'); 
	$values=array(					
        array('id'=>'Y','text'=> __('Yes','event_espresso')),
        array('id'=>'N','text'=> __('No','event_espresso')));				
		echo select_input('use_groupon_code', $values, 'Y');
}
function event_espresso_edit_event_groupon($use_groupon_code){ ?>
<label><?php	_e('Allow GROUPON codes?','event_espresso') ?></label> <?php
	$values=array(					
        array('id'=>'Y','text'=> __('Yes','event_espresso')),
        array('id'=>'N','text'=> __('No','event_espresso')));				
		echo select_input('use_groupon_code', $values, $use_groupon_code);
}
function event_espresso_add_event_to_db_groupon($sql, $use_groupon_code){
	$use_groupon_code = array('use_groupon_code' => $use_groupon_code);
	$sql = array_merge((array)$sql, (array)$use_groupon_code);
	return $sql;
}

function espresso_get_groupon_total_cost($payment_data) {
	global $wpdb;
	$sql = "SELECT ac.cost, ac.quantity, dc.coupon_code_price, dc.use_percentage, gc.id  FROM " . EVENTS_ATTENDEE_TABLE . " a ";
	$sql .= " JOIN " . EVENTS_ATTENDEE_COST_TABLE . " ac ON a.id=ac.attendee_id ";
	$sql .= " LEFT JOIN " . EVENTS_DISCOUNT_CODES_TABLE . " dc ON a.coupon_code=dc.coupon_code ";
	$sql .= " LEFT JOIN " . EVENTS_GROUPON_CODES_TABLE . " gc ON a.id=gc.attendee_id AND gc.groupon_status='0' ";
	$sql .= " WHERE a.attendee_session='" . $payment_data['attendee_session'] . "'";
	$tickets = $wpdb->get_results($sql, ARRAY_A);
	$total_cost = 0;
	$total_quantity = 0;
	foreach ($tickets as &$ticket) {
		if (!empty($ticket['id'])) {
			$ticket['quantity'] -= 1;
			$ticket['coupon_code_price'] = 0;
		}
		$total_cost += $ticket['quantity'] * $ticket['cost'];
		$total_quantity += $ticket['quantity'];
	}
	if (!empty($tickets[0]['coupon_code_price'])) {
		if ($tickets[0]['use_percentage'] == 'Y') {
			$payment_data['total_cost'] = $total_cost * (1 - ($tickets[0]['coupon_code_price'] / 100));
		} else {
			$payment_data['total_cost'] = $total_cost - $tickets[0]['coupon_code_price'];
		}
	} else {
		$payment_data['total_cost'] = $total_cost;
	}
	$payment_data['total_cost'] = number_format($payment_data['total_cost'], 2, '.', '');
	$payment_data['quantity'] = $total_quantity;
	return $payment_data;
}

function espresso_use_groupon_function_for_total_cost() {
	remove_filter('filter_hook_espresso_get_total_cost', 'espresso_get_total_cost');
	add_filter('filter_hook_espresso_get_total_cost', 'espresso_get_groupon_total_cost');
}
add_action('plugins_loaded','espresso_use_groupon_function_for_total_cost');

function espresso_itemize_paypal_items_with_groupon($myPaypal, $attendee_id) {
	global $wpdb;
	$sql = "SELECT attendee_session FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id='" . $attendee_id . "'";
	$session_id = $wpdb->get_var($sql);
	$sql = "SELECT ac.cost, ac.quantity, ed.event_name, a.price_option, a.fname, a.lname, dc.coupon_code_price, dc.use_percentage, gc.id FROM " . EVENTS_ATTENDEE_COST_TABLE . " ac JOIN " . EVENTS_ATTENDEE_TABLE . " a ON ac.attendee_id=a.id JOIN " . EVENTS_DETAIL_TABLE . " ed ON a.event_id=ed.id ";
	$sql .= " LEFT JOIN " . EVENTS_DISCOUNT_CODES_TABLE . " dc ON a.coupon_code=dc.coupon_code ";
	$sql .= " LEFT JOIN " . EVENTS_GROUPON_CODES_TABLE . " gc ON a.id=gc.attendee_id AND gc.groupon_status='0' ";
	$sql .= " WHERE attendee_session='" . $session_id . "'";
	$items = $wpdb->get_results($sql);
	$coupon_amount = empty($items[0]->coupon_code_price) ? 0 : $items[0]->coupon_code_price;
	$is_coupon_pct = (!empty($items[0]->use_percentage) && $items[0]->use_percentage=='Y') ? true : false;
	$groupon_used = false;
	foreach ($items as $key=>$item) {
		$item_num=$key+1;
		if (!empty($item->id)) {
			$groupon_text = ' (groupon code used)';
			$groupon_used = true;
			$item_cost = "0.00";
		} else {
			$groupon_text = '';
			$item_cost = $item->cost;
		}
		$myPaypal->addField('item_name_' . $item_num, $item->price_option . ' for ' . $item->event_name . '. Attendee: '. $item->fname . ' ' . $item->lname . $groupon_text);
		$myPaypal->addField('amount_' . $item_num, $item_cost);
		$myPaypal->addField('quantity_' . $item_num, $item->quantity);
	}
	if (!empty($coupon_amount) && !$groupon_used) {
		if ($is_coupon_pct) {
			$myPaypal->addField('discount_rate_cart', $coupon_amount);
		} else {
			$myPaypal->addField('discount_amount_cart', $coupon_amount);
		}
	}
}

add_action('action_hook_espresso_itemize_paypal_items', 'espresso_itemize_paypal_items_with_groupon');

function espresso_use_groupon_functions() {
	remove_all_actions('action_hook_espresso_itemize_paypal_items');
	add_action('action_hook_espresso_itemize_paypal_items', 'espresso_itemize_paypal_items_with_groupon', 10, 2);
}

add_action('action_hook_espresso_use_add_on_functions', 'espresso_use_groupon_functions');