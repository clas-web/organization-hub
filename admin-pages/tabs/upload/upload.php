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
	public function __construct(
		$parent,
		$name = 'upload', 
		$tab_title = 'Upload', 
		$page_title = 'Upload Batch' )
	{
		parent::__construct( $parent, $name, $tab_title, $page_title );
        $this->model = OrgHub_Model::get_instance();
	}
	
	
	/**
	 * Processes the current admin page.
	 */
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


	/**
	 * Process the upload action, by importing a CSV file.
	 */
	public function upload_file()
	{
		// check for upload file.
		if( !isset($_FILES) || !isset($_FILES['upload']) )
        {
        	$this->set_error( 'No uploaded file.' );
            return;
        }
        
		// check for file upload errors.
		if( $_FILES['upload']['error'] > 0 )
		{
			$this->set_error( 'Error uploading file: "'.$_FILES['upload']['name'].'".  Error code "'.$_FILES['csv-file']['error'].'".' );
			return;
		}

		// check that uploaded file type is supported.
		if( $_FILES['upload']['type'] !== 'text/csv' )
		{
			$this->set_error( 'Error uploading file: "'.$_FILES['upload']['name'].'".  Unsupported filetype: "'.$_FILES['csv-file']['type'].'".' );
			return;
		}

		require_once( ORGANIZATION_HUB_PLUGIN_PATH . '/libraries/csv-handler/csv-handler.php' );
		
		// parse the CSV files.
		$rows = array();
		$results = PHPUtil_CsvHandler::import( $_FILES['upload']['tmp_name'], $rows, false );
		if( $results === false )
		{
			$this->set_error( PHPUtil_CsvHandler::$last_error );
            return;
		}
		
		// process each row of the CSV file.
		$processed_rows = 0;
		$count = 1;
		$errors = '';
		foreach( $rows as &$row )
		{
			if( $uid = $this->model->upload->add_item($row) )
			{
				$processed_rows++;
			}
			else
			{
				$this->add_error( 'Row '.$count.': '.$this->model->last_error );
			}
			$count++;
		}
		
		// store upload results to display to users.
		$this->add_notice( 'Upload file: "'.$_FILES['upload']['name'].'".' );
		$this->add_notice( count($rows) . ' rows found in file.' );
		$this->add_notice( $processed_rows . ' rows added or updated successfully.' );
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

