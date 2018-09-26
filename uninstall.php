<?php

/**
 * Trigger this file on plugin uninstall.
 * @package calhau-website
 */

if(! defined("WP_UNINSTALL_PLUGIN"))
  die;

// Importing the Class CalhauWebsite...
require plugin_dir_path( __FILE__ ) . 'includes/class_calhau_website.php';
$CalhauWebsite = new CalhauWebsite();
$CalhauWebsite->destroyPluginTables();