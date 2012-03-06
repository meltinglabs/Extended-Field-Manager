<?php 
/**
 * TextField
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
 * TextField
 * 
 * A simple text input
 *
 * @package efm
 * @subpackage fields
 * @extends EFMField
 */
class TextField extends EFMField {
	public $options = array();
	
	public function getSetupOtions(){
		ob_start();
		?>
			<p>A simple text field input</p>
			<div class="field_display_preview centered">
				<img src="<?php echo EFM_URL . 'core/fields/text/preview.jpg' ?>" alt="Text field diplay preview"/>
			</div>
			
			<div class="form_block">
				<label for="required">
					<input type="checkbox" name="options[required]" <?php $required = $this->getOption('required'); !empty( $required ) AND print( ' checked="checked"' ); ?>/>
					Required						
				</label>
				<span class="description">Check this if you want this field to must be filled</span>					
			</div>
				
			<div class="form_block">
				<label for="duplicable">
					<input type="checkbox" name="options[duplicable]" <?php $duplicable = $this->getOption('duplicable'); !empty( $duplicable ) AND print( ' checked="checked"' ); ?>/>
					Duplicable						
				</label>
				<span class="description">Check this if you want this field to be duplicable</span>					
			</div>
		<?php
		return ob_get_clean();
	}	
	public function displayField(){}
	
	public static function getInfo( $key = null ){
		$infos = array(
			'name' => 'Text',
			'type' => 'text',
			'description' => 'A simple textbox',
		);
		if( $key !== null ){
			return $infos[$key];
		}			
		return $infos;
	}
	
	public static function render( &$field, &$values ){
		$options = unserialize( $field->options );
		$duplicable =  isset( $options['duplicable'] ) && $options['duplicable'] == 'on' ? true : false;
		$value = array_key_exists( $field->name, $values ) ? $values[$field->name] : '';
		$cls = $duplicable ? ' duplicable' : '';
		ob_start();
		?>
			<div class="form_block<?php echo $cls; ?>">				
				<label for="<?php echo $id; ?>">
					<?php echo $field->label; ?>					
				</label>
				
				<?php 
					/* The field is duplicable */
					if( $duplicable ){
						$value = $value == '' ? array() : unserialize( $value );
						$i = 0;	$total = count($value);
						echo $total == 1 ? '<ul class="efm_box single">' : '<ul class="efm_box">';						
						/* Empty value, init field */
						if( empty( $value ) ) {
							echo self::renderMetaField( $field, '', true );
						/* Or render each added fields with its value */
						} else {									
							foreach($value as $v){
								echo self::renderMetaField( $field, $v, true, $i);								
								$i++;
							}
						}
						echo '</ul>';
					/* Field is not duplicable */
					} else {
						echo self::renderMetaField( $field, $value );
					}
					
				if( !empty( $field->description ) ): ?>
					<span class="description"><?php echo $field->description; ?></span>
				<?php endif; ?>
				
			</div>					
		<?php
		return ob_get_clean();
	}
	
	public static function renderMetaField($field, $value, $duplicable = false, $index = 0){
		$name = $duplicable ? $field->field_id .'[]': $field->field_id ;
		$id = $index > 0 ? $field->field_id . '-' . $index: $field->field_id;	
		$input = '<input type="text" name="'. $name .'" id="'. $id .'" value="'. $value .'"/>';		
		if( $duplicable ){ $input = '<li>'. $input .'<a class="button remove" href=""><span>Remove</span></a><a class="button add" href=""><span>Add another</span></a></li>'; }
		return $input;
	}
}