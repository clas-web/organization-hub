<?php
/**
 * Controls the tab admin page "Sites > Log".
 * 
 * @package    organization-hub
 * @subpackage admin-pages/tabs/sites
 * @author     Crystal Barton <atrus1701@gmail.com>
 */
if( !class_exists('OrgHub_SitesLogTabAdminPage') ):
class OrgHub_SitesLogTabAdminPage extends APL_TabAdminPage
{
	/**
	 * The main model for the Organization Hub.
	 * @var  OrgHub_Model
	 */	
	private $model = null;
	
	
	/**
	 * Controller.
	 */
	public function __construct(
		$parent,
		$name = 'log', 
		$tab_title = 'Log', 
		$page_title = 'Sites Log' )
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
	
} // class OrgHub_SitesLogTabAdminPage extends APL_TabAdminPage
endif; // if( !class_exists('OrgHub_SitesLogTabAdminPage') ):

