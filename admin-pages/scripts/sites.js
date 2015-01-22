


function refresh_all_sites_start( settings )
{
	jQuery('#ajax-status').html('AJAX refresh started.');
	jQuery(settings.this).prop('disabled', true);
}


function refresh_all_sites_end( settings )
{
	jQuery('#ajax-status').html('AJAX refresh done.');
	jQuery(settings.this).prop('disabled', false);
}


function refresh_all_sites_loop_start( fi, settings )
{
	jQuery('#ajax-progress').html('Contacting server for AJAX data.');
}


function refresh_all_sites_loop_end( fi, settings, success, data )
{
	jQuery('#ajax-progress').html('Received AJAX data.');
}


function refresh_site_start( ajax )
{
	jQuery('#ajax-status').html('Performing AJAX refresh.');
	jQuery('table.orghub-sites .site_title').addClass('processing');
}


function refresh_site_end( ajax )
{
	jQuery('#ajax-status').html('AJAX refresh done.');
	jQuery('table.orghub-sites .site_title').removeClass('processing');
}


function refresh_site_loop_start( fi, settings, ai, ajax )
{
	jQuery('#ajax-progress').html('Refreshing blog '+(ai+1)+' of '+(ajax.items.length));
}


function refresh_site_loop_end( fi, settings, ai, ajax, success, data )
{
	if( !success || !data.success )
	{
		jQuery('#ajax-data').html('An error occurred....'+data.message);
		return;
	}
	
	var row = jQuery('tr.blog-'+data.site.blog_id);
	if( !row ) return;
	
	jQuery(row).find('td.site_title').removeClass('processing');
	for( var column_name in data.columns )
	{
		jQuery(row).find('td.'+column_name).html(data.columns[column_name]);
	}
	
	if( ai+1 === ajax.items.length )
	{
		jQuery('#orghub-sites-time').html(data.refresh_date);
	}
}

