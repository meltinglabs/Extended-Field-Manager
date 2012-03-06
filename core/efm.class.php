<?php 
/**
 * EFM
 *
 * @package efm
 * @subpackage core
 */
class EFM {
	protected $db = null;
	protected $post = null;
	protected $initialized = false;
	protected $panels = null;
	protected $selectedPanel = false;
	protected $debug = true;
	
	/**
     * The Plugin Constructor for front end operation.
     *
     * This method is used to create a new EFM object.
     *
     * @return object $efm A unique EFM instance.
     */
    function __construct() {
		global $wpdb, $post;		
		$this->db = &$wpdb;		
	}
	
	/**
     * initialize.
     *
     * Get all panels informations for the current post
     *
	 * @access protected
     */
	protected function initialize(){	
		global $post;
		$this->post = &$post;
	
		$panels = $this->db->get_results( $this->db->prepare(
			'SELECT 
				p.id,
				p.name,
				p.title
			FROM
				'. EFM_DB_OWNER .' o 
				LEFT JOIN '. EFM_DB_APL .' a 
					ON o.id = a.owner_id 
				LEFT JOIN '. EFM_DB_PANELS .' p 
					ON a.panel_id = p.id 
				WHERE o.owner_type = "posttype"
				AND o.slug = "'. $this->post->post_type .'"
				ORDER BY a.field_order'
		));
		
		foreach($panels as $key => $panel){		
			$this->panels{$panel->name} = $this->createPanelObject( $panel );	
		}
		$this->initialized = true;
		// $this->db->show_errors();
		// $this->db->print_error();		
		// echo '<pre>'. print_r( $this->panels, true ) .'</pre>';
	}	
	
	/**
     * getMetaValues.
     *
     * Retreive all fields values for the specified panel 
     *
	 * @param array $panel - The request panel informations
	 * @access protected
     */
	protected function createPanelObject( $panel ){			
		$fields = $this->db->get_results( $this->db->prepare(
			'SELECT 
				REPLACE(m.`meta_key`, %s, "") AS meta_key,
				f.`id`,
				f.`type`,
				f.`options`,
				f.`duplicable`,
				m.`meta_value` AS value
				 
			FROM
				'. $this->db->postmeta .' m 
				LEFT JOIN '. EFM_DB_METAS .' efm 
					ON efm.`meta_id` = m.`meta_id` 
				LEFT JOIN '. EFM_DB_FIELDS .' f 
					ON f.`id` = efm.`field_id` 
			WHERE m.`post_id` = %d 
				AND efm.`field_id` IS NOT NULL',
			$panel->name .'_', $this->post->ID
		));
		if ( !empty( $fields ) ){			
			foreach( $fields as $value ){
				$key = $value->meta_key;
				unset( $value->meta_key );				
				$panel->fields{ $key } = $value;
			}	
		}
		return $panel;
	}	
	
	/**
     * select.
     *
     * Select a specific panel to use for direct field access
     *
	 * @access public
	 * @return mixed|object|boolean - $this the object instance - false if the panel does not exist
     */
	public function select( $panel ){
		if( !$this->initialized ){			
			$this->initialize();
		}
		if ( isset( $this->panels{$panel} ) ) {
			$this->selectedPanel = &$this->panels{$panel};
			return $this;
		}
		return false;
	}
	
	public function __get( $key ){
		if( !$this->selectedPanel && $this->debug ){
			return "No panel selected, set the current panel by using the select method( your_panel_name ) before trying to retreive a field";
		}
		if( $this->selectedPanel &&  isset( $this->selectedPanel->fields{$key} ) ){			
			return $this->getValue( $this->selectedPanel->fields{$key}->value );
		}
		if( !isset( $this->selectedPanel->fields{$key} ) && $this->debug ){
			return "There are no field named <strong>{$key}</strong> for the requested panel : <strong>{$this->selectedPanel->name}</strong>";
		}
		return '';
	}
	
	public function __call( $name, $argument ) {		
		if( !$this->initialized ){			
			$this->initialize();
		}
		$panel = substr( $name, 4 );
		$name = substr( $name, 0, 4 );		
	
		if ( $name == 'get_' && isset( $this->panels{$panel} ) ) {
			if( isset( $this->panels{$panel}->fields{$argument[0]} ) ){
				return $this->getValue( $this->panels{$panel}->fields{$argument[0]}->value );
			} 			
		}
		return false;
	}
	
	public function getValue( $raw ){
		$fromArray = @unserialize( $raw );
		if( $raw === 'b:0;' || $fromArray !== false){
			return $fromArray;
		} else {
			return $raw;
		}
	}
			
	/*** debug ***/
	public function getMicrotime(){
		$mtime = microtime();
		$mtime = explode(" ", $mtime);
		$mtime = $mtime[1] + $mtime[0];
		return $mtime;
	} 
	public function getBench($tstart, $tend, $name = 'not_set'){
		$totalTime = ($tend - $tstart);
		$totalTime = sprintf("Exec time for %s * %2.4f s", $name, $totalTime);
		return $totalTime .'<br/>';
	}
}