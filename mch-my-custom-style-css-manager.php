<?php
/*
Plugin Name: MyCustomStyleCssManager
Description: Manage custom CSS for adding to style.css without any hassles.
Author: macha795
Version: 0.0.9
Text Domain:my-custom-style-css-manager
*/



if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !is_admin() ) {
	return;
}


define( 'MCH_MCSCM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
require_once (MCH_MCSCM_PLUGIN_DIR . 'mch_mcscm_main.php');



if ( function_exists( 'add_action' ) && class_exists('Mch_My_Custom_Style_Css_Manager') ) {
	add_action( 'plugins_loaded', array('Mch_My_Custom_Style_Css_Manager', 'get_object' ) );
	add_action( 'plugins_loaded', ['Mch_My_Custom_Style_Css_Manager', 'myplugin_load_textdomain'] );
}

