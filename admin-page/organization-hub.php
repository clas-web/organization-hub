<?php


if( !class_exists('OrganizationHub_Model') )
	require_once( ORGANIZATION_HUB_PLUGIN_PATH . '/classes/model.php' );

/**
 *
 */
class OrganizationHub_AdminPage_Main extends OrganizationHub_AdminPage
{

	private static $_instance = null;
	
	
	public $slug = null;
	public $tabs = array();
	public $tab = null;
	
	private $process_results = null;
	private $model = null;
	private $users_table = null;

	
	//------------------------------------------------------------------------------------
	// Constructor.
	// Setup the page's slug and tabs.
	//------------------------------------------------------------------------------------
	private function __construct( $slug )
	{
		$this->slug = $slug;
		
		$this->tabs = array(
			'settings' => 'Settings',
			'list' => 'Users List',
			'log' => 'Log',
			'edit-user' => 'User',
		);
		$this->tabs = apply_filters( $this->slug.'-tabs', $this->tabs );
		
        $this->tab = ( !empty($_GET['tab']) && array_key_exists($_GET['tab'], $this->tabs) ? $_GET['tab'] : apply_filters( $this->slug.'-default-tab', 'settings' ) );		
        
        $this->process_results = '';
        $this->model = OrganizationHub_Model::get_instance();
	}
	
	
	
	//------------------------------------------------------------------------------------
	// Create or get the current instance of this page.
	//------------------------------------------------------------------------------------
	public static function get_instance( $slug )
	{
		if( self::$_instance === null )
		{
			self::$_instance = new OrganizationHub_AdminPage_Main( $slug );
		}
		
		return self::$_instance;
	}



//========================================================================================
//=============================================================== Scripts and Styles =====

	
	//------------------------------------------------------------------------------------
	//  
	//------------------------------------------------------------------------------------
	public function enqueue_scripts()
	{
//		wp_enqueue_style( 'google-jquery-ui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css' );
//		wp_enqueue_script( 'google-jquery-ui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js' );
// 		wp_enqueue_media();
	}
	
	
	
	//------------------------------------------------------------------------------------
	//  
	//------------------------------------------------------------------------------------
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



//========================================================================================
//========================================================================= Settings =====


