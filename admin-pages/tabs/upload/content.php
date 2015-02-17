<?php

if( !class_exists('OrgHub_UploadListTable') )
	require_once( ORGANIZATION_HUB_PLUGIN_PATH.'/classes/upload-list-table.php' );

/**
 * OrgHub_ContentUploadTabAdminPage
 * 
 * This class controls the tab admin page "Upload > Content".
 * 
 * @package    orghub
 * @subpackage admin-pages/tabs/upload
 * @author     Crystal Barton <cbarto11@uncc.edu>
 */

if( !class_exists('OrgHub_ContentUploadTabAdminPage') ):
class OrgHub_ContentUploadTabAdminPage extends APL_TabAdminPage
{
	
	private $model = null;
	private $list_table = null;
	private $orderby = null;
	
	
	/**
	 * Creates an OrgHub_ContentUploadTabAdminPage object.
	 */
	public function __construct( $parent )
	{
		parent::__construct( $parent, 'content', 'Content' );
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
			case 'upload':
				$this->upload_file();
				break;
			
			case 'clear':
				$this->model->upload->clear_blog_batch_items();
				$this->handler->force_redirect_url = $this->get_page_url();
				break;
				
			case 'process-items':
				break;
		}
	}


	/**
	 * Process the upload action, by importing a CSV file.
	 */
	public function upload_file()
	{
		if( !isset($_FILES) || !isset($_FILES['upload']) )
        {
        	$this->set_error( 'No uploaded file.' );
            return;
        }
		
        require_once( ORGANIZATION_HUB_PLUGIN_PATH . '/classes/csv-handler.php' );

		$rows = array();
		$results = OrgHub_CsvHandler::import( $_FILES['upload']['tmp_name'], $rows, false );
		if( $results === false )
		{
			$this->set_error( OrgHub_CsvHandler::$last_error );
            return;
		}
		
		$processed_rows = 0;
		$errors = '';
		foreach( $rows as &$row )
		{
			if( $uid = $this->model->upload->add_item($row) )
			{
				$processed_rows++;
			}
			else
			{
				$errors .= $this->model->last_error.'<br/>';
			}
		}
		
		$results = count($rows) . ' rows found in file.<br/>';
		$results .= $processed_rows . ' rows added successfully.<br/>';

		$this->set_notice( $results );
		$this->set_error( $errors );
	}
		
	
	/**
	 * Displays the current admin page.
	 */
	public function display()
	{
		$this->list_table->prepare_items( $this->orderby );

		?>
		<h4>Upload</h4>
		
		<?php
		$this->form_start( 'upload', array('enctype' => 'multipart/form-data'), 'upload', null );
		?>
		
		<input type="file"
			   name="<?php apl_name_e( 'upload' ); ?>"
			   accept=".csv" />
		<div class="upload-submit"><?php submit_button( 'Upload List', 'small' ); ?></div>
		<div style="clear:both"></div>
 		
 		<?php
 		$this->form_end();
		?>
		
		<h4>Batch List</h4>

		<?php
		$this->form_start_get( 'clear', null, 'clear' );
			?><button>Clear Items</button><?php
		$this->form_end();
		
		$this->form_start( 'upload-table' );
			$this->list_table->display();
		$this->form_end();
	}
	
} // class OrgHub_ContentUploadTabAdminPage extends APL_AdminPage
endif; // if( !class_exists('OrgHub_ContentUploadTabAdminPage') ):

