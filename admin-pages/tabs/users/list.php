<?php


if( !class_exists('OrgHub_UsersListTable') ):
require_once( ORGANIZATION_HUB_PLUGIN_PATH.'/classes/users-list-table.php' );
endif;

/**
 * OrgHub_UsersListTabAdminPage
 * 
 * 
 * 
 * @package    orghub
 * @subpackage admin-pages/tabs/users
 * @author     Crystal Barton <cbarto11@uncc.edu>
 */

if( !class_exists('OrgHub_UsersListTabAdminPage') ):
class OrgHub_UsersListTabAdminPage extends APL_TabAdminPage
{
	
	private $model = null;	
	private $list_table = null;
	
	private $filter_types;
	private $filter;
	private $search;
	private $orderby;
	private $show_errors;
	
	
	
	/**
	 * 
	 */
	public function __construct( $parent )
	{
		parent::__construct( 'list', 'Users List', $parent );
		$this->model = OrgHub_Model::get_instance();
	}

	
	public function init()
	{
		$this->setup_filters();
		$this->list_table = new OrgHub_UsersListTable( $this );
	}
	
	/**
	 * 
	 */
	public function load()
	{
		$this->list_table->load();
	}
	

	/**
	 *
	 */
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
		
		#users-table > table {
			white-space: nowrap !important;
			width: auto !important;
			min-width:100% !important;
			table-layout: auto !important;
		}
		
		#users-table > table td,
		#users-table > table th {
			white-space: nowrap;
			text-overflow: ellipsis;
		}
		
		</style>
  		<script type="text/javascript">
			jQuery(document).ready( function()
			{
			
				
			
			});
		</script>
		<?php
	}
	
	
	/**
	 * 
	 */
	public function add_screen_options()
	{
		$this->add_per_page_screen_option( 'orghub_users_per_page', 'Users', 100 );
		$this->add_selectable_columns( $this->list_table->get_selectable_columns() );
	}
	
	
	/**
	 * 
	 */
	public function process()
	{
		$this->list_table->process_batch_action();

		if( empty($_REQUEST['action']) ) return;
		
		switch( $_REQUEST['action'] )
		{
			case 'Process All Users':
			case 'process-users':
				$this->process_users();
				break;
			
			case 'export':
				$this->export_users();
				break;
		}
	}
	

	/**
	 * 
	 */
	public function process_users()
	{
		$users = $this->model->get_users();
		
		foreach( $users as $user )
		{
			$this->model->process_user( $user );
		}
		
		$this->model->update_options(
			array(
				'last-process' => date('Y-m-d H:i:s'),
				'last-process-results' => 'Successfully done processing the user list.',
			),
			true
		);
	}
	
	
	/**
	 * 
	 */
	public function export_users()
	{
        require_once( ORGANIZATION_HUB_PLUGIN_PATH . '/classes/csv-handler.php' );
		$this->model->get_csv_export( $this->filters, $this->search, $this->show_errors, $this->orderby );
		exit;
	}
	
	
	/**
	 * 
	 */
	private function setup_filters()
	{
		if( isset($this->filter_types) ) return $this->filter_types;
		
		$this->filter_types = array(
			'status' => array(
				'name' => 'Status',
				'values' => $this->model->get_all_status_values(),
				'filter' => array(),
			),
			'type' => array(
				'name' => 'Type',
				'values' => $this->model->get_all_type_values(),
				'filter' => array(),
			),
			'category' => array(
				'name' => 'Category',
				'values' => $this->model->get_all_category_values(),
				'filter' => array(),
			),
			'site_domain' => array(
				'name' => 'Domain',
				'values' => $this->model->get_all_site_domain_values(),
				'filter' => array(),
			),
			'site' => array(
				'name' => 'Connections Sites',
				'values' => $this->model->get_all_connections_sites_values(),
				'filter' => array(),
			),
		);
		
		foreach( array_keys($this->filter_types) as $type )
		{
			if( !empty($_GET[$type]) )
			{
				if( is_array($_GET[$type]) ) $this->filter_types[$type]['filter'] = $_GET[$type];
				else $this->filter_types[$type]['filter'] = array( $_GET[$type] );
			}
		}
		
		$this->filters = array();
		foreach( $this->filter_types as $type => $f )
		{
			if( count($f['filter']) > 0 )
				$this->filters[$type] = $f['filter'];
		}
		
		$this->search = array();
		if( !empty($_REQUEST['s']) )
		{
			$this->search['username'] = array( $_REQUEST['s'] );
			$this->search['first_name'] = array( $_REQUEST['s'] );
			$this->search['last_name'] = array( $_REQUEST['s'] );
		}
		
		$this->show_errors = false;
		if( isset($_REQUEST['show-only-errors']) && $_REQUEST['show-only-errors'] === '1' )
			$this->show_errors = true;
		
		$this->orderby = ( !empty($_GET['orderby']) ? $_GET['orderby'] : 'username' );
		$order = ( !empty($_GET['order']) ? $_GET['order'] : 'asc' );
		
		switch( $order )
		{
			case 'asc': case 'desc': break;
			default: $order = null; break;
		}

		switch( $this->orderby )
		{
			case 'namedesc':
				$this->orderby = 'last_name';
				if( !$order ) $order = 'asc';
				break;
				
			case 'username':
			case 'type':
			case 'category':
				if( !$order ) $order = 'asc';
				break;

			default:
				$this->orderby = 'username';
				if( !$order ) $order = 'asc';
				break;
		}
		

		if( !isset($_GET) ) $_GET = array();
		$_GET['orderby'] = $this->orderby;
		$_GET['order'] = $order;
		
		$this->orderby .= ' '.$order;
	}
	
	
	/**
	 * 
	 */
	public function display()
	{
		$this->list_table->prepare_items( $this->filters, $this->search, $this->show_errors, $this->orderby );

		?>
		
		<div class="notice notice-success">
		<?php if( $this->model->get_option('last-process') ): ?>
			<div class="date">
				The last process users was preformed on <?php echo $this->model->get_option('last-process'); ?>.
			</div>
			<div class="results">
				<?php echo $this->model->get_option('last-process-results'); ?>
			</div>
		<?php else: ?>
			<div class="no results">
				No users have been processed.
			</div>
		<?php endif; ?>
		</div>

		
		<?php $this->form_start_get( 'process-users', null, 'process-users' ); ?>
			<button>Process All Users</button>
		<?php $this->form_end(); ?>
		
		<?php $this->form_start_get( 'filter-form' ); ?>
			
			
			<div class="filter-boxes">
			
			<?php foreach( $this->filter_types as $key => $type ): ?>
			
				<div class="<?php echo $key; ?> filter-box">
				
					<div class="title"><?php echo $type['name']; ?></div>
	
					<div class="scroll-box">
					<?php foreach( $type['values'] as $value ): ?>
						<div class="item">
						<input type="checkbox"
							   name="<?php echo $key; ?>[]"
							   id="<?php apl_name_e( $key, $value ); ?>"
							   value="<?php echo $value; ?>"
							   <?php checked( true, in_array($value, $type['filter']) ); ?> />
						<label for="<?php apl_name_e( $key, $value ); ?>">
							<?php echo $value; ?>
						</label>
						</div>
					<?php endforeach; ?>
					</div>
				
				</div>
			
			<?php endforeach; ?>
			
			</div>
			
			
						
			<div class="errors-checkbox">
				<input type="checkbox"
					   name="show-only-errors"
					   id="<?php apl_name_e( 'show-only-errors' ); ?>"
					   value="1"
					   <?php checked( true, $this->show_errors ); ?> />
				<label for="<?php apl_name_e( 'show-only-errors' ); ?>" >
					   Only show users that have errors.
				</label>
			</div>
			
			
			
			<button>Apply Filters</button>
			
			<a href="<?php echo $this->get_page_url(); ?>" />Clear Filters</a>
			
			<?php $this->list_table->search_box( 'search', 'users-table-search' ); ?>

		<?php $this->form_end(); ?>



		<?php $this->form_start( 'users-table' ); ?>
			<?php $this->list_table->display(); ?>
		<?php $this->form_end(); ?>
		
		
		<?php
	}
	
	
	// DO LATER...
	public function ajax_request( $action, $input )
	{
		switch( $action )
		{
			case 'test-ajax':
				$output['status'] = true;
				$output['message'] = 'The action was "test-ajax".';
				break;
			
			case 'test-ajax-2':
				$output['status'] = true;
				$output['message'] = 'The action was "test-ajax-2".';
				break;
			
			default:
				$output['status'] = false;
				$output['message'] = 'No action was given.';
				break;
		}
	}

} // class OrgHub_UsersListTabAdminPage extends APL_TabAdminPage
endif; // if( !class_exists('OrgHub_UsersListTabAdminPage') ):
