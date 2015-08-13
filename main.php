<?php
/**
 * The filters and functions used by both the Network and Blog-level Organization Hub.
 * 
 * @package    organization-hub
 * @author     Crystal Barton <atrus1701@gmail.com>
 */


if( !defined('ORGANIZATION_HUB') ):

/**
 * The full title of the Organization Hub plugin.
 * @var  string
 */
define( 'ORGANIZATION_HUB', 'Organization Hub' );

/**
 * True if debug is active, otherwise False.
 * @var  bool
 */
define( 'ORGANIZATION_HUB_DEBUG', false );

/**
 * The path to the plugin.
 * @var  string
 */
define( 'ORGANIZATION_HUB_PLUGIN_PATH', __DIR__ );

/**
 * The url to the plugin.
 * @var  string
 */
define( 'ORGANIZATION_HUB_PLUGIN_URL', plugins_url('', __FILE__) );

/**
 * The version of the plugin.
 * @var  string
 */
define( 'ORGANIZATION_HUB_VERSION', '0.0.1' );

/**
 * The database version of the plugin.
 * @var  string
 */
define( 'ORGANIZATION_HUB_DB_VERSION', '1.0' );

/**
 * The database options key for the Organization Hub version.
 * @var  string
 */
define( 'ORGANIZATION_HUB_VERSION_OPTION', 'organization-hub-version' );

/**
 * The database options key for the Organization Hub database version.
 * @var  string
 */
define( 'ORGANIZATION_HUB_DB_VERSION_OPTION', 'organization-hub-db-version' );

/**
 * The database options key for the Organization Hub options.
 * @var  string
 */
define( 'ORGANIZATION_HUB_OPTIONS', 'organization-hub-options' );

/**
 * The full path to the log file used for debugging.
 * @var  string
 */
define( 'ORGANIZATION_HUB_LOG_FILE', __DIR__.'/log.txt' );

endif;


// User custom fields.
add_action( 'show_user_profile', 'orghub_show_custom_user_fields' );
add_action( 'edit_user_profile', 'orghub_show_custom_user_fields' );


/**
 * Enqueue the admin page's CSS styles.
 */
if( !function_exists('orghub_enqueue_scripts') ):
function orghub_enqueue_scripts()
{
	wp_enqueue_style( 'orghub-style', ORGANIZATION_HUB_PLUGIN_URL.'/admin-pages/styles/style.css' );
}
endif;


/**
 * Print the custom user information.
 * @param  WP_User  $user  The current user object.
 */
if( !function_exists('orghub_show_custom_user_fields') ):
function orghub_show_custom_user_fields( $user )
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
endif;

