<?php
/**
 * OrgHub_SitesUploadTabAdminPage
 * 
 * This class controls the tab admin page "Upload > Sites".
 * 
 * @package    orghub
 * @subpackage admin-pages/tabs/upload
 * @author     Crystal Barton <cbarto11@uncc.edu>
 */

if( !class_exists('OrgHub_SitesUploadTabAdminPage') ):
class OrgHub_SitesUploadTabAdminPage extends APL_TabAdminPage
{
	
	private $model = null;
	
	
	/**
	 * Creates an OrgHub_UploadAdminPage object.
	 */
	public function __construct( $parent )
	{
		parent::__construct( $parent, 'sites', 'Sites', 'Upload Sites' );
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
		
	}
		
	
	/**
	 * Displays the current admin page.
	 */
	public function display()
	{
		?>
		<h4>Instructions</h4>
		
		<p>
		Organizational Hub can be used to create any kind of sites by uploading a CSV file with the following field names (must be in the first line of the file): site_name, site_title, site_description, username, password, user_email, user_role
		</p>
		
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
	
} // class OrgHub_SitesUploadTabAdminPage extends APL_AdminPage
endif; // if( !class_exists('OrgHub_SitesUploadTabAdminPage') ):

