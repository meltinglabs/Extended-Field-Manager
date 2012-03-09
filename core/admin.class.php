<?php 
/**
 * EFMAdmin
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
 * EFMAdmin
 *
 * @package efm
 * @subpackage core
 */
class EFMAdmin {
	public $page = null;
	protected $debugInfos = array();
	
	/**
     * The Plugin Manager Constructor.
     *
     * This method is used to create a new PluginManager object.
     *
     * @return PluginManager A unique PluginManager instance.
     */
    function __construct() {
		/*** Load the metabox loader ***/
		add_action('load-post.php', array( &$this, 'loadMetaboxes' ));
		
		/*** Load the administration panel menus ***/
		add_action('admin_menu', array( &$this, 'loadAdminMenu' ));		
							
		/*** nullify any existing autoloads ***/
		spl_autoload_register(null, false);
		
		/*** specify extensions that may be loaded ***/
		spl_autoload_extensions('.class.php');			

		/*** Handle ajax request ***/
		add_action('wp_ajax_efmrequest', array( $this, 'handleAjaxRequest' ));
	}
	
	public function loadMetaboxes(){
		global $wpdb;
		
		$metaboxes = $wpdb->get_results( $wpdb->prepare(
			'SELECT 
				o.slug,
				p.id,
				p.name,
				p.title,
				( SELECT GROUP_CONCAT(DISTINCT f.type) 
				  FROM wp_efm_fields f 
				  WHERE f.owner_type = "panel" 
				  AND f.owner_id = p.id 
				) AS fieldtypes
			FROM
				'. EFM_DB_OWNER .' o 
				LEFT JOIN '. EFM_DB_APL .' a 
					ON o.id = a.owner_id 
				LEFT JOIN '. EFM_DB_PANELS .' p 
					ON a.panel_id = p.id 
				WHERE o.owner_type = "posttype"
				ORDER BY a.field_order'
		), ARRAY_A);
		
		// $wpdb->show_errors();
		// $wpdb->print_error();
		
		if( !empty( $metaboxes ) ){
			/* Load the metabox class handler and the field abstract class */
			include_once 'efmmetabox.class.php';
			include_once EFM_CORE_PATH . DIRECTORY_SEPARATOR . 'efmfield.class.php';
			$loaded = array();
			$i = 0;	
		
			/*** debug ***/
			// echo '<br/><br/>';
			// $tstart = $this->getMicrotime();	
			
			foreach( $metaboxes as $metabox ){	
				$fields = explode( ',', $metabox['fieldtypes'] );
				if($i == 0){		
					/* First metabox, include all fields */
					foreach( $fields as $field ){
						include EFM_FIELDS_PATH . DIRECTORY_SEPARATOR . $field . DIRECTORY_SEPARATOR . $field . '.class.php';
						$loaded[] = $field;
					}					
				} else {
					/* Following metabox, only load unloaded fields */
					foreach( $fields as $field ){
						if( !in_array( $field, $loaded ) ){
							include EFM_FIELDS_PATH . DIRECTORY_SEPARATOR . $field . DIRECTORY_SEPARATOR . $field . '.class.php';
							$loaded[] = $field;
						}
					}					
				}
				// EFMMetabox::init( $metabox );
				$panel = new EFMMetabox( $metabox, $this );				
				$i++;
			}
			
			/*** debug ***/
			// $tend = $this->getMicrotime();		
			// echo $this->getBench($tstart, $tend, 'new class method');
		}	
		add_action( 'admin_print_styles-post.php', array( &$this, 'loadMetaboxAssets' ) );
		add_action( 'admin_print_styles-post-new.php', array( &$this, 'loadMetaboxAssets' )  );
		
		/* Load File uplaoder configs - This is ugly as hell */
		add_action( 'admin_head', array( &$this, 'loadMetaboxConfig' ) );
		// add_action('wp_ajax_plupload_action', array( &$this, 'handleUpload' ) );
	}
	
	public function setDebug( $method, $description, $value ){
		$this->debugInfos[$method][$description] = $value;
	}
	public function getDebug(){
		return $this->debugInfos;
	}
	
	
	/**
     * loadAssets.
     *
     * Load CSS and JS for PLugin Management Pages
     *
	 * @access public
     */
	public function loadMetaboxAssets(){		
		echo '<link rel="stylesheet" href="'. EFM_CSS_URL . 'metabox.css" type="text/css" charset="utf-8" />';		
		wp_enqueue_script( 'efm_metabox', EFM_JS_URL . 'efm_metabox.js', array(), false, true );			
		wp_enqueue_script('plupload-all');
	}
	
