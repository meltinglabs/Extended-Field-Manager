<?php 
/**
 * CheckboxField
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
 * CheckboxField
 * 
 * A simple checkbox field
 *
 * @package efm
 * @subpackage fields
 * @extends EFMField
 */
class CheckboxField extends EFMField {
	public $options = array();
	public function getSetupOtions(){
		ob_start();
		?>
			<p><?php echo self::getInfo('description') ?></p>
		<?php
		return ob_get_clean();
	}	
	public function displayField(){}
	
	public static function getInfo( $key = null ){
		$infos = array(
			'name' => 'Checkbox',
			'type' => 'checkbox',
			'description' => 'A Checkbox field',
		);
		if( $key !== null ){
			return $infos[$key];
		}			
		return $infos;
	}
	
	
	public static function render( &$field, &$values ){		
		$options = unserialize( $field->options );
		$value = array_key_exists( $field->name, $values ) && $values[$field->name] == "on" ? ' checked="checked"' : '';
		
		ob_start();
		?>
			<div class="form_block">
				<label for="<?php echo $field->field_id; ?>">
					<input type="checkbox" name="<?php echo $field->field_id; ?>" id="<?php echo $field->field_id; ?>" <?php echo $value; ?>/>	
					<?php echo $field->label; ?>						
				</label>
				
				<?php if( !empty( $field->description ) ): ?>
					<span class="description"><?php echo $field->description; ?></span>
				<?php endif; ?>
			</div>					
		<?php
		return ob_get_clean();
	}
}