<?php 
/**
 * Field
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
 * Field
 * 
 * All custom fields have to extend this class
 *
 * @package efm
 * @subpackage controllers
 */
class TextField extends Field {
	public $options = array();
	public function getSetupOtions(){
		ob_start();
		?>
			<p>A simple text field input</p>
			<div class="field_display_preview centered">
				<img src="<?php echo EFM_URL . 'fields/text/preview.jpg' ?>" alt="Text field diplay preview"/>
			</div>
			<div class="form_block">
				<label for="field_default_value">
					Default Value					
				</label>
				<input type="text" name="options[default_value]" id="field_default_value" value="<?php echo $this->getOption('default_value') ?>"/>	
				<span class="description">Default value to instanciate the field with</span>					
			</div>
		<?php
		return ob_get_clean();
	}	
	public function displayField(){}
}