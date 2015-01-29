<?php
/**
 * OrgHub_UsersEditTabAdminPage
 * 
 * This class controls the admin page USERS when in edit user mode.
 * 
 * @package    orghub
 * @subpackage admin-pages/tabs/users
 * @author     Crystal Barton <cbarto11@uncc.edu>
 */

if( !class_exists('OrgHub_UsersEditTabAdminPage') ):
class OrgHub_UsersEditTabAdminPage extends APL_TabAdminPage
{
	
	private $model = null;
	
	
	/**
	 * Creates an OrgHub_UsersEditTabAdminPage object.
	 */
	public function __construct( $parent )
	{
		parent::__construct( 'edit', 'Edit User', $parent );
		$this->model = OrgHub_Model::get_instance();
	}


	/**
	 * Processes the current admin page.
	 */
	public function process()
	{
		if( empty($_REQUEST['action']) ) return;
		
		$user_id = intval($_REQUEST['id']);
		if( !$user_id ) return;
		$db_user = $this->model->user->get_user_by_id($user_id);
		
		switch( $_REQUEST['action'] )
		{
			case 'update-status':
				// TODO: update status function
				break;
			case 'Process User':
				$this->model->user->process_user( $user_id );
				break;
			case 'create-username':
				$this->model->user->create_wp_user( $user_id );
				break;
			case 'delete-username':
				// TODO: delete username function
				//$this->model->user->delete_wp_user( $user_id );
				break;
			case 'reset-wp-user-id':
				$this->model->user->set_user_column( $user_id, 'wp_user_id', null );
				break;
			case 'create-site':
				$this->model->user->create_site( $user_id, true );
				break;
			case 'archive-site':
				$this->model->user->archive_site( $user_id );
				break;
			case 'publish-site':
				$this->model->user->publish_site( $user_id );
				break;
			case 'reset-profile-site-id':
				$this->model->user->set_user_column( $user_id, 'profile_site_id', null );
				break;
			case 'create-connections-post':
				$this->model->user->create_connections_post( $user_id, $_REQUEST['site'], true );
				break;
			case 'draft-connections-post':
				$this->model->user->draft_connections_post( $user_id, $_REQUEST['site'] );
				break;
			case 'publish-connections-post':
				$this->model->user->publish_connections_post( $user_id, $_REQUEST['site'] );
				break;
			case 'reset-connections-post-id':
				$this->model->user->update_connections_post_id( $user_id, $_REQUEST['site'], null );
				break;
			case 'clear-username-error':
				$this->model->user->set_user_column( $user_id, 'wp_user_error', null );
				break;
			case 'clear-username-warning':
				$this->model->user->set_user_column( $user_id, 'wp_user_warning', null );
				break;
			case 'clear-site-error':
				$this->model->user->set_user_column( $user_id, 'profile_site_error', null );
				break;
			case 'clear-site-warning':
				$this->model->user->set_user_column( $user_id, 'profile_site_warning', null );
				break;
			case 'clear-connections-error':
				$this->model->user->set_connections_column( $user_id, $_REQUEST['site'], 'connections_error', null );
				break;
			case 'clear-connections-warning':
				$this->model->user->set_connections_column( $user_id, $_REQUEST['site'], 'connections_warning', null );
				break;
		}
	}	
		

