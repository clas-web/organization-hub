<?php


/**
 * Print the content of a variable with a label as a "title".  The entire contents is 
 * enclosed in a <pre> block.
 * @param  mixed		$var	The variable to "dumped"/printed to screen.
 * @param  string|null  $label  The label/title of the variable information.
 */
if( !function_exists('apl_print') ):
function apl_print( $var, $label = null )
{
	echo '<pre>';
	
	if( $label !== null )
	{
		$label = print_r( $label, true );
		echo "<strong>$label:</strong><br/>";
	}
	
	var_dump($var);
	
	echo '</pre>';
}
endif;


/**
 * Prints the name of an input field.
 * @param  {args}  The keys of the input name.  For example:
 *				 apl_name_e( 'a', 'b', 'c' ) will echo "a[b][c]"
 *				 apl_name_e( array( 'a', 'b', 'c' ) ) will echo "a[b][c]"
 */
if( !function_exists('apl_name_e') ):
function apl_name_e()
{
	echo apl_name( func_get_args() );
}
endif;


/**
 * Constructs the name of an input field.
 * @param   array|{args}  The keys of the input name.  For example:
 *						 apl_name( 'a', 'b', 'c' ) will return "a[b][c]"
 *						 apl_name( array( 'a', 'b', 'c' ) ) will return "a[b][c]"
 * @return  string		The constructed input name. 
 */
if( !function_exists('apl_name') ):
function apl_name()
{
	$args = func_get_args();
	if( count($args) == 1 && is_array($args[0]) ) $args = $args[0];
	
	$name = '';
	
	if( count($args) > 0 )
	{
		$name .= $args[0];
	}
	
	for( $i = 1; $i < count($args); $i++ )
	{
		$arg = $args[$i];
		
		if( is_array($arg) )
			$name .= apl_name( $arg );
		else
			$name .= "[$arg]";
	}

	return $name;
}
endif;


/**
 * Constructs the current page's complete url.
 * @return  string  The constructed page URL.
 */
if( !function_exists('apl_get_page_url') ):
function apl_get_page_url( $full_url = true )
{
	$page_url = 'http';
	if( isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on') ) $page_url .= 's';
	$page_url .= '://';
	
	if( $full_url )
	{
		if( $_SERVER['SERVER_PORT'] != '80' )
			$page_url .= $_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].$_SERVER['REQUEST_URI'];
		else
			$page_url .= $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
	}
	else
	{
		if( $_SERVER['SERVER_PORT'] != '80' )
			$page_url .= $_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].strtok( $_SERVER['REQUEST_URI'], '?' );
		else
			$page_url .= $_SERVER['SERVER_NAME'].strtok( $_SERVER['REQUEST_URI'], '?' );
	}
	
	return $page_url;
}
endif;


/**
 * 
 */
if( !function_exists('apl_start_session') ):
function apl_start_session()
{
	if( !session_id() ) @session_start();
}
endif;


if( !function_exists('apl_backtrace') ):
function apl_backtrace()
{
	if(!function_exists('debug_backtrace')) 
	{
		apl_print( 'function debug_backtrace does not exists' ); 
		return; 
	}
	
	$title = 'Debug backtrace';
	$text = "\r\n";
	
	foreach(debug_backtrace() as $t) 
	{ 
		$text .= "\t" . '@ '; 
		if(isset($t['file'])) $text .= basename($t['file']) . ':' . $t['line']; 
		else 
		{ 
			// if file was not set, I assumed the functioncall 
			// was from PHP compiled source (ie XML-callbacks). 
			$text .= '<PHP inner-code>'; 
		} 

		$text .= ' -- '; 

		if(isset($t['class'])) $text .= $t['class'] . $t['type']; 

		$text .= $t['function']; 

		if(isset($t['args']) && sizeof($t['args']) > 0) $text .= '(...)'; 
		else $text .= '()'; 

		$text .= "\r\n"; 
	}
	
	apl_print( $text, $title );
}
endif;
