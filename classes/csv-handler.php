<?php


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
    
}

