<?php
/**
 * OrgHub_UploadLogTabAdminPage
 * 
 * This class controls the tab admin page "Sites > Log".
 * 
 * @package    orghub
 * @subpackage admin-pages/tabs/sites
 * @author     Crystal Barton <cbarto11@uncc.edu>
 */

if( !class_exists('OrgHub_UploadLogTabAdminPage') ):
class OrgHub_UploadLogTabAdminPage extends APL_TabAdminPage
{
	
	private $model = null;
	
	
	/**
	 * Creates an OrgHub_UploadLogTabAdminPage object.
	 */
	public function __construct(
		$parent,
		$name = 'log', 
		$tab_title = 'Log', 
		$page_title = 'Upload Batch Log' )
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
		}
	}
	
	
	/**
	 * Displays the current admin page.
	 */
	public function display()
	{
		
	}
	
} // class OrgHub_UploadLogTabAdminPage extends APL_TabAdminPage
endif; // if( !class_exists('OrgHub_UploadLogTabAdminPage') )

