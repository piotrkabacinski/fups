<?php

class fups {

    private $host;
    private $login;
    private $password;
    private $dir;
    private $port;
    
    var $connection;
    var $source;
    var $location;

    // Colors

    var $red = "\033[31m";
    var $green = "\033[32m";
    var $yellow = "\033[33m";
    var $grey = "\033[30m";
    var $colorEnd = "\033[0m";
    var $messageEnd = "\033[0m\n";

    public function __construct( $location ) {

    	$this->location = $location;

    	return;

    }

    function connect() {

    	if( file_exists( __DIR__ . "/../fups_connects/" . sha1( $this->location ) . ".json" ) ) {

	        $fups = json_decode( file_get_contents( __DIR__ . "/../fups_connects/" . sha1( $this->location ) . ".json" ) , true );

	        $this->host = $fups['connection']['host'];
	        $this->login = $fups['connection']['login'];
	        $this->password = $fups['connection']['password'];
	        $this->dir = $fups['dir'];
		$this->port = $fups['connection']['port'];

	        $connection = ftp_connect( $this->host, $this->port ) or die ($this->red . "Cannot connect to host" .$this->messageEnd); 

	        ftp_login( $connection, $this->login, $this->password ) or die($this->red . "Cannot login" .$this->messageEnd);

	            if( ftp_site( $connection , sprintf('CHMOD %o %s', 0777 , $this->dir) ) ) {
	
	                $this->connection = $connection;
	
	            } else {
	
	                echo $this->red . "No " . $this->dir . " @ " . $this->host . $this->messageEnd;
	
	                exit;
	
	            }
	        

    	} else {

    		echo $this->red . "No fups connection file for " . $this->location . $this->messageEnd;

            exit;

    	}

    	return;

    }

    function testConnection() {

        $this->connect();

        if( $this->connection && @ftp_chdir( $this->connection , $this->dir ) ) {

            echo $this->green . "Connected successfully to " . $this->dir . " @ " . $this->host . $this->messageEnd;
        }
        
        return;
        
    }

    function disconnect() {
    	
        ftp_close( $this->connection );
        return;
    }


    function upload( $source ) {

	if( empty( $source ) ) {

		echo $this->red . "No files to upload?" . $this->messageEnd;

	}  else { 

            $list = explode( ";" , $source );

	            for( $i = 0; $i < count($list); $i++ ) {
	
	                $file = $list[$i];
	
	                if( substr( $file, -1 ) == "/") {
	
	                    $file = substr( $file, 0, -1 );
	
	                }
	
	                if( $file[0] == "/" ) {
	
	                    $file = substr( $file , 1 );
	
	                }
	
	               if( !empty($file) ) {
	
	               	 $this->uploadHandler( $file );
	
	               }              
	
	               unset( $file );
	            }
       	}

       return;
       
    }

    function uploadHandler( $source ) {

    	echo "Uploading..." . $this->messageEnd;

    	$time_start = $this->microtime();

        if( is_dir( $source ) ) {

            $this->createPath( $source , 0 );
            $this->ftp_putAll( $source );
            $status = $this->green . "Directory \"" . $source . "\" uploaded (" . $this->dir . " @ ". $this->host ." t: ".round( $this->microtime() - $time_start , 3).")" . $this->messageEnd;

        } else if( is_file( $source ) && strpos( $source , "/" ) == false ) {

            ftp_pasv($this->connection, true);
            $upload = ftp_put( $this->connection , $this->dir."/".$source , $source, FTP_BINARY );
            $status = $this->green . "File \"" . $source . "\" uploaded! (" . $this->dir . " @ ". $this->host ." t: ".round( $this->microtime() - $time_start , 3 ).")" . $this->messageEnd;

        } else {

            $this->createPath( $source , 1 );
            ftp_pasv ($this->connection, true);
            $upload = ftp_put( $this->connection , $this->dir."/".$source , $source, FTP_BINARY );
            $status = $this->green . "File \"" . $source . "\" uploaded! (" . $this->dir . " @ ". $this->host ." t: ".round( $this->microtime() - $time_start , 3 ).")" . $this->messageEnd;
        }

        echo $status;
        
        return;
        
    }

   function createPath( $source , $option ) {

        // $option: 1: file, 0: dir

        $ftp_path = $this->dir . "/";
        $path = explode( "/" , $source );

        for( $i = 0; $i < count($path) - $option; $i++ ) {

            if( !@ftp_chdir( $this->connection , $ftp_path . $path[$i] ) ) {

                ftp_mkdir($this->connection, $ftp_path . $path[$i] );
                ftp_chmod($this->connection, 0777, $ftp_path . $path[$i] );
                
            }

            $ftp_path = $ftp_path . $path[$i] . "/";

        }

        return;
        
    }

    function ftp_putAll( $source ) {

    /*
     * @author: Uku Loskit <http://stackoverflow.com/questions/8773843/php-ftp-put-copy-entire-folder-structure#8773896>
     */

        $d = dir( $source );
        $dir = $this->dir . "/" . $source;

        while($file = $d->read()) {

            if ($file !== "." && $file !== ".." ) {

                if (is_dir($source."/".$file)) {

                    if (!@ftp_chdir($this->connection, $dir."/".$file)) {

                        ftp_mkdir($this->connection, $dir."/".$file);

                    }

                    $this->ftp_putAll( $source."/".$file );

                } else {

                    $upload = ftp_put($this->connection, $dir."/".$file, $source."/".$file, FTP_BINARY); 

                }
            }
        }

        $d->close();
        
        return;
        
    }

    function createFups() {

	$file = __DIR__ . "/../fups_connects/" . sha1( $this->location ) . ".json";

$content = "{
  \"connection\" : {
    
    \"host\" : \"\",
    \"login\" : \"\",
    \"password\" : \"\",
    \"port\" : 21
    
  },
  
  \"dir\" : \"/\"

}";

	if( file_exists( $file ) ) {
	
		echo $this->red . "Fups file exists!" . $this->messageEnd;
	
	} else {
	
		$fp = fopen( $file , "a");
		fwrite($fp , $content );
		fclose($fp);
	
		echo $this->green . "Fups file created!" . $this->messageEnd;
	}
	
	return;
	
    }

    function cfname() {

        if( file_exists( __DIR__ . "/../fups_connects/" . sha1( $this->location ) . ".json" ) ) {

            echo __DIR__ . "/../fups_connects/" . sha1( $this->location ) . ".json" . $this->messageEnd;

        } else {

            echo $this->red . "No fups connection file for " . $this->location . $this->messageEnd;

        }

	return;

    }

    function rmcf() {

        if( file_exists( __DIR__ . "/../fups_connects/" . sha1( $this->location ) . ".json" ) ) {

            unlink( __DIR__ . "/../fups_connects/" . sha1( $this->location ) . ".json" );

            echo $this->green . "Fups connection file for ". $this->location ." removed" . $this->messageEnd;

        } else {

            echo $this->red . "No fups connection file for " . $this->location . $this->messageEnd;

        }


    }

    function microtime() {
	
	    list($usec, $sec) = explode(" ", microtime());
	    return ((float)$usec + (float)$sec);
	    
	    return;
	    
    }
}

?>
