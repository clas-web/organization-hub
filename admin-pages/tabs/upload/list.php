<?php

if( !class_exists('OrgHub_UploadListTable') )
	require_once( ORGANIZATION_HUB_PLUGIN_PATH.'/classes/upload-list-table.php' );


/**
 * Controls the tab admin page "Upload > List".
 * 
 * @package    organization-hub
 * @subpackage admin-pages/tabs/upload
 * @author     Crystal Barton <atrus1701@gmail.com>
 */
if( !class_exists('OrgHub_UploadListTabAdminPage') ):
class OrgHub_UploadListTabAdminPage extends APL_TabAdminPage
{
	/**
	 * The main model for the Organization Hub.
	 * @var  OrgHub_Model
	 */	
	private $model = null;	

	/**
	 * The Upload admin table.
	 * @var  OrgHub_UploadListTable
	 */
	private $list_table = null;
	
	/**
	 * The current orderby name and ordering (ASC or DESC).
	 * @var  string
	 */
	private $orderby;	
	
	
	/**
	 * Controller.
	 */
	public function __construct(
		$parent,
		$name = 'list', 
		$tab_title = 'List', 
		$page_title = 'Upload Batch List' )
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
		$this->list_table = new OrgHub_UploadListTable( $this );
	}
	
	/**
	 * Loads the list table's items.
	 */
	public function load()
	{
		$this->list_table->load();
	}
	

	/**
	 * Setup the filters for the list table, such as time, posts count, and page count.
	 */
	protected function setup_filters()
	{
		$this->orderby = ( !empty($_GET['orderby']) ? $_GET['orderby'] : 'timestamp' );
		$order = ( !empty($_GET['order']) ? $_GET['order'] : 'asc' );
		
		switch( $order )
		{
			case 'asc': case 'desc': break;
			default: $order = null; break;
		}

		switch( $this->orderby )
		{
			case 'timestamp':
				if( !$order ) $order = 'asc';
				break;

			default:
				$this->orderby = 'timestamp';
				if( !$order ) $order = 'asc';
				break;
		}
		

		if( !isset($_GET) ) $_GET = array();
		$_GET['orderby'] = $this->orderby;
		$_GET['order'] = $order;
		
		$this->orderby .= ' '.$order;
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
			case 'clear':
				$this->model->upload->clear_blog_batch_items();
				$this->handler->force_redirect_url = $this->get_page_url();
				break;
			
			case 'delete':
				if( empty($_GET['id']) || !is_numeric($_GET['id']) )
				{
					$this->set_error( 'No id provided or is invalid.' );
					return;
				}
				
				$this->model->upload->delete_item( $_GET['id'] );
				$this->handler->force_redirect_url = $this->get_page_url();
				break;
				
			case 'process':
				if( empty($_GET['id']) || !is_numeric($_GET['id']) )
				{
					$this->set_error( 'No id provided or is invalid.' );
					return;
				}
				
				$result = $this->model->upload->process_item( $_GET['id'] );
				$this->set_notice( 'Processed 1 item.' );
				if( !$result )
					$this->set_error( $this->model->last_error );
				$this->handler->force_redirect_url = $this->get_page_url();
				break;
				
			case 'process-all-items':
				$result = $this->model->upload->process_items();
				$this->set_notice( 'Processed all items.' );
				if( !$result )
					$this->set_error( $this->model->last_error );
				$this->handler->force_redirect_url = $this->get_page_url();
				break;
		}
	}
	

	/**
	 * Enqueues all the scripts or styles needed for the admin page. 
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_script( 'orghub-upload-extended-data', ORGANIZATION_HUB_PLUGIN_URL.'/admin-pages/scripts/upload-extended-data.js' );		
	}
	
	/**
	 * Displays the current admin page.
	 */
	public function display()
	{
		$this->list_table->prepare_items( $this->orderby );

		$this->form_start_get( 'process-all-items', null, 'process-all-items' );
			?><button>Process All Items</button><?php
		$this->form_end();
		
		$this->form_start_get( 'clear', null, 'clear' );
			?><button>Clear Items</button><?php
		$this->form_end();

		$this->form_start( 'upload-table' );
			$this->list_table->display();
		$this->form_end();
	}
	
} // class OrgHub_UploadListTabAdminPage extends APL_AdminPage
endif; // if( !class_exists('OrgHub_UploadListTabAdminPage') )

