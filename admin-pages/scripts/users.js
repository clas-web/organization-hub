

jQuery(document).ready(
	function()
	{

	}
);


//========================================================================================
//================================================================ Process All Users =====


/**
 * Start the processing of all users.
 * @param  array  settings  The AJAX buttons settings.
 */
function process_all_users_start( settings )
{
	jQuery('#process-users-status').html( 'Started processing Users.' );
	jQuery('#process-users-substatus')
		.removeClass('error')
		.html( '&nbsp;' );
	jQuery('#process-users-results').html( '' );
	jQuery('.apl-ajax-button').prop( 'disabled', true );
}


/**
 * Start the processing of all Users.
 * @param  array  settings  The AJAX buttons settings.
 */
function process_all_users_end( settings )
{
	jQuery('#process-users-status').html( 'Done processing Users.' );
	jQuery('.apl-ajax-button').prop( 'disabled', false );
}


/**
 * Start contacting the server via AJAX for Users list.
 * @param  int    fi        The current form count.
 * @param  array  settings  The AJAX buttons settings.
 */
function process_all_users_loop_start( fi, settings )
{
	jQuery('#process-users-status').html( 'Getting the Users list.' );
}


/**
 * Finished contacting the server via AJAX for the Users list.
 * @param  int    fi        The current form count.
 * @param  array  settings  The AJAX buttons settings.
 * @param  bool   success   True if the AJAX call was successful, otherwise false.
 * @param  array  data      The returned data on success, otherwise error information.
 */
function process_all_users_loop_end( fi, settings, success, data )
{
	if( !success || !data.success )
	{
		jQuery('#process-users-status')
			.html( 'Failed to get the Users List.' );

		jQuery('#process-users-substatus')
			.addClass('error')
			.html( data.message );
	}
	else
	{
		jQuery('#process-users-status').html( 'Received the Users List.' );
	}
}


/**
 * Start cycling through the Users list.
 * @param  array  ajax  The AJAX settings returned from the server.
 */
function process_user_start( ajax )
{
	jQuery('#process-users-status').html( 'Processing each User.' );
}


/**
 * Finished cycling through the Users list.
 * @param  array  ajax  The AJAX settings returned from the server.
 */
function process_user_end( ajax )
{
	
}


/**
 * Start contacting the server via AJAX to process one User.
 * @param  int    fi        The current form count.
 * @param  array  settings  The AJAX buttons settings.
 * @param  int    ai        The current ajax items count.
 * @param  array  ajax      The AJAX settings returned from the server.
 */
function process_user_loop_start( fi, settings, ai, ajax )
{
	jQuery('#process-users-substatus')
		.removeClass('error')
		.html(
			'Processing User '+(ai+1)+' of '+ajax.items.length+' "'+ajax.items[ai]['name']+'".'
		);
}


/**
 * Finished contacting the server via AJAX to process one User.
 * @param  int    fi        The current form count.
 * @param  array  settings  The AJAX buttons settings.
 * @param  int    ai        The current ajax items count.
 * @param  array  ajax      The AJAX settings returned from the server.
 * @param  bool   success   True if the AJAX call was successful, otherwise false.
 * @param  array  data      The returned data on success, otherwise error information.
 */
function process_user_loop_end( fi, settings, ai, ajax, success, data )
{
	//add_post_results( ajax.items[ai]['user_id'], ajax.items[ai]['name'], success, data );
}

