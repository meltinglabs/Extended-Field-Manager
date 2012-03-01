<?php 
/**
 * EFMPage
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
 * Page Controller
 * 
 * The base abstract class that all pages extend from when browsing the EFM Manager admin
 *
 * @package efm
 * @subpackage controllers
 */
abstract class EFMPage {
	/**
     * @access public
     * @var title - The panel title.
     */
    public $controller = null;
	
	/**
     * @access public
     * @var title - The panel title.
     */
    public $title = 'My Sites Variable - Panel Default Title';
	
	/**
     * @access public
     * @var icon - The panel icon to show on the left of the panel title
     */
	public $icon = 'icon-options-general';
	
	/**
     * @access public
     * @var errors - Errors to show below the page title if any
     */
	public $errors = array();
	public $success = false;
	public $successMessage;
	public $db;

	/**
     * The Page Constructor.
     *
     * This method is used to create a new PageController object.
     *
     * @return PageController A unique PageController instance.
     */
    function __construct() {
		global $wpdb;
		$this->db = &$wpdb;
	}
	
	function setController(EFMAdminController &$controller){
		$this->controller = &$controller;
	}
		
	public function getUrl( $args = array() ){
		$params = array_merge(array(
			'page' => EFM_PREFIX . $this->getPageKey(),
		), $args);
		$queryString = http_build_query($params);
		return admin_url( 'admin.php?' . $queryString );
	}	
	
	public function getTitle(){
		return $this->title;
	}
	
	public function getIcon(){
		return $this->icon;
	}
	
	public function loadAssets(){
		return true;
	}
	
	/**
     * render
     *
     * Render the requested page
     *
	 * @access public
	 * @return string The processed content || && error message if any
     */
	public function render(){	
		$content =  $this->getContent();
		?>
			<div id="admin" class="wrap">
				<?php if($icon !== null): ?>
					<div class="icon32" id="<?php echo $this->icon; ?>"><br/></div>
				<?php endif; ?>
				<h2><?php echo $this->getTitle(); ?></h2>
				<?php if( !empty( $this->errors ) ) { $this->showErrors(); } ?>
				<?php if( $this->success ){ $this->showSucessMessage(); } ?>
				<?php echo $content; ?>
			</div>
		<?php
	}
		
	public function addError($error){
		array_push($this->errors, $error);
	}
	
	public function showErrors(){
		?>
			<div class="error below-h2" id="notice">
				<p>Please fix the following errors:</p>
				<ul class="ul-disc">
				<?php foreach( $this->errors as $error ): ?>
					<li><?php echo $error['message']; ?></li>
				<?php endforeach; ?>
				</ul>						
			</div>
		<?php
	}
	
	public function setSuccessMessage( $message ){
		$this->success = true;
		$this->successMessage = $message;
	}
	
	public function showSucessMessage(){
		?>
			<div class="updated below-h2">
				<p><?php echo $this->successMessage; ?></p>					
			</div>
		<?php
	}
	
	abstract public function getPageKey();	
	abstract public function getContent();	
}