<?php
/**
 * OrgHub_UploadAdminPage
 * 
 * 
 * 
 * @package    orghub
 * @subpackage admin-pages/pages
 * @author     Crystal Barton <cbarto11@uncc.edu>
 */

if( !class_exists('OrgHub_UploadAdminPage') ):
class OrgHub_UploadAdminPage extends APL_AdminPage
{
	
	private $model = null;
	private $users_table = null;
	
	private $process_results = '';


	/**
	 * 
	 */
	public function __construct()
	{
		parent::__construct( 'upload', 'Upload', 'Upload' );
        $this->model = OrgHub_Model::get_instance();
	}
	
	
	/**
	 * 
	 */
	public function add_head_script()
	{
		?>
		<style>
		
			
		
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
	 * 
	 */
	public function upload_file()
	{
		if( !isset($_FILES) || !isset($_FILES['upload']) )
        {
            $this->process_results = 'No uploaded file.';
            return;
        }
		
        require_once( ORGANIZATION_HUB_PLUGIN_PATH . '/classes/csv-handler.php' );

		$rows = array();
		$results = OrgHub_CsvHandler::import( $_FILES['upload']['tmp_name'], $rows, false );
		
		if( $results === false )
		{
            $this->process_results = OrgHub_CsvHandler::$last_error;
            return;
		}
		
		$processed_rows = 0;
		$process_results = '';
		$user_ids = array();
		foreach( $rows as &$row )
		{
			if( $uid = $this->model->add_user($row) )
			{
				$user_ids[] = $uid;
				$processed_rows++;
			}
			else
			{
				$process_results .= $this->model->last_error.'<br/>';
			}
		}
		
		$this->model->set_inactive_users( $user_ids );
		
		$results = count($rows) . ' rows found in file.<br/>';
		$results .= $processed_rows . ' rows added or updated successfully.<br/>';
		$this->process_results = $results . $this->process_results;

		$this->model->update_options(
			array(
				'last-upload' => date('Y-m-d H:i:s'),
				'last-upload-results' => $results . $process_results,
			),
			true
		);

	}
		
	
	/**
	 * 
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
	
} // class OrgHub_UploadAdminPage extends APL_AdminPage
endif; // if( !class_exists('OrgHub_UploadAdminPage') ):

