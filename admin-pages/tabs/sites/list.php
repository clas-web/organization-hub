<?php

if( !class_exists('OrgHub_SitesListTable') )
	require_once( ORGANIZATION_HUB_PLUGIN_PATH.'/classes/sites-list-table.php' );

/**
 * OrgHub_SitesAdminPage
 * 
 * This class controls the admin page "Sites > List".
 * 
 * @package    orghub
 * @subpackage admin-pages/pages
 * @author     Crystal Barton <cbarto11@uncc.edu>
 */

if( !class_exists('OrgHub_SitesListTabAdminPage') ):
class OrgHub_SitesListTabAdminPage extends APL_TabAdminPage
{
	
	private $model = null;	
	private $list_table = null;
	
	private $filter_types;
	private $filter;
	private $search;
	private $orderby;
	
	
	/**
	 * Creates an OrgHub_SitesListTabAdminPage object.
	 */
	public function __construct( $parent )
	{
		parent::__construct( $parent, 'list', 'List', 'Sites List' );
		$this->model = OrgHub_Model::get_instance();
	}
	
	
	/**
	 * Initialize the admin page.  Called during "admin_init" action.
	 */
	public function init()
	{
		$this->setup_filters();
		$this->list_table = new OrgHub_SitesListTable( $this );
	}
	
	
	/**
	 * Loads the admin page.  Called during "load-{page}" action.
	 */
	public function load()
	{
		$this->list_table->load();
	}
	
	
	/**
	 * Add the screen options for the page.
	 * Called during "load-{page}" action.
	 */
	public function add_screen_options()
	{
		$this->add_per_page_screen_option( 'orghub_sites_per_page', 'Sites', 100 );
		$this->add_selectable_columns( $this->list_table->get_selectable_columns() );
	}

	
	
