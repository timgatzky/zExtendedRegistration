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
		
		// compile	
		parent::compile($this);
		
		
		// add email confirmation javascript
		
	}
	
	/**
	 * OVERRIDE
	 * Create a new user and redirect
	 * @param array
	 */
	protected function createNewUser($arrData)
	{
		$arrData['tstamp'] = time();
		$arrData['login'] = $this->reg_allowLogin;
		$arrData['activation'] = md5(uniqid(mt_rand(), true));
		$arrData['dateAdded'] = $arrData['tstamp'];

		// Set default groups
		if (!array_key_exists('groups', $arrData))
		{
			$arrData['groups'] = $this->reg_groups;
		}
			

		// Disable account
		$arrData['disable'] = 1;

		// Send activation e-mail
		if ($this->reg_activate)
		{
			$arrChunks = array();

			$strConfirmation = $this->reg_text;
			preg_match_all('/##[^#]+##/i', $strConfirmation, $arrChunks);

			foreach ($arrChunks[0] as $strChunk)
			{
				$strKey = substr($strChunk, 2, -2);

				switch ($strKey)
				{
					case 'domain':
						$strConfirmation = str_replace($strChunk, $this->Environment->host, $strConfirmation);
						break;

					case 'link':
						$strConfirmation = str_replace($strChunk, $this->Environment->base . $this->Environment->request . (($GLOBALS['TL_CONFIG']['disableAlias'] || strpos($this->Environment->request, '?') !== false) ? '&' : '?') . 'token=' . $arrData['activation'], $strConfirmation);
						break;

					// HOOK: support newsletter subscriptions
					case 'channel':
					case 'channels':
						if (!in_array('newsletter', $this->Config->getActiveModules()))
						{
							break;
						}

						// Make sure newsletter is an array
						if (!is_array($arrData['newsletter']))
						{
							if ($arrData['newsletter'] != '')
							{
								$arrData['newsletter'] = array($arrData['newsletter']);
							}
							else
							{
								$arrData['newsletter'] = array();
							}
						}

						// Replace the wildcard
						if (!empty($arrData['newsletter']))
						{
							$objChannels = $this->Database->execute("SELECT title FROM tl_newsletter_channel WHERE id IN(". implode(',', array_map('intval', $arrData['newsletter'])) .")");
							$strConfirmation = str_replace($strChunk, implode("\n", $objChannels->fetchEach('title')), $strConfirmation);
						}
						else
						{
							$strConfirmation = str_replace($strChunk, '', $strConfirmation);
						}
						break;

					default:
						$strConfirmation = str_replace($strChunk, $arrData[$strKey], $strConfirmation);
						break;
				}
			}

			$objEmail = new Email();

			$objEmail->from = $GLOBALS['TL_ADMIN_EMAIL'];
			$objEmail->fromName = $GLOBALS['TL_ADMIN_NAME'];
			$objEmail->subject = sprintf($GLOBALS['TL_LANG']['MSC']['emailSubject'], $this->Environment->host);
			$objEmail->text = $strConfirmation;
			$objEmail->sendTo($arrData['email']);
		}

		// Make sure newsletter is an array
		if (isset($arrData['newsletter']) && !is_array($arrData['newsletter']))
		{
			$arrData['newsletter'] = array($arrData['newsletter']);
		}
		
		// ---
		// get additional fields
		$objExtRegFields = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE module=?")
						->limit(1)
						->execute($this->id);
		
		// build set array and clean out set array for tl_member
		if($objExtRegFields->numRows)
		{
			foreach(deserialize($objExtRegFields->formfields) as $f)
			{
				if(array_key_exists($f['name'], $arrData))
				{
					// tl_zextendedregistration_fields
					$arrDataExtReg = array
					(
						'pid'		=> $objExtRegFields->form,
						'tstamp'	=> time(),
						'name'		=> $f['name'],
						'type'		=> $f['type'],
						'fieldConf'	=> $f['fieldConf'],
						'data'		=> $arrData[$f['name']]
					);
					$objMoreUserFields = $this->Database->prepare("INSERT INTO " . $this->strTableFields . " %s")->set($arrDataExtReg)->execute();
		
					// tl_member
					unset($arrData[$f['name']]);
				}
			}
		}
		
		// Create user
		$objNewUser = $this->Database->prepare("INSERT INTO tl_member %s")->set($arrData)->execute();
		$insertId = $objNewUser->insertId;
		
		// Update user id in tl_zextendedregistration_fields
		$this->Database	->prepare("UPDATE " . $this->strTableFields . " SET user=? WHERE pid=?")
						->execute($objNewUser->insertId, $objExtRegFields->id);
		

		// Assign home directory
		if ($this->reg_assignDir && is_dir(TL_ROOT . '/' . $this->reg_homeDir))
		{
			$this->import('Files');
			$strUserDir = strlen($arrData['username']) ? $arrData['username'] : 'user_' . $insertId;

			// Add the user ID if the directory exists
			if (is_dir(TL_ROOT . '/' . $this->reg_homeDir . '/' . $strUserDir))
			{
				$strUserDir .= '_' . $insertId;
			}

			new Folder($this->reg_homeDir . '/' . $strUserDir);

			$this->Database->prepare("UPDATE tl_member SET homeDir=?, assignDir=1 WHERE id=?")
						   ->execute($this->reg_homeDir . '/' . $strUserDir, $insertId);
		}

		// HOOK: send insert ID and user data
		if (isset($GLOBALS['TL_HOOKS']['createNewUser']) && is_array($GLOBALS['TL_HOOKS']['createNewUser']))
		{
			foreach ($GLOBALS['TL_HOOKS']['createNewUser'] as $callback)
			{
				$this->import($callback[0]);
				$this->$callback[0]->$callback[1]($insertId, $arrData, $this);
			}
		}

		// Inform admin if no activation link is sent
		if (!$this->reg_activate)
		{
			$this->sendAdminNotification($insertId, $arrData);
		}

		$this->jumpToOrReload($this->jumpTo);
	}
	
	
	
}

?>