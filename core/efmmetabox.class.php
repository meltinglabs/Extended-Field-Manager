<?php 
/**
 * EFMMetabox
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
		
		/*** Attach the save action for the current metabox ***/
		add_action( 'save_post', array( &$this, 'savePost' ), 1, 2 );	
		
		/*** Register the metabox ***/
		$this->register( $panel );
	}
	
	/**
     * getMetaValues.
     *
     * Get the current metabox existing value
     *
	 * @param object $post - The post object
	 * @access public
     */	
	public function getMetaValues( $post ){			
		$metas = $this->db->get_results( $this->db->prepare(
			'SELECT 
				REPLACE(m.`meta_key`, %s, "") AS meta_key,
				m.`meta_value`
			FROM
				'. $this->db->postmeta .' m
				LEFT JOIN '. EFM_DB_METAS .'  efm
					ON efm.`meta_id` = m.`meta_id`
			WHERE m.`post_id` = %d
				AND efm.`field_id` IS NOT NULL',
			$this->panel['name'] .'_', $post->ID
		));
		if( !empty( $metas) ){
			foreach( $metas as $value ){
				$this->meta[$value->meta_key] = $value->meta_value;
			}
		}		
	}
	
	/**
     * getFields.
     *
     * Get the csutom field list for the current metabox
     *
	 * @access public
	 * @return object $fields - The metabox assigend fields
     */
	public function getFields(){	
		$select = '*, CONCAT( "'. $this->panel['name'] .'", "_", name ) AS field_id';		
		$fields = $this->db->get_results( $this->db->prepare(
			'SELECT '. $select .' 
			FROM wp_efm_fields 
			WHERE owner_type = "panel" 
			AND owner_id = %d
			ORDER BY display_order',
			$this->panel['id']
		));
		return $fields;
	}
	
	/**
     * renderFields.
     *
     * Render fields in the metabox
     *
	 * @param object $post - The post object
	 * @access public
     */	
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
		
		/*** debug ***/
		// $this->db->show_errors();
		// $this->db->print_error();	
	}			
	
	/**
     * register.
     *
     * Register a metabox for this instance
     *
	 * @param array $metabox - The panel information
	 * @param string $context - The metabox context - default : normal
	 * @param string $prority - The metabox prority - default : default
	 * @access public
     */	
	public function register( $metabox, $context = 'normal', $prority = 'default' ){
		add_meta_box(
			$metabox['name'],
			$metabox['title'],
			array( &$this , 'renderFields' ),
			$metabox['slug'],
			$context,
			$priority
		);
	}
	
	/**
     * deletePostMeta.
     *
     * Delete the specified meta from both native wp meta table and efm specific table if the row exist
     *
	 * @param integer $post_id - The post id
	 * @param object $field - The current field object
	 * @access public
     */
	public function deletePostMeta( $post_id, $field ){
		/* Remove the current meta */
		delete_post_meta( $post_id, $field->field_id );
		
		/* Then if the join reference exist delete it as well */
		$exist = $this->db->get_var(
			'SELECT meta_id 
			FROM '. EFM_DB_METAS .' 
				WHERE post_id ='. $post_id .'
				AND field_id ='. $field->id 
		);
		if( !empty( $exist ) ){
			$this->db->query('DELETE FROM '. EFM_DB_METAS .' WHERE meta_id = '. $exist);
		}
	}
		
	public function sanitizeArray( $data ){
		/* Remove empty value from options */
		if( is_array( $data ) ){
			foreach( $data as $k => $v ){
				if( is_array( $data[$k] ) ) $data[$k] = $this->sanitizeArray( $data[$k] );
				if( $v == '' ) unset( $data[$k] );
			}
		}		
		return $data;
	}
	
	/**
     * deletePostMeta.
     *
     * Create/Upadte the specified meta from both native wp meta table and efm specific table
     *
	 * @param integer $post_id - The post id
	 * @param object $field - The current field object
	 * @param mixed $value - The current field meta value
	 * @access public
     */
	public function updatePostMeta( $post_id, $field, $value ){
		/* Remove empty value from meta */
		$value = $this->sanitizeArray( $value );
		
		/* Update/Create meta key */
		update_post_meta( $post_id, $field->field_id, $value);
		
		/* Select all meta keys related to the current panel */
		$meta = $this->db->get_var( $this->db->prepare(
			'SELECT meta_id
			  FROM '. $this->db->postmeta .'
			  WHERE meta_key = "%s"
			  AND post_id = %d',
			$field->field_id, $post_id
		));
		
		/* Select all meta keys that already have a joint entry from efm */
		$exist = $this->db->get_var(
			'SELECT meta_id
			FROM '. EFM_DB_METAS .' 
				WHERE post_id ='. $post_id .'
				AND field_id ='. $field->id 
		);
		
		/* Prepare data */
		$data = array(
			'panel_id' => $this->panel['id'],
			'post_id' => $post_id,
			'meta_id' => $meta,			
			'field_id' => $field->id,			
		);
		
		/* Update/Create join table reference */
		if( !empty( $exist ) ){
			$this->db->update( 
				EFM_DB_METAS, 
				$data, 
				array( 'meta_id' => $data['meta_id'] ),
				array( '%d', '%d', '%d', '%d' ),
				array( '%d' )
			);
		} else {
			$this->db->insert( EFM_DB_METAS, $data );
		}	
	}	
	
	/**
     * savePost.
     *
     * Save post hook for the current metabox
     *
	 * @param integer $post_id - The post id
	 * @param object $post - The post object
	 * @access public
     */	
	public function savePost( $post_id, $post ){
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE || wp_is_post_revision( $post ) ) return $post_id;
		
		if ( $post->post_type == 'page' ) {
			if ( !current_user_can( 'edit_page', $post_id ) ) return $post_id;
		} else {
			if ( !current_user_can( 'edit_post', $post_id ) ) return $post_id;
		}
		
		if ( !wp_verify_nonce( $_POST['efm_metabox_'. $this->panel['id']], __FILE__ ) ) return $post_id;
		
		$fields = $this->getFields();
		foreach($fields as $field){
			$value = $_POST[$field->field_id];
			if( empty( $value ) || $value == "" ){
				$this->deletePostMeta( $post_id, $field );
			} else {				
				$this->updatePostMeta( $post_id, $field, $value);
			}			
		}
	}
}