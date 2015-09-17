/**
 * jQuery script for uploading table extended data.
 *
 * @package    organization-hub
 * @author     Crystal Barton <atrus1701@gmail.com>
 * @version    1.0
 */

jQuery(document).ready( function()
{
	
	// Setup all Upload Table Extended Data.
	jQuery('table.orghub-upload tr').each( function() {

		var extended = jQuery(this).find('div.extended-data');
		var button = jQuery(this).find('div.more-data-button');
		
		if( !extended || !button ) return;
		
		jQuery(extended).hide();
		
		jQuery(button).click( function() {
			jQuery(extended).toggle();
		});
	});

});

