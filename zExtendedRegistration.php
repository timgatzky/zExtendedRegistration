<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Tim Gatzky 2012 
 * @author     Tim Gatzky <info@tim-gatzky.de>
 * @package    zExtendedRegistration 
 * @license    LGPL 
 * @filesource
 */


class zExtendedRegistration extends Backend
{
	/**
	 * Reference parent table
	 * @var
	 */
	protected $strTable = 'tl_zextendedregistration';

	/**
	 * Table to store field data
	 * @var
	 */
	protected $strTableFields = 'tl_zextendedregistration_fields';
	
	
	/**
	 * Insert additional fields
	 * @param integer
	 * @param array
	 * @param object
	 * called from createNewUser HOOK
	 */
	public function createNewUserCallback($intUser, $arrData, $objModule)
	{
		// get additional fields
		$objExtRegFields = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE module=?")
								->limit(1)
								->execute($objModule->id);

		// build set array
		if($objExtRegFields->numRows > 0)
		{
			foreach(deserialize($objExtRegFields->formfields) as $f)
			{
				if(array_key_exists($f['name'], $arrData))
				{
					// insert fields in tl_zextendedregistration_fields
					$arrDataExtReg = array
					(
						'pid'  		=> $objExtRegFields->form,
						'tstamp' 	=> time(),
						'name'  	=> $f['name'],
						'type'  	=> $f['type'],
						'fieldConf' => $f['fieldConf'],
						'user'		=> $intUser,
						'data'  	=> $arrData[$f['name']]
					);
					$this->Database	->prepare("INSERT INTO " . $this->strTableFields . " %s")
									->set($arrDataExtReg)
									->execute();
				}
			}
		}
	}


}

?>