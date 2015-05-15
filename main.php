<?php

//error_reporting(-1);

if( !defined('ORGANIZATION_HUB') ):

define( 'ORGANIZATION_HUB', 'Organization Hub' );

define( 'ORGANIZATION_HUB_DEBUG', true );

define( 'ORGANIZATION_HUB_PLUGIN_PATH', dirname(__FILE__) );
define( 'ORGANIZATION_HUB_PLUGIN_URL', plugins_url('', __FILE__) );

define( 'ORGANIZATION_HUB_VERSION', '0.0.1' );
define( 'ORGANIZATION_HUB_DB_VERSION', '1.0' );

define( 'ORGANIZATION_HUB_VERSION_OPTION', 'organization-hub-version' );
define( 'ORGANIZATION_HUB_DB_VERSION_OPTION', 'organization-hub-db-version' );

define( 'ORGANIZATION_HUB_OPTIONS', 'organization-hub-options' );
define( 'ORGANIZATION_HUB_LOG_FILE', dirname(__FILE__).'/log.txt' );

endif;


add_action( 'show_user_profile', array('OrgHub_Main', 'show_custom_user_fields') );
add_action( 'edit_user_profile', array('OrgHub_Main', 'show_custom_user_fields') );


if( !class_exists('OrgHub_Main') ):
class OrgHub_Main
{
	
	public static function enqueue_scripts()
	{
		wp_enqueue_style( 'orghub-style', plugins_url('admin-pages/styles/style.css', __FILE__) );
	}
	
	public static function show_custom_user_fields( $user )
	{
		$description = get_user_meta( $user->ID, 'description', true );
		$category = get_user_meta( $user->ID, 'category', true );
		$type = get_user_meta( $user->ID, 'type', true );
		$profile_link = get_user_meta( $user->ID, 'profile_blog', true );
		$connections_sites = get_user_meta( $user->ID, 'connections_sites', true );

		if( !$description && !$category && !$type && !$connections_sites ) return;
		
		?>
		<h3>Organization Hub</h3>
	
		<table class="form-table">
			<tr>
				<th>
					<label for="description">Description</label>
				</th>
				<td>
					<?php echo $description; ?>
				</td>
			</tr>
			<tr>
				<th>
					<label for="category">Category</label>
				</th>
				<td>
					<?php echo $category; ?>
				</td>
			</tr>
			<tr>
				<th>
					<label for="type">Type</label>
				</th>
				<td>
					<?php echo $type; ?>
				</td>
			</tr>
			<tr>
				<th>
					<label for="profile_blog">Profile Site</label>
				</th>
				<td>
					<a href="<?php echo $profile_link; ?>"><?php echo $profile_link; ?></a>
				</td>
			</tr>
				<?php
				$connections_sites = explode( ',', $connections_sites );
				foreach( $connections_sites as $site ):
				
				$link = get_user_meta( $user->ID, 'connections_post_url-'.$site, true );
				
				?>
			<tr>
				<th>
					<label for="connections-site-link-<?php echo $site; ?>">Connections Post:<br/><?php echo $site; ?></label>
				</th>
				<td>
					<a href="<?php echo $link; ?>"><?php echo $link; ?></a>
				</td>
			</tr>
				<?php
					
				endforeach;
				?>
		</table>
		<?php
	}
}
endif;


