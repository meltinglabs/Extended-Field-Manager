<?php 
/**
 * CreateFieldPage
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
class CreateFieldPage extends PanelsPage {
	public $db = null;
	
	function __construct(){
		global $wpdb;
		$this->db = &$wpdb;
		
		$this->panel = $this->db->get_row( $this->db->prepare(
			"SELECT * FROM ". EFM_DB_PANELS ." WHERE id = %u"
		,$_GET['id'] ));
		
		if( empty( $this->panel ) ){
			wp_safe_redirect( $this->getUrl() );
		}
	}
	
	public function loadAssets(){
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'field', EFM_JS_URL . 'field.js', array(), false, true );
	}
	
	public function getTitle(){
		return 'Add a new Field';
	}
	
	public function getContent(){
		$this->owner = $_GET['owner'];
		$selfUrl = array(
			'action' => 'createfield',
			'owner' => $this->owner,
			'id' => $_GET['id'],
		);
		
		if(isset($_POST['submit'])){
			$this->setFields($_POST);
			$this->checkForm();
			if( empty( $this->errors ) ){
				$this->saveField();
			}
		}
		
		ob_start();
			if( !empty( $this->errors ) ) { $this->showErrors(); }
		?>
			<form id="panel-menu-settings" action="<?php echo $this->getUrl( $selfUrl ) ?>" method="post">
				<input name="owner_id" type="hidden" value="<?php echo $this->panel->id; ?>"/>
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
					<input id="name" name="name" type="text" value="<?php echo $this->get('name'); ?>"/>
					<span class="description">
						This will be used along with php to retreive the formatted field value, therefore spaces and uppercased chars are not allowed.<br/>
						Once the field created, this value cannot be changed.
					</span>
				</div>
				
				<div class="form_block">
					<label for="label">
						Label
						<span class="required">*</span>
					</label>
					<input id="label" name="label" type="text" value="<?php echo $this->get('label'); ?>"/>
					<span class="description">Text to show above your field</span>
				</div>
				
				<div class="form_block">
					<label for="description">
						Description
					</label>
					<textarea id="description" name="description"><?php echo $this->get('description'); ?></textarea>
					<span class="description">Text to show under your field</span>
				</div>
				
				<div class="form_block">
					<label for="required">
						<input type="checkbox" name="required" />
						Required						
					</label>
					<span class="description">Check this if you want this field to must be filled</span>					
				</div>
				
				<div class="form_block">
					<label for="duplicable">
						<input type="checkbox" name="duplicable" />
						Can be duplicated						
					</label>
					<span class="description">Check this if you want this field to be duplicable</span>					
				</div>
				
				<p class="submit">
					<a class="button" href="<?php echo $this->getUrl() ?>">Cancel</a>
					<input type="submit" value="Save Field" class="button-primary" id="submit" name="submit">
				</p>				
			</div>			
		<?php
	}	
	
	public function getRightSide(){
		?>
			<div id="right-side-liquid" class="metabox-holder nav-menus-php">
				<div class="postbox">
					<h3 class="hndle"><span>Field Type Setting</span></h3>
					<div class="inside">
					
						<div id="select_type" class="misc-pub-section form_block">
							<label for="type">
								Field Type
							</label>
							<select name="type" id="type">
								<?php echo $this->getFieldTypesList() ?>
							</select>
						</div>
						
						<div id="select_type" class="misc-pub-section main">
							<?php 
								$typeName = $this->get('type');
								if($typeName == '' || $typeName == 'none'): 
							?>
								<p class="centered"><em>Select the type of field you want to create<br/> 
								Once selected, the appopriate options will replace this text.</em></p>
							<?php else:
								$class = $this->controller->loadFieldController( $typeName );
								if(!$class){			
									echo 'Could not load Field Class :'. $typeName .'Field';
								} else {
									$className = ucfirst($typeName) .'Field';
									$this->field = new $className();
									$this->field->setOptions( $this->get('options') );
									echo $this->field->getSetupOtions();
								}
							?>
							<?php endif; ?>
						</div>
					
					</div>
				</div>
			</div>
		<?php
	}
	
	public function getFieldTypesList(){
		$type = $this->get('type');
		$selected = ($type == 'text') ? ' selected="selected"' : '';
		$fieldTypes = '<option value="none">-</option>';
		$fieldTypes .= '<option value="text"'. $selected .'>Text</option>';
		$fieldTypes .= '<option value="test">Testing</option>';
		return $fieldTypes;
	}
	
	public function checkForm(){		
		$name = $this->get('name');
		if(empty($name)){
			$this->addError( array(
				'message' => "The name field cannot be empty",
			));
		}

		if($this->alreadyExist($name, 'name')){
			$this->addError( array(
				'message' => "You cannot have two field sharing the same name in the same panel",
			));
		}
		
		$label = $this->get('label');
		if(empty($label)){
			$this->addError( array(
				'message' => "The label field cannot be empty",
			));
		}			
		
		if($this->alreadyExist($label, 'label')){
			$this->addError( array(
				'message' => "You cannot have two field sharing the same label in the same panel",
			));
		}
		
		$type = $this->get('type');
		if($type == "none"){
			$this->addError( array(
				'message' => "The type field cannot be empty",
			));
		}		
	}
	
	public function alreadyExist($name, $field){
		$exist = $this->db->get_var( $this->db->prepare( 
			"SELECT ". $field ." 
			FROM ". EFM_DB_FIELDS ." f 
			WHERE f.owner ='". $this->owner ."' 
			AND f.". $field ."  = '". $name ."' 
			AND f.owner_id=". $this->panel->id
		));
		if(!empty($exist)){
			return true;
		}
		return false;
	}
	
	public function setFields($data){
		unset($data['submit']);
		$this->fields = $data;
	}
	
	public function get($name){
		$value = !empty($this->fields) ? $this->fields[$name] : '';
		return $value;
	}
	
	public function saveField(){
		$lastFieldDisplayOrder = $this->db->get_var( $this->db->prepare( "SELECT MAX(f.display_order) FROM ". EFM_DB_FIELDS ." f WHERE f.owner='". $this->owner ."' AND f.owner_id=". $this->panel->id ) );
		$this->fields['display_order'] = !empty($lastFieldDisplayOrder) ? $lastFieldDisplayOrder + 1 : 0;
		$this->fields['options'] = serialize($this->fields['options']);
		$this->db->insert(EFM_DB_FIELDS, $this->fields);
	}
}