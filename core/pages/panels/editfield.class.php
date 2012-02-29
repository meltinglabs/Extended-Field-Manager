<?php 
/**
 * EditFieldPage
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
 * Edit an existing field
 *
 * @package efm
 * @subpackage controllers
 * @extend PosttypesPage
 */
class EditFieldPage extends PanelsPage {
	public $db = null;
	public $field = null;
	public $success = false;
	
	function __construct(){
		global $wpdb;
		$this->db = &$wpdb;
		
		$this->field = $this->db->get_row( $this->db->prepare(
			"SELECT * FROM ". EFM_DB_FIELDS ." WHERE id = %u"
		,$_GET['id'] ));
		
		if( empty( $this->field ) ){
			wp_safe_redirect( $this->getUrl() );
		}
	}
	
	public function loadAssets(){
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'field', EFM_JS_URL . 'field.js', array(), false, true );
	}
	
	public function getTitle(){
		return 'Edit field : '. $this->get('name');
	}
	
	public function getContent(){
		$selfUrl = array(
			'action' => 'editfield',
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
			if( $this->success ){ $this->showSucessMessage('Field change sucessfully saved'); }
		?>
			<form id="panel-menu-settings" action="<?php echo $this->getUrl( $selfUrl ) ?>" method="post">
				<input name="owner_id" type="hidden" value="<?php echo $this->get('owner_id'); ?>"/>
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
						You cannot edit this field.
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
					<a class="button" href="<?php echo $this->getUrl(); ?>">Cancel</a>
					<a class="button" href="<?php echo $this->getUrl( array( 'action' => 'editpanel', 'id' => $this->get('owner_id') ) ); ?>">Back to Panel</a>
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
									$field = new $className();
									$field->setOptions( $this->get('options') );
									echo $field->getSetupOtions();
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
		$this->field = $this->toArray( $this->field );	
		$this->field = array_merge($this->field, $data);
		unset($this->field['submit']);
	}
	
	public function get($name){
		if(is_array($this->field)){
			$value = !empty($this->field) ? $this->field[$name] : '';
			return $value;
		}
		$value = !empty($this->field) ? $this->field->$name : '';
		return $value;
	}
	
	public function toArray($object){
		if( !is_object( $object ) && !is_array( $object ) ){
            return $object;
        }
        if( is_object( $object ) ){
            $object = get_object_vars( $object );
        }
        return array_map(array($this, __FUNCTION__), $object );
	}
	
	public function fromArray($array){
		$object = new stdClass;
		foreach($array as $key => $value) {
			if(is_array($value)) {
				$object->$key = $this->fromArray($value);
			} else {
				$object->$key = $value;
			}
		}
		return $object;
	}
	
	public function saveField(){
		if( is_object( $this->field ) ){
			$this->field = $this->toArray( $this->field );
		}		
		$this->field['options'] = serialize( $this->field['options'] );
		$this->db->update( EFM_DB_FIELDS, $this->field, array('id' => $this->get('id') ) );
		// $this->db->show_errors();
		// $this->db->print_error();
		$this->success = true;
	}
}