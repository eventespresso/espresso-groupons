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