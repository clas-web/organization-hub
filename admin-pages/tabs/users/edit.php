<?php
/**
 * OrgHub_UsersEditTabAdminPage
 * 
 * 
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
	 * 
	 */
	public function __construct( $parent )
	{
		parent::__construct( 'edit', 'Edit User', $parent );
		$this->model = OrgHub_Model::get_instance();
	}


	/**
	 * 
	 */
	public function add_head_script()
	{
		?>
		<style>
		
		.nav-tab.active {
			color:#000;
			background-color:#fff;
		}
		
		.position-controller {
			display:block;
			clear:both;
			text-align:center;
			border:solid 1px #000;
			background-color:#fff;
			padding:0px 5px;
		}
		
		.position-controller > div {
			display:inline-block;
			width:20%;
			height:30px;
			border:solid 1px #ccc;
			background-color:#eee;
			margin:10px 5px;
			cursor:pointer;
		}
		
		.position-controller > div.selected {
			border:solid 1px #000;
		}
		
		.position-controller > div:hover {
			background-color:#ffc;
		}
		
		.position-controller .hleft {
			float:left;
		}

		.position-controller .hright {
			float:right;
		}
		
		.position-controller > div.selected {
			background-color:#000;
		}
		
		.top-submit {
			float:right;
		}
		
		input.no-border {
			border:none;
			outline:none;
			box-shadow:none;
			background:transparent;
		}
		
		.filter-form {
			min-width:50%;
			max-width:100%;
		}
		
		.filter-form table {
			border:0;
			border-collapse:collapse;
			display:block;
		}
		
		.filter-form table tr {
			width:100%;
		}
		
		.filter-form table th {
			font-weight:bold;
		}
		
		.filter-form table th,
		.filter-form table td {
			width:33%;
			padding:0em 0.5em;
		}
		
		.filter-form table tr th:first-child,
		.filter-form table tr td:first-child {
			padding-left:0em;
		}

		.filter-form table tr th:last-child,
		.filter-form table tr td:last-child {
			padding-right:0em;
		}
		
		.filter-form button {
			float:left;
			margin:5px;
			margin-left:0;
		}
		
		.filter-form .scroll-box {
			height:100px;
			border:solid 1px #ccc;
			padding:5px;
			overflow-x:hidden;
			overflow-y:scroll;
		}
		
		.filter-form .scroll-box .item {
			display:block;
			white-space:nowrap;
		}
		
		.errors-checkbox {
			padding:0.5em 0em;
		}
		
		h4 {
			margin-bottom:0.2em;
		}
		
		button.process-user {
			margin:1em 0em;
		}
		
		p.exception {
			color:red;
		}
		
		p.error {
			color:orange;
		}
		
		.details-box {
			border:solid 1px #999;
			padding:1em;
		}
		
		.details-box > p {
			margin-top:0;
		}
		
		.details-box > div {
			display:inline-block;
			margin-right:1em;
		}

		.details-box > div > label {
			display:inline-block;
			vertical-align:baseline;
			padding-right:0.3em;
			font-weight:bold;
			border-right:solid 1px #ccc;
		}

		.details-box > div > span {
			display:inline-block;
			vertical-align:baseline;
			padding-left:0.3em;
		}

		.details-box .buttons {
			margin:0;
			margin-top:1em;
			padding-top:1em;
			border-top:solid 1px #ccc;
			text-align:right;
			display:block;
		}
		
		.details-box .buttons a {
			float:left;
		}

		
		.buttons button {
			margin-left:0.5em;
		}
		
		form.upload {
			padding:1em;
			margin-bottom:2em;
			border:dotted 1px #ccc;
		}
		
		form.upload h4 {
			margin-top:0;
		}
		
		form.upload p.submit {
			margin:0; padding:0;
			text-align:right;
		}
		
		#users-table {
			margin-top:2em;
		}
		
		#users-table .user-exception {
			color:red;
		}
		
		</style>
  		<script type="text/javascript">
			jQuery(document).ready( function()
			{
			
				
			
			});
		</script>
		<?php
	}
	
	
	/**
	 * 
	 */
	public function process()
	{
		apl_print($_REQUEST);
		if( empty($_REQUEST['action']) ) return;
		
		$user_id = intval($_REQUEST['id']);
		if( !$user_id ) return;
		$db_user = $this->model->get_user_by_id($user_id);
		
		switch( $_REQUEST['action'] )
		{
			case 'update-status':
				// TODO: update status function
				break;
			case 'Process User':
				$this->model->process_user( $user_id );
				break;
			case 'create-username':
				$this->model->create_username( $user_id );
				break;
			case 'delete-username':
				// TODO: delete username function
				//$this->model->delete_username( $user_id );
				break;
			case 'reset-wp-user-id':
				$this->model->update_wp_user_id( $user_id, null );
				break;
			case 'create-site':
				$this->model->create_site( $user_id, true );
				break;
			case 'archive-site':
				$this->model->archive_site( $user_id );
				break;
			case 'publish-site':
				$this->model->publish_site( $user_id );
				break;
			case 'reset-profile-site-id':
				$this->model->update_profile_site_id( $user_id, null );
				break;
			case 'create-connections-post':
				$this->model->create_connections_post( $user_id, $_REQUEST['site'], true );
				break;
			case 'draft-connections-post':
				$this->model->draft_connections_post( $user_id, $_REQUEST['site'] );
				break;
			case 'publish-connections-post':
				$this->model->publish_connections_post( $user_id, $_REQUEST['site'] );
				break;
			case 'reset-connections-post-id':
				$this->model->update_connections_post_id( $user_id, $_REQUEST['site'], null );
				break;
			case 'clear-username-error':
				$this->model->set_wp_user_error( $user_id, null );
				break;
			case 'clear-username-warning':
				$this->model->set_wp_user_warning( $user_id, null );
				break;
			case 'clear-site-error':
				$this->model->set_profile_site_error( $user_id, null );
				break;
			case 'clear-site-warning':
				$this->model->set_profile_site_warning( $user_id, null );
				break;
			case 'clear-connections-error':
				$this->model->set_connections_error( $user_id, $_REQUEST['site'], null );
				break;
			case 'clear-connections-warning':
				$this->model->set_connections_warning( $user_id, $_REQUEST['site'], null );
				break;
		}
	}	
		

	/**
	 * 
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

		$user = $this->model->get_user_by_id( $id );
		if( empty($user) )
		{
			?>
			<p class="invalid-id">Invalid id provided: "<?php echo $id; ?>"</p>
			<?php
			return;
		}
		
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
			if( $error = $this->model->get_wp_user_error( $user['id'] ) ) $class .= 'exception error';
			elseif( $warning = $this->model->get_wp_user_warning( $user['id'] ) ) $class .= 'exception warning';
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
			if( $user['wp_user_id'] ):
				$wp_user = $this->model->get_wp_user( $user['wp_user_id'] );
			
				if( $wp_user ):
					?>
					<div class="user-id"><label>ID</label><span><?php echo $wp_user->data->ID; ?></span></div>
					<div class="user-login"><label>Login</label><span><?php echo $wp_user->data->user_login; ?></span></div>
					<div class="user-name"><label>Name</label><span><?php echo $wp_user->data->display_name; ?></span></div>
					<div class="user-email"><label>Email</label><span><?php echo $wp_user->data->user_email; ?></span></div>
					<?php
				else:
					?>
					<p class="error">ERROR: wp_user_id set ("<?php echo $user['wp_user_id']; ?>") but user does not exist.</p>
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
				
				if( $user['wp_user_id'] ):
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
			$class = '';
			if( $error = $this->model->get_profile_site_error( $user['id'] ) ) $class .= 'exception error';
			elseif( $warning = $this->model->get_profile_site_warning( $user['id'] ) ) $class .= 'exception warning';
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
			if( $user['profile_site_id'] ):
				$profile_site = $this->model->get_profile_site( $user['profile_site_id'] );
			
				if( $profile_site ):
					?>
					<div class="site-id"><label>ID</label><span><?php echo $profile_site['blog_id']; ?></span></div>
					<div class="site-name"><label>Name</label><span><?php echo $profile_site['blogname']; ?></span></div>
					<div class="site-url"><label>URL</label><span><?php echo $profile_site['siteurl']; ?></span></div>
					<div class="site-archived"><label>Archived</label><span><?php echo ($profile_site['archived'] == '0' ? 'No' : 'Yes'); ?></span></div>
					<?php
				else:
					?>
					<p class="error">ERROR: profile_site_id set ("<?php echo $user['profile_site_id']; ?>") but site does not exist.</p>
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
 					<label><?php echo $user['site_domain'].'/'.$user['site_path']; ?></label>
					<button name="action" value="create-site">Create Site</button>
					<?php
				endif;
				
				if( $user['profile_site_id'] ):
					?>
					<button name="action" value="reset-profile-site-id">Reset profile_site_id</button>
					<?php
				endif;
				?>		
			
			</div>
		</div>		
		<?php $this->form_end(); ?>

		<?php
		//
		// Connections Post(s)
		//
		?>
		
		<?php foreach( $user['connections_sites'] as $cs ): ?>
		
		<?php $this->form_start( 'connections-site', null, null, array( 'id' => $id ) ); ?>

		<h4>Connections Post: <?php echo $cs['site']; ?></h4>

		<div id="connections-post-details" class="details-box">

			<?php
			$class = '';
			if( $error = $this->model->get_connections_error( $user['id'], $cs['site'] ) ) $class .= 'exception error';
			elseif( $warning = $this->model->get_connections_warning( $user['id'], $cs['site'] ) ) $class .= 'exception warning';
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
				$connections_post = $this->model->get_connections_post( $cs['post_id'], $cs['site'] );
	
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
					<p class="error">ERROR: connections_post_id set ("<?php echo $cs['post_id']; ?>") but connections post does not exist.</p>
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
					<a href="<?php echo $this->model->get_connections_post_edit_link($connections_post['ID'], $cs['site']); ?>" target="_blank">Edit Post</a>
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

