<?php
/**
 * OrgHub_CsvHandler
 * 
 * 
 * 
 * @package    orghub
 * @subpackage classes
 * @author     Crystal Barton <cbarto11@uncc.edu>
 */

if( !class_exists('OrgHub_CsvHandler') ):
class OrgHub_CsvHandler
{
	public static $length = 99999;
	public static $delimiter = ',';
	public static $enclosure = '"';
	public static $escape = '\\';

	public static $last_error = '';

	
	/**
	 * 
	 */
    public static function import( $filename, &$rows, $use_comment_column = true )
    {
    	$headers = null;
		$rows = array();
		
		if( !file_exists($filename) )
		{
        	self::$last_error = 'File does not exist: "'.$filename.'".';
        	return false;
		}
        
        $resource = @fopen( $filename, 'r' );
        
        if( $resource === false )
        {
        	self::$last_error = 'Unable to open file: "'.$filename.'".';
        	return false;
		}

        while( $keys = fgetcsv($resource, self::$length, self::$delimiter, self::$enclosure, self::$escape) )
        {
			$keys = array_map( 'trim', $keys );

			if( $headers === null ) 
			{
				$headers = $keys;
				continue;
			}


			$row = array();
			
			for( $i = 0; $i < count($keys); $i++ )
			{
				if( ($i < count($headers)) && ($headers[$i] !== '') )
				{
					$row[$headers[$i]] = $keys[$i];
				}
			}
			
			for( $i = count($keys); $i < count($headers); $i++ )
			{
				$row[$headers[$i]] = '';
			}
			
			array_push($rows, $row);
        }

        fclose( $resource );
        return true;
    }
    
    
	/**
	 * 
	 */
    public static function export( $filename, &$headers, &$rows )
    {
		$delimiter_esc = preg_quote(self::$delimiter, '/'); 
		$enclosure_esc = preg_quote(self::$enclosure, '/');
		$space_esc = preg_quote(' ', '/');
		
		foreach( $rows as &$row )
		{
			foreach( $row as &$column )
			{
				if( is_array($column) )
				{
					if( count($column) > 1 ):
    				foreach( $column as &$c )
    				{
    					if( preg_match("/(?:${delimiter_esc}|${enclosure_esc}|${space_esc}|\s)/", $c) )
    					{
	    					$c = self::$enclosure.str_replace(self::$enclosure, self::$enclosure.self::$enclosure, $c).self::$enclosure;
//	    					$c = str_replace(self::$enclosure, self::$enclosure.self::$enclosure, $c);
	    				}
    				}
    				endif;

//					$column = self::$enclosure.implode( self::$enclosure.self::$delimiter.self::$enclosure, $column ).self::$enclosure;
					$column = implode( self::$delimiter, $column );
				}
			}
		}
		
     	header( 'Content-type: text/csv' );
 		header( 'Content-Disposition: attachment; filename='.$filename.'.csv' );
 		header( 'Pragma: no-cache' );
 		header( 'Expires: 0' );
		
		$outfile = fopen( 'php://output', 'w' );
		
		fputcsv( $outfile, $headers );
		
		for( $i = 0; $i < count($rows); $i++ )
		{
			fputcsv( $outfile, $rows[$i] );
		}
		
		fclose( $outfile );
    }

}
endif;

