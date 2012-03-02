<?php 
/**
 * TestField
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
 * TestField
 * 
 * A debugging purpose field, mimic a text field
 *
 * @package efm
 * @subpackage fields
 * @extends EFMField
 */
class TestField extends EFMField {
	public $options = array();
	public function getSetupOtions(){
		ob_start();
		?>
			<p>A field existing only for testing purpose</p>
			<div class="form_block">
				<label for="field_default_value">
					Default Value					
				</label>
				<input type="text" name="options[default_value]" id="field_default_value" value="<?php echo $this->getOption('default_value') ?>"/>	
				<span class="description">A basic testing option</span>					
			</div>
		<?php
		return ob_get_clean();
	}	
	public function displayField(){}
	
	public static function getInfo( $key = null ){
		$infos = array(
			'name' => 'Test',
			'type' => 'test',
			'description' => 'A simple test field',
		);
		if( $key !== null ){
			return $infos[$key];
		}			
		return $infos;
	}
	
	
	public static function render( &$field, &$values ){		
		$options = unserialize( $field->options );
		$value = array_key_exists( $field->name, $values ) ? $values[$field->name] : $options['default_value'];
		
		ob_start();
		?>
			<div class="form_block">
				<label for="<?php echo $field->field_id; ?>">
					<?php echo $field->label; ?>					
				</label>
				<input type="text" name="<?php echo $field->field_id; ?>" id="<?php echo $field->field_id; ?>" value="<?php echo $value; ?>"/>	
				<?php if( !empty( $field->description ) ): ?>
					<span class="description"><?php echo $field->description; ?></span>
				<?php endif; ?>
			</div>					
		<?php
		return ob_get_clean();
	}
}