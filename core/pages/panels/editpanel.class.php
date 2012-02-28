<?php 
/**
 * EditpanelPage
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
class EditpanelPage extends PanelsPage {
	public $db = null;
	public $panel = null;
	public $errors = array();
	
		
	function __construct(){
		global $wpdb;
		$this->db = &$wpdb;
		
		$this->panel = $this->db->get_row( $this->db->prepare(
			"SELECT * FROM ". EFM_DB_PANELS ." WHERE id = %u"
		, $_GET['id'] ));
		
		if( empty( $this->panel ) ){
			wp_safe_redirect( $this->getUrl() );
		}
		// $wpdb->show_errors();
		// $wpdb->print_error();
		// echo '<pre>'. print_r($this->panel, true) .'</pre>';
	}
	
	public function getTitle(){		
		return 'Editing Panel : '. $this->panel->label;
	}
	
	public function getContent(){
			
	
			ob_start();
			?>
				<?php if( isset( $_POST['submit'] ) ): ?>
						<pre><?php// echo print_r($_POST, true); ?></pre>
						<?php
							$this->save($_POST);						
						?>
				<?php endif; ?>
				
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
				
				<form id="panel-menu-settings" action="<?php $this->getUrl( array( 'action' => 'editpanel' ) ) ?>" method="post">
					<input name="id" type="hidden" value="<?php echo $this->panel->id; ?>"/>
					<div id="left-side-form-column">
													
						<div class="form_block">
							<label for="name">
								Name
								<span class="required">*</span>
							</label>
							<input id="name" name="name" type="text" value="<?php echo $this->panel->name; ?>"/>
							<span class="description">
								Le nom du panel qui sera utilisé pour trier vos champs.<br/>
								Les espaces et majuscules ne sont pas acceptés.
							</span>
						</div>
					
						<div class="form_block">
							<label for="label">
								Label
								<span class="required">*</span>
							</label>
							<input id="label" name="label" type="text" value="<?php echo $this->panel->label; ?>"/>
							<span class="description">Le texte qui sera placé dans la barre de titre</span>
						</div>
										
						<p class="submit">
							<a class="button" href="<?php $this->getUrl() ?>">Cancel</a>
							<a class="button" href="<?php $this->getUrl() ?>">Duplicate</a>
							<input type="submit" value="Save Changes" class="button-primary" id="submit" name="submit">
						</p>
												
					</div>
					<!-- End #left-side-form-column -->
					
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
										<a class="button" href="<?php $this->getUrl() ?>">Add Field</a>
									</p>
									
									<ul class="sortable">									
										<li>
											<div class="menu-item-handle">
												<div class="item-desc">
													<span class="drag-up-and-down">Field</span>													
												</div>
												<span class="item-controls">
													<span class="item-type">text</span>
													<a href="#" class="item-edit">Text</a>
												</span>
												<input name="fields_order[]" type="hidden" value="1"/>
											</div>
											
											<div class="menu-item-settings">												
												<div class="misc-pub-section">
													<p><strong>Description :</strong><em>A simple description of the field</em></p>
												</div>
												<div class="misc-pub-section">
													<p><strong>Name :</strong><em>field</em></p>
												</div>
												<div class="misc-pub-section">
													<a href="#" id="remove-19" class="delete">Remove</a>
													<span class="meta-sep"> | </span>
													<a href="#" id="cancel-19" class="item-cancel">Edit</a>
												</div>
											</div>											
										</li>
										
										<li>
											<div class="menu-item-handle">
												<div class="item-desc">
													<span class="drag-up-and-down">Field</span>																
												</div>
												<span class="item-controls">
													<a href="#" class="item-edit">Text</a>
												</span>
												<input name="fields_order[]" type="hidden" value="2"/>
											</div>
											
											<div class="menu-item-settings">												
												<div class="misc-pub-section">
													<p><strong>Description :</strong><em>A simple description of the field</em></p>
												</div>
												<div class="misc-pub-section">
													<p><strong>Name :</strong><em>field</em></p>
												</div>
												<div class="misc-pub-section">
													<a href="#" id="remove-19" class="delete">Remove</a>
													<span class="meta-sep"> | </span>
													<a href="#" id="cancel-19" class="item-cancel">Edit</a>
												</div>
											</div>
										</li>	
										
										<li>
											<div class="menu-item-handle">
												<div class="item-desc">
													<span class="drag-up-and-down">Field</span>																
												</div>
												<span class="item-controls">
													<a href="#" class="item-edit">Text</a>
												</span>
												<input name="fields_order[]" type="hidden" value="4"/>
											</div>
											
											<div class="menu-item-settings">												
												<div class="misc-pub-section">
													<p><strong>Description :</strong><em>A simple description of the field</em></p>
												</div>
												<div class="misc-pub-section">
													<p><strong>Name :</strong><em>field</em></p>
												</div>
												<div class="misc-pub-section">
													<a href="#" id="remove-19" class="delete">Remove</a>
													<span class="meta-sep"> | </span>
													<a href="#" id="cancel-19" class="item-cancel">Edit</a>
												</div>
											</div>
										</li>
										
										<li>
											<div class="menu-item-handle">
												<div class="item-desc">
													<span class="drag-up-and-down">Field</span>																
												</div>
												<span class="item-controls">
													<a href="#" class="item-edit">Text</a>
												</span>
												<input name="fields_order[]" type="hidden" value="3"/>
											</div>
											
											<div class="menu-item-settings">												
												<div class="misc-pub-section">
													<p><strong>Description :</strong><em>A simple description of the field</em></p>
												</div>
												<div class="misc-pub-section">
													<p><strong>Name :</strong><em>field</em></p>
												</div>
												<div class="misc-pub-section">
													<a href="#" id="remove-19" class="delete">Remove</a>
													<span class="meta-sep"> | </span>
													<a href="#" id="cancel-19" class="item-cancel">Edit</a>
												</div>
											</div>
										</li>										
									</ul>
								</div>
								
							</div>
						</div>
					</div>
					<!-- End #right-side-liquid -->
					
				</form>
				<!-- End #panel-menu-settings -->				
			<?php
			return ob_get_clean();
	}
	
	public function checkForm( $data ){		
		$query = sprintf("SELECT * FROM %s WHERE name = '%s'", EFM_DB_PANELS, $data['name']);
		$exist = $this->db->get_results($query, ARRAY_A);
		if( !empty( $exist ) ){ $this->addError('This name is already taken, please choose another one'); }
	}

	public function createPanel( $data ){  
		$sql = sprintf(
			"INSERT INTO %s ".
			"(name,label) ".
			"VALUES ('%s','%s')",
			EFM_DB_PANELS,
			$data['name'],
			$data['label']
		);
		$this->db->query($sql);
    
		$postTypeId = $this->db->insert_id;
		return $postTypeId;
	}
	
	public function addError( $text ){
		$this->errors[] = $text;
	}
	
	public function save( $data ){
		$id = $data['id'];
		unset($data['id'], $data['submit']);
		$data['fields_order'] = serialize($data['fields_order']);
		$this->db->update( 
			EFM_DB_PANELS, 
			$data, 
			array( 'id' => $id )
		);
	}
}