	/**
     * loadAssets.
     *
     * Load CSS and JS for PLugin Management Pages
     *
	 * @access public
     */
	public function loadMetaboxConfig(){		
		$defaultConfig = array(
			'runtimes' => 'html5,silverlight,flash,html4',
			'browse_button' => 'plupload-browse-button', // will be adjusted per uploader
			'container' => 'plupload-upload-ui', // will be adjusted per uploader
			'drop_element' => 'drag-drop-area', // will be adjusted per uploader
			'file_data_name' => 'async-upload', // will be adjusted per uploader
			'multiple_queues' => true,
			'max_file_size' => wp_max_upload_size() . 'b',
			'url' => admin_url('admin-ajax.php'),
			'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
			'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
			'filters' => array(),
			'multipart' => true,
			'urlstream_upload' => true,
			'multi_selection' => false, // will be added per uploader
			 // additional post data to send to our ajax hook
			'multipart_params' => array(
				'_ajax_nonce' => "", // will be added per uploader
				'action' => 'efmrequest', // the ajax action name
				'task' => 'upload', // the ajax action name
				'imgid' => 0 // will be added per uploader
			)
		);
		?>
		<script type="text/javascript">
			var efm_plupload_config = <?php echo json_encode( $defaultConfig ); ?>;
		</script>
		<?php
	}
		
	/**
     * setIncludePaths.
     *
     * Set the included path for the plugin classes autoloading
     *
	 * @access public
	 * @param string $task Tell the method wheter to set the include path to a specific task
	 * @return void
     */
	public function setIncludePaths( $task = null ){
		/* @infos : http://framework.zend.com/manual/en/performance.classloading.html */
		$paths = array(
			EFM_CORE_PATH,
		);
		if( $task == null ){
			/* Pages, subpages and fields directories added to the include path */
			$paths = array_merge( array(
				EFM_PAGES_PATH,
				EFM_PAGES_PATH . DIRECTORY_SEPARATOR . $this->classFilename,
				EFM_FIELDS_PATH,
			), $paths);
		}
		if( $task == 'fields' ){
			$paths = array_merge( array(
				EFM_FIELDS_PATH,
			), $paths);
		}
		set_include_path( implode( PATH_SEPARATOR, $paths ) );
			
		/*** register the page class loader method ***/
		spl_autoload_register( array( &$this, 'loadClass' ) );	
	}
	
	/**
     * loadClass.
     *
     * Load the current page controller along with the abstract class all page should extends from
     *
	 * @access public
     */
	public function loadClass( $className ){
		include_once $className .'.class.php';
	}
	
	/**
     * loadFields.
     *
     * Load field classes
     *
	 * @access public
	 * @param mixed array|string $load - Whether to include all fields, only a subset of field classes or a single field class
	 * @return array $classes - An array of instantiable field classes
     */
	public function loadFields( $load = array() ){	
		if( is_array( $load ) ){
			$dirRoot = new DirectoryIterator( EFM_FIELDS_PATH );
			foreach($dirRoot as $value){
				if( $value->isDir() && !$value->isDot() ){
					require_once $value . DIRECTORY_SEPARATOR . $value . '.class.php';
					$className = ucfirst( $value ) . 'Field';
					$type = $className::getInfo('type');
					$classes[$type] = array(
						'class' => $className,
						'name' => $className::getInfo('name'),
					);
				}			
			}
			return $classes;
		}
		$file = $load . DIRECTORY_SEPARATOR . $load . '.class.php';
		if( require_once $file ){
			return true;
		}
		return false;
	}
		
	// function uploadFile() {	 
		// check ajax noonce
		// $image_id = $_POST["image_id"];
		
		// check_ajax_referer( $image_id . '_efm_upload' );
	
		// handle file upload
		// $status = wp_handle_upload( $_FILES[$image_id . '_efm_fdn'], array('test_form' => false, 'action' => 'plupload_action') );
		
		// send the uploaded file url in response
		// if( !array_key_exists( 'error', $status )  ){
			// $status['fieldname'] = $image_id;
		// }
		// return json_encode( $status );
	// }
	
