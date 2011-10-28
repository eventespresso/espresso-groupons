<?php
function event_espresso_groupon_config_mnu(){
global $wpdb;
//$wpdb->show_errors();
//ini_set('display_errors',1); 
//error_reporting(E_ALL);	
?>
<div class="wrap">
	<div id="icon-options-event" class="icon32"> </div>
		<h2><?php echo _e('Manage Groupon Codes', 'event_espresso') ?>
<?php
	if ($_REQUEST[ 'action' ] !='edit' && $_REQUEST[ 'action' ] !='add_new_groupon') {
		$href = 'admin.php?page=groupons&amp;action=add_new_groupon';
		echo '<a href="'.$href.'" class="button add-new-h2" style="margin-left: 20px;">' . __('Add New Groupon Code', 'event_espresso') . '</a>';
	}
?>
		</h2>

		<div id="poststuff" class="metabox-holder has-right-sidebar">
			<?php event_espresso_display_right_column ();?>
			<div id="post-body">
		<div id="post-body-content"> 

<?php
	//Delete
if( $_POST['delete_groupon'] ) {
	if ( is_array($_POST['checkbox'] )) {
		while( list( $key, $value ) = each($_POST['checkbox'] )) {
			$del_id=$key;
			//Delete discount data
			$sql = "DELETE FROM ".EVENTS_GROUPON_CODES_TABLE." WHERE id='$del_id'";
			$wpdb->query($sql);
		}
	}
?>
		<div id="message" class="updated fade">
			<p><strong><?php _e('Groupon Code(s) have been successfully deleted from the database.','event_espresso'); ?></strong></p>
		</div>
<?php
}
	
	//Redeem
	if( $_POST['redeem_groupon'] ) {
		if ( is_array($_POST['checkbox'] )) {
			while( list( $key, $value ) = each($_POST['checkbox'] )) {
				$update_id=$key;
				//Update status
				$sql = "UPDATE ".EVENTS_GROUPON_CODES_TABLE." SET groupon_status='0' WHERE id = $update_id";
				$wpdb->query($sql);
			}
		}
?>
		<div id="message" class="updated fade">
			<p><strong><?php _e('Groupon Code(s) have been redeemed successfully.','event_espresso'); ?></strong></p>
		</div>
<?php
}

	//Unredeem
	if( $_POST['unredeem_groupon'] ) {
		if ( is_array($_POST['checkbox'] )) {
			while( list( $key, $value ) = each($_POST['checkbox'] )) {
				$update_id=$key;
				//Update status
				$sql = "UPDATE ".EVENTS_GROUPON_CODES_TABLE." SET groupon_status='1' WHERE id = $update_id";
				$wpdb->query($sql);
			}
		}
?>
		<div id="message" class="updated fade">
			<p><strong><?php _e('Groupon Code(s) have been redeemed successfully.','event_espresso'); ?></strong></p>
		</div>
<?php
}

	if ( isset($_POST['Submit'] )) {
		if ( $_REQUEST['action'] == 'update' ) {
			$groupon_id= $_REQUEST['groupon_id'];
			$groupon_code= htmlentities2($_REQUEST['groupon_code']);
			$groupon_status = $_REQUEST['groupon_status'];
			$groupon_holder = $_REQUEST['groupon_holder'];
			global $wpdb;
			//Post the new event into the database
			$sql = "UPDATE ".EVENTS_GROUPON_CODES_TABLE." SET groupon_code='$groupon_code', groupon_status='$groupon_status', groupon_holder='$groupon_holder' WHERE id = $groupon_id";

			if ( $wpdb->query($sql) ) {
?>
			<div id="message" class="updated fade">
				<p><strong><?php _e('The groupon code '.$_REQUEST['groupon_code'].' has been updated.','event_espresso'); ?></strong></p>
			</div>
<?php } else { ?>
			<div id="message" class="error">
			<p><strong><?php _e('The groupon code '.$_REQUEST['groupon_code'].' was not updated.','event_espresso'); ?> <?php  print $wpdb->print_error(); ?></strong></p>
			</div>
	<?php
		}
	}
}

function add_groupon_to_db() {
	global $wpdb;
	if (isset($_POST['Submit'])) {
		if ( $_REQUEST['action'] == 'add' ){
			$groupon_code= $_REQUEST['groupon_code'];
			$groupon_status = $_REQUEST['groupon_status'];
			$groupon_holder = $_REQUEST['groupon_holder'];
		
			$sql="INSERT INTO ".EVENTS_GROUPON_CODES_TABLE." (groupon_code, groupon_status, groupon_holder) VALUES('$groupon_code', '$groupon_status', '$groupon_holder')";
	
			if ($wpdb->query($sql)) {
?>
		<div id="message" class="updated fade">
			<p><strong><?php _e('The groupon code '.$_REQUEST['groupon_code'].' has been added.','event_espresso'); ?></strong></p>
		</div>
<?php
			} else {
?>
			<div id="message" class="error">
				<p><strong><?php _e('The groupon code '.$_REQUEST['groupon_code'].' was not saved.','event_espresso'); ?> <?php  //print $wpdb->print_error(); ?>.</strong></p>
			</div>
		<?php
			}
		}
	}
}


if ( $_REQUEST['action'] == 'add' ){
	add_groupon_to_db();
}


function add_new_event_groupon() {
?>
<div class="metabox-holder">
	<div class="postbox">
    <h3><?php _e('Add a Groupon Code','event_espresso'); ?></h3>
    <div class="inside">
		
      <form id="add-new-groupon" method="post" action="<?php echo $_SERVER['REQUEST_URI'];?>">
      	<input type="hidden" name="action" value="add">
				<ul>
					<li>
					<label for="groupon_code">
						<?php _e('Groupon Code: ','event_espresso'); ?><em title="<?php _e('This field is required', 'event_espresso') ?>"> *</em></label> <input type="text" id="groupon_code" name="groupon_code" size="25">
					</li>
					<li>
					<label><?php _e('Groupon Status: ','event_espresso'); ?></label>
					<input type="radio" checked="checked" name="groupon_status" value="1"><?php _e('Active', 'event_espresso'); ?>
					<input type="radio" name="groupon_status" value="0"><?php _e('Used', 'event_espresso'); ?></li>
					<!--<input name="groupon_status"></li>-->
					<li><label><?php _e('Groupon Holder: ','event_espresso'); ?></label><input type="text" name="groupon_holder" size="25"></li>
					<li>
						<p>
							<input class="button-primary" type="submit" name="Submit" value="<?php _e('Submit','event_espresso'); ?>" id="add_new_groupon" />
						</p>
					</li>
				</ul>
			</form>
		</div>
	</div>
</div>
<?php 
}

if ($_REQUEST['action'] == 'add_new_groupon'){
	add_new_event_groupon();
}
	
function edit_event_groupon(){
	
	global $wpdb;
	$id=$_REQUEST['id'];
	
	 $event_groupons = $wpdb->get_results("SELECT * FROM " . EVENTS_GROUPON_CODES_TABLE . " WHERE id = " . $id);
	 
	foreach ($event_groupons as $event_groupon){				
		$groupon_id= $event_groupon->id;
		$groupon_code= $event_groupon->groupon_code;
		$groupon_status= $event_groupon->groupon_status;
		$groupon_holder= $event_groupon->groupon_holder;
	}
?>

<!--Add event display-->
<div class="metabox-holder">
	<div class="postbox">
    <h3><?php _e('Edit Groupon Code:','event_espresso'); ?> <?php echo $groupon_code ?></h3>
		<div class="inside">
			<form method="post" action="<?php echo $_SERVER['REQUEST_URI'];?>">
				<input type="hidden" name="groupon_id" value="<?php echo $groupon_id; ?>">
				<input type="hidden" name="action" value="update">
				<ul>
					<li>
						<label>
							<?php _e('Groupon Code: ','event_espresso'); ?> <input type="text" name="groupon_code" value="<?php echo $groupon_code ?>" size="25">
						</label>
					</li>
					<li>
						<label><?php _e('Groupon Status: ','event_espresso'); ?></label>
						<?php $values=array(					
            array('id'=>'1','text'=> __('Active','event_espresso')),
            array('id'=>'0','text'=> __('Used','event_espresso')));				
            echo select_input('groupon_status', $values, $groupon_status); ?> 
					</li>
					<li>
						<label>
							<?php _e('Groupon Holder: ','event_espresso'); ?><input type="text" name="groupon_holder" value="<?php echo $groupon_holder ?>">
						</label>
					</li>
					<li>
          	<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update','event_espresso'); ?>" id="update_groupon" /> 
					</li>
				</ul>
			</form>
		</div>
	</div>
</div>
<?php 
} 

if ($_REQUEST['action'] == 'edit'){
	edit_event_groupon();
}
	
?>
<form id="form1" name="form1" method="post" action="<?php echo $_SERVER["REQUEST_URI"]?>">
 
<table id="table" class="widefat fixed" width="100%"> 

	<thead>
		<tr>
			<th class="manage-column column-cb check-column" id="cb" scope="col" style="width:4%;">
				<input type="checkbox">
			</th>
			<th class="manage-column column-comments num" id="id" style="padding-top:7px; width:4%;" scope="col" title="Click to Sort">
				<?php _e('ID','event_espresso'); ?>
			</th>
			<th class="manage-column column-title" id="name" scope="col" title="Click to Sort" style="width:20%;">
				<?php _e('Code','event_espresso'); ?>
			</th>
			<th class="manage-column column-author" id="status" scope="col" title="Click to Sort" style="width:20%;">
				<?php _e('Status','event_espresso'); ?>
			</th>
			<th class="manage-column column-author" id="name" scope="col" title="Click to Sort" style="width:20%;">
				<?php _e('Groupon Holder','event_espresso'); ?>
			</th>
			<th class="manage-column column-author" id="action" scope="col" title="Click to Sort" style="width:20%;">
				<?php _e('Action','event_espresso'); ?>
			</th>
		</tr>
	</thead>

	<tbody>
<?php 
	$wpdb->get_results("SELECT * FROM ". EVENTS_GROUPON_CODES_TABLE);
	if ($wpdb->num_rows > 0) {
		$event_groupons = $wpdb->get_results("SELECT * FROM ". EVENTS_GROUPON_CODES_TABLE ." ORDER BY id ASC");
		
		foreach ($event_groupons as $event_groupon) {
				
			$groupon_id = $event_groupon->id;
			$groupon_code = $event_groupon->groupon_code;
			$groupon_status = $event_groupon->groupon_status;
			$groupon_holder = $event_groupon->groupon_holder;		
			$active_groupon = '<span style="color: #F00; font-weight:bold;">'.__('USED','event_espresso').'</span>';
			
			if($groupon_status > 0) {
				$active_groupon = '<span style="color: #090; font-weight:bold;">'.__('ACTIVE','event_espresso').'</span>';
			}					
?>
		<tr>
			<td><input name="checkbox[<?php echo $groupon_id?>]" type="checkbox"  title="Delete <?php echo $groupon_code?>"></td>
			<td><?php echo $groupon_id?></td>
			<td><?php echo $groupon_code?></td>
			<td><?php echo $active_groupon?></td>
			<td><?php echo $groupon_holder?></td>
			<td><a href="admin.php?page=groupons&action=edit&id=<?php echo $groupon_id?>"><?php _e('Edit Groupon','event_espresso'); ?></a></td>
		</tr>
		
<?php 
		}
	}
?>
	</tbody>

</table>

	<label><input type="checkbox" name="sAll" onclick="selectAll(this)" /> <strong><?php _e('Check All','event_espresso'); ?></strong></label>

	<input name="delete_groupon" type="submit" class="button-secondary" id="delete_groupon" value="<?php _e('Delete Groupon','event_espresso'); ?>" style="margin:10px 0 0 20px;" onclick="return confirmDelete();">

	<input name="unredeem_groupon" type="submit" class="button-secondary" id="unredeem_groupon" value="<?php _e('Unredeem Groupon(s)','event_espresso'); ?>" style="margin:10px 0 0 20px;">

	<input name="redeem_groupon" type="submit" class="button-primary" id="redeem_groupon" value="<?php _e('Redeem Groupon(s)','event_espresso'); ?>" style="margin:10px 0 0 20px;">

	<?php echo '<a href="admin.php?page=groupons&amp;action=add_new_groupon" class="button-primary" style="margin-left: 20px;">' . __('Add New Groupon Code', 'event_espresso') . '</a>';
	
/***************************** ADDED BY BRENT ************************/
		$table_entries = count($event_groupons);
		 if ($_REQUEST[ 'action' ] !='edit' && $_REQUEST[ 'action' ] !='export_csv' && $table_entries > 0 ) {
		 	// makes it easier to read link
		 	$goto = get_bloginfo('wpurl') . '/wp-admin/admin.php?event_espresso&amp;export=report&action=groupons&amp;type=csv';
			echo '<a href="'.$goto.'" class="button-secondary" style="margin:10px 0 0 20px;">' . __('Export Groupon Codes to CSV', 'event_espresso') . '</a>';
		}
/***************************** brent done adding ************************/
?>		

</form>
		
<?php 
			
/***************************** ADDED BY BRENT ************************/
	
		if ($_REQUEST[ 'action' ] !='edit' && $_REQUEST[ 'action' ] !='groupon_import_csv') { 
			include( EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/form-builder/csv_uploader.php' );
			$import_what = 'Groupon Codes';
			$import_intro = 'If Groupon has supplied you with a list of Codes in a Comma Separated Value (CSV) file format, you can upload the file here: ';
			$page = 'groupons';
			$name = 'groupon_import_csv';			
			echo csv_uploader( $import_what, $import_intro, $page, $name );
		}
		
/***************************** brent done adding ************************/
		
 ?>
		
			</div>
		</div>
	</div>
</div>
	<script type="text/javascript">
        jQuery(document).ready(function($) {						
            /* show the table data */
            var mytable = $('#table').dataTable( {
                "sDom": 'Clfrtip',
                "aoColumns": [
                    { "bSortable": false },
                    null,
                    null,
                    null,
                    null,
                    null
                ],
               
                "bAutoWidth": false,	
                "bStateSave": true,
                "sPaginationType": "full_numbers",
                "oLanguage": {	"sSearch": "<strong><?php _e('Live Search Filter', 'event_espresso'); ?>:</strong>",
                    "sZeroRecords": "<?php _e('No Records Found!', 'event_espresso'); ?>" },
				"oColVis": {
					"aiExclude": [ 0, 1],
					"buttonText": "Filter: Show / Hide Columns",
					"bRestore": true
				},
        			
            } );
        	
        } );
	// Add new groupon code form validation
	jQuery(function(){
		jQuery('#add-new-groupon').validate( {
		 rules: {
		  groupon_code: "required"
		 },
		 messages: {
		 groupon_code: "Please add your groupon code"
		}
		});
		});
 </script>
<?php
	
	

//============= End Event Registration Groupon Subpage - Add/Delete/Edit Groupon Codes  =============== //
}
