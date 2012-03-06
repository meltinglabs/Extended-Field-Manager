<?php 
/**
 * ListboxField
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
 * ListboxField
 * 
 * A simple text input
 *
 * @package efm
 * @subpackage fields
 * @extends EFMField
 */
class ListboxField extends EFMField {
	public $options = array();
	
	public function getSetupOtions(){
		ob_start();
		?>
			<p><?php echo self::getInfo('description') ?></p>
			<div class="field_display_preview centered">
				<img src="<?php echo EFM_URL . 'core/fields/text/preview.jpg' ?>" alt="Text field diplay preview"/>
			</div>
			<div class="form_block duplicable">
				<label for="list">
					List
				</label>
				<?php
					$list = $this->getOption('list');
					$total = count($list['keys']);
					echo $total == 1 ? '<ul class="efm_box single">' : '<ul class="efm_box">';
					
					if( !empty( $list ) ){
						for( $i = 0; $i < $total; $i++ ){							
							$result = '<li>';
							$result .= '<input type="text" name="options[list][keys][]" value="'. $list['keys'][$i] .'">';
							$result .= '<input type="text" name="options[list][values][]" value="'. $list['values'][$i] .'">';
							$result .= '<a class="button remove" href=""><span>Remove</span></a>';
							$result .= '<a class="button add" href=""><span>Add another</span></a>';
							$result .= '</li>';
							echo $result;
						}
					} else {
						$result = '<li>';
						$result .= '<input type="text" name="options[list][keys][]">';
						$result .= '<input type="text" name="options[list][values][]">';
						$result .= '<a class="button remove" href=""><span>Remove</span></a>';
						$result .= '<a class="button add" href=""><span>Add another</span></a>';
						$result .= '</li>';
						echo $result;
					}
				?>
				</ul>
				
			</div>
		<?php
		return ob_get_clean();
	}	
	public function displayField(){}
	
	public static function getInfo( $key = null ){
		$infos = array(
			'name' => 'Listbox',
			'type' => 'listbox',
			'description' => 'A select box with static values',
		);
		if( $key !== null ){
			return $infos[$key];
		}			
		return $infos;
	}
	
	public static function render( &$field, &$values ){
		$options = unserialize( $field->options );
		$value = array_key_exists( $field->name, $values ) ? $values[$field->name] : '';
		
		ob_start();
		?>
			<div class="form_block">
				<label for="<?php echo $field->field_id; ?>">
					<?php echo $field->label; ?>					
				</label>
				<select name="<?php echo $field->field_id; ?>">
					<?php
						$list = '';
						$items = $options['list'];
						$total = count( $items['keys'] );
						if( !empty( $items ) ){
							for( $i = 0; $i < $total; $i++ ){
								$list .= $items['keys'][$i] == $value ?
								'<option value="'. $items['keys'][$i] .'" selected="selected">'. $items['values'][$i] .'</option>' :
								'<option value="'. $items['keys'][$i] .'">'. $items['values'][$i] .'</option>';								
							}
						} else {
							$list .= '<option value="">No options available</option>';	
						}
						echo $list;
					?>
				</select>
				<?php if( !empty( $field->description ) ): ?>
					<span class="description"><?php echo $field->description; ?></span>
				<?php endif; ?>
			</div>					
		<?php
		return ob_get_clean();
	}
}