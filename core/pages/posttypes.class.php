<?php 
/**
 * PosttypesPage
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
 * List all Post Types, enven those who are not registered but do contains some groups assigned to them in the db.
 *
 * @package efm
 * @subpackage controllers
 * @extend PageController
 */
class PosttypesPage extends PageController {	
	public $title = 'Post Types List';
	public $icon = 'icon-options-general';
	
	public function getPageKey(){
		return  'posttypes';
	}
	
	public function getContent(){
		ob_start(); 
		?>
			<p>Cette page contient la liste des post types enregistré actuellement, et les champs qui leurs sont attribués.</p>
			<table class="wp-list-table widefat fixed">
				<thead>
					<?php $this->getTheadTfoot(); ?>
				</thead> <!-- End /thead -->
				<tfoot>
					<?php $this->getTheadTfoot(); ?>
				</tfoot> <!-- End /tfoot -->
				<tbody>
					<?php
						/* List Post, Page and all Custom Post Types */
						$this->getPostTypesList( array('name' => 'post'), 'objects');
						$this->getPostTypesList( array('name' => 'page'), 'objects');
						$this->getPostTypesList( array('_builtin' => false), 'objects');
					?>
				</tbody> <!-- End /tbody -->
			</table>
		<?php 
		return ob_get_clean();
	}
	
	public function getTheadTfoot(){
		/* @TODO : Custom css classes plz */
		?>
			<tr>
				<th style="" class="manage-column column-title" id="title" scope="col">
					<span>Label</span>
				</th>
				<th style="" class="manage-column column-role" id="slug" scope="col">
					<span>Type</span>
				</th>
			</tr>
		<?php
	}
	
	public function getPostTypesList( $args, $output ){
		$postTypes = get_post_types( $args, $output );
		foreach($postTypes as $postType){
			?>
				<tr>
					<td>
						<strong><?php echo $postType->label; ?></strong>
						<div class="row-actions">
							<span class="edit">
								<a title="Edit Panels" href="<?php echo $this->getUrl( array( 'action' => 'managepanels', 'slug' => $postType->name ) ) ?>">Edit Panels</a>
							</span>
						</div>
					</td>
					<td class="column-role"><?php echo $postType->name ?></td>
				</tr>
			<?php
		}
	}
}