	//------------------------------------------------------------------------------------
	//  
	//------------------------------------------------------------------------------------
	public function register_settings()
	{
		add_filter( $this->slug.'-process-input', array($this, 'process_input'), 99, 5 );
	}
	
	
	//------------------------------------------------------------------------------------
	//  
	//------------------------------------------------------------------------------------
	public function add_settings_sections()
	{

		//
		// Settings
		//
		
		add_settings_section(
			'settings', 'Settings', array( $this, 'print_settings_section' ),
			$this->slug.':settings'
		);

		//
		// List
		//
		
		add_settings_section(
			'list', 'Users List', array( $this, 'print_list_section' ),
			$this->slug.':list'
		);

		//
		// Log
		//
		
		add_settings_section(
			'log', 'Log', array( $this, 'print_log_section' ),
			$this->slug.':log'
		);

		add_settings_section(
			'edit-user', 'Edit User', array( $this, 'print_edit_user_section' ),
			$this->slug.':edit-user'
		);

	}
	
	
	//------------------------------------------------------------------------------------
	//  
	//------------------------------------------------------------------------------------
	public function add_settings_fields()
	{
		//
		// Settings
		//
		
		add_settings_field(
			'path-creation-type', 'Path Creation Type', array( $this, 'print_path_creation_type' ),
			$this->slug.':settings', 'settings', array( $this->tab, 'path-creation-type' )
		);
		
		add_settings_field( 
			'create-user-type', 'Create User Type', array( $this, 'print_create_user_type' ),
			$this->slug.':settings', 'settings', array( $this->tab, 'create-user-type' )
		);

		add_settings_field( 
			'connections-site-slug', 'Connections site slug', array( $this, 'print_connections_site' ),
			$this->slug.':settings', 'settings', array( $this->tab, 'connections-site-slug' )
		);

		add_settings_field( 
			'connections-site-categories', 'Connections site categories', array( $this, 'print_categories' ),
			$this->slug.':settings', 'settings', array( $this->tab, 'connections-site-categories' )
		);

		add_settings_field( 
			'profile-site-categories', 'Profile site categories', array( $this, 'print_categories' ),
			$this->slug.':settings', 'settings', array( $this->tab, 'profile-site-categories' )
		);

		//
		// List
		//

// 		add_settings_field(
// 			'list-upload-form', 'Upload New List', array( $this, 'print_upload_form' ),
// 			$this->slug.':list', 'list', array( 'list', 'upload' )
// 		);
// 
// 		add_settings_field(
// 			'list-table', 'List Table', array( $this, 'print_list_table' ),
// 			$this->slug.':list', 'list', array( 'list', 'table' )
// 		);

		//
		// Log
		//
		
// 		add_settings_field( 
// 			'log', 'Log', array( $this, 'print_log' ),
// 			$this->slug.':log', 'log', array(  )
// 		);
	}
	

//========================================================================================
//============================================================================= Save =====

	
	//------------------------------------------------------------------------------------
	//  
	//------------------------------------------------------------------------------------
	public function process_input( $options, $page, $tab, $option, $input )
	{
		if( $option !== 'organization-hub-options' ) return $options;
		
		if( !array_key_exists($tab, $input) ) return $options;
		$tab_input = $input[$tab];
		
		$tab_input = array_map( 'orghub_string_to_value', $tab_input );
// 		orghub_print($tab_input);
	
		switch( $tab )
		{
			case 'settings':
				// [settings]
// 				if( isset($tab_input['variation']) ):
// 					$variations = $orghub_config->get_variations();
// 					$chosen_variation = $tab_input['variation'];
// 					if( (!array_key_exists($chosen_variation, $variations)) && ($chosen_variation !== 'default') )
// 					{
// 						add_settings_error( '', '', 'Invalid variation: '.$chosen_variation );
// 						return $options;
// 					}
// 					$orghub_config->set_variation( $chosen_variation );
// 				endif;
				
				break;
			
			case 'list':
				echo '<pre>';
				var_dump($input);
				var_dump($_POST);
				var_dump($_FILES);
				echo '</pre>';
				exit;
				break;
			
			case 'log':
				break;
		}

		return array_merge( $options, array($tab => $tab_input) );
	}
	


//========================================================================================
//========================================================================== Display =====


	public function process()
	{
		$users = $this->model->get_users();
		
		foreach( $users as $u )
		{
			$exceptions = $this->model->get_exceptions( $u['id'] );
			if( count($exceptions) == 0 )
			{
				$this->model->clear_user_exceptions( $u['id'] );
			}
		}
		
		if( empty($_REQUEST['action']) ) return;
		
		switch( $this->tab )
		{
			case 'settings':
				$this->process_settings_page();
				break;
			case 'list':
				$this->process_list_page();
				break;
			case 'log':
				$this->process_log_page();
				break;
			case 'edit-user':
				$this->process_edit_user_page();
				break;
		}
	}
	
	private function process_settings_page()
	{
		switch( $_REQUEST['action'] )
		{
			case 'save':
				$this->save_settings();
				break;
		}
	}
	
	private function process_list_page()
	{
		switch( $_REQUEST['action'] )
		{
			case 'upload':
				echo 'upload : ';
				$this->upload_file();
				break;
			case 'Process All Users':
				$this->process_users();
				break;
		}
	}
	
	private function process_log_page()
	{
		switch( $_REQUEST['action'] )
		{
		}
	}
	
