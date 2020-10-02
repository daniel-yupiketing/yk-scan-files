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
 * Domain Path: /lang/
 */

	defined( 'ABSPATH' );

	function yk_scan_files_function( $search, $path, $config ) {
		$result = '';
		$contents = scandir( $path );
		array_splice( $contents, 0, 2 );
		foreach ( $contents as $item ) {
			$file = $path.'/'.$item;
			if ( is_file ( $file ) ){
				$line_found = 0;
				$lines = file( $file );
				foreach ( $lines as $lineNumber => $line ) {
					if ( strpos( $line, $search ) !== false && !$config["file_type"] ) {
						$line_found = $lineNumber;
					}
					if ( strpos( $line, $search ) !== false && $config["file_type"] && strtolower( end( explode( '.', $file ) ) ) == $config["file_type"] ) {
						$line_found = $lineNumber;
					}
				}
				if ( $line_found ){
					$result .= '<li>'.str_replace($config["initial_path"], '', $file).' ('.__( "Line", "yk-scan-files" ).': '.$line_found.')</li>';
				}
			}
			if ( is_dir( $file ) && ( substr( $item, 0, 1 ) != '.' ) ) {
				$result .= yk_scan_files_function( $search, $file, $config );
			}
		}
		return $result;
	}

	function yk_scan_files_page(){
		$path = ( isset( $_POST["path"] ) ) ? $_POST["path"] : '';
		$string = ( isset( $_POST["string"] ) ) ? $_POST["string"] : '';
		$file_type = ( isset( $_POST["file_type"] ) ) ? $_POST["file_type"] : '';
		$action = ( isset( $_POST["action"] ) ) ? $_POST["action"] : '';
		echo '<div class="wrap">
			<h1>'.__( "Scan files", "yk-scan-files" ).'</h1>
			<p>
			<form method="post" action="">
				<input type="hidden" name="action" value="scan">
				<input type="text" name="path" value="'.$path.'" placeholder="'.__('Path', 'yk-scan-files').'" required>
				<input type="text" name="string" value="'.$string.'" placeholder="'.__('String', 'yk-scan-files').'" required>
				<input type="text" name="file_type" value="'.$file_type.'" placeholder="'.__('File type', 'yk-scan-files').'">
				<input type="submit" class="button button-primary" value="'.__('Search', 'yk-scan-files').'">
			</form>
			</p>';
			if ( $action == 'scan'){
				$config = array(
					"initial_path" 	=> '../'.$_POST["path"].'/',
					"file_type" 		=> strtolower( $_POST["file_type"] ),
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