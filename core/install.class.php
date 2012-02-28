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
    }
	
	public function create($sql){
		dbDelta($sql . $this->charset_collate);
	}

    public function setup() {
		// Table for Post types containing panels
		if( $this->db->get_var( sprintf("SHOW tables LIKE '%s'", EFM_DB_POSTTYPES) ) != EFM_DB_POSTTYPES ){
			$this->create('CREATE TABLE `'. EFM_DB_POSTTYPES .'` (
				`type` varchar(20) NOT NULL,				
				`built_in` tinyint(1) NOT NULL,
				`register` tinyint(1) DEFAULT 0,
				`arguments` text,
				`panels` text,
				PRIMARY KEY (`type`) )'
			);
		}
		
		// Table for Panels definition
		if( $this->db->get_var( sprintf("SHOW tables LIKE '%s'", EFM_DB_PANELS) ) != EFM_DB_PANELS ){
			$this->create('CREATE TABLE `'. EFM_DB_PANELS .'` (
				`id` mediumint(9) NOT NULL AUTO_INCREMENT,			
				`name` varchar(50) NOT NULL,
				`label` varchar(50) NOT NULL,
				`fields_order` text,
				PRIMARY KEY (`id`),
				KEY `name` (`name`)	)'
			);
		}
		
		// Table for fields
		if( $this->db->get_var( sprintf("SHOW tables LIKE '%s'", EFM_DB_FIELDS) ) != EFM_DB_FIELDS ){
			$this->create('CREATE TABLE `'. EFM_DB_FIELDS .'` (
				`id` mediumint(9) NOT NULL AUTO_INCREMENT,			
				`name` varchar(50) NOT NULL,
				`label` varchar(50) NOT NULL,
				`description` text,
				`panel_id` mediumint(9) NOT NULL,
				`type` varchar(20) NOT NULL,
				`required` tinyint(1) DEFAULT 0,
				`duplicable` tinyint(1) DEFAULT 0,
				`options` text,
				PRIMARY KEY (`id`),
				KEY `panel_id` (`panel_id`),
				KEY `name` (`name`)	)'
			);
		}
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