	private function process_edit_user_page()
	{
		$user_id = intval($_REQUEST['id']);
		if( !$user_id ) return;
		
		switch( $_REQUEST['action'] )
		{
			case 'update-status':
				// TODO: update status function
				break;
			case 'Process User':
				$this->model->process_user( $user_id );
				break;
			case 'create-username':
				$this->model->create_username( $user_id );
				break;
			case 'delete-username':
				// TODO: delete username function
				//$this->model->delete_username( $user_id );
				break;
			case 'reset-wp-user-id':
				$this->model->update_wp_user_id( $user_id, null );
				break;
			case 'create-site':
				$this->model->create_site( $user_id, $_REQUEST['site-path'], true );
				break;
			case 'archive-site':
				$this->model->archive_site( $user_id );
				break;
			case 'publish-site':
				$this->model->publish_site( $user_id );
				break;
			case 'reset-profile-site-id':
				$this->model->update_profile_site_id( $user_id, null );
				break;
			case 'create-connections-post':
				$this->model->create_connections_post( $user_id, true );
				break;
			case 'draft-connections-post':
				$this->model->draft_connections_post( $user_id );
				break;
			case 'reset-connections-post-id':
				$this->model->update_connections_post_id( $user_id, null );
				break;
			case 'clear-username-error':
				$this->model->remove_user_exception( $user_id, 'username' );
				break;
			case 'clear-site-error':
				$this->model->remove_user_exception( $user_id, 'site' );
				break;
			case 'clear-connections-error':
				$this->model->remove_user_exception( $user_id, 'connections' );
				break;
		}
		
		//wp_redirect( 'admin.php?page=organization-hub&tab=edit-user&id='.$_REQUEST['id'] );
	}
	
	public function upload_file()
	{
		$filename = $_FILES['organization-hub-options']['tmp_name'][$this->tab]['upload-list'];
		if( empty($filename) )
        {
            $this->process_results = 'No uploaded file.';
            return;
        }

        require_once( ORGANIZATION_HUB_PLUGIN_PATH . '/classes/csv-importer.php' );

		$rows = array();
		$results = OrganizationHub_CSVImporter::import( $filename, $rows, false );
		
		if( $results === false )
		{
            $this->process_results = OrganizationHub_CSVImporter::$last_error;
            return;
		}
		
		$processed_rows = 0;
		$this->process_results = '';
		foreach( $rows as &$row )
		{
			// TODO: check for required fields (here or in add_user).
			
			if( $this->model->add_user($row) )
			{
				$processed_rows++;
			}
			else
			{
				$this->process_results .= $this->model->last_error.'<br/>';
			}
		}
		
		$results = count($rows) . ' rows found in file.<br/>';
		$results .= $processed_rows . ' rows processed successfully.<br/>';
		$this->process_results = $results . $this->process_results;
	}
	
	public function process_users()
	{
		$users = $this->model->get_users();
		//orghub_print($users, 'users');
		
		foreach( $users as $user )
		{
			$this->model->process_user( $user );
		}
		
		$this->process_results = 'Done processing the user list.';
	}
	
	public function save_settings()
	{
		$options = $_POST['organization-hub-options'][$this->tab];
		if( !$options ) return;

		$this->model->update_options( $options, true );
	}

	
	//------------------------------------------------------------------------------------
	//  
	//------------------------------------------------------------------------------------
	public function show()
	{
		$this->process();
		?>
		
		<div class="wrap tab-<?php echo $this->tab; ?>">
	 
			<div id="icon-themes" class="icon32"></div>
			<h2>Organization Hub</h2>
			<?php settings_errors(); ?>
		 
			<h2 class="nav-tab-wrapper">
				<?php foreach( $this->tabs as $k => $t ): ?>
					<?php if( $k != 'edit-user' ): ?>
					<a href="?page=<?php echo $this->slug; ?>&tab=<?php echo $k; ?>" class="nav-tab <?php if($k==$this->tab) echo 'active'; ?>"><?php echo $t; ?></a>
					<?php endif; ?>
				<?php endforeach; ?>
			</h2>
		
			<?php
			switch( $this->tab )
			{
				case 'settings':
					$this->print_settings_form_page();
					break;
				
				default:
					$this->print_settings_page();
					break;
			}
			?>
			
		</div><!-- /.wrap -->
		
		<?php
	}
	
	
	private function print_settings_page()
	{
		global $wp_settings_sections;
		
		?>
		<div style="clear:both"></div>
		
		<?php
		do_settings_sections( $this->slug.':'.$this->tab );
		
		$tab_section = $this->slug.':'.$this->tab.':';
		foreach( array_keys($wp_settings_sections) as $section_name )
		{
			if( substr($section_name, 0, strlen($tab_section)) === $tab_section )
			{
				do_settings_sections( $section_name );
			}
		}
		?>
		
		<div style="clear:both"></div>
		<?php
	}
	
	
	private function print_settings_form_page()
	{
		global $wp_settings_sections;
		?>
		
		<form method="post" action="admin.php?page=<?php echo $this->slug; ?>&tab=<?php echo $this->tab; ?>&action=save">
			<div class="top-submit"><?php submit_button(); ?></div>
			<div style="clear:both"></div>
			<?php settings_fields( $this->slug ); ?>
			<input type="hidden" name="tab" value="<?php echo $this->tab; ?>" />
			
			<?php
			do_settings_sections( $this->slug.':'.$this->tab );
			
			$tab_section = $this->slug.':'.$this->tab.':';
			foreach( array_keys($wp_settings_sections) as $section_name )
			{
				if( substr($section_name, 0, strlen($tab_section)) === $tab_section )
				{
					do_settings_sections( $section_name );
				}
			}
			?>
			
			<div style="clear:both"></div>
			<div class="bottom-submit"><?php submit_button(); ?></div>
		</form>
		
		<?php
	}
	
	
	
