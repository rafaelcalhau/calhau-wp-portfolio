<?php
/*
Plugin Name:       Calhau's Portfolio
Description:       Plugin
Plugin URI:        https://calhau.me
Author:            Rafael Calhau
Author URI:        https://calhau.me
Tags:              calhau, portfolio
Version:           1.0
Stable tag:        1.0
Text Domain:       calhau-portfolio
Domain Path:       /languages
License:           Copyrighted
License URI:       
*/

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Plugin's contants.
 */
define( 'CALHAU_PORTFOLIO_VERSION', '1.0.0' );
define( 'CALHAU_PORTFOLIO_PREFIX', 'calhau_' );
define( 'CALHAU_TBL_PORTFOLIO', CALHAU_PORTFOLIO_PREFIX . "portfolio" );
define( 'CALHAU_TBL_PORTFOLIO_IMAGES', CALHAU_TBL_PORTFOLIO . "_images" );

// Importing the Class CalhauPortfolio...
require plugin_dir_path( __FILE__ ) . 'includes/class_calhau_portfolio.php';

global $CalhauPortfolio;
$CalhauPortfolio = new CalhauPortfolio();

// Defining main hooks
register_activation_hook( __FILE__ , [$CalhauPortfolio, "activate"] );
register_deactivation_hook( __FILE__ , [$CalhauPortfolio, "deactivate"] );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
$CalhauPortfolio->run();

// display the plugin settings page
function calhau_portfolio_main_page() {

	global $CalhauPortfolio;
	global $wpdb;
	
	// check if user is allowed access
	if ( ! current_user_can( 'manage_options' ) ){
		return;
	}

	// Plugin's path
	$pluginsPath = plugin_dir_path( __FILE__ );

	// Importing CalhauViews class
	require $pluginsPath . 'includes/class_calhau_views.php';

	// Nonce
	$nonce = wp_create_nonce( 'calhau_add_meta_form_nonce' ); 

	// Plugin's path
	if(substr(ABSPATH, -1) == "/") {
		$abspath = substr(ABSPATH, 0, strlen(ABSPATH) - 1);
		$pluginsPath = str_replace( $abspath, "", $pluginsPath );
		$pluginsPath = str_replace( "\\", "/", $pluginsPath );
	}

	$action = isset($_GET['action']) ? $_GET['action'] : "manage";
	$formProcessed = isset($_GET['result']) ? $_GET['result'] : "";
	$item_id = isset($_GET['item_id']) ? (int) $_GET['item_id'] : null;
	$page = isset($_GET['page']) ? $_GET['page'] : "";

	$CalhauViews = new CalhauViews();

	if($action == 'manage') {

		// Portfolio's Items
		$items = $wpdb->get_results(
			"SELECT a.*, b.filename FROM ". CALHAU_TBL_PORTFOLIO ." a ".
			"INNER JOIN ". CALHAU_TBL_PORTFOLIO_IMAGES ." b ON a.id = b.portfolio_item_id AND b.is_featured = true ".
			"ORDER BY a.published_at DESC, a.ordering"
		);
		
		$CalhauViews->view( "admin.views.main", [
			"formProcessed" => $formProcessed,
			"items" => $items,
			"nonce" => $nonce,
			"page" => $page,
			"pluginPath" => str_replace( ABSPATH, "", $pluginsPath )
		]);

	} elseif($action == 'edit' and $item_id != null) {

		// Portfolio's Item
		$item = $wpdb->get_row(
			"SELECT a.*, b.filename FROM ". CALHAU_TBL_PORTFOLIO ." a ".
			"INNER JOIN ". CALHAU_TBL_PORTFOLIO_IMAGES ." b ON a.id = b.portfolio_item_id AND b.is_featured = true ".
			"WHERE a.id = '{$item_id}' "
		);

		if($item == null) {
			if ( wp_get_referer() ) {
				wp_safe_redirect( wp_get_referer() . "&result=ok" );
			} else {
				wp_safe_redirect( get_home_url() );
			}
		}

		$CalhauViews->view( "admin.views.edit", [
			"formProcessed" => $formProcessed,
			"item" => $item,
			"nonce" => $nonce,
			"page" => $page,
			"pluginPath" => str_replace( ABSPATH, "", $pluginsPath )
		]);

	} elseif($action == 'delete' and $item_id != null) {

		if($CalhauPortfolio->item_exists( $item_id )) {
			$CalhauPortfolio->item_delete( $item_id );
		} else {
			wp_die( "This item does not exists." );
		}

	}
	
	
}