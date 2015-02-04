

/**
 * Start the refresh of all sites.
 * @param  array  settings  The AJAX buttons settings.
 */
function refresh_all_sites_start( settings )
{
	jQuery('#ajax-status').html('AJAX refresh started.');
	jQuery(settings.this).prop('disabled', true);
}


/**
 * Done with the refresh of all sites.
 * @param  array  settings  The AJAX buttons settings.
 */
function refresh_all_sites_end( settings )
{
	jQuery('#ajax-status').html('AJAX refresh done.');
	jQuery(settings.this).prop('disabled', false);
	window.location.replace(window.location.href);
}


/**
 * Start contacting the server via AJAX for refresh sites list.
 * @param  int    fi        The current form count.
 * @param  array  settings  The AJAX buttons settings.
 */
function refresh_all_sites_loop_start( fi, settings )
{
	jQuery('#ajax-progress').html('Contacting server for AJAX data.');
}


/**
 * Finished contacting the server via AJAX for refresh sites list.
 * @param  int    fi        The current form count.
 * @param  array  settings  The AJAX buttons settings.
 * @param  bool   success   True if the AJAX call was successful, otherwise false.
 * @param  array  data      The returned data on success, otherwise error information.
 */
function refresh_all_sites_loop_end( fi, settings, success, data )
{
	jQuery('#ajax-progress').html('Received AJAX data.');
}


/**
 * Start cycling through the sites list returned via AJAX.
 * @param  array  ajax  The AJAX settings returned from the server.
 */
function refresh_site_start( ajax )
{
	jQuery('#ajax-status').html('Performing AJAX refresh.');
	jQuery('table.orghub-sites .site_title').addClass('processing');
}


/**
 * Finished cycling through the sites list returned via AJAX.
 * @param  array  ajax  The AJAX settings returned from the server.
 */
function refresh_site_end( ajax )
{
	jQuery('#ajax-status').html('AJAX refresh done.');
	jQuery('table.orghub-sites .site_title').removeClass('processing');
}


/**
 * Start contacting the server via AJAX to refresh one site.
 * @param  int    fi        The current form count.
 * @param  array  settings  The AJAX buttons settings.
 * @param  int    ai        The current ajax items count.
 * @param  array  ajax      The AJAX settings returned from the server.
 */
function refresh_site_loop_start( fi, settings, ai, ajax )
{
	jQuery('#ajax-progress').html('Refreshing blog '+(ai+1)+' of '+(ajax.items.length));
}


/**
 * Finished contacting the server via AJAX to refresh one site.
 * @param  int    fi        The current form count.
 * @param  array  settings  The AJAX buttons settings.
 * @param  int    ai        The current ajax items count.
 * @param  array  ajax      The AJAX settings returned from the server.
 * @param  bool   success   True if the AJAX call was successful, otherwise false.
 * @param  array  data      The returned data on success, otherwise error information.
 */
function refresh_site_loop_end( fi, settings, ai, ajax, success, data )
{
	if( !success || !data.success )
	{
		jQuery('#ajax-data').html('An error occurred....'+data.message);
		return;
	}
	
	var dajax = data.ajax;
	
	var row = jQuery('tr.blog-'+dajax.site.blog_id);
	if( !row ) return;
	
	jQuery(row).find('td.site_title').removeClass('processing');
	for( var column_name in data.ajax.columns )
	{
		jQuery(row).find('td.'+column_name).html(dajax.columns[column_name]);
	}
	
	if( ai+1 === ajax.items.length )
	{
		jQuery('#orghub-sites-time').html(dajax.refresh_date);
	}
}

