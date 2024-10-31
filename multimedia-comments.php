<?php

/*
Plugin Name: CIO Multimedia Comments
Plugin URI: http://vipp.com.au/cio-custom-fields-importer/how-it-works/cio-multimedia-comments/
Description: Upload multiple files in comments, add custom fields, interact with readers.  Premium version supports conditional display by page or post, access control by group. 
Author: <a href="http://vipp.com.au">VisualData</a>
Version: 1.0.0

*/

// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

//include_once plugin_dir_path(__FILE__). "class-cf-group-pods.php";

include_once plugin_dir_path(__FILE__). "class-mmcomments.php";

if (!function_exists('cio_showv')) {

	function cio_showv($v) {

		echo '<pre>';	
		print_r($v);
		echo '</pre>';
	}
}


/* gather information about all active plugins, including network activated and subsite activated plugins. 
 * get_site_option returns an array with plugin file as key, get_option returns an array with plugin name in array value.
 */ 
if (is_multisite()) {

	$cio_mmc_active_plugins = array_merge(array_keys(get_site_option('active_sitewide_plugins', array() )), get_option( 'active_plugins' , array() ));
	
} else {

	$cio_mmc_active_plugins = get_option( 'active_plugins', array() );
}

//register activation hook to run the function once when the plugin is activated. WooCommerce default customer fields are inserted, visible when WooCommerce is activated.

//register_activation_hook( __FILE__, array('VippCustomFieldsWooPro','cio_custom_fields_wc_activate' ) );



	

 /**
 * Check if Pods is active. the plugin should no nothing if pods is not active.
 **/ 
if (in_array('pods/init.php', $cio_mmc_active_plugins)) {
	

	
	$cio_mmc = new VippMMComments();
	
	$cio_mmc->run();
	
	$cio_mmc->cio_enable_mm_comments();
	
	/*
	
	//check whether the header and footer setting options are already installed. 
	if (!get_option('cio_group_settings_group_header_prefix')) {
	
		$cio_cus_fields_wc->run_pods_install_setting_script();
	
		
	}
	*/
	

} else {

	function cio_mmc_display_notice () {
	
		echo '<div class="error"> <p>' . __('CIO Multimedia Comments needs PODS to run. Please install and activate PODS first.', 'multimedia-comments') .' </p></div>';
	
	}
	
	add_action('admin_notices', 'cio_mmc_display_notice');
	return;



}




?>