<?php 
/**
 * Panels
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
 * @extend EFMPage
 */
class PanelsManager extends EFMPage {	
		
	function __construct(){
		parent::__construct();
	}
	
	public function getTitle(){
		return 'Panels Manager <a class="add-new-h2" href="'. $this->getUrl( array( 'action' => 'createpanel' ) ) .'">Add New</a>';
	}
	
	public function getPageKey(){
		return  'panels';
	}
	
	public function getContent(){
		$panels = $this->getPanelList();
		
		ob_start();
		if( !empty( $panels ) ){			
			?>
				<p>Create your panels and fields from this page - How aweful !</p>
				<table class="wp-list-table widefat fixed">
					<thead>
						<?php $this->getTheadTfoot(); ?>
					</thead> <!-- End /thead -->
					<tfoot>
						<?php $this->getTheadTfoot(); ?>
					</tfoot> <!-- End /tfoot -->
					<tbody>
						<?php foreach($panels as $panel): ?>
							<tr>
								<td>
									<strong><?php echo $panel->title; ?></strong>
									<div class="row-actions">
										<span class="edit">
											<a title="Edit Panels" href="<?php echo $this->getUrl( array( 'action' => 'editpanel', 'id' => $panel->id ) ) ?>">Edit Panel</a>
										</span>
									</div>
								</td>
								<td class="column-role">
									<?php echo $panel->total_fields ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody> <!-- End /tbody -->
				</table>
			<?php			
		} else {
			?>
				<div class="updated">
					<p>They're no panels created yet. <br/>Create your first panel by using the buttons Add New next to the title.</p>
				</div>				
			<?php
		}
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
					<span>Fields</span>
				</th>
			</tr>
		<?php
	}	
	
	
	public function getPanelList(){
		$panels = $this->db->get_results( $this->db->prepare(
			"SELECT 
			p.id,
			p.title,
			p.name,
			COUNT(f.id) AS total_fields 
			FROM ". EFM_DB_PANELS ." p 
			LEFT JOIN ". EFM_DB_FIELDS ."  f
			ON p.id = f.owner_id
			GROUP BY p.id"
		));
		return $panels;
	}
}