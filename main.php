<?php 
/*
Plugin Name: Extended Fields Manager
Plugin URI: http://example.com
Description: Manager custom fields for either your post types or themes
Version: 0.1a
Author:  lossendae
Author URI: http://meltinlab.com
License: GPL2
*/ 
/**
 * Extended Fields Manager
 *
 * Copyright 2006-2012 by lossendae.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package efm
 */
global $wpdb;

/* Declare constants */
define('EFM_PREFIX', 'efm_'); 
define('EFM_DB_OWNER', $wpdb->prefix . EFM_PREFIX . 'owner'); 
define('EFM_DB_PANELS', $wpdb->prefix . EFM_PREFIX . 'panels'); 
define('EFM_DB_APL', $wpdb->prefix . EFM_PREFIX . 'assigned_panels_list'); 
define('EFM_DB_FIELDS', $wpdb->prefix . EFM_PREFIX . 'fields'); 
define('EFM_DB_META_GROUP', $wpdb->prefix . EFM_PREFIX . 'meta_group'); 
define('EFM_CORE_PATH', realpath( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'core' ) ); 
define('EFM_PAGES_PATH', EFM_CORE_PATH . DIRECTORY_SEPARATOR . 'pages' ); 
define('EFM_FIELDS_PATH', EFM_CORE_PATH . DIRECTORY_SEPARATOR . 'fields' ); 

define('EFM_BASENAME', plugins_url() .'/'. str_replace( basename(__FILE__), "", plugin_basename(__FILE__) ) );
define('EFM_URL', EFM_BASENAME);

define('EFM_JS_URL', EFM_URL . 'assets/js/'); 
define('EFM_CSS_URL', EFM_URL . 'assets/css/'); 

// register_deactivation_hook( __FILE__, array( 'EFMInstaller', 'deactivate' ) );
// register_uninstall_hook( __FILE__, array( 'EFMInstaller', 'onUninstall' ) );
 
if( is_admin() ) {
	require_once EFM_CORE_PATH .'/install.class.php';
	if( class_exists( 'EFMInstaller' ) ) {
		$installer = new EFMInstaller(__FILE__);
	}
	
	require_once EFM_CORE_PATH .'/admin.class.php';
		
	if( class_exists('EFMAdminController') ) {
		$efm = new EFMAdminController();
	}
}