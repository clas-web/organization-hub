<?php
/**
 * Controls the admin page "Users".
 * 
 * @package    organization-hub
 * @subpackage admin-pages/pages
 * @author     Crystal Barton <atrus1701@gmail.com>
 */

if( !class_exists('OrgHub_UsersAdminPage') ):
class OrgHub_UsersAdminPage extends APL_AdminPage
{
	
	/**
	 * Creates an OrgHub_UsersAdminPage object.
	 */
	public function __construct(
		$name = 'users',
		$menu_title = 'Users',
		$page_title = 'Users',
		$capability = 'administrator' )
	{
		parent::__construct( $name, $menu_title, $page_title, $capability );
		
		$this->add_tab( new OrgHub_UsersListTabAdminPage($this) );
		$this->add_tab( new OrgHub_UsersUploadTabAdminPage($this) );
		$this->add_tab( new OrgHub_UsersLogTabAdminPage($this) );

		$this->add_tab( new OrgHub_UsersEditTabAdminPage($this) );
	}
		
} // class OrgHub_UsersAdminPage extends APL_AdminPage
endif; // if( !class_exists('OrgHub_UsersAdminPage') )

