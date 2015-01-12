<?php


if( !class_exists('OrgHub_UploadAdminPage') ):
class OrgHub_UploadAdminPage extends APL_AdminPage
{
	
	private $model = null;
	private $users_table = null;
	
	private $process_results = '';


	public function __construct()
	{
		parent::__construct( 'upload', 'Upload', 'Upload' );
        $this->model = OrgHub_Model::get_instance();
	}
	
	public function add_head_script()
	{
		?>
		<style>
		
		.nav-tab.active {
			color:#000;
			background-color:#fff;
		}
		
		.position-controller {
			display:block;
			clear:both;
			text-align:center;
			border:solid 1px #000;
			background-color:#fff;
			padding:0px 5px;
		}
		
		.position-controller > div {
			display:inline-block;
			width:20%;
			height:30px;
			border:solid 1px #ccc;
			background-color:#eee;
			margin:10px 5px;
			cursor:pointer;
		}
		
		.position-controller > div.selected {
			border:solid 1px #000;
		}
		
		.position-controller > div:hover {
			background-color:#ffc;
		}
		
		.position-controller .hleft {
			float:left;
		}

		.position-controller .hright {
			float:right;
		}
		
		.position-controller > div.selected {
			background-color:#000;
		}
		
		.top-submit {
			float:right;
		}
		
		input.no-border {
			border:none;
			outline:none;
			box-shadow:none;
			background:transparent;
		}
		
		.filter-form {
			min-width:50%;
			max-width:100%;
		}
		
		.filter-form table {
			border:0;
			border-collapse:collapse;
			display:block;
		}
		
		.filter-form table tr {
			width:100%;
		}
		
		.filter-form table th {
			font-weight:bold;
		}
		
		.filter-form table th,
		.filter-form table td {
			width:33%;
			padding:0em 0.5em;
		}
		
		.filter-form table tr th:first-child,
		.filter-form table tr td:first-child {
			padding-left:0em;
		}

		.filter-form table tr th:last-child,
		.filter-form table tr td:last-child {
			padding-right:0em;
		}
		
		.filter-form button {
			float:left;
			margin:5px;
			margin-left:0;
		}
		
		.filter-form .scroll-box {
			height:100px;
			border:solid 1px #ccc;
			padding:5px;
			overflow-x:hidden;
			overflow-y:scroll;
		}
		
		.filter-form .scroll-box .item {
			display:block;
			white-space:nowrap;
		}
		
		.errors-checkbox {
			padding:0.5em 0em;
		}
		
		h4 {
			margin-bottom:0.2em;
		}
		
		button.process-user {
			margin:1em 0em;
		}
		
		p.exception {
			color:red;
		}
		
		p.error {
			color:orange;
		}
		
		.details-box {
			border:solid 1px #999;
			padding:1em;
		}
		
		.details-box > p {
			margin-top:0;
		}
		
		.details-box > div {
			display:inline-block;
			margin-right:1em;
		}

		.details-box > div > label {
			display:inline-block;
			vertical-align:baseline;
			padding-right:0.3em;
			font-weight:bold;
			border-right:solid 1px #ccc;
		}

		.details-box > div > span {
			display:inline-block;
			vertical-align:baseline;
			padding-left:0.3em;
		}

		.details-box .buttons {
			margin:0;
			margin-top:1em;
			padding-top:1em;
			border-top:solid 1px #ccc;
			text-align:right;
			display:block;
		}
		
		.details-box .buttons a {
			float:left;
		}

		
		.buttons button {
			margin-left:0.5em;
		}
		
		form.upload {
			padding:1em;
			margin-bottom:2em;
			border:dotted 1px #ccc;
		}
		
		form.upload h4 {
			margin-top:0;
		}
		
		form.upload p.submit {
			margin:0; padding:0;
			text-align:right;
		}
		
		#users-table {
			margin-top:2em;
		}
		
		#users-table .user-exception {
			color:red;
		}
		
		</style>
  		<script type="text/javascript">
			jQuery(document).ready( function()
			{
			
				
			
			});
		</script>
		<?php
	}


	public function process()
	{
		if( empty($_REQUEST['action']) ) return;
		
		switch( $_REQUEST['action'] )
		{
			case 'upload':
				$this->upload_file();
				break;
		}
	}


	public function upload_file()
	{
		if( !isset($_FILES) || !isset($_FILES['upload']) )
        {
            $this->process_results = 'No uploaded file.';
            return;
        }
		
        require_once( ORGANIZATION_HUB_PLUGIN_PATH . '/classes/csv-handler.php' );

		$rows = array();
		$results = OrgHub_CsvHandler::import( $_FILES['upload']['tmp_name'], $rows, false );
		
		if( $results === false )
		{
            $this->process_results = OrgHub_CsvHandler::$last_error;
            return;
		}
		
		$processed_rows = 0;
		$process_results = '';
		$user_ids = array();
		foreach( $rows as &$row )
		{
			if( $uid = $this->model->add_user($row) )
			{
				$user_ids[] = $uid;
				$processed_rows++;
			}
			else
			{
				$process_results .= $this->model->last_error.'<br/>';
			}
		}
		
		$this->model->set_inactive_users( $user_ids );
		
		$results = count($rows) . ' rows found in file.<br/>';
		$results .= $processed_rows . ' rows added or updated successfully.<br/>';
		$this->process_results = $results . $this->process_results;

		$this->model->update_options(
			array(
				'last-upload' => date('Y-m-d H:i:s'),
				'last-upload-results' => $results . $process_results,
			),
			true
		);

	}
		
	
	public function display()
	{
		$this->form_start( 'upload', array('enctype' => 'multipart/form-data'), 'upload', null );
		?>
		
		<input type="file"
			   name="<?php apl_name_e( 'upload' ); ?>"
			   accept=".csv" />
		<div class="upload-submit"><?php submit_button( 'Upload List', 'small' ); ?></div>
		<div style="clear:both"></div>
 		
 		<?php
 		$this->form_end();
	}
	
} // class OrgHub_UploadAdminPage extends APL_AdminPage
endif; // if( !class_exists('OrgHub_UploadAdminPage') ):

