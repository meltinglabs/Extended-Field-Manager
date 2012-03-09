<?php 
/**
 * ImagesField
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
 * ImagesField
 * 
 * A simple checkbox field
 *
 * @package efm
 * @subpackage fields
 * @extends EFMField
 */
class ImagesField extends EFMField {
	public $options = array();
	public function getSetupOtions(){
		$resize = $this->getOption('resize');
		$width = !empty( $resize ) ? $resize['width'] : '';
		$height = !empty( $resize ) ? $resize['height'] : '';
		$quality = !empty( $resize ) ? $resize['quality'] : '';
		
		ob_start();
		?>
			<p><?php echo self::getInfo('description') ?></p>
			<div class="form_block">
				<label for="multiple">
					<input type="checkbox" name="options[multi_selection]" <?php $multiple = $this->getOption('multi_selection'); !empty( $multiple ) AND print( ' checked="checked"' ); ?>/>
					Multiple						
				</label>
				<span class="description">Do you want to upload several images at once</span>					
			</div>
			
			<div class="form_block">
				<label for="width">					                                                                                                     
					Image Width						
				</label>
				<input type="text" name="options[resize][width]" value="<?php echo $width ?>"/>
				<span class="description">Set a fixed width for the image(s)</span>					
			</div>
			
			<div class="form_block">
				<label for="height">					                                                                                                     
					Image Height						
				</label>
				<input type="text" name="options[resize][height]" value="<?php echo $height ?>"/>
				<span class="description">Set a fixed height for the image(s)</span>					
			</div>
			
			<div class="form_block">
				<label for="quality">					                                                                                                     
					Image quality						
				</label>
				<input type="text" name="options[resize][quality]" value="<?php echo $quality ?>"/>
				<span class="description">Set a quality for the resized image(s)</span>					
			</div>
		<?php
		return ob_get_clean();
	}	
	
	public static function getInfo( $key = null ){
		$infos = array(
			'name' => 'Images',
			'type' => 'images',
			'description' => 'A multiple image uploader',
		);
		if( $key !== null ){
			return $infos[$key];
		}			
		return $infos;
	}
	
	
	public static function render( &$field, &$values, &$post ){		
		$options = unserialize( $field->options );
		$meta = array_key_exists( $field->name, $values ) ? $values[$field->name] : array();
		$fieldName = isset( $options['multi_selection'] ) && $options['multi_selection'] ? $field->field_id .'[]' : $field->field_id;
		
		$config = array_merge( array(
			'browse_button' => $field->field_id .'_browse_btn',
			'container' => $field->field_id .'_container',
			'drop_element' => $field->field_id .'_drop_element',
			'file_data_name' => $field->field_id .'_efm_fdn',
			'multipart_params' => array(
				'field_type' => 'images',
				'action' => 'efmrequest',
				'task' => 'upload',
				'image_id' => $field->field_id,
				'_ajax_nonce' => wp_create_nonce( $field->field_id .'_efm_upload' ),
			),
		), $options );		
		
		ob_start();
		?>
			<div class="form_block efm_upload  id="<?php echo $config['drop_element'] ?>">
				<label for="<?php echo $fieldName; ?>">	
					<?php echo $field->label; ?>						
				</label>
				<input type="hidden" class="efm_upload_config" value="<?php echo htmlspecialchars( json_encode( $config ) ); ?>"/>
				
				<ul class="efm_image" id="<?php echo $config['container'] ?>">
				<?php 
					if( !empty( $meta ) ):
					
						if( is_array( $meta ) ):
						foreach( $meta as $key => $value ):							
					?>
							<li>
								<input type="hidden" class="field" name="<?php echo $fieldName; ?>" value="<?php echo $value; ?>"/>
								<input type="hidden" class="meta" value="<?php echo $field->field_id; ?>"/>
								<input type="hidden" class="post" value="<?php echo $post->ID; ?>"/>
								<div class="img_pw">
									<img src="<?php echo $value; ?>" alt="<?php echo $field->field_id; ?>" />
									<a href="" class="remove">Remove</a>
								</div>							
							</li>	
				<?php endforeach;  else: ?>
					<li>
						<input type="hidden" class="field" name="<?php echo $fieldName; ?>" value="<?php echo $meta; ?>"/>
						<input type="hidden" class="meta" value="<?php echo $field->field_id; ?>"/>
						<input type="hidden" class="post" value="<?php echo $post->ID; ?>"/>
						<div class="img_pw">
							<img src="<?php echo $meta; ?>" alt="<?php echo $field->field_id; ?>" />
							<a href="" class="remove">Remove</a>
						</div>							
					</li>
				<?php endif; endif; ?>
				</ul>		
				<div class="clear"></div>
				<div class="efm_upload_btn">
				<?php 
					if( isset( $config['multi_selection'] ) && $config['multi_selection'] ): 
						$buttonText = $value !== "" ? 'Add another' : 'Browse';
				?>
					<a href="" class="button add" id="<?php echo $config['browse_button'] ?>"><span><?php echo $buttonText ?></span></a>
				<?php 
					else: 
						$buttonText = $value !== "" ? 'Replace this image' : 'Browse';
				?>
					<a href="" class="button add" id="<?php echo $config['browse_button'] ?>"><span><?php echo $buttonText ?></span></a>
				<?php endif ?>
				</div>
				
				<?php if( !empty( $field->description ) ): ?>
					<span class="description"><?php echo $field->description; ?></span>
				<?php endif; ?>
			</div>					
		<?php
		return ob_get_clean();
	}
	
	public function removeFile( $data ){
		/* Remove files that were removed from upload */
		$uploads = wp_upload_dir();
		$toRemove = str_replace( $uploads['baseurl'], $uploads['basedir'], $data['to_remove'] );
		if( @unlink( $toRemove ) ){
			/* @TODO - Remove the meta_box as well, i should pass more informations one way or another */
			if( $data['post'] && $data['meta'] ){
				$metaValues = get_post_meta( $data['post'], $data['meta'] );				
				if( !empty( $metaValues ) && is_array( $metaValues ) ){
					$key = array_search( $data['to_remove'], $metaValues[0] );
					if( $key !== false ){
						unset( $metaValues[0][$key] );
						$total = count( $metaValues[0] );
						if( $total > 0 ){
							$metaValues = array_values( $metaValues[0] );
							update_post_meta( $data['post'], $data['meta'], $metaValues);
						} else {
							delete_post_meta( $data['post'], $data['meta'] );
						}
					}					
				}
				if( !empty( $metaValues ) && !is_array( $metaValues ) && $metaValues == $data['to_remove'] ){
					delete_post_meta( $data['post'], $data['meta'] );
				}
			}
			return true;	
		}
		return false;
	}
	
	public function uploadFile( $data ){
		// check ajax noonce
		$image_id = $data["image_id"];
		
		check_ajax_referer( $image_id . '_efm_upload' );
	
		// handle file upload
		$status = wp_handle_upload( $_FILES[$image_id . '_efm_fdn'], array('test_form' => false, 'action' => 'plupload_action') );
		
		// send the uploaded file url in response
		if( !array_key_exists( 'error', $status )  ){
			$status['fieldname'] = $image_id;
		}
		return json_encode( $status );
	}
}