	public function add_screen_options()
	{
		if( $this->tab == 'list' )
		{
			$option = 'per_page';
			$args = array(
				'label' => 'Users',
				'default' => 100,
				'option' => 'users_per_page'
			);
			add_screen_option( $option, $args );
			
			require_once( ORGANIZATION_HUB_PLUGIN_PATH.'/classes/list-table.php' );
			$this->users_table = new OrganizationsHub_ListTable();
		}
	}
	
	

//========================================================================================
//========================================================= Display Setting Sections =====

	
	
	//------------------------------------------------------------------------------------
	//  
	//------------------------------------------------------------------------------------
	public function print_settings_section()
	{
		echo '<p>print_settings_section</p>';
		
//		orghub_print( get_current_blog_id(), 'blog id' );
	}
	

	//------------------------------------------------------------------------------------
	//  
	//------------------------------------------------------------------------------------
	public function print_list_section()
	{
		?>
		
		
		<form class="upload" method="post" action="admin.php?page=<?php echo $this->slug; ?>&tab=<?php echo $this->tab; ?>&action=upload" enctype="multipart/form-data">

		<h4>Upload New List</h4>

			<?php //settings_fields( $this->slug ); ?>
 	    	<input type="file"
 			       name="<?php orghub_input_name_e( $this->tab, 'upload-list' ); ?>"
 			       accept=".csv" />
 			<div class="upload-submit"><?php submit_button( 'Upload List', 'small' ); ?></div>
 			<div style="clear:both"></div>
		</form>
		
		
		<?php if( $this->process_results ): ?>
			<div class="process-results">
				<?php echo $this->process_results; ?>
			</div>
		<?php endif; ?>
		
		<form action="admin.php">
			<input type="hidden" name="page" value="<?php echo $this->slug; ?>" />
			<input type="hidden" name="tab" value="<?php echo $this->tab; ?>" />
			<?php submit_button( 'Process All Users', 'primary', 'action' ); ?>
		</form>
		
		<?php
		$filter_types = array(
			'status' => array(
				'name' => 'Status',
				'values' => $this->model->get_all_status_types(),
				'filter' => array(),
			),
			'user' => array(
				'name' => 'User',
				'values' => $this->model->get_all_user_types(),
				'filter' => array(),
			),
			'category' => array(
				'name' => 'Category',
				'values' => $this->model->get_all_category_types(),
				'filter' => array(),
			)
		);
		
		foreach( array_keys($filter_types) as $type )
		{
			if( !empty($_GET[$type]) )
			{
				if( is_array($_GET[$type]) ) $filter_types[$type]['filter'] = $_GET[$type];
				else $filter_types[$type]['filter'] = array( $_GET[$type] );
			}
		}
		
		$filter = array();
		foreach( $filter_types as $type => $f )
		{
			if( count($f['filter']) > 0 )
			{
				switch($type)
				{
					case 'user':
						$filter['type'] = $f['filter'];
						break;
					default:
						$filter[$type] = $f['filter'];
						break;
				}
			}
		}
		
		//orghub_print($filter);
		
		//orghub_print( $filter_types );

		if( $this->users_table == null )
		{
			require_once( ORGANIZATION_HUB_PLUGIN_PATH.'/classes/list-table.php' );
			$this->users_table = new OrganizationsHub_ListTable();
		}
		
		$only_errors = false;
		if( $_REQUEST['show-only-errors'] === '1' )
			$only_errors = true;
		
		$this->users_table->prepare_items( $filter, $only_errors );
		?>
		
		<form action="admin.php" class="filter-form">
			
			<?php if( !empty($_GET) ): ?>
				<?php foreach( $_GET as $k => $v ): ?>
					<?php if( (!in_array($k, array_keys($filter_types))) && ($k !== 'action') ): ?>
						<input type="hidden" name="<?php echo $k; ?>" value="<?php echo $v; ?>" />
					<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>

			<table>
			<tr>
			
			<?php foreach( $filter_types as $key => $type ): ?>
			
				<th class="<?php echo $key; ?>">
				<?php echo $type['name']; ?>
				</th>
			
			<?php endforeach; ?>
			
			</tr>
			<tr>
			
			<?php foreach( $filter_types as $key => $type ): ?>
			
				<td class="<?php echo $key; ?>">	
				<div class="scroll-box">
				<?php foreach( $type['values'] as $value ): ?>
					<div class="item">
					<input type="checkbox"
						   name="<?php echo $key; ?>[]"
						   id="<?php orghub_input_name_e( $key, $value ); ?>"
						   value="<?php echo $value; ?>"
				           <?php checked( true, in_array($value, $type['filter']) ); ?> />
					<label for="<?php orghub_input_name_e( $key, $value ); ?>">
						<?php echo $value; ?>
					</label>
					</div>
				<?php endforeach; ?>
				</div>
				</td>
			
			<?php endforeach; ?>
			
			</tr>
			</table>
			
			<div class="errors-checkbox">
			<input type="checkbox"
			       name="show-only-errors"
			       id="<?php orghub_input_name_e( 'show-only-errors' ); ?>"
			       value="1"
			       <?php checked( '1', $_REQUEST['show-only-errors'] ); ?> />
			<label for="<?php orghub_input_name_e( 'show-only-errors' ); ?>" >
			       Only show users that have errors.
			</label>
			</div>
			
			<button>Apply Filters</button>
			
		</form>
		

		<form action="admin.php" class="filter-form">
			
			<?php if( !empty($_GET) ): ?>
				<?php foreach( $_GET as $k => $v ): ?>
					<?php if( (!in_array($k, array_keys($filter_types))) && ($k !== 'action') ): ?>
						<input type="hidden" name="<?php echo $k; ?>" value="<?php echo $v; ?>" />
					<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>
		
			<button>Clear Filters</button>
			
		</form>

		<form id="users-table" action="admin.php?page=<?php echo $this->slug; ?>&tab=<?php echo $this->tab; ?>" method="post">
			<?php $this->users_table->search_box('search','users-table-search'); ?>
			<?php $this->users_table->display(); ?>
		</form>
		<?php
	}
	

