<?php

if( !class_exists('OrgHub_UploadListTable') )
	require_once( ORGANIZATION_HUB_PLUGIN_PATH.'/classes/upload-list-table.php' );

/**
 * OrgHub_UploadListTabAdminPage
 * 
 * This class controls the tab admin page "Upload > Content".
 * 
 * @package    orghub
 * @subpackage admin-pages/tabs/upload
 * @author     Crystal Barton <cbarto11@uncc.edu>
 */

if( !class_exists('OrgHub_UploadListTabAdminPage') ):
class OrgHub_UploadListTabAdminPage extends APL_TabAdminPage
{
	
	private $model = null;
	private $list_table = null;
	private $orderby = null;
	
	
	/**
	 * Creates an OrgHub_UploadListTabAdminPage object.
	 */
	public function __construct( $parent )
	{
		parent::__construct( $parent, 'list', 'List', 'Upload List' );
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
		$order = ( !empty($_GET['order']) ? $_GET['order'] : 'desc' );
		
		switch( $order )
		{
			case 'asc': case 'desc': break;
			default: $order = null; break;
		}

		switch( $this->orderby )
		{
			case 'timestamp':
				if( !$order ) $order = 'desc';
				break;

			default:
				$this->orderby = 'timestamp';
				if( !$order ) $order = 'desc';
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
				break;
				
			case 'process':
				if( empty($_GET['id']) || !is_numeric($_GET['id']) )
				{
					$this->set_error( 'No id provided or is invalid.' );
					return;
				}
				
				$this->model->upload->delete_item( $_GET['id'] );
				break;
				
			case 'process-items':
				break;
		}
	}
	
	
	/**
	 * Displays the current admin page.
	 */
	public function display()
	{
		$this->list_table->prepare_items( $this->orderby );

		$this->form_start_get( 'clear', null, 'clear' );
			?><button>Clear Items</button><?php
		$this->form_end();
		
		$this->form_start( 'upload-table' );
			$this->list_table->display();
		$this->form_end();
	}
	
} // class OrgHub_UploadListTabAdminPage extends APL_AdminPage
endif; // if( !class_exists('OrgHub_UploadListTabAdminPage') )

