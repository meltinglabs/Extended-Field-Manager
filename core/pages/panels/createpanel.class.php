<?php 
/**
 * Createpanel
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
class Createpanel extends PanelsManager {
	public $db = null;
	public $errors = array();
	
	public function getTitle(){
		return 'Create a new Panel';
	}
	
	public function getContent(){
		if( isset( $_POST['submit'] ) ){
			$data = $this->checkForm( $_POST );
			if( empty( $this->errors ) ){
				unset( $_POST['submit'] );
				$id = $this->savePanel( $_POST );
			}
		}
		
		if( empty( $this->errors ) && isset( $id ) ){
			wp_safe_redirect( $this->getUrl( array( 
				'action' => 'editpanel',
				'id' => $id,
			)));
		} else {
			ob_start();
			?>
				<?php if( !empty( $this->errors ) ): ?>
					<div class="error below-h2" id="notice">
						<p>Please fix the following errors:</p>
						<ul class="ul-disc">
						<?php foreach( $this->errors as $error ): ?>
							<li><?php echo $error; ?></li>
						<?php endforeach; ?>
						</ul>						
					</div>
				<?php endif; ?>
				<form action="<?php $this->getUrl( array( 'action' => 'createpanel' ) ) ?>" method="post">
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row">
									<label for="title">Title</label>
								</th>
								<td>
									<input id="title" name="title" class="regular-text" type="text" />
									<span class="description">Le texte qui sera placé dans la barre de titre</span>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<label for="name">Name</label>
								</th>
								<td>
									<input id="name" name="name" class="regular-text" type="text" />
									<span class="description">Pas d'espace, pas d'accent, rien stp</span>
								</td>
							</tr>
						</tbody>
					</table>
					<p class="submit">
						<input type="submit" value="Save Changes" class="button-primary" id="submit" name="submit">
					</p>
				</form>
			<?php
			return ob_get_clean();
		}
	}
	
	public function checkForm( $data ){		
		$query = sprintf("SELECT * FROM %s WHERE name = '%s'", EFM_DB_PANELS, $data['name']);
		$exist = $this->db->get_results($query, ARRAY_A);
		if( !empty( $exist ) ){ $this->addError('This name is already taken, please choose another one'); }
	}

	public function savePanel( $data ){  
		$this->db->insert(EFM_DB_PANELS, $data);
		$postTypeId = $this->db->insert_id;
		return $postTypeId;
	}
	
	public function addError($text){
		$this->errors[] = $text;
	}
}