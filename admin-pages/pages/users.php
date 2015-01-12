<?php


if( !class_exists('OrgHub_UsersAdminPage') ):
class OrgHub_UsersAdminPage extends APL_AdminPage
{
	
	public function __construct()
	{
		parent::__construct( 'users', 'Users', 'Users' );
		
		$this->display_page_tab_list = false;
		$this->add_tab( new OrgHub_UsersListTabAdminPage($this) );
		$this->add_tab( new OrgHub_UsersEditTabAdminPage($this) );
	}
	
	
	public function display()
	{
		// Using tabs, so display should never be called.
	}
	
} // class OrgHub_UsersAdminPage extends APL_AdminPage
endif; // if( !class_exists('OrgHub_UsersAdminPage') ):

