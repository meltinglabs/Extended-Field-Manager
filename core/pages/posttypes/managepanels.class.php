<?php 
/**
 * ManagepanelsPage
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
 * Manage panels attached to this post type
 *
 * @package efm
 * @subpackage controllers
 * @extend PosttypesPage
 */
class ManagepanelsPage extends PosttypesPage {
	public $title = 'Edit Panels for Post Type :';
	
	function __construct(){
		global $wpdb;
		$this->db = &$wpdb;
		$this->panels = $this->db->get_results( $this->db->prepare(
			"SELECT 
			p.id,
			p.label,
			p.name,
			COUNT(f.id) AS amount 
			FROM ". EFM_DB_PANELS ." p 
			LEFT JOIN ". EFM_DB_FIELDS ."  f
			ON p.id = f.owner_id"
		));
		$this->posttype = get_post_type_object($_GET['slug']);			
	}
	
	public function loadAssets(){
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'assignpanels', EFM_JS_URL . 'assignpanels.js', array(), false, true );
	}
	
	public function setAssignedPanels(){	
		$this->assigned = $this->db->get_row( $this->db->prepare( "SELECT * FROM ". EFM_DB_POSTTYPES ." WHERE type='". $this->posttype->name ."'" ) );
		$this->assignedPanels = !empty( $this->assigned ) ? unserialize( $this->assigned->panels ) : array();
	}
	
	public function getTitle(){		
		return 'Manage Panel for post-type : '. $this->posttype->label;
	}
	
	public function getContent(){
		$this->setAssignedPanels();
		if(isset($_POST['submit'])){
			unset($_POST['submit']);
			$this->save($_POST);
		}
			
		ob_start();
			if( !empty( $this->errors ) ) { $this->showErrors(); }
			if( $this->success ){ $this->showSucessMessage('Change saved succesfully'); }
			?>
				<div id="panel-menu-settings">
					<?php $this->getLeftSide() ?>				
					<?php $this->getRightSide() ?>						
				</div>
			<?php
		return ob_get_clean();
	}
	
	public function getLeftSide(){
		?>	
			<div id="left-side-form-column">
				<div id="left-side" class="metabox-holder nav-menus-php">
					<div class="postbox">
						<h3 class="hndle"><span>Available Panels</span></h3>
						<div class="inside">
						
							<div class="misc-pub-section">
								<p>This list shows the panel that can be assigned to the cusrrent post type</p>
								<p>Drag and drop any paenl into the list on the right side</p>
							</div>
							
							<div class="misc-pub-section main available">
								<ul id="available" class="sortable field">
									<?php echo $this->getPanelsList();?>
								</ul>
							</div>
							
						</div>
					</div>
				</div>
			</div>
			<!-- End #left-side-form-column -->	
		<?php
	}
	
	public function getPanelsList( $action = 'available' ){
		$this->assignedList = '';
		$list = '';
		if( !empty( $this->panels ) ){
			foreach($this->panels as $panel):			
				ob_start();
				?>
					<li class="menu-item-handle">
						<input name="panels[]" type="hidden" value="<?php echo $panel->id; ?>"/>
						<span class="drag-icon">&nbsp;</span>
						<div class="field-label">						
							<?php echo $panel->label; ?>					
						</div>
						<div class="item-desc">							
							<span class="field-name">Name : <span><?php echo $panel->name; ?></span></span>
							<span class="field-type">Fields : <span><?php echo $panel->amount; ?></span></span>									
						</div>									
					</li>
				<?php
				if( in_array( $panel->id, $this->assignedPanels ) ){
					$this->assignedList .= ob_get_clean();
				} else {
					$list .= ob_get_clean();
				}		
			endforeach;
		}
		return $list;
	}
	
	public function getRightSide(){
		$saveAction = array(
			'action' => 'managepanels',
			'slug' => $this->posttype->name,
		);
		?>				
			<form id="right-side-liquid" class="metabox-holder nav-menus-php" action="<?php echo $this->getUrl( $saveAction ) ?>" method="post">
				<div class="postbox">
					<h3 class="hndle"><span>Assigned Panels</span></h3>
					<div class="inside">
					
						<div class="misc-pub-section">
							<p>This show the list of the panel that will be shown on the current post-type edit page as custom fields.</p>
							<p>You can drag & drop any panel from the left side list.</p>
						</div>
						
						<div class="misc-pub-section main assigned">
							<p>
								<input type="submit" value="Save Panels" class="button" id="submit" name="submit">	
							</p>
							<ul id="assigned" class="sortable field">
								<?php echo $this->assignedList; ?>
							</ul>
						</div>
						
						
					</div>
				</div>
			</form>
			<!-- End #right-side-liquid -->		
		<?php
	}
	
	
	public function save( $data ){		
		$data['panels'] = isset($data['panels']) ? serialize( $data['panels'] ) : serialize( array() );
		
		if( empty( $this->assigned ) ){
			$new = array();
			$new['type'] = $this->posttype->name;
			$new['built_in'] = $this->posttype->_builtin;
			if( !$new['built_in'] ){
				$new['arguments'] = array();				
			}
			$new['register'] = 0;
			$data = array_merge($new, $data);				
			$this->db->insert( EFM_DB_POSTTYPES, $data );
			$this->success = true;
		} else {
			$this->db->update( EFM_DB_POSTTYPES, $data, array('type' => $this->assigned->type ) );	
			$this->success = true;
		}
		// $this->db->show_errors();
		// $this->db->print_error();
		if( $this->success ){
			$this->setAssignedPanels();
		}		
	}
}