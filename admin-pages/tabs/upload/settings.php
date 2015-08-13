<?php
/**
 * Controls the tab admin page "Batch Upload > Settings".
 * Only seen in a blog, not on the network admin panel.
 * 
 * @package    organization-hub
 * @subpackage admin-pages/tabs/upload
 * @author     Crystal Barton <atrus1701@gmail.com>
 */
if( !class_exists('OrgHub_UploadSettingsTabAdminPage') ):
class OrgHub_UploadSettingsTabAdminPage extends APL_TabAdminPage
{
	/**
	 * The main model for the Organization Hub.
	 * @var  OrgHub_Model
	 */	
	private $model = null;

	/**
	 * The network version of the Settings page.
	 * @var  OrgHub_SettingsAdminPage
	 */	
	private $settings_admin_page = null;
	
	
	/**
	 * Creates an OrgHub_UploadSettingsTabAdminPage object.
	 */
	public function __construct(
		$parent,
		$name = 'settings', 
		$tab_title = 'Settings', 
		$page_title = 'Upload Batch Settings' )
	{
		parent::__construct( $parent, $name, $tab_title, $page_title );
        
        $this->model = OrgHub_Model::get_instance();
        $this->settings_admin_page = new OrgHub_SettingsAdminPage( 'orghub-upload-settings' );
	}
	
	
	/**
	 * Processes the current admin page.
	 */
	public function process()
	{
		$this->settings_admin_page->process();
	}
	
	
	/**
	 * Displays the current admin page.
	 */
	public function display()
	{
		$this->settings_admin_page->display();
	}
	
	
	/**
	 * Setup the page's APL handler.
	 * @param  APL_Handler  $handler  The APL handler that contains this admin page.
	 */
	public function set_handler( $handler )
	{
		parent::set_handler( $handler );
		$this->settings_admin_page->set_handler( $handler );
	}	
	
} // class OrgHub_UploadSettingsTabAdminPage extends APL_AdminPage
endif; // if( !class_exists('OrgHub_UploadSettingsTabAdminPage') )

