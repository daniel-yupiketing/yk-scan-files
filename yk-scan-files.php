<?php

/**
 * Plugin Name: Scan files
 * Plugin URI: https://yupiketing.com/
 * Description: Search a string in all the files in a given path.
 * Version: 1.0
 * Author: Yupiketing
 * Author URI: https://yupiketing.com/
 * Requires at least: 5.0
 * Tested up to: 5.4.1
 *
 * Text Domain: yk-scan-files
 * Domain Path: /languages/
 */

	defined( 'ABSPATH' );

	function yk_scan_files_function($search, $path, $config ) {
		$result = '';
		$contents = scandir($path);
		array_splice($contents, 0,2);
		foreach ( $contents as $item ) {
			$file = $path.'/'.$item;
			if ( is_file ($file) ){
				$line_found = 0;
				$lines = file($file);
				foreach ($lines as $lineNumber => $line) {
					if (strpos($line, $search) !== false) {
							$line_found = $lineNumber;
					}
				}
				if ( $line_found ){
					$result .= '<li>'.str_replace($config["initial_path"], '', $file).' ('.__( "Line", "yk-scan-files" ).': '.$line_found.')</li>';
				}
			}
			if ( is_dir($file) && (substr($item, 0,1) != '.') ) {
				$result .= yk_scan_files_function($search, $file, $config );
			}
		}
		return $result;
	}

	function yk_scan_files_page(){
		echo '<div class="wrap">
			<h1>'.__( "Scan files", "yk-scan-files" ).'</h1>
			<p>
			<form method="post" action="">
				<input type="hidden" name="action" value="scan">
				<input type="text" name="path" value="'.$_POST["path"].'" placeholder="'.__('Path', 'yk-scan-files').'">
				<input type="text" name="string" value="'.$_POST["string"].'" placeholder="'.__('String', 'yk-scan-files').'" required>
				<input type="submit" class="button button-primary" value="'.__('Search', 'yk-scan-files').'">
			</form>
			</p>';
			if ( $_POST["action"] == 'scan'){
				$config = array(
					"initial_path" 	=> '../'.$_POST["path"].'/',
					"file_type" 		=> '',
				);
				$result = yk_scan_files_function($_POST["string"], '../'.$_POST["path"], $config );
				echo '<h2>'.__( "Results", "yk-scan-files" ).'</h2>';
				if ( $result != '' ) echo '<ol>'.$result.'</ol>';
				else echo __( "Not found", "yk-scan-files" );
			}
		echo '</div>';
	}

	function yk_scan_files_menu() {
		add_submenu_page(	'tools.php',
										__( "Scan files", "yk-scan-files" ),
										__( "Scan files", "yk-scan-files" ),
										'manage_options',
										'scan-files',
										'yk_scan_files_page',
										10 );
	}
	add_action( 'admin_menu', 'yk_scan_files_menu', 10 );

?>
