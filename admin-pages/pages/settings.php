<?php
/**
 * OrgHub_SettingsAdminPage
 * 
 * This class controls the admin page "Upload".
 * 
 * @package    orghub
 * @subpackage admin-pages/pages
 * @author     Crystal Barton <cbarto11@uncc.edu>
 */

if( !class_exists('OrgHub_SettingsAdminPage') ):
class OrgHub_SettingsAdminPage extends APL_AdminPage
{
	
	private $model = null;	
	
	/**
	 * Creates an OrgHub_UploadAdminPage object.
	 */
	public function __construct(
		$name = 'settings',
		$menu_title = 'Settings',
		$page_title = 'Settings',
		$capability = 'administrator' )
	{
		parent::__construct( $name, $menu_title, $page_title, $capability );
		$this->model = OrgHub_Model::get_instance();
	}
	
	
	public function process()
	{
		if( empty($_REQUEST['action']) ) return;
		
		switch( $_REQUEST['action'] )
		{
			case 'Save':
				$this->save();
				break;
		}
	}
	
	
	protected function save()
	{
		if( empty($_POST[ORGANIZATION_HUB_OPTIONS]) ) return;
		
		$this->model->update_options( $_POST[ORGANIZATION_HUB_OPTIONS], true );
	}
	
	
	public function display()
	{
		$create_user_types = apply_filters(
			'orghub_create_users_types',
			array( 'wpmu-ldap' )
		);
		$create_user_types = array_merge( array('local'), $create_user_types );
		$create_user_types = array_unique( $create_user_types );
		
		$current_type = $this->model->get_option( 'create-user-type', 'local' );
		
		$this->form_start( 'save-settings' );
		
		submit_button( 'Save', 'primary', 'action' );
		?>
		
		<select name="<?php echo ORGANIZATION_HUB_OPTIONS; ?>[create-user-type]">
			<?php foreach( $create_user_types as $type ): ?>
				<option value="<?php echo $type; ?>" <?php selected($current_type, $type); ?>>
					<?php echo $type; ?>
				</option>
			<?php endforeach; ?>
		</select>
		
		<?php
		$this->form_end();
	}
	
} // class OrgHub_SettingsAdminPage extends APL_AdminPage
endif; // if( !class_exists('OrgHub_SettingsAdminPage') )

