<?php

/**
 * Plugin Name: Scan files
 * Plugin URI: https://yupiketing.com/
 * Description: Search a string in all the files in a given path.
 * Version: 1.1
 * Author: Yupiketing
 * Author URI: https://yupiketing.com/
 * Requires at least: 5.0
 * Tested up to: 5.4.1
 *
 * Text Domain: yk-scan-files
 * Domain Path: /lang/
 */

	defined( 'ABSPATH' );
	
	// Self hosted updates
	
	function yk_scan_files_plugin_info( $res, $action, $args ){
		$plugin_slug = 'yk-scan-files';
		$remote_url = 'https://admin.yupiketing.com/wp-content/plugins/yk-update-self-host-plugins/updates.php?plugin='.$plugin_slug.'&cache='.strtotime( date( 'Y-m-d H:i:s' ) );
		$remote = wp_remote_get( $remote_url );
		$body = json_decode( wp_remote_retrieve_body( $remote ), true );
		if ( $body && $args->slug == $plugin_slug ){
			$res = new stdClass();
			$res->name = $body["name"];
			$res->slug = $plugin_slug;
			$res->version = $body["version"];
			$res->tested = $body["tested"];
			$res->requires = $body["requires"];
			$res->requires_php = $body["requires_php"];
			$res->download_link = $body["download_url"];
			$res->last_updated = $body["last_updated"];
			$res->sections = array(
				'changelog' => $body["sections"]["changelog"],
			);
		}
		return $res;
	}
	add_filter('plugins_api', 'yk_scan_files_plugin_info', 20, 3);

	function yk_scan_files_plugin_update( $transient ){
		$plugins = get_plugins();
		$plugin_slug = 'yk-scan-files';
		$remote_url = 'https://admin.yupiketing.com/wp-content/plugins/yk-update-self-host-plugins/updates.php?plugin='.$plugin_slug.'&cache='.strtotime( date( 'Y-m-d H:i:s' ) );
		$remote = wp_remote_get( $remote_url );
		$body = json_decode( wp_remote_retrieve_body( $remote ), true );
		if ( $body && $plugins["$plugin_slug/$plugin_slug.php"]["Version"] != $body["version"] ) {
			$res = new stdClass();
			$res->slug = $plugin_slug;
			$res->plugin = $plugin_slug.'/'.$plugin_slug.'.php';
			$res->new_version = $body["version"];
			$res->package = $body["download_url"];
			$transient->response[$res->plugin] = $res;
		}
		return $transient;
	}
	add_filter('site_transient_update_plugins', 'yk_scan_files_plugin_update', 10, 1 );
	
	// Main code

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