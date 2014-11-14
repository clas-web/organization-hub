<?php



if( !function_exists('orghub_echo_name') ):
function orghub_input_name_e()
{
	echo 'organization-hub-options'.orghub_input_name( func_get_args() );
}
endif;

if( !function_exists('orghub_echo_name') ):
function orghub_input_name()
{
	$args = func_get_args();
	if( count($args) == 1 && is_array($args[0]) ) $args = $args[0];
	
	$name = '';
	
	foreach( $args as $arg )
	{
		if( is_array($arg) )
			$name .= orghub_input_name( $arg );
		else
			$name .= "[$arg]";
	}

	return $name;
}
endif;



if( !function_exists('orghub_string_to_value') ):
function orghub_string_to_value( $value )
{
	if( is_array($value) ) $value = array_map( 'orghub_string_to_value', $value );
	if( !is_string($value) ) return $value;
	
	switch( substr( $value, 0, 2 ) )
	{
		case 'b:':
			$value = ( ($value === 'b:true') ? true : false );
			break;
			
		case 'i:':
			$value = intval( substr($value, 2) );
			break;
	}
	
	return $value;
}
endif;



if( !function_exists('orghub_print') ):
function orghub_print( $text, $title = null )
{
	echo '<pre>';
	if( $title !== null ) var_dump($title);
	var_dump($text);
	echo '</pre>';
}
endif;


