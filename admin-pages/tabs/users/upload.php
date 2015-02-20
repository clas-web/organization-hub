<?php
/**
 * OrgHub_UsersUploadTabAdminPage
 * 
 * This class controls the tab admin page "Users > Upload".
 * 
 * @package    orghub
 * @subpackage admin-pages/tabs/upload
 * @author     Crystal Barton <cbarto11@uncc.edu>
 */

if( !class_exists('OrgHub_UsersUploadTabAdminPage') ):
class OrgHub_UsersUploadTabAdminPage extends APL_TabAdminPage
{
	
	private $model = null;
	
	
	/**
	 * Creates an OrgHub_UploadAdminPage object.
	 */
	public function __construct( $parent )
	{
		parent::__construct( $parent, 'upload', 'Upload', 'Upload Users' );
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
		$process_results = '';
		$user_ids = array();
		foreach( $rows as &$row )
		{
			if( $uid = $this->model->user->add_user($row) )
			{
				$user_ids[] = $uid;
				$processed_rows++;
			}
			else
			{
				$process_results .= $this->model->last_error.'<br/>';
			}
		}
		
		$this->model->user->set_inactive_users( $user_ids );
		
		$this->model->update_options(
			array(
				'last-upload' => date('Y-m-d H:i:s'),
				'last-upload-results' => $results . $process_results,
			),
			true
		);
		
		$this->add_notice( count($rows) . ' rows found in file.' );
		$this->add_notice( $processed_rows . ' rows added or updated successfully.' );
	}
		
	
	/**
	 * Displays the current admin page.
	 */
	public function display()
	{
		?>
		<h4>Instructions</h4>
		
		<p>
		Organizational Hub users can be uploaded by means of a CSV file that has the following format. The first line of this file should be the following field names: username, category, first_name, last_name, description, email, site_domain, site_path, connections_sites, type
		</p>
		
		<ul>
			<li>category: department, unit, program</li>
			<li>description: position title</li>
			<li>type: faculty, staff, student</li>
			<li>site_domain: domain of the server of your WordPress site (or one of its mapped domains).</li>
			<li>site_path: name of site to be created for a user</li>
		</ul>
		
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
	}
	
} // class OrgHub_UsersUploadTabAdminPage extends APL_AdminPage
endif; // if( !class_exists('OrgHub_UsersUploadTabAdminPage') )
