<?php 
/**
 * EFMInstaller
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
 
/**
 * This class is only run on plugin activate/deactivate/uninstall actions
 *
 * @package efm
 * @subpackage controllers
 */
class EFMInstaller {
	public $db = null;
	protected $charset_collate = null;
		
    function __construct($file){	
		global $wpdb;
		$this->db = &$wpdb;
		
		require_once( ABSPATH.'wp-admin/includes/upgrade.php' );
		
		// Get collation info
		if ( ! empty($wpdb->charset) )
			$this->charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if ( ! empty($wpdb->collate) )
			$this->charset_collate .= " COLLATE $wpdb->collate";
		
		
		register_activation_hook( $file, array( &$this, 'setup' ) );
		// register_deactivation_hook( __FILE__, array( 'EFMInstaller', 'deactivate' ) );
		// register_uninstall_hook( __FILE__, array( 'EFMInstaller', 'onUninstall' ) );
    }
	
	public function create($sql){
		dbDelta($sql . $this->charset_collate);
	}

    public function setup() {
		// Table for Post types containing panels
		if( $this->db->get_var( sprintf("SHOW tables LIKE '%s'", EFM_DB_OWNER) ) !== EFM_DB_OWNER ){
			$this->create('CREATE TABLE `'. EFM_DB_OWNER .'` (
				`id` mediumint(9) NOT NULL AUTO_INCREMENT,
				`owner_type` varchar(20) NOT NULL,				
				`slug` varchar(20) NOT NULL,				
				`built_in` tinyint(1) NOT NULL,
				`register` tinyint(1) DEFAULT 0,
				`arguments` text,
				PRIMARY KEY (`id`),
				KEY `owner` (`owner_type`),
				KEY `slug` (`slug`)	)'
			);
		}
		
		// Table for Panels definition
		if( $this->db->get_var( sprintf("SHOW tables LIKE '%s'", EFM_DB_PANELS) ) !== EFM_DB_PANELS ){
			$this->create('CREATE TABLE `'. EFM_DB_PANELS .'` (
				`id` mediumint(9) NOT NULL AUTO_INCREMENT,			
				`name` varchar(50) NOT NULL,
				`title` varchar(50) NOT NULL,
				PRIMARY KEY (`id`),
				KEY `name` (`name`)	)'
			);
		}
		
		// Table for Panel attribution
		if( $this->db->get_var( sprintf("SHOW tables LIKE '%s'", EFM_DB_APL) ) !== EFM_DB_APL ){
			$this->create('CREATE TABLE `'. EFM_DB_APL .'` (
				`field_order` mediumint(9) NOT NULL,			
				`owner_id` mediumint(9) NOT NULL,
				`panel_id` mediumint(9) NOT NULL,
				KEY `field_order` (`field_order`),
				KEY `owner_id` (`owner_id`),
				KEY `panel_id` (`panel_id`)	)'
			);
		}
		
		// Table for meta groups
		if( $this->db->get_var( sprintf("SHOW tables LIKE '%s'", EFM_DB_METAS) ) !== EFM_DB_METAS ){
			$this->create('CREATE TABLE `'. EFM_DB_METAS .'` (
				`field_id` mediumint(9) NOT NULL,
				`panel_id` mediumint(9) NOT NULL,
				`post_id` mediumint(9) NOT NULL,
				`meta_id` mediumint(9) NOT NULL,
				KEY `field_id` (`field_id`),
				KEY `panel_id` (`panel_id`),
				KEY `post_id` (`post_id`),
				KEY `meta_key` (`meta_id`) )'
			);
		}
		
		// Table for fields
		if( $this->db->get_var( sprintf("SHOW tables LIKE '%s'", EFM_DB_FIELDS) ) !== EFM_DB_FIELDS ){
			$this->create('CREATE TABLE `'. EFM_DB_FIELDS .'` (
				`id` mediumint(9) NOT NULL AUTO_INCREMENT,			
				`name` varchar(50) NOT NULL,
				`label` varchar(50) NOT NULL,
				`description` text,
				`type` varchar(20) NOT NULL,
				`required` tinyint(1) DEFAULT 0,
				`duplicable` tinyint(1) DEFAULT 0,
				`options` text,
				`owner_type` varchar(20) NOT NULL DEFAULT "panel",
				`owner_id` mediumint(9) NOT NULL,
				`display_order` mediumint(9) NOT NULL,
				PRIMARY KEY (`id`),
				KEY `owner_type` (`owner_type`),
				KEY `owner_id` (`owner_id`),
				KEY `display_order` (`display_order`),
				KEY `name` (`name`)	)'
			);
		}
		add_action('admin_notices', array( &$this, 'showErrors' ) );
    }
	public function showErrors(){
		
	}
    public function deactivate() {}
    public function uninstall() {}
	
    /**
     * trigger_error()
     * 
     * @param (string) $error_msg
     * @param (boolean) $fatal_error | catched a fatal error - when we exit, then we can't go further than this point
     * @param unknown_type $error_type
     * @return void
     */
    public function error( $error_msg, $fatal_error = false, $error_type = E_USER_ERROR ) {
        if( isset( $_GET['action'] ) && 'error_scrape' == $_GET['action'] ) {
            echo "{$error_msg}\n";
            if ( $fatal_error )
                exit;
        } else {
            trigger_error( $error_msg, $error_type );
        }
    }
}