<?php 
/**
 * EFMField
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
 * EFMField
 * 
 * All custom fields have to extend this class
 *
 * @package efm
 * @subpackage core
 */
abstract class EFMField {
	
	/**
     * The Plugin Panel Constructor.
     *
     * This method is used to create a new PluginPanel object.
     *
     * @return PluginPanel A unique PluginManager instance.
     */
    function __construct() {}
	
	/**
     * setOptions
     *
     * Set the field custom options
     *
     * @param mixed string|array $data
     * @return void
     */
	public function setOptions( $options ){
		$data = @unserialize( $options );
		if( $options === 'b:0;' || $data !== false){
			$this->options = $data;
		} else {
			$this->options = $options;
		}	
	}
	
	/**
     * getOption
     *
     * Get a custom option by name for the field
     *
     * @param string $name The option name
     * @return string $value The requested option value
     */
	public function getOption( $name ){
		$value = !empty( $this->options ) ? $this->options[$name] : '';
		return $value;
	}
	
	abstract public function getSetupOtions();		
	abstract public static function getInfo( $key = null );
}