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
   <?php  if ($_REQUEST[ 'action' ] !='edit' && $_REQUEST[ 'action' ] !='add_new_groupon'){
				echo '<a href="admin.php?page=groupons&amp;action=add_new_groupon" class="button add-new-h2" style="margin-left: 20px;">' . __('Add New Groupon Code', 'event_espresso') . '</a>';
			}
			?>
    </h2>

	<?php
	
	
	
	/***************************** ADDED BY BRENT ************************/
	
	
	
		if ($_REQUEST[ 'action' ] !='edit' && $_REQUEST[ 'action' ] !='groupon_import_csv') { ?>
		<h3>Import Groupon Codes</h3>
		<p>If Groupon has supplied you with a list of Codes in a Comma Separted Value (CSV) file format, you can upload the file here: 
		<?php /*?><a href="admin.php?page=groupons&amp;action=groupon_import_csv" class="button-primary" style="margin-left: 20px;"><?php echo __('Import CSV ', 'event_espresso'); ?></a><?php */?>
		<form action='<?php echo $_SERVER['PHP_SELF']; ?>?page=groupons' method='post' enctype='multipart/form-data'>
		<input type='hidden' name='csv_submitted' value='TRUE' id='<?php echo time(); ?>'>
		<input name='action' type='hidden' value='groupon_import_csv' />
		<input type='hidden' name='MAX_FILE_SIZE' value='1048576'>
		<font color='red'>*</font><input type='file' name='file[]'>
		<input class='button-primary' type='submit' value='Upload File'>
		</p>
		<p><font color='red'>*</font>Maximum file name length (minus extension) is 15 characters. Anything over that will be cut to only 15 characters. Only .csv file types.</p>
		</form>
		<?php }
		
		
	/***************************** brent done adding ************************/
		
		
		 ?>


 <div id="poststuff" class="metabox-holder has-right-sidebar">
  <?php event_espresso_display_right_column ();?>
  <div id="post-body">
<div id="post-body-content"> 

<?php 

	
	
	
	/***************************** ADDED BY BRENT ************************/
	
	
	