	/**
	 * Displays the current admin page.
	 */
	public function display()
	{
		$id = $_REQUEST['id'];

		if( empty($id) )
		{
			?>
			<p class="no-id">No id provided.</p>
			<?php
			return;
		}

		$user = $this->model->user->get_user_by_id( $id );
		
		if( empty($user) )
		{
			?>
			<p class="invalid-id">Invalid id provided: "<?php echo $id; ?>"</p>
			<?php
			return;
		}

		extract($user);
		?>
		
		<?php
		//
		// Process User
		//
		?>

		<?php $this->form_start( 'process-user', null, 'process-user', array( 'id' => $id ) ); ?>
			<?php submit_button( 'Process User' ); ?>
		<?php $this->form_end(); ?>
		
		
		
		<?php
		//
		// WordPress User account
		//
		?>
		
		<?php $this->form_start( 'wp-user', null, null, array( 'id' => $id ) ); ?>

		<h4>WordPress User Account</h4>
		
		<div id="wp-user-account-details" class="details-box">

			<?php
			$class = '';
			if( $error = $this->model->user->get_user_column( $id, 'wp_user_error' ) ) $class .= 'exception error';
			elseif( $warning = $this->model->user->get_user_column( $id, 'wp_user_warning' ) ) $class .= 'exception warning';
			if( $class )
			{
				?>
				<p class="<?php echo $class; ?>">
					<?php
					if( $error ):
						echo $error;
						?>
						<button name="action" value="clear-user-error">Clear Error</button>
						<?php
					else:
						echo $warning;
						?>
						<button name="action" value="clear-user-warning">Clear Warning</button>
						<?php
					endif;
					?>
				</p>
				<?php
			}
				
			$wp_user = null;
			if( $wp_user_id ):
				$wp_user = $this->model->user->get_wp_user( $wp_user_id );
			
				if( $wp_user ):
					?>
					<div class="user-id"><label>ID</label><span><?php echo $wp_user->data->ID; ?></span></div>
					<div class="user-login"><label>Login</label><span><?php echo $wp_user->data->user_login; ?></span></div>
					<div class="user-name"><label>Name</label><span><?php echo $wp_user->data->display_name; ?></span></div>
					<div class="user-email"><label>Email</label><span><?php echo $wp_user->data->user_email; ?></span></div>
					<?php
				else:
					?>
					<p class="notice notice-error">ERROR: wp_user_id set ("<?php echo $wp_user_id; ?>") but user does not exist.</p>
					<?php
				endif;
			else:
				?>
				<p>No user set.</p>
				<?php
			endif;
			?>
		
			<div class="buttons">
			
				<?php
				if( $wp_user ):
					?>
					<a href="<?php echo network_admin_url( 'users.php?s='.$wp_user->data->user_login ); ?>" target="_blank">Edit User</a>
					<?php
				else:
					?>
					<button name="action" value="create-username">Create User</button>
					<?php
				endif;
				
				if( $wp_user_id ):
					?>
					<button name="action" value="reset-wp-user-id">Reset wp_user_id</button>
					<?php
				endif;
				?>		
			
			</div>
			
		</div>
		<?php $this->form_end(); ?>
		
		<?php
		//
		// Profile Site
		//
		?>

		<?php $this->form_start( 'profile-site', null, null, array( 'id' => $id ) ); ?>

		<h4>Profile Site</h4>
		
		<div id="profile-site-details" class="details-box">
			
		<?php
		if( $blog_path ):
			$class = '';
			if( $error = $this->model->user->get_user_column( $id, 'profile_site_error' ) ) $class .= 'exception error';
			elseif( $warning = $this->model->user->get_user_column( $id, 'profile_site_warning' ) ) $class .= 'exception warning';
			if( $class ):
				?>
				<p class="<?php echo $class; ?>">
					<?php
					if( $error ):
						echo $error;
						?>
						<button name="action" value="clear-site-error">Clear Error</button>
						<?php
					else:
						echo $warning;
						?>
						<button name="action" value="clear-site-warning">Clear Warning</button>
						<?php
					endif;
					?>
				</p>
				<?php
			endif;

			$profile_site = null;
			if( $profile_site_id ):
				$profile_site = $this->model->user->get_profile_site( $profile_site_id );
			
				if( $profile_site ):
					?>
					<div class="site-id"><label>ID</label><span><?php echo $profile_site['blog_id']; ?></span></div>
					<div class="site-name"><label>Name</label><span><?php echo $profile_site['blogname']; ?></span></div>
					<div class="site-url"><label>URL</label><span><?php echo $profile_site['siteurl']; ?></span></div>
					<div class="site-archived"><label>Archived</label><span><?php echo ($profile_site['archived'] == '0' ? 'No' : 'Yes'); ?></span></div>
					<?php
				else:
					?>
					<p class="notice notice-error">ERROR: profile_site_id set ("<?php echo $profile_site_id; ?>") but site does not exist.</p>
					<?php
				endif;
			else:
				?>
				<p>No profile site set.</p>
				<?php
			endif;
			?>
		
			<div class="buttons">
			
				<?php
				if( $profile_site ):
					?>
					<a href="<?php echo network_admin_url( 'site-info.php?id='.$profile_site['blog_id'] ); ?>" target="_blank">Edit Site</a>
					<?php
					if( $profile_site['archived'] == '0' ):
						?>
						<button name="action" value="archive-site">Archive Site</button>
						<?php
					else:
						?>
						<button name="action" value="publish-site">Publish Site</button>
						<?php
					endif;
				else:

					?>
 					<label><?php echo $blog_domain.'/'.$blog_path; ?></label>
					<button name="action" value="create-site">Create Site</button>
					<?php
				endif;
				
				if( $profile_site_id ):
					?>
					<button name="action" value="reset-profile-site-id">Reset profile_site_id</button>
					<?php
				endif;
				?>		
			
			</div>
		<?php
		else:
			echo 'Profile Site not specified.';
		endif;
		?>
		</div>		
		<?php $this->form_end(); ?>

		<?php
		//
		// Connections Post(s)
		//
		?>
		
		<?php foreach( $connections_sites as $cs ): ?>
		
		<?php $this->form_start( 'connections-site', null, null, array( 'id' => $id ) ); ?>

		<h4>Connections Post: <?php echo $cs['site']; ?></h4>

		<div id="connections-post-details" class="details-box">

			<?php
			$class = '';
			if( $error = $this->model->user->get_connections_column( $id, $cs['site'], 'connections_error' ) ) $class .= 'exception error';
			elseif( $warning = $this->model->user->get_connections_column( $id, $cs['site'], 'connections_warning' ) ) $class .= 'exception warning';
			if( $class ):
				?>
				<p class="<?php echo $class; ?>">
					<?php
					if( $error ):
						echo $error;
						?>
						<button name="action" value="clear-connections-error">Clear Error</button>
						<?php
					else:
						echo $warning;
						?>
						<button name="action" value="clear-connections-warning">Clear Warning</button>
						<?php
					endif;
					?>
				</p>
				<?php
			endif;

			$connections_post = null;
			if( $cs['post_id'] ):
				$connections_post = $this->model->user->get_connections_post( $cs['post_id'], $cs['site'] );
	
				if( $connections_post ):				
					$author = get_user_by( 'id', $connections_post['post_author'] );

					?>
					<div class="connections-id"><label>ID</label><span><?php echo $connections_post['ID']; ?></span></div>
					<div class="connections-title"><label>Title</label><span><?php echo $connections_post['post_title']; ?></span></div>
					<div class="connections-author"><label>Author</label><span><?php echo $author->display_name; ?></span></div>
					<div class="connections-draft"><label>Status</label><span><?php echo $connections_post['post_status']; ?></span></div>
					<?php
				else:
					?>
					<p class="notice notice-error">ERROR: connections_post_id set ("<?php echo $cs['post_id']; ?>") but connections post does not exist.</p>
					<?php
				endif;
			else:
				?>
				<p>No connections post set.</p>
				<?php
			endif;
			?>

			<div class="buttons">
	
				<input type="hidden" name="site" value="<?php echo $cs['site']; ?>" />
				<?php
				if( $connections_post ):
					?>
					<a href="<?php echo $this->model->user->get_connections_post_edit_link($connections_post['ID'], $cs['site']); ?>" target="_blank">Edit Post</a>
					<?php
					if( $connections_post['post_status'] != 'draft' ):
						?>
						<button name="action" value="draft-connections-post">Draft Post</button>
						<?php
					else:
						?>
						<button name="action" value="publish-connections-post">Publish Post</button>
						<?php
					endif;
				else:
					?>
					<button name="action" value="create-connections-post">Create Post</button>
					<?php
				endif;
		
				if( $cs['post_id'] ):
					?>
					<button name="action" value="reset-connections-post-id">Reset connections_post_id</button>
					<?php
				endif;
				?>		
	
			</div>
		</div>
		<?php $this->form_end(); ?>
		
		<?php endforeach; ?>

		
		
		<?php
		//
		// Process User
		//
		?>

		<?php $this->form_start( 'process-user', null, 'process-user', array( 'id' => $id ) ); ?>
			<?php submit_button( 'Process User' ); ?>
		<?php $this->form_end(); ?>
		
		<?php
	}

} // class OrgHub_UsersEditTabAdminPage extends APL_TabAdminPage
endif; // if( !class_exists('OrgHub_UsersEditTabAdminPage') ):

