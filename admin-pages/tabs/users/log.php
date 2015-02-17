<?php
/**
 * OrgHub_UsersLogTabAdminPage
 * 
 * This class controls the tab admin page "Users > Log".
 * 
 * @package    orghub
 * @subpackage admin-pages/tabs/sites
 * @author     Crystal Barton <cbarto11@uncc.edu>
 */

if( !class_exists('OrgHub_UsersLogTabAdminPage') ):
class OrgHub_UsersLogTabAdminPage extends APL_TabAdminPage
{
	
	private $model = null;
	
	
	/**
	 * Creates an OrgHub_UsersLogTabAdminPage object.
	 */
	public function __construct( $parent )
	{
		parent::__construct( $parent, 'log', 'Log', 'Users Log' );
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
	
} // class OrgHub_UsersLogTabAdminPage extends APL_TabAdminPage
endif; // if( !class_exists('OrgHub_UsersLogTabAdminPage') )

