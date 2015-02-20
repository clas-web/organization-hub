<?php

if( !class_exists('OrgHub_UsersListTable') )
	require_once( ORGANIZATION_HUB_PLUGIN_PATH.'/classes/users-list-table.php' );

/**
 * OrgHub_UsersListTabAdminPage
 * 
 * This class controls the admin page Users when in list mode.
 * 
 * @package    orghub
 * @subpackage admin-pages/tabs/users
 * @author     Crystal Barton <cbarto11@uncc.edu>
 */

if( !class_exists('OrgHub_UsersListTabAdminPage') ):
class OrgHub_UsersListTabAdminPage extends APL_TabAdminPage
{
	
	private $model = null;			// The OrgHub's main model.
	private $list_table = null;		// The page's WP list table.
	
	private $filter_types;			// The types of filters.
	private $filter;				// 
	private $search;				// 
	private $orderby;				// 
	private $show_errors;			// 
	
	
	
	/**
	 * Constructor.
	 * Creates an OrgHub_UsersListTabAdminPage object.
	 */
	public function __construct(
		$parent,
		$name = 'list', 
		$tab_title = 'List', 
		$page_title = 'Users List' )
	{
		parent::__construct( $parent, $name, $tab_title, $page_title );
		$this->model = OrgHub_Model::get_instance();
	}

	
	/**
	 * Initialize the admin page by setting up the filters and list table.
	 */
	public function init()
	{
		$this->setup_filters();
		$this->list_table = new OrgHub_UsersListTable( $this );
	}
	
	/**
	 * Loads the list table's items.
	 */
	public function load()
	{
		$this->list_table->load();
	}
	

	/**
	 * Add screen options.
	 */
	public function add_screen_options()
	{
		$this->add_per_page_screen_option( 'orghub_users_per_page', 'Users', 100 );
		$this->add_selectable_columns( $this->list_table->get_selectable_columns() );
	}
	
	
	/**
	 * Process any action present in the $_REQUEST data.
	 */
	public function process()
	{
		if( $this->list_table->process_batch_action() ) return;

		if( empty($_REQUEST['action']) ) return;
		
		switch( $_REQUEST['action'] )
		{
			case 'Process All Users':
			case 'process-all-users':
				$this->process_users();
				break;

			case 'clear':
				$this->model->user->clear_tables();
				$this->handler->force_redirect_url = $this->get_page_url();
				break;
			
			case 'export':
		        require_once( ORGANIZATION_HUB_PLUGIN_PATH . '/classes/csv-handler.php' );
				$this->model->user->get_csv_export( $this->filters, $this->search, $this->show_errors, $this->orderby );
				exit;
				break;
		}
	}
	

	/**
	 * Processes the 'process-users' action.
	 */
	public function process_users()
	{
		$users = $this->model->user->get_users();
		
		foreach( $users as $user )
		{
			$this->model->user->process_user( $user );
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
	 * Setup the filters for the list table, such as time, posts count, and page count.
	 */
	protected function setup_filters()
	{
		if( isset($this->filter_types) ) return $this->filter_types;
		
		$this->filter_types = array(
			'status' => array(
				'name' => 'Status',
				'values' => $this->model->user->get_all_status_values(),
				'filter' => array(),
			),
			'type' => array(
				'name' => 'Type',
				'values' => $this->model->user->get_all_type_values(),
				'filter' => array(),
			),
			'category' => array(
				'name' => 'Category',
				'values' => $this->model->user->get_all_category_values(),
				'filter' => array(),
			),
			'site_domain' => array(
				'name' => 'Domain',
				'values' => $this->model->user->get_all_site_domain_values(),
				'filter' => array(),
			),
			'connection' => array(
				'name' => 'Connections Sites',
				'values' => $this->model->user->get_all_connections_sites_values(),
				'filter' => array(),
			),
			'site' => array(
				'name' => 'Profile Sites',
				'values' => array( 
					'na-site'  => 'Profile site not specified.', 
					'no-site'  => 'Profile site not created.', 
					'has-site' => 'Profile site created.' ),
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
	 * Displays the current admin page.
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

		
		<?php $this->form_start( 'process-all-users', null, 'process-all-users' ); ?>
			<button>Process All Users</button>
		<?php $this->form_end(); ?>

		<?php
		if( ORGANIZATION_HUB_DEBUG ):
		$this->form_start_get( 'clear', null, 'clear' );
			?><button>Clear Users</button><?php
		$this->form_end();
		endif;
		?>
		
		<?php $this->form_start_get( 'filter-form' ); ?>
			
			
			<div class="filter-boxes">
			
			<?php foreach( $this->filter_types as $key => $type ): ?>
			
				<div class="<?php echo $key; ?> filter-box">
				
					<div class="title"><?php echo $type['name']; ?></div>
	
					<div class="scroll-box">
					<?php foreach( $type['values'] as $vk => $vv ): ?>
						<div class="item">
						<?php if( is_int($vk) ) $vk = $vv; ?>
						<?php 
						if( $key == 'site_domain' && $vv == '' )
							$vv = '[default domain]';
						?>
							<input type="checkbox"
								   name="<?php echo $key; ?>[]"
								   id="<?php apl_name_e( $key, $vk ); ?>"
								   value="<?php echo $vk; ?>"
								   <?php checked( true, in_array($vk, $type['filter']) ); ?> />
							<label for="<?php apl_name_e( $key, $vk ); ?>">
								<?php echo $vv; ?>
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

} // class OrgHub_UsersListTabAdminPage extends APL_TabAdminPage
endif; // if( !class_exists('OrgHub_UsersListTabAdminPage') )