	// function removeFile( $data ){		
		/* Remove files that were removed from upload */
		// $uploads = wp_upload_dir();
		// $toRemove = str_replace( $uploads['baseurl'], $uploads['basedir'], $data['to_remove'] );
		// if( @unlink( $toRemove ) ){
			// return true;
		// }
		// return false;
	// }
	
	/**
     * handleAjaxRequest
     *
     * Dispatch admin ajax request
     *
	 * @access public
     */
	public function handleAjaxRequest(){
		$task = $_POST['task'];
		
		switch( $task ){
			case 'upload':
				$this->getField();
				echo $this->field->uploadFile( $_POST );
				break;
			// case 'remove_file':
				// echo $this->removeFile( $_POST );
				// break;
			case 'remove_file':
				$this->getField();
				echo $this->field->removeFile( $_POST );
				break;
			default:				
				$this->getField();
				if( $_POST['current_field'] !== 0 ){
					$options = $wpdb->get_var( $wpdb->prepare(
						"SELECT options FROM ". EFM_DB_FIELDS ." WHERE id = %u"
						,$_POST['current_field'] 
					));
					if( !empty( $options ) )
						$this->field->setOptions( $options );
					echo $this->field->getSetupOptions();
				}				
				break;
		}			
		exit;
	}
	
	public function getField(){
		global $wpdb;
		
		$this->setIncludePaths('fields');
		$name = $_POST['field_type'];
		if( !$this->loadFields( $name ) ){
			echo 'Could not load Field Class :'. $name;
			die();
		}		
		$className = ucfirst($name) .'Field';
		$this->field = new $className();
	}
		
	/**
     * loadAdminMenu.
     *
     * Load the plugin menu in the wordpress admin
     *
	 * @access public
     */
	public function loadAdminMenu(){
		$mainLandingPage = EFM_PREFIX . 'posttypes';
		add_menu_page( 'EFM Admin', 'EFM Admin', 10, $mainLandingPage, array( &$this, 'loadPage') );
		$pluginPages[] = add_submenu_page( $mainLandingPage, 'Post Types', 'Post Types', 10, $mainLandingPage, array( &$this, 'loadPage' ) );	
		$pluginPages[] = add_submenu_page( $mainLandingPage, 'Panels', 'Panels', 10, EFM_PREFIX . 'panels', array( &$this, 'loadPage' ) );	
		
		/*** Load the full admin css only when on plugin managing pages ***/
		foreach($pluginPages as $key => $page){
			add_action('admin_head-'. $page, array(&$this, 'loadAssets'), $page);
		}		
	}
	
	/**
     * load.
     *
     * Load the requested page to manage fields, postypes, panels, whatever...
     *
	 * @access public
	 * @return string The processed content || && error message if any
     */
	public function loadPage($render = 'page'){		
		$requestedPage = (string) $_GET['page'];
		$this->classFilename = str_replace( EFM_PREFIX, '', $requestedPage );
		$className = ucfirst( $this->classFilename ) . 'Manager';
		
		/* We may be on a subpage */
		if( isset( $_GET['action'] ) && is_string( $_GET['action'] ) ){ 
			$this->subPage = $_GET['action']; 
			$className = ucfirst( $this->subPage );
		}	
		
		/*** Set the plugin include paths here after dynamic path name has been set ***/
		$this->setIncludePaths();
		
		/* Instanciate the requested page */
		$this->page = new $className();
		
		switch($render){
			case 'assets':
				if( !$this->page instanceof EFMPage ){
					return false;
				}
				return true;
				break;
			default:
				if( !$this->page instanceof EFMPage ){
					return $this->render(
						'Sorry...', 
						'<div class="error below-h2" id="notice"><p>All Pages have to extend the EFMPage abstract class.</p></div>'
					);	
				}
				$this->page->setController($this);
				return $this->page->render();
				break;
		}		
	}	
	
	/**
     * loadAssets.
     *
     * Load CSS and JS for PLugin Management Pages
     *
	 * @access public
     */
	public function loadAssets(){		
		echo '<link rel="stylesheet" href="'. EFM_CSS_URL . 'style.css" type="text/css" charset="utf-8" />';		
		if( $this->loadPage('assets') ){
			$this->page->loadAssets();
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