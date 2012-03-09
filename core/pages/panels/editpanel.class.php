<?php 
/**
 * Editpanel
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
 * @extend PanelsManager
 */
class Editpanel extends PanelsManager {
	public $db = null;
	public $panel = null;
	
	function __construct(){
		parent::__construct();
		
		$this->panel = $this->db->get_row( $this->db->prepare(
			"SELECT * FROM ". EFM_DB_PANELS ." WHERE id = %u"
		,$_GET['id'] ));
		
		if( empty( $this->panel ) ){
			wp_safe_redirect( $this->getUrl() );
		}
		// $wpdb->show_errors();
		// $wpdb->print_error();
		// echo '<pre>'. print_r($this->panel, true) .'</pre>';
	}
		
	public function loadAssets(){
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'editpanel', EFM_JS_URL . 'editpanel.js', array(), false, true );
	}
	
	public function getTitle(){		
		return 'Editing Panel : '. $this->panel->title;
	}
	
	public function getContent(){			
		if(isset($_POST['submit'])){
			unset($_POST['submit']);
			// $this->setFields($_POST);
			// $this->checkForm();
			if( empty( $this->errors ) ){
				/* @TODO : CHANGE THAT */
				$this->save($_POST);
			}
		}
			
		ob_start();
		?>
			<form id="panel-menu-settings" action="<?php echo $this->getUrl( array( 'action' => 'editpanel', 'id' => $this->panel->id ) ) ?>" method="post">
				<input name="id" type="hidden" value="<?php echo $this->panel->id; ?>"/>
				<?php $this->getLeftSide() ?>						
				<?php $this->getRightSide() ?>						
			</form>
		<?php
		return ob_get_clean();
	}
	
	public function getLeftSide(){
		?>						
			<div id="left-side-form-column">											
				<div class="form_block">
					<label for="name">
						Name
						<span class="required">*</span>
					</label>
					<input id="name" name="name" type="text" value="<?php echo $this->panel->name; ?>"/>
					<span class="description">
						Method name used as prefix to retreive fields, therefore spaces and uppercased chars are not allowed.<br/>
						Once the field created, this value cannot be changed.
					</span>
				</div>
			
				<div class="form_block">
					<label for="title">
						Tiltle
						<span class="required">*</span>
					</label>
					<input id="title" name="title" type="text" value="<?php echo $this->panel->title; ?>"/>
					<span class="description">Text used as Panel header</span>
				</div>
								
				<p class="submit">
					<a class="button" href="<?php echo $this->getUrl() ?>">Cancel</a>
					<a class="button" href="<?php echo $this->getUrl() ?>">Copy</a>
					<input type="submit" value="Save Panel" class="button-primary" id="submit" name="submit">
				</p>										
			</div>
			<!-- End #left-side-form-column -->				
		<?php
	}
	
	public function getRightSide(){
		$fieldAction = array(
			'action' => 'createfield',
			'owner_type' => 'panel',
			'id' => $this->panel->id,
		);
		?>				
			<div id="right-side-liquid" class="metabox-holder nav-menus-php">
				<div class="postbox">
					<h3 class="hndle"><span>About this Panel</span></h3>
					<div class="inside">
					
						<div class="misc-pub-section">
							<p>A group allows us to group a series of custom fields and to have a better managing of the custom fields</p>
							<p>The groups have the great usefulness of which it is possible to duplicate, this is, one creates new instance of the group (with all the custom fields that the group contains)</p>
						</div>
						
						<div class="misc-pub-section main">
							<p>
								<strong class="label">Assigned Fields</strong> 
								<a class="button" href="<?php echo $this->getUrl( $fieldAction ) ?>">Add Field</a>
							</p>
							
							<?php echo $this->getFieldsList(); ?>
						</div>
						
					</div>
				</div>
			</div>
			<!-- End #right-side-liquid -->		
		<?php
	}
	
	public function getFieldsList(){
		$fields = $this->db->get_results( $this->db->prepare( 
			"SELECT id, label, type, name 
			FROM ". EFM_DB_FIELDS ." f 
			WHERE f.owner_type = 'panel' 
			AND f.owner_id = ". $this->panel->id ."
			ORDER BY f.display_order" 
		));
		$list = '';
		if( !empty( $fields ) ){
			$list .= '<ul class="sortable field">';
			
			foreach($fields as $field):
				$editLink = array(
					'action' => 'editfield',
					'id' => $field->id,
				);
				ob_start();
				?>
					<li class="menu-item-handle">
						<input name="display_order[]" type="hidden" value="<?php echo $field->id; ?>"/>
						<span class="drag-icon">&nbsp;</span>
						<div class="field-label">						
							<?php echo $field->label; ?>
							
							<span class="item-actions">
								<a href="#" id="remove-19" class="delete">Remove</a>
								<span class="meta-sep"> | </span>
								<a href="<?php echo $this->getUrl( $editLink ); ?>" id="cancel-19" class="item-cancel">Edit</a>
							</span>
							
						</div>
						<div class="item-desc">							
							<span class="field-name">Name : <span><?php echo $field->name; ?></span></span>
							<span class="field-type">Type : <span><?php echo $field->type; ?></span></span>									
						</div>									
					</li>
				<?php
				$list .= ob_get_clean();
			endforeach;
			
			$list .= '</ul>';
		}
		// $this->db->show_errors();
		// $this->db->print_error();
		return $list;
	}
	
	public function checkForm(){}
	
	public function save( $data ){
		$id = $data['id'];
		
		/* Update display order for each fields */
		foreach( $data['display_order'] as $order => $fieldID ){		
			$this->db->update( 
				EFM_DB_FIELDS, 
				array('display_order' => $order), 
				array( 'id' => $fieldID ),
				array( '%d' ),
				array( '%d' )
			);
		}	
		unset($data['id'], $data['submit'], $data['display_order']);
		
		/* Update panel informations */
		$this->db->update( 
			EFM_DB_PANELS, 
			$data, 
			array( 'id' => $id )
		);
	}
}