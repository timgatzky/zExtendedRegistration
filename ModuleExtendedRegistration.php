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


class ModuleExtendedRegistration extends ModuleRegistration
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
	 * Generate module
	 */
	protected function compile()
	{
		// store module settings
		$arrDefault = array
		(
			'reg_activate' => $this->reg_activate,
			'reg_allowLogin' => $this->reg_allowLogin,
			'jumpTo' => $this->jumpTo,
		);

		// overwrite
		//$this->Input->setPost('FORM_SUBMIT') == 'tl_registration_noCompile';
		$this->reg_activate = 0;


		// Check for double email conformation
		if ($this->Input->post('FORM_SUBMIT') == 'tl_registration' && strlen($this->Input->post('email_confirmation')) )
		{
			if( $this->Input->post('email') != $this->Input->post('email_confirmation') )
			{
				$this->Input->setPost('FORM_SUBMIT','tl_registration_hasError');
				$this->reg_activate = 0;
			}
		}

		$recommended = false;
		$arrRecommended = array();

		// Check for recommendation
		if ($this->Input->post('FORM_SUBMIT') == 'tl_registration' && $this->extReg_recommendation )
		{
			if( $this->Input->post('recommended_from_email') && $this->Input->post('recommended_from_email') != $this->Input->post('email')  )
			{
				$arrRecommended['email'] = $this->Input->post('recommended_from_email');

			}
			if( $this->Input->post('recommended_from_username') && $this->Input->post('recommended_from_username') != $this->Input->post('username') )
			{
				$arrRecommended['username'] = $this->Input->post('recommended_from_username');
			}

			if(count($arrRecommended))
			{
				$strWHERE = '';
				foreach($arrRecommended as $field => $value)
				{
					$strWHERE .= $field . '=' . "'" . $value . "'" . ' AND ';
				}
				$strWHERE = substr($strWHERE, 0, -5);

				$objMember = $this->Database->prepare("SELECT * FROM tl_member WHERE " . $strWHERE . " AND disable!=1")
				->limit(1)
				->execute();
				if($objMember->numRows)
				{
					$recommended = true;
				}

			}
		}

		// user has filled out the inputs, but entered an invalid recommendation
		if($this->extReg_recommendation && !$recommended && $this->Input->post('recommended_from_email') || $this->Input->post('recommended_from_username'))
		{
			$this->Input->setPost('FORM_SUBMIT','tl_registration_hasError');
			$this->reg_activate = 0;
			$this->hasRecommendationError = true;
			$this->recommendation_error = sprintf('<p class="error">'.$GLOBALS['TL_LANG']['MSC']['recommended_error'].'</p>', implode(',',$arrRecommended) );
		}
		// user has filled out the inputs, recommendation is valid
		else if($this->extReg_recommendation && $recommended && $this->Input->post('recommended_from_email') || $this->Input->post('recommended_from_username'))
			{
				$this->reg_activate = 1;
			}
		// user has NOT filled out the inputs (default)
		// do not send activation link directely, send to admin only
		else if($this->Input->post('FORM_SUBMIT') == 'tl_registration' && $this->extReg_recommendation && $this->extReg_adminOnly)
			{
				$this->reg_activate = 0;
			}
		// contao default
		else
		{
			$this->reg_activate = $arrDefault['reg_activate'];
		}

		#$this->editable = deserialize($this->editable);

		// compile
		parent::compile();
	}
	
	/**
	 * OVERRIDE
	 * Create a new user and redirect
	 * @param array
	 */
	protected function createNewUser($arrData)
	{
		// get additional fields
		$objExtRegFields = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE module=?")
								->limit(1)
								->execute($this->objModule->id);

		// build set array and clean out set array for tl_member
		if($objExtRegFields->numRows > 0)
		{
			foreach(deserialize($objExtRegFields->formfields) as $f)
			{
				if(array_key_exists($f['name'], $arrData))
				{
					// clean out tl_member fields
					unset($arrData[$f['name']]);
				}
			}
		}
		
		// call inherited
		parent::createNewUser($arrData);
	}
	

	
	
	

}

?>