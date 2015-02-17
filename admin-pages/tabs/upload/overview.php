<?php
/**
 * OrgHub_ContentUploadTabAdminPage
 * 
 * This class controls the tab admin page "Upload > Overview".
 * 
 * @package    orghub
 * @subpackage admin-pages/tabs/upload
 * @author     Crystal Barton <cbarto11@uncc.edu>
 */

if( !class_exists('OrgHub_OverviewUploadTabAdminPage') ):
class OrgHub_OverviewUploadTabAdminPage extends APL_TabAdminPage
{
	
	private $model = null;
	private $list_table = null;
	
	
	/**
	 * Creates an OrgHub_OverviewUploadTabAdminPage object.
	 */
	public function __construct( $parent )
	{
		parent::__construct( $parent, 'overview', 'Overview' );
        $this->model = OrgHub_Model::get_instance();
	}
	
		
	/**
	 * Displays the current admin page.
	 */
	public function display()
	{
		?>
		<p>
		Organizational Hub allows you to create, edit and archive users, sites and posts via comma-separated values (CSV) files. All CSV files that contain data to be used for creating users, sites and posts must contain field names in the first line of the file and must use commas to separate different values. If a given value has a comma in it, then this value must be enclosed in quotes. Applications like Google spreadsheets and Microsoft Excel have options for exporting as CSV.
		</p>
		<?php
	}
	
} // class OrgHub_OverviewUploadTabAdminPage extends APL_AdminPage
endif; // if( !class_exists('OrgHub_OverviewUploadTabAdminPage') ):

