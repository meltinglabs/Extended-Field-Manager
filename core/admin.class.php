<?php 
/**
 * Plugin Admin Controller
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
 * The plugin base class
 *
 * @package efm
 * @subpackage controllers
 */
class EFMAdminController {
	public $page = null;
	
	/**
     * The Plugin Manager Constructor.
     *
     * This method is used to create a new PluginManager object.
     *
     * @return PluginManager A unique PluginManager instance.
     */
    function __construct() {
		/*** Load the administration panel menus ***/
		add_action('admin_menu', array( &$this, 'loadAdminMenu' ));
					
		/*** nullify any existing autoloads ***/
		spl_autoload_register(null, false);
		
		/*** specify extensions that may be loaded ***/
		spl_autoload_extensions('.php, .class.php');	

		/*** Handle ajax request ***/
		add_action('wp_ajax_efmrequest', array( $this, 'handleAjaxRequest' ));
	}
	
	/**
     * loadPageController.
     *
     * Load the current page controller along with the abstract class all page should extends from
     *
	 * @access public
     */
	public function loadPageController(){
		$base = EFM_CORE_PATH . 'page.class.php';
		if( !file_exists($base) ){
			return false;
		}		
		
		$page = $this->classFilename;
		$filename =  $page .'.class.php';
		$file = EFM_CORE_PATH . 'pages/' . $filename;
		
		if( !file_exists($file) ){
			return false;
		}
		include $base;
		include $file;
		
		if( null !== $this->subPage ){
			$filename =  $this->subPage .'.class.php';
			$file = EFM_CORE_PATH . 'pages/'. $page .'/'. $filename;
			if( !file_exists($file) ){
				return false;
			}
			include $file;
		}
		return true;
	}
	
	public function loadFieldController($field){
		$base = EFM_CORE_PATH . 'field.class.php';
		if( !file_exists($base) ){
			return false;
		}
		$filename = $field . '.class.php';
		$file = EFM_FIELDS_PATH . $field .'/' . $filename;
		
		if( !file_exists($file) ){
			return false;
		}
		
		include $base;
		include $file;
		return true;
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
		add_menu_page( 'EFM Admin', 'EFM Admin', 10, $mainLandingPage, array( &$this, 'load') );
		$pluginPages[] = add_submenu_page( $mainLandingPage, 'Post Types', 'Post Types', 10, $mainLandingPage, array( &$this, 'load' ) );	
		$pluginPages[] = add_submenu_page( $mainLandingPage, 'Panels', 'Panels', 10, EFM_PREFIX . 'panels', array( &$this, 'load' ) );	
		
		/*** Load the full admin css only when on plugin managing pages ***/
		foreach($pluginPages as $key => $page){
			add_action('admin_head-'. $page, array(&$this, 'loadAssets'), $page);
		}		
	}
	
	/**
     * loadAssets.
     *
     * Load CSS and JS for PLugin Management Pages
     *
	 * @access public
     */
	public function loadAssets($er){		
		echo '<link rel="stylesheet" href="'. EFM_CSS_URL . 'style.css" type="text/css" charset="utf-8" />';		
		if($this->load('assets')){
			$this->page->loadAssets();
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
	public function load($render = true){
		$this->classFilename = str_replace( EFM_PREFIX, '', $_GET['page'] );
		$className = ucfirst( $this->classFilename ) . 'Page';
		
		/* We may be on a subpage */
		if( isset( $_GET['action'] ) ){ 
			$this->subPage = $_GET['action']; 
			/* Change the className to initialize accordingly */
			$className = ucfirst( $this->subPage ) . 'Page';
		}
		
		/*** register the page loader method ***/
		spl_autoload_register( array( &$this, 'loadPageController' ) );			
		
		$this->page = new $className();
		switch($render){
			case 'assets':
				if( !$this->page instanceof PageController ){
					return false;
				}
				return true;
				break;
			default:
				if( !$this->page instanceof PageController ){
					return $this->render(
						'Uh Oh !', 
						'<div class="error below-h2" id="notice"><p>All Pages have to extend the PageController abstract class.</p></div>'
					);	
				}
				$this->page->setController($this);
				return $this->render($this->page->getTitle(), $this->page->getContent(), $this->page->icon);
				break;
		}		
	}	
	
	/**
     * render
     *
     * Render the requested page
     *
	 * @access public
	 * @param string $title - The page title
	 * @param string $content - The page content
	 * @param string $icon - The page icon to use (optionnal - default : null)
	 * @return string The processed content || && error message if any
     */
	public function render($title, $content, $icon =  null){	
		?>
			<div id="admin" class="wrap">
				<?php if($icon !== null): ?>
					<div class="icon32" id="<?php echo $icon; ?>"><br/></div>
				<?php endif; ?>
				<h2><?php echo $title; ?></h2>
				<?php echo $content; ?>
			</div>
		<?php
	}
	
	public function handleAjaxRequest(){
		$name = $_POST['value'];
		$class = $this->loadFieldController($name);
		if(!$class){			
			echo 'Could not load Field Class :'. $name .'Field';
		} else {
			$className = ucfirst($name) .'Field';
			$this->field = new $className();
			echo $this->field->getSetupOtions();
		}		
		die();
	}
}