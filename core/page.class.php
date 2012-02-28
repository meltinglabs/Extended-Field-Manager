<?php 
/**
 * PageController
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
 * Page Controller
 * 
 * The plugin panel base abstract class that main controllers extend to add uis to wordpress wp-admin
 *
 * @package efm
 * @subpackage controllers
 */
abstract class PageController {
	/**
     * @access public
     * @var title - The panel title.
     */
    public $title = 'My Sites Variable - Panel Default Title';
	
	/**
     * @access public
     * @var icon - The panel icon to show on the left of the panel title
     */
	public $icon = 'icon-options-general';

	/**
     * The Plugin Panel Constructor.
     *
     * This method is used to create a new PluginPanel object.
     *
     * @return PluginPanel A unique PluginManager instance.
     */
    function __construct() {}
		
	public function getUrl( $args = array() ){
		$params = array_merge(array(
			'page' => EFM_PREFIX . $this->getPageKey(),
		), $args);
		$queryString = http_build_query($params);
		return admin_url( 'admin.php?' . $queryString );
	}	
	
	public function getTitle(){
		return $this->title;
	}
	public function getIcon(){
		return $this->icon;
	}
	
	abstract public function getPageKey();	
	abstract public function getContent();	
}