	//------------------------------------------------------------------------------------
	//  
	//------------------------------------------------------------------------------------	
	public function print_log_section()
	{
		echo '<p>print_log_section</p>';
	}
	
	
//========================================================================================
//================================================================== Settings Fields =====



	public function print_path_creation_type( $args )
	{
		?>
		<div class="path-creation-type">
			<select name="<?php orghub_input_name_e( 'settings', 'path-creation-type' ); ?>">
				<option value="username-slug"
				        <?php selected( 'username-slug', $this->model->get_option('path-creation-type') ); ?>>
				    Username slug
				</option>
				
				<option value="full-name-slug"
				        <?php selected( 'full-name-slug', $this->model->get_option('path-creation-type') ); ?>>
				    Full Name slug
				</option>

			</select>
		</div>
		<?php
	}

	//------------------------------------------------------------------------------------
	//  
	//------------------------------------------------------------------------------------
	public function print_create_user_type( $args )
	{
		?>
		<div class="create-user-type">
			<select name="<?php orghub_input_name_e( 'settings', 'create-user-type' ); ?>">
				<option value="local"
				        <?php selected( 'local', $this->model->get_option('create-user-type') ); ?>>
				    Local
				</option>
				
				<?php if( $this->model->is_ldap_plugin_active() ): ?>
				<option value="wpmu-ldap"
				        <?php selected( 'wpmu-ldap', $this->model->get_option('create-user-type') ); ?>>
				    WPMU LDAP User
				</option>
				<?php endif; ?>

			</select>
		</div>
		<?php
	}
	
	
	public function print_connections_site( $args )
	{
		?>
		<div class="connections-site">
			<input type="text"
			       name="<?php orghub_input_name_e( $args ); ?>"
			       value="<?php echo $this->model->get_option( $args[count($args)-1] ); ?>" />
		</div>
		<?php
	}
	
	
	public function print_categories( $args )
	{
		?>
		<div class="categories">
			<input type="text"
			       name="<?php orghub_input_name_e( $args ); ?>"
			       value="<?php echo $this->model->get_option( $args[count($args)-1] ); ?>" />
		</div>
		<?php
	}
	
	
	//------------------------------------------------------------------------------------
	//  
	//------------------------------------------------------------------------------------
	public function print_upload_form( $args )
	{
		echo 'print_upload_form';
	}
	
	
	//------------------------------------------------------------------------------------
	//  
	//------------------------------------------------------------------------------------
	public function print_list_table( $args )
	{
		echo 'print_list_table';

/*		
// 		$title_position = $orghub_config->get_value( 'header', 'title-position' );
// 		
// 	    <div class="position">
// 			<input type="text"
// 			       name="<?php orghub_input_name_e( $this->tab, 'title-position' ); ?>"
// 			       value="<?php echo $title_position; ?>" />
// 		</div>
*/

/*
		require_once( ORGANIZATION_HUB_PLUGIN_PATH.'/classes/list-table.php' );
		$list_table = new OrganizationsHub_ListTable();
		$list_table->prepare_items(); 
		$list_table->display();
*/
	}
	
	
	//------------------------------------------------------------------------------------
	//  
	//------------------------------------------------------------------------------------
	public function print_log( $args )
	{
		echo 'print_log';
	}
	
	
	public function print_edit_user_section( $args )
	{
		$id = $_REQUEST['id'];

		if( empty($id) )
		{
			?>
			<p class="no-id">No id provided.</p>
			<?php
			return;
		}

		$user = $this->model->get_user_by_id( $id );
		if( empty($user) )
		{
			?>
			<p class="invalid-id">Invalid id provided: "<?php echo $id; ?>"</p>
			<?php
			return;
		}
		?>
		
		<form action="admin.php">
			<input type="hidden" name="page" value="<?php echo $this->slug; ?>" />
			<input type="hidden" name="tab" value="<?php echo $this->tab; ?>" />
			<input type="hidden" name="id" value="<?php echo $id; ?>" />
		
		<?php
		// button: Process User
		submit_button( 'Process User', 'primary', 'action' ); ?>
		<?php
		
		
		// WP User account
		?>
		<h4>WordPress User Account</h4>
		
		<div id="wp-user-account-details" class="details-box">
			
			<?php

			$exception = $this->model->get_user_exception( $id, 'username' );
			if( $exception )
			{
				?>
				<p class="exception">
					<?php echo $exception; ?>
					<a href="admin.php?page=<?php echo $this->slug; ?>&tab=<?php echo $this->tab; ?>&id=<?php echo $id; ?>&action=clear-username-error">Clear Error</a>
				</p>
				<?php
			}
				
			// if wp_user_id is set
				// if user id exists, then list user details
				// else ERROR, wp_user_id set but user does not exist.

			$wp_user = null;
			if( $user['wp_user_id'] ):
				$wp_user = $this->model->get_wp_user( $user['wp_user_id'] );
			
				if( $wp_user ):
					/*
					["ID"]=>string(3) "294"
					["user_login"]=>string(8) "iaggarwa"
					["user_email"]=>string(24) "Ishwar.Aggarwal@uncc.edu"
					["display_name"]=>string(15) "Ishwar Aggarwal"
					*/
					//orghub_print($wp_user->data);
// 					orghub_print($wp_user->data->ID);
// 					orghub_print($wp_user->data->user_login);
// 					orghub_print($wp_user->data->user_email);
// 					orghub_print($wp_user->data->display_name);
					
					?>
					<div class="user-id"><label>ID</label><span><?php echo $wp_user->data->ID; ?></span></div>
					<div class="user-login"><label>Login</label><span><?php echo $wp_user->data->user_login; ?></span></div>
					<div class="user-name"><label>Name</label><span><?php echo $wp_user->data->display_name; ?></span></div>
					<div class="user-email"><label>Email</label><span><?php echo $wp_user->data->user_email; ?></span></div>
					<?php
				else:
					?><p class="error">ERROR: wp_user_id set ("<?php echo $user['wp_user_id']; ?>") but user does not exist.</p><?php
				endif;
			else:
				?><p>No user set.</p><?php
			endif;
			?>
		
			<div class="buttons">
			
				<?php
				// buttons: 
					// if user exists, delete user; else, create user.
					// if wp_user_id is set, reset wp_user_id 
		
				if( $wp_user ):
					?><a href="<?php echo network_admin_url( 'user-edit.php?user_id='.$wp_user->ID ); ?>" target="_blank">Edit User</a><?php
					/*?><button name="action" value="delete-username">Delete User</button><?php*/
				else:
					?><button name="action" value="create-username">Create User</button><?php
				endif;
				
				if( $user['wp_user_id'] ):
					?><button name="action" value="reset-wp-user-id">Reset wp_user_id</button><?php
				endif;
				?>		
			
			</div>
		
		</div>
		
		<?php
		// Profile Site
		?>
		<h4>Profile Site</h4>
		
		<div id="profile-site-details" class="details-box">
			
			<?php

			$exception = $this->model->get_user_exception( $id, 'site' );
			if( $exception )
			{
				?>
				<p class="exception">
					<?php echo $exception; ?>
					<a href="admin.php?page=<?php echo $this->slug; ?>&tab=<?php echo $this->tab; ?>&id=<?php echo $id; ?>&action=clear-site-error">Clear Error</a>
				</p>
				<?php
			}

			// if profile_site_id is set
				// if profile site exists, then list site details
				// else ERROR, profile_site_id is set but does not exist.

			$profile_site = null;
			if( $user['profile_site_id'] ):
				$profile_site = $this->model->get_profile_site( $user['profile_site_id'] );
			
				if( $profile_site ):
					/*
					["blog_id"]=>
					["domain"]=>
					["path"]=>
  					["archived"]=>
  					["blogname"]=>
  					["siteurl"]=>
    				*/
// 					orghub_print($profile_site->blog_id);
// 					orghub_print($profile_site->domain);
// 					orghub_print($profile_site->path);
// 					orghub_print($profile_site->archived);
// 					orghub_print($profile_site->blogname);
// 					orghub_print($profile_site->siteurl);

					?>
					<div class="site-id"><label>ID</label><span><?php echo $profile_site->blog_id; ?></span></div>
					<div class="site-name"><label>Name</label><span><?php echo $profile_site->blogname; ?></span></div>
					<div class="site-url"><label>URL</label><span><?php echo $profile_site->siteurl; ?></span></div>
					<div class="site-archived"><label>Archived</label><span><?php echo ($profile_site->archived == '0' ? 'No' : 'Yes'); ?></span></div>
					<?php
				else:
					?><p class="error">ERROR: profile_site_id set ("<?php echo $user['profile_site_id']; ?>") but site does not exist.</p><?php
				endif;
			else:
				?><p>No profile site set.</p><?php
			endif;
			?>
		
			<div class="buttons">
			
				<?php
				// buttons:
					// if profile site exists, archive site & delete site; else create Site
					// create site, specify path to site.
					// if profile_site_id is set, reset profile_site_id
		
				if( $profile_site ):
					?><a href="<?php echo network_admin_url( 'site-info.php?id='.$profile_site->blog_id ); ?>" target="_blank">Edit Site</a><?php
					if( $profile_site->archived == '0' ):
						?><button name="action" value="archive-site">Archive Site</button><?php
					else:
						?><button name="action" value="publish-site">Publish Site</button><?php
					endif;
				else:
					$path_creation_type = $this->model->get_option( 'path-creation-type', 'username-slug' );
					$path = '';
					switch( $path_creation_type )
					{
						case 'full-name-slug':
							$path = sanitize_title( $user['first_name'].' '.$user['last_name'] );
							break;
				
						case 'username-slug':
							$path = $user['username'];
							break;
					}
					?>
					<label for="site-path">Path:</label>
					<input type="text" id="site-path" name="site-path" value="<?php echo $path; ?>" />
					<button name="action" value="create-site">Create Site</button>
					<?php
				endif;
				
				if( $user['profile_site_id'] ):
					?><button name="action" value="reset-profile-site-id">Reset profile_site_id</button><?php
				endif;
				?>		
			
			</div>
		
		</div>		

		<?php
		// Connections Post
		?>
		<h4>Connections Post</h4>
		
		<div id="connections-post-details" class="details-box">
			
			<?php
			$exception = $this->model->get_user_exception( $id, 'connections' );
			if( $exception )
			{
				?>
				<p class="exception">
					<?php echo $exception; ?>
					<a href="admin.php?page=<?php echo $this->slug; ?>&tab=<?php echo $this->tab; ?>&id=<?php echo $id; ?>&action=clear-connections-error">Clear Error</a>
				</p>
				<?php
			}

			// if connections_post_id is set
				// if connection post exists, then list connection post details
				// else ERROR, connections_post_id is set but does not exist.

			$connections_post = null;
			if( $user['connections_post_id'] ):
				$connections_post = $this->model->get_connections_post( $user['connections_post_id'] );
			
				if( $connections_post ):
					/*
					["ID"]=>int(2000)
					["post_author"]=>string(3) "294"
					["post_title"]=>string(17) "Ishwar   Aggarwal"				  
  					["post_status"]=>string(5) "draft"
    				*/
    				//orghub_print($connections_post);
// 					orghub_print($connections_post['ID']);
// 					orghub_print($connections_post['post_author']);
// 					orghub_print($connections_post['post_title']);
// 					orghub_print($connections_post['post_status']);
					
					$author = get_user_by( 'id', $connections_post['post_author'] );

					?>
					<div class="connections-id"><label>ID</label><span><?php echo $connections_post['ID']; ?></span></div>
					<div class="connections-title"><label>Title</label><span><?php echo $connections_post['post_title']; ?></span></div>
					<div class="connections-author"><label>Author</label><span><?php echo $author->display_name; ?></span></div>
					<div class="connections-draft"><label>Status</label><span><?php echo $connections_post['post_status']; ?></span></div>
					<?php

				else:
					?><p class="error">ERROR: connections_post_id set ("<?php echo $user['connections_post_id']; ?>") but connections post does not exist.</p><?php
				endif;
			else:
				?><p>No connections post set.</p><?php
			endif;
			?>
		
			<div class="buttons">
			
				<?php
				// buttons:
					// if connection post exists, draft & delete connection post; else create post
					// if connections_post_id is set, reset connections_post_id
		
				if( $connections_post ):
					?><a href="<?php echo $this->model->get_connections_post_edit_link($connections_post['ID']); ?>" target="_blank">Edit Post</a><?php
					?><button name="action" value="draft-connections-post">Draft Post</button><?php
				else:
					?><button name="action" value="create-connections-post">Create Post</button><?php
				endif;
				
				if( $user['connections_post_id'] ):
					?><button name="action" value="reset-connections-post-id">Reset connections_post_id</button><?php
				endif;
				?>		
			
			</div>
		
		</div>
		<?php
		
		// button: Process User
		submit_button( 'Process User', 'primary', 'action' ); 
		?>
		</form>
		<?php
		
	}
	
}


