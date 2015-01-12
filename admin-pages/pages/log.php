<?php


if( !class_exists('OrgHub_LogAdminPage') ):
class OrgHub_LogAdminPage extends APL_AdminPage
{
	
	public function __construct()
	{
		parent::__construct( 'log', 'Log', 'Log' );
	}
	
	public function display()
	{
		echo 'LOG';
	}
	
} // class OrgHub_LogAdminPage extends APL_AdminPage
endif; // if( !class_exists('OrgHub_LogAdminPage') ):

