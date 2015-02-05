<?php
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
	
	
	/**
	 * Creates an OrgHub_ContentUploadTabAdminPage object.
	 */
	public function __construct( $parent )
	{
		parent::__construct( $parent, 'content', 'Content' );
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
		Organizational Hub can be used to add posts to an existing site. It can also be used to edit or delete existing posts or add, edit or delete taxonomy terms. All of this is done by means of CSV files with the appropriate header lines. The first column of each row indicates how the row should be processed.
		</p>
		<p>
		# : Comments out the row. This row will be ignored and not processed at all.
		</p>
		
		<h5>Posts and Pages</h5>
		<p>
		These four fields are required for all posts and pages.
		</p>
		<ul>
			<li>site : The slug of the site.</li>
			<li>type : "post" or "page" or a custom post type.</li>
			<li>action : The action to take on the object. There are a number fo actions that can be taken. Further details on each of the actions are detailed under the type section.</li>
			<li>add : Adds a post or page, but does not check for duplicates before creation.</li>
			<li>update : Updates a post or page, if it exists.</li>
			<li>replace : Replaces a post or page, if it exists, otherwise it creates the post.</li>
			<li>prepend : Prepends data to a post or page's excerpt and content.</li>
			<li>append : Appends data to a post or page's excerpt and content.</li>
			<li>delete : Deletes a post or page.</li>
			<li>grep : Updates a portion of a post or page using a regex expression and replacement text. Requires the "subject" column. Valid subject values: "title", "excerpt", "content", "slug", "guid"</li>
			<li>title : The title of the post or page.</li>
		</ul>
		<p>
		For more detailed instructions about header and various other fields that can be used, see: https://github.com/clas-web/multisite-csv-importer
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
	
} // class OrgHub_ContentUploadTabAdminPage extends APL_AdminPage
endif; // if( !class_exists('OrgHub_ContentUploadTabAdminPage') ):