// brent stole, i mean "borrowed" existing EE code for file uploader
	if( isset( $_POST['csv_submitted'] )) {
		foreach($_FILES["file"]["error"] as $key => $value){
			if($_FILES["file"]["name"][$key]!=""){
				if($value==UPLOAD_ERR_OK){
					$origfilename = $_FILES["file"]["name"][$key];
					$filename = explode(".", $_FILES["file"]["name"][$key]);
					$filenameext = $filename[count($filename)-1];
					unset($filename[count($filename)-1]);
					$filename = implode(".", $filename).".".$filenameext;
					if($filenameext=='csv'){
						require( EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/functions/import_export_csv.php' );
						$max_upload = espresso_get_max_upload_size();
						if($_FILES["file"]["size"][$key]<$max_upload){ 
							if(move_uploaded_file($_FILES["file"]["tmp_name"][$key], $upload_dir.$filename)){
							
								$path_to_file = $upload_dir.$filename;
								$groupon_codes_array = espresso_import_csv_to_array( $path_to_file );
								// was data successfully stored in an array?
								if ( is_array( $groupon_codes_array ) ) {
								
									// the db fields and the csv column names we want the data saved to
									$columns_to_save = array(
																													'groupon_code'			=> 'Groupon No.',
																													'groupon_status' 	=> 'Status',
																													'groupon_holder'	=> 	'Customer Name'						
																												);
																													
									$processed_groupon_codes = array();
									
									// loop through data array to do a little processing
									foreach ( $groupon_codes_array as $outerkey => $innerdata ) {
										foreach ( $innerdata as $innerkey => $value ) {
											// change Unredeemed / Redeemed values to boolean
											if ( $innerkey == 'Status' ) {
												$value = 'Redeemed' ? 1 : 0 ;
											}
											// check if column is to be saved
											if ( in_array( $innerkey, $columns_to_save ) ) {
												$processed_groupon_codes[$outerkey][$innerkey] = $value;
											}																				
										}																				
									}					

									// save processed codes to db
									if ( $result = espresso_save_csv_to_db( $processed_groupon_codes, $columns_to_save, EVENTS_GROUPON_CODES_TABLE ) ) {
?>
	<div id="message" class="updated fade"><p><strong><?php _e('Groupon Code(s) have been successfully imported into the database.','event_espresso'); ?></strong></p></div>
<?php									
									} else { ?>
<div id="message" class="error"><p><strong><?php _e('An error occured and the Groupon Code(s) were not imported into the database.','event_espresso'); ?> <?php  print $wpdb->print_error(); ?></strong></p></div><?php
									}
								
								} else {
								// no array? must be an error
									echo $groupon_csv_data;
								}
	
							}else{
								echo($origfilename." was not successfully uploaded<br />");
							} 
						}else{ 
							echo($origfilename." was too big, not uploaded<br />");
						}
					}else{
						echo($origfilename." had an invalid file extension, not uploaded<br />");
					}
				}else{
					echo($origfilename." was not successfully uploaded<br />");
				}
			}
		}
	}
		
		
	/***************************** brent done adding ************************/
		
		
?>

<?php
	if($_POST['delete_groupon']){
		if (is_array($_POST['checkbox'])){
			while(list($key,$value)=each($_POST['checkbox'])):
				$del_id=$key;
				//Delete discount data
				$sql = "DELETE FROM ".EVENTS_GROUPON_CODES_TABLE." WHERE id='$del_id'";
				$wpdb->query($sql);
				
				//$sql = "DELETE FROM ".EVENTS_GROUPON_REL_TABLE." WHERE groupon_id='$del_id'";
				//$wpdb->query($sql);
			endwhile;	
		}
		?>
	<div id="message" class="updated fade"><p><strong><?php _e('Groupon Code(s) have been successfully deleted from the database.','event_espresso'); ?></strong></p></div>
<?php
	}

if (isset($_POST['Submit'])){
	if ( $_REQUEST['action'] == 'update' ){
		$groupon_id= $_REQUEST['groupon_id'];
		$groupon_code= htmlentities2($_REQUEST['groupon_code']);
		$groupon_status = $_REQUEST['groupon_status'];
		$groupon_holder = $_REQUEST['groupon_holder'];
	global $wpdb;
		//Post the new event into the database
		$sql="UPDATE ".EVENTS_GROUPON_CODES_TABLE." SET groupon_code='$groupon_code', groupon_status='$groupon_status', groupon_holder='$groupon_holder' WHERE id = $groupon_id";

		if ($wpdb->query($sql)){ ?>
		<div id="message" class="updated fade"><p><strong><?php _e('The groupon code '.$_REQUEST['groupon_code'].' has been updated.','event_espresso'); ?></strong></p></div>
	<?php }else { ?>
		<div id="message" class="error"><p><strong><?php _e('The groupon code '.$_REQUEST['groupon_code'].' was not updated.','event_espresso'); ?> <?php  print $wpdb->print_error(); ?></strong></p></div>
	<?php
		}
	}
}

function add_groupon_to_db(){
	global $wpdb;
	if (isset($_POST['Submit'])){
		if ( $_REQUEST['action'] == 'add' ){
			$groupon_code= $_REQUEST['groupon_code'];
			$groupon_status = $_REQUEST['groupon_status'];
			$groupon_holder = $_REQUEST['groupon_holder'];
		
			$sql="INSERT INTO ".EVENTS_GROUPON_CODES_TABLE." (groupon_code, groupon_status, groupon_holder) VALUES('$groupon_code', '$groupon_status', '$groupon_holder')";
	
			if ($wpdb->query($sql)){ ?>
			<div id="message" class="updated fade"><p><strong><?php _e('The groupon code '.$_REQUEST['groupon_code'].' has been added.','event_espresso'); ?></strong></p></div>
		<?php
			}else {
		?>
			<div id="message" class="error"><p><strong><?php _e('The groupon code '.$_REQUEST['groupon_code'].' was not saved.','event_espresso'); ?> <?php  //print $wpdb->print_error(); ?>.</strong></p></div>
		<?php
			}
		}
	}
}
if ( $_REQUEST['action'] == 'add' ){add_groupon_to_db();}


function add_new_event_groupon(){
?>
    <div class="metabox-holder">
      <div class="postbox">
    <h3><?php _e('Add a Groupon Code','event_espresso'); ?></h3>
    <div class="inside">
      <form id="add-new-groupon" method="post" action="<?php echo $_SERVER['REQUEST_URI'];?>">
      <input type="hidden" name="action" value="add">
       <ul>
           <li>
           <label for="groupon_code"><?php _e('Groupon Code: ','event_espresso'); ?><em title="<?php _e('This field is required', 'event_espresso') ?>"> *</em></label> <input type="text" id="groupon_code" name="groupon_code" size="25"></li>
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
           <label><?php _e('Groupon Code: ','event_espresso'); ?></label> <input type="text" name="groupon_code" value="<?php echo $groupon_code ?>" size="25"></li>
           <li>
           <label><?php _e('Groupon Status: ','event_espresso'); ?></label>
            <?php $values=array(					
            array('id'=>'1','text'=> __('Active','event_espresso')),
            array('id'=>'0','text'=> __('Used','event_espresso')));				
            echo select_input('groupon_status', $values, $groupon_status); ?> 
          </li>
           <li><label><?php _e('Groupon Holder: ','event_espresso'); ?></label><input type="text" name="groupon_holder" value="<?php echo $groupon_holder ?>"></li>
           
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
		  <th class="manage-column column-cb check-column" id="cb" scope="col" style="width:2.5%;"><input type="checkbox"></th>
          <th class="manage-column column-comments num" id="id" style="padding-top:7px; width:2.5%;" scope="col" title="Click to Sort"><?php _e('ID','event_espresso'); ?></th>
		  <th class="manage-column column-title" id="name" scope="col" title="Click to Sort" style="width:20%;"><?php _e('Code','event_espresso'); ?></th>
		  <th class="manage-column column-author" id="status" scope="col" title="Click to Sort" style="width:20%;"><?php _e('Status','event_espresso'); ?></th>
          <th class="manage-column column-author" id="name" scope="col" title="Click to Sort" style="width:20%;"><?php _e('Groupon Holder','event_espresso'); ?></th>
          <th class="manage-column column-author" id="action" scope="col" title="Click to Sort" style="width:20%;"><?php _e('Action','event_espresso'); ?></th>
		</tr>
</thead>
    <tbody>
<?php 
		$wpdb->get_results("SELECT * FROM ". EVENTS_GROUPON_CODES_TABLE);
		if ($wpdb->num_rows > 0) {
		$event_groupons = $wpdb->get_results("SELECT * FROM ". EVENTS_GROUPON_CODES_TABLE ." ORDER BY id ASC");
			foreach ($event_groupons as $event_groupon){
				
					$groupon_id = $event_groupon->id;
					$groupon_code = $event_groupon->groupon_code;
					$groupon_status = $event_groupon->groupon_status;
					$groupon_holder = $event_groupon->groupon_holder;
					
					$active_groupon = '<span style="color: #F00; font-weight:bold;">'.__('USED','event_espresso').'</span>';
					if($groupon_status > 0){
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
		<input type="checkbox" name="sAll" onclick="selectAll(this)" /> <strong><?php _e('Check All','event_espresso'); ?></strong> 
    	<input name="delete_groupon" type="submit" class="button-secondary" id="delete_groupon" value="<?php _e('Delete Groupon','event_espresso'); ?>" style="margin-left:100px;" onclick="return confirmDelete();"> <?php echo '<a href="admin.php?page=groupons&amp;action=add_new_groupon" class="button add-new-h2" style="margin-left: 20px;">' . __('Add New Groupon Code', 'event_espresso') . '</a>';?> <?php /*?><a  style="margin-left:5px"class="button-primary" href="admin.php?page=groupons&amp;action=csv_import"><?php _e('Import CSV','event_espresso'); ?></a><?php */?>
		</form>
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
                    "sZeroRecords": "<?php _e('No Records Found!', 'event_espresso'); ?>" }
        			
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
