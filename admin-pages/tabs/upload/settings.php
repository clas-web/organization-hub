<?php

/**
 * OrgHub_UploadSettingsTabAdminPage
 * 
 * This class controls the tab admin page "Batch Upload > Settings".
 * Only seen in a blog, not on the network admin panel.
 * 
 * @package    orghub
 * @subpackage admin-pages/tabs/upload
 * @author     Crystal Barton <cbarto11@uncc.edu>
 */

if( !class_exists('OrgHub_UploadSettingsTabAdminPage') ):
class OrgHub_UploadSettingsTabAdminPage extends APL_TabAdminPage
{
	
	private $model = null;
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
	
	
	public function set_handler( $handler )
	{
		parent::set_handler( $handler );
		$this->settings_admin_page->set_handler( $handler );
	}	
	
} // class OrgHub_UploadSettingsTabAdminPage extends APL_AdminPage
endif; // if( !class_exists('OrgHub_UploadSettingsTabAdminPage') )

