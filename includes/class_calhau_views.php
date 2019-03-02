<?php

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class CalhauViews
{
	public function view(string $filename, array $vars = [], string $base = "admin")
	{

		if($base != "admin" and $base != "public") {
			return;
		}

		$basedir = plugin_dir_path( __FILE__ );
		$basedir = str_replace("includes/", "", $basedir);
		$filename = str_replace(".", "/", $filename) . ".php";
		$file = $basedir . $filename;
		$fileHeader = $basedir . $base . "/views/header.php";
		$fileFooter = $basedir . $base . "/views/footer.php";

		if(count($vars) > 0) {
			foreach( $vars as $name => $value ) {
				$$name = $value;
			}
		}

		if(is_file($file)) {

			// Loading header...
			if(is_file($fileHeader)) {

				require_once $fileHeader;
	
			}

			require_once $file;

			// Loading header...
			if(is_file($fileFooter)) {

				require_once $fileFooter;
	
			}

		} else {

			wp_die( "The specified view file does not exist." );

		}
	}
	
}