	/**
	 * Processes the current admin page.
	 */
	public function process()
	{
		if( $this->list_table->process_batch_action() ) return;

		if( empty($_REQUEST['action']) ) return;
		
		switch( $_REQUEST['action'] )
		{
			case 'refresh':
				$this->model->site->refresh_all_sites();
				$this->handler->force_redirect_url = $this->get_page_url();
				break;
			
			case 'clear':
				$this->model->site->clear_tables();
				$this->handler->force_redirect_url = $this->get_page_url();
				break;

			case 'export':
				require_once( ORGANIZATION_HUB_PLUGIN_PATH . '/classes/csv-handler.php' );
				$this->model->site->get_site_csv_export( $this->filters, $this->search, $this->orderby );
				exit;
				break;
		}
	}
	
	
	/**
	 * Enqueues all the scripts or styles needed for the admin page. 
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_script( 'orghub-sites', ORGANIZATION_HUB_PLUGIN_URL.'/admin-pages/scripts/sites.js', array('jquery') );
	}
	
	
	/**
	 * Setup the filters for the list table, such as time, posts count, and page count.
	 */
	protected function setup_filters()
	{
		if( isset($this->filter_types) ) return $this->filter_types;
		
		$this->filter_types = array(
			'filter_by_time' => array( 'default' => false ),
			'time_compare' => array(
				'values' => array( 'greater', 'less' ),
				'default' => 'greater',
			),
			'time' => array(
				'values' => array(
					'1 day',
					'1 week',
					'2 weeks',
					'1 month',
					'3 months',
					'6 months',
					'1 year',
				),
				'default' => '1 day',
			),
			'filter_by_posts' => array( 'default' => false ),
			'posts_compare' => array(
				'values' => array( 'greater', 'less' ),
				'default' => 'greater'
			),
			'posts' => array(
				'values' => array(
					1,
					2,
					5,
					10,
					15,
					20,
					25,
					50,
					100,
					1000,
					10000,
				),
				'default' => 1,
			),
			'filter_by_pages' => array( 'default' => false ),
			'pages_compare' => array(
				'values' => array( 'greater', 'less' ),
				'default' => 'greater'
			),
			'pages' => array(
				'values' => array(
					1,
					2,
					5,
					10,
					15,
					20,
					25,
					50,
					100,
					1000,
					10000,
				),
				'default' => 1,
			),
		);
		
		$this->filters = array();
		foreach( $this->filter_types as $type => $settings )
		{
			$this->filters[$type] = $settings['default'];
			if( !empty($_GET[$type]) )
			{
				$this->filters[$type] = $_GET[$type];
			}
		}
		
		$this->search = array();
		if( !empty($_REQUEST['s']) )
		{
			$this->search['title'] = array( $_REQUEST['s'] );
			$this->search['admin_email'] = array( $_REQUEST['s'] );
			$this->search['display_name'] = array( $_REQUEST['s'] );
			$this->search['user_login'] = array( $_REQUEST['s'] );
		}

		$this->orderby = ( !empty($_GET['orderby']) ? $_GET['orderby'] : 'last_post_date' );
		$order = ( !empty($_GET['order']) ? $_GET['order'] : 'desc' );

		switch( $order )
		{
			case 'asc': case 'desc': break;
			default: $order = null; break;
		}

		switch( $this->orderby )
		{
			case 'title':
			case 'num_posts':
			case 'num_pages':
			case 'num_comments':
			case 'comment_post':
			case 'post_page':
			case 'last_post_date':
			case 'last_comment_date':
				if( !$order ) $order = 'desc';
				break;
				
			case 'administrator':
				$this->orderby = 'display_name';
				if( !$order ) $order = 'asc';
				break;

			default:
				$this->orderby = 'last_post_date';
				if( !$order ) $order = 'desc';
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
		$this->list_table->prepare_items( $this->filters, $this->search, $this->orderby );
		?>
		
		<div class="notice notice-success">
		<?php if( $this->model->get_option('sites-refresh-time') ): ?>
			<div class="time">
				The last sites listing refresh happened on <span id="orghub-sites-time"><?php echo $this->model->get_option('sites-refresh-time'); ?></span>.
			</div>
		<?php else: ?>
			<div class="no results">
				The sites lists has never been cached.
			</div>
		<?php endif; ?>
		</div>
		
		<?php
		$this->form_start_get( 'refresh', null, 'refresh' );
			$this->create_ajax_submit_button( 'Refresh Sites', 'refresh-all-sites', null, null, 'refresh_all_sites_start', 'refresh_all_sites_end', 'refresh_all_sites_loop_start', 'refresh_all_sites_loop_end' );
		$this->form_end();
		?>

		<?php
		if( ORGANIZATION_HUB_DEBUG ):
		$this->form_start_get( 'clear', null, 'clear' );
			?><button>Clear Sites</button><?php
		$this->form_end();
		endif;
		?>
		
		<div id="ajax-status">
			AJAX not started...
		</div>
		<div id="ajax-progress">
			No progress...
		</div>
		<div id="ajax-data">
		</div>
		
		
		
		<?php $this->form_start_get( 'filter-form' ); ?>
			
			
			<div class="filters">
			
				<div class="timeframe">
					<div class="title">
						<input type="checkbox"
						       name="filter_by_time"
						       value="1"
						       <?php checked( true, $this->filters['filter_by_time'] !== false ); ?> />
						Last Updated
					</div>
					
					<select name="time_compare">
						<option value="greater"
							<?php selected( 'greater', $this->filters['time_compare'] ); ?> >
							older than
						</option>
						<option value="less"
							<?php selected( 'less', $this->filters['time_compare'] ); ?> >
							newer than
						</option>
					</select>
					
					<select name="time">
						<?php foreach( $this->filter_types['time']['values'] as $value ): ?>
							<option value="<?php echo $value; ?>"
								    <?php selected( $value, $this->filters['time'] ); ?> >
								<?php echo $value; ?>
							</option>
						<?php endforeach; ?>
					</select>
					
				</div>
				
				<div class="posts-count">
					<div class="title">
						<input type="checkbox"
						       name="filter_by_posts"
						       value="1"
						       <?php checked( true, $this->filters['filter_by_posts'] !== false ); ?> />
						# of Posts
					</div>
					
					<select name="posts_compare">
						<option value="greater"
							<?php selected( 'greater', $this->filters['posts_compare'] ); ?> >
							more than
						</option>
						<option value="less"
							<?php selected( 'less', $this->filters['posts_compare'] ); ?> >
							less than
						</option>
					</select>
					
					<select name="posts">
						<?php foreach( $this->filter_types['posts']['values'] as $value ): ?>
							<option value="<?php echo $value; ?>"
								    <?php selected( $value, $this->filters['posts'] ); ?> >
								<?php echo $value; ?>
							</option>
						<?php endforeach; ?>
					</select>
					
				</div>

				<div class="pages-count">
					<div class="title">
						<input type="checkbox"
						       name="filter_by_pages"
						       value="1"
						       <?php checked( true, $this->filters['filter_by_pages'] !== false ); ?> />
						# of Pages
					</div>
					
					<select name="pages_compare">
						<option value="greater"
							<?php selected( 'greater', $this->filters['pages_compare'] ); ?> >
							more than
						</option>
						<option value="less"
							<?php selected( 'less', $this->filters['pages_compare'] ); ?> >
							less than
						</option>
					</select>
					
					<select name="pages">
						<?php foreach( $this->filter_types['pages']['values'] as $value ): ?>
							<option value="<?php echo $value; ?>"
								    <?php selected( $value, $this->filters['pages'] ); ?> >
								<?php echo $value; ?>
							</option>
						<?php endforeach; ?>
					</select>
					
				</div>			
			</div>
			
			
						
			<button>Apply Filters</button>
			
			<a href="<?php echo $this->get_page_url(); ?>" />Clear Filters</a>

			<?php $this->list_table->search_box( 'search', 'sites-table-search' ); ?>
			
		<?php $this->form_end(); ?>
		
				
		
		<?php
		$this->form_start( 'sites-table' );
			$this->list_table->display();
		$this->form_end();
		
		if( $this->list_table->has_items() ):
			$this->list_table->inline_change_theme();
			$this->list_table->inline_change_admin();
		endif;
	}
	
	
	/**
	 * Processes and displays the output of an ajax request.
	 * @param  string  $action  The AJAX action.
	 * @param  array   $input   The AJAX input array.
	 * @param  int     $count   When multiple AJAX calls are made, the current count.
	 * @param  int     $total   When multiple AJAX calls are made, the total count.
	 */
	public function ajax_request( $action, $input, $count, $total )
	{
		switch( $action )
		{
			case 'refresh-all-sites':
				$ids = $this->model->site->get_blog_ids();
				
				$items = array();
				foreach( $ids as $id ) $items[] = array( 'blog_id' => $id );
				
				$this->ajax_set_items( 'refresh-site', $items, 'refresh_site_start', 'refresh_site_end', 'refresh_site_loop_start', 'refresh_site_loop_end' );
				break;
			
			case 'refresh-site':
				if( !isset($input['blog_id']) )
				{
					$this->ajax_failed( 'No blog id given.' );
					return;
				}
				
				$site_data = $this->model->site->refresh_site( $input['blog_id'] );
				$column_data = array();
				
				$this->list_table = new OrgHub_SitesListTable( $this );
				$columns = $this->list_table->get_columns();
				foreach( array_keys($columns) as $column_name )
				{
					if( 'cb' == $column_name )
					{
						continue;
					}
					elseif( method_exists($this->list_table, 'column_'.$column_name) )
					{
						$column_data[$column_name] = call_user_func( array($this->list_table, 'column_'.$column_name), $site_data );
					}
					else
					{
						$column_data[$column_name] = $this->list_table->column_default( $site_data, $column_name );
					}
				}
				
				$this->ajax_set( 'site', $site_data );
				$this->ajax_set( 'columns', $column_data );
				
				if( $count === $total )
				{
					$refresh_date = date('Y-m-d H:i:s');
					$this->model->update_option( 'sites-refresh-time', $refresh_date );
					$this->ajax_set( 'refresh_date', $refresh_date );
					
					$this->set_notice( 'Successfully refreshed '.$count.' sites.' );
				}
				break;
			
			default:
				$this->ajax_failed( 'No valid action was given.' );
				break;
		}
	}
	
} // class OrgHub_SitesListTabAdminPage extends APL_TabAdminPage
endif; // if( !class_exists('OrgHub_SitesListTabAdminPage') )

