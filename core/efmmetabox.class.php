<?php 
/**
 * EFMMetabox
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
 * EFMMetabox
 * 
 * Metabox Manager 
 *
 * @package efm
 * @subpackage core
 */
class EFMMetabox {
	public $db = null;
	public $panel = null;
	public $fields = array();
	public $meta = array();
	
	public function __construct( &$panel ){
		global $wpdb;
		$this->db = &$wpdb;
		$this->panel = $panel;
		add_action( 'save_post', array( &$this, 'save_post' ), 1, 2 );	
		$this->register( $panel );
	}
	
	public function save_post( $post_id, $post ){
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE || wp_is_post_revision( $post ) ) return $post_id;
		
		if ( $post->post_type == 'page' ) {
			if ( !current_user_can( 'edit_page', $post_id ) ) return $post_id;
		} else {
			if ( !current_user_can( 'edit_post', $post_id ) ) return $post_id;
		}
		
		if ( !wp_verify_nonce( $_POST['efm_metabox_'. $this->panel['id']], __FILE__ ) ) return $post_id;
		
		$fields = $this->getFields( true );
		foreach($fields as $field){
			$value = $_POST[$field->field_id];
			if( empty( $value ) || $value == "" ){
				delete_post_meta( $post_id, $field->field_id );
			} else {				
				update_post_meta( $post_id, $field->field_id, $value);
			}			
		}
		$this->updateMetaKeys( $post_id );
	}
	
	public function updateMetaKeys( $post_id ){
		$meta = $this->db->get_var( $this->db->prepare(
			'SELECT GROUP_CONCAT(meta_id) AS ids
			  FROM '. $this->db->postmeta .'
			  WHERE meta_key LIKE "%s"
			  AND post_id = %d',
			$this->panel['name'].'_%', $post_id
		));
		
		$data = array(
			'panel_id' => $this->panel['id'],
			'post_id' => $post_id,
			'meta_keys' => $meta,			
		);
		
		$exist = $this->db->get_var('SELECT panel_id FROM '. EFM_DB_META_GROUP .' WHERE panel_id ='. $this->panel['id'] .' AND post_id ='. $post_id );
		if( !empty( $exist ) ){
			$this->db->update( 
				EFM_DB_META_GROUP, 
				$data, 
				array( 'panel_id' => $data['panel_id'], 'post_id' => $data['post_id'] ),
				array( '%d', '%d', '%s'),
				array( '%d', '%d' )
			);
		} else {
			$this->db->insert( EFM_DB_META_GROUP, $data );
		}		
	}
	
	public function getMetaValues( $post ){
		$metaKeys = $this->db->get_var($this->db->prepare(
			'SELECT 
			  meta_keys 
			FROM
			  '. EFM_DB_META_GROUP .' 
			WHERE panel_id = %d 
			  AND post_id = %d', 
			$this->panel['id'], $post->ID 
		));
		
		if ( !empty( $metaKeys ) ){
			$fields = $this->db->get_results( $this->db->prepare(
				'SELECT REPLACE(meta_key, %s , "") AS meta_key,
				  meta_value 
				FROM
				  '. $this->db->postmeta .' 
				WHERE meta_id IN ('. $metaKeys .')',
				$this->panel['name'] .'_'
			));
			
			foreach( $fields as $value ){
				$this->meta[$value->meta_key] = $value->meta_value;
			}	
		}			
	}
	
	public function register( $metabox, $context = 'normal', $prority = 'default' ){
		add_meta_box(
			$metabox['name'] .'_new',
			$metabox['title'] .' New class method',
			array( &$this , 'renderFields' ),
			$metabox['slug'],
			$context,
			$priority
		);
	}
	
	public function renderFields( $post ){
		$this->getMetaValues( $post );		
		$fields = $this->getFields();
		
		/* begin render with metabox wrapper */
		echo '<div class="efm_metabox">';
		
		/* Render each field from their own class - MODx shadow is here too */
		foreach( $fields as $field ){
			$className = $field->type .'Field';
			echo $className::render( $field, $this->meta );
		}
		/* end metabox with nonce and close the wrapper */
		echo '<input type="hidden" name="efm_metabox_'. $this->panel['id'] .'" value="' . wp_create_nonce(__FILE__) . '" /></div>';
		
		$meta = $this->db->get_var( $this->db->prepare(
			'SELECT GROUP_CONCAT(meta_id) AS ids
			FROM '. $this->db->postmeta .'
			WHERE meta_key LIKE "%s"
			AND post_id = %d',
			$this->panel['name'].'_%', 1
		));
		// $this->db->show_errors();
		// $this->db->print_error();
	}
	
	public function getFields( $returnName = false ){	
		$select = $returnName ? 'CONCAT( "'. $this->panel['name'] .'", "_", name ) AS field_id' : '*, CONCAT( "'. $this->panel['name'] .'", "_", name ) AS field_id';		
		$fields = $this->db->get_results( $this->db->prepare(
			'SELECT '. $select .' 
			FROM wp_efm_fields 
			WHERE owner = "panel" 
			AND owner_id = '. $this->panel['id'] .' 
			ORDER BY display_order'
		));
		return $fields;
	}
}