<?php

if( !class_exists('OrgHub_UploadListTable') )
	require_once( ORGANIZATION_HUB_PLUGIN_PATH.'/classes/upload-list-table.php' );

/**
 * OrgHub_UploadUploadTabAdminPage
 * 
 * This class controls the tab admin page "Batch Upload > Upload".
 * 
 * @package    orghub
 * @subpackage admin-pages/tabs/upload
 * @author     Crystal Barton <cbarto11@uncc.edu>
 */

if( !class_exists('OrgHub_UploadUploadTabAdminPage') ):
class OrgHub_UploadUploadTabAdminPage extends APL_TabAdminPage
{
	
	private $model = null;
	private $list_table = null;
	private $orderby = null;
	
	
	/**
	 * Creates an OrgHub_UploadUploadTabAdminPage object.
	 */
	public function __construct( $parent )
	{
		parent::__construct( $parent, 'upload', 'Upload', 'Upload Batch' );
        $this->model = OrgHub_Model::get_instance();
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
	
} // class OrgHub_UploadUploadTabAdminPage extends APL_AdminPage
endif; // if( !class_exists('OrgHub_UploadUploadTabAdminPage') )

