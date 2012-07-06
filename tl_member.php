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

/**
 * HOOK
 */
if(TL_MODE=='FE')
{
	$GLOBALS['TL_DCA']['tl_member']['config']['onload_callback'][] = array('tl_member_zExtendedRegistration', 'initializeDataContainerFE');
}
else
{
	$GLOBALS['TL_DCA']['tl_member']['config']['onload_callback'][] = array('tl_member_zExtendedRegistration', 'initializeDataContainerBE');
	#$GLOBALS['TL_DCA']['tl_member']['config']['onload_callback'][] = array('tl_member_zExtendedRegistration', 'dropTemporaryColumns');
	
	$this->import('tl_member_zExtendedRegistration');
	$this->tl_member_zExtendedRegistration->dropTemporaryColumns();
}



/**
 * Add palettes to tl_member
 */
$GLOBALS['TL_DCA']['tl_member']['palettes']['default'] = str_replace
(
	'{address_legend:hide}',
	'{recommendation_legend:hide},recommended_from_email,recommended_from_username;{address_legend:hide}',
	$GLOBALS['TL_DCA']['tl_member']['palettes']['default']
);


/**
 * Add fields to tl_member
 */
$GLOBALS['TL_DCA']['tl_member']['fields']['recommended_from_email'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_member']['recommended_from_email'],
	'exclude'                 => true,
	'search'                  => true,
	'sorting'                 => true,
	'flag'					  => 1,
	'inputType'               => 'text',
	'eval'                    => array('rgex'=>'email', 'feEditable'=>true, 'feViewable'=>true, 'tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_member']['fields']['recommended_from_username'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_member']['recommended_from_username'],
	'exclude'                 => true,
	'search'                  => true,
	'sorting'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('feEditable'=>true, 'feViewable'=>true, 'tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_member']['fields']['email_confirmation'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_member']['email_confirmation'],
	'exclude'                 => true,
	'sorting'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('feEditable'=>true, 'feViewable'=>true, 'tl_class'=>'w50', 'mandatory'=>true)
);


/**
 * Class tl_member_zExtendedReistration
 */
class tl_member_zExtendedRegistration extends Backend
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
	 * DCA Table
	 * @var
	 */
	protected $dcTable = 'tl_member';
		

	/**
	 * Initialize the current DataContainer and insert new fields in frontend
	 * called from onload_callback
	 * @param object, DataContainer
	 */
	public function initializeDataContainerFE()
	{
		global $objPage;
		
		// get module settings
		$arrModules = $this->getFrontendModulesOnPage($objPage->id);
		
		if(!count($arrModules))
		{
			return;
		}
		
		
		foreach($arrModules as $module)
		{
			if($module['type'] == 'registration' && $module['extReg_addformfields'])
			{}
			else if($module['type'] == 'personalData' && $module['extReg_addformfields'])
			{
				$this->import('FrontendUser', 'User');
			}
			else
			{
				continue;
			}
			
			// set array for database
			$arrSet = array
			(
				'tstamp'		=> time(),
				'module'		=> $module['id'],
				'form'			=> $module['extReg_form'],
				'formfields'	=> array()
			);
			
			$arrEditable = deserialize($module['editable']);
			$arrFormfields = $this->getFormfields($module['extReg_form']);
			
			// generate field dca
			foreach($arrFormfields as $i => $field)
			{
				$fieldName = $field['name'];
				if(!strlen($fieldName))
				{
					$fieldName = $field['type'] . $field['id'];				
				}
				
				// filter fields not selected by user
				if(!in_array($fieldName, $arrEditable))
				{
					unset($arrFormfields[$i]);
					continue;
				}
				
				// field dca
				$fieldConf = $this->createFormfieldDCA($field);		
				
				// HOOK allow custom field dca
				if (isset($GLOBALS['TL_HOOKS']['zExtendedRegistration']['getFormFieldDCA']) && count($GLOBALS['TL_HOOKS']['zExtendedRegistration']['getFormFieldDCA']))
				{
					foreach ($GLOBALS['TL_HOOKS']['zExtendedRegistration']['getFormFieldDCA'] as $callback)
					{
						$this->import($callback[0]);
						$fieldConf = $this->$callback[0]->$callback[1]($fieldName,$fieldConf,$field,$this);
					}
				}
						
				// get values for personal data in front end
				if($module['type'] == 'personalData')
				{
					$arrUserData = $this->getUserData($this->User->id, $fieldName);
					$this->User->$fieldName = $arrUserData['data'];
				}
				
				// Set DCA
				$GLOBALS['TL_DCA'][$this->dcTable]['fields'][$fieldName] = $fieldConf;
												
				// database set array
				$arrSet['formfields'][] = array
				(
					'id'		=> $field['id'],
					'name'		=> $fieldName,
					'type'		=> $field['type'],
					'fieldConf'	=> $fieldConf,
					'row'		=> $field
				);
				
			}
			
			
			
			
			// update/insert new external form field user data in database from registration form
			if($this->Input->post('FORM_SUBMIT') == 'tl_registration')
			{
				// serialize arrays
				$arrSet['formfields'] = serialize($arrSet['formfields']);
			
				// check if entry should be updated or a new entry should be created
				$objExtReg = $this->Database->prepare("SELECT id FROM " . $this->strTable . " WHERE module=? AND form=?")
								->limit(1)
								->execute($module['id'], $module['extReg_form']);
				
				if($objExtReg->numRows)
				{
					$this->updateDatabase($arrSet);
				}
				else
				{
					$this->insertDatabase($arrSet);
				}
			}
			
			// update database in FE from personal data form, member form
			if($this->Input->post('FORM_SUBMIT') == 'tl_member_'.$module['id'])
			{
				foreach($arrSet['formfields'] as $set)
				{
					$dc = new DataContainer();
					$dc->id = $this->User->id;
					$dc->field = $set['name'];
					
					if(array_key_exists($set['name'], $_POST) && !empty($_POST[$set['name']]))
					{
						$varValue = $_POST[$set['name']];
					}
					
					$this->saveCallback($varValue, $dc);
				}
				
			}
		
		}
	}
	
	
	/**
	 * Get user data
	 * @param integer
	 * @return array
	 */
	protected function getUserData($intUser, $strColumn='')
	{
		$objExtRegFields = $this->Database->prepare("SELECT * FROM ".$this->strTableFields." WHERE user=?" . (strlen($strColumn)? " AND name='".$strColumn ."'" :"") )
						->execute($intUser);
		
		if(!$objExtRegFields->numRows)
		{
			return array();
		}
		
		if(strlen($strColumn))
		{
			return $objExtRegFields->row();
		}
		
		return $objExtRegFields->fetchAllAssoc();
	}
	
		
	
	/**
	 * Create field dca
	 * @param array
	 * @return array
	 */
	protected function createFormfieldDCA($fieldRow)
	{
		if(!count($fieldRow))
		{
			return array();
		}
		$f = $fieldRow;
		
		$fieldName = $f['name'];
		if(!strlen($fieldName))
		{
			$fieldName = $f['type'] . $f['id'];				
		}

		// Language and labels
		$label = $f['label'] ?: $f['name'] ?: $f['type'] . $f['id'];
		$description = sprintf($GLOBALS['TL_LANG']['MSC']['field_description'], $fieldName);
		
		$GLOBALS['TL_LANG'][$this->dcTable][$fieldName] = array($label,$description);
		
		
		// Field config
		$fieldConf = array
		(
			'label'		=> &$GLOBALS['TL_LANG'][$this->dcTable][$fieldName],
			'exclude'	=> false,
			'eval' 		=> array('feEditable'=>true,'feViewable'=>true),
		);
		
		switch($f['type'])
		{
			case 'text': 
			case 'textarea':
			case 'hidden':
				$fieldConf['inputType'] = $f['type'];
				break;
			case 'checkbox':
				$fieldConf['inputType'] = $f['type'];
				
				$options = array();
				foreach(deserialize($f['options']) as $v)
				{
					$options[$v['value']] = $v['label']; 
				}
				$fieldConf['options'] = $options;
				
				break;
			case 'select':
				$fieldConf['inputType'] = $f['type'];
				
				$options = array();
				foreach(deserialize($f['options']) as $v)
				{
					$options[$v['value']] = $v['label']; 
				}
				$fieldConf['options'] = $options;
				$fieldConf['eval']['multiple'] = $f['multiple'];
				$fieldConf['eval']['size'] = $f['mSize'];
				$fieldConf['eval']['includeBlankOption'] = true;
				break;
			case 'explanation':
				$fieldConf['inputType'] = $f['type'];
				$fieldConf['default'] = $f['text'];
				break;
			case 'html':
				$fieldConf['inputType'] = $f['type'];
				$fieldConf['default'] = $f['html'];
				break;
			case 'headline':
				$fieldConf['inputType'] = $f['type'];
				$fieldConf['default'] = $f['text'];
				break;
			default:
				
				$fieldConf['inputType'] = $f['type'];
				
				break;		
		}
		
		if($f['mandatory'])	$fieldConf['eval']['mandatory'] = true;
		if($f['maxlength'])	$fieldConf['eval']['maxlength'] = $f['maxlength'];
		if($f['rgxp']) 		$fieldConf['eval']['rgxp'] = $f['rgxp'];
		if($f['value']) 	$fieldConf['default'] = $f['value']; // default value
		
		$fieldConf['class'] = $fieldName;	
		
		return $fieldConf;
	}
	
	
	
	/**
	 * Initialize DataContainer in backend
	 * called from onload_callback
	 * @return object, DataContainer
	 */
	public function initializeDataContainerBE(DataContainer $dc)
	{
		if($this->Input->get('act') != 'edit')
		{
			return $dc;
		}
		
		// get registration modules
		$objModule = $this->Database->prepare("SELECT * FROM tl_module WHERE type=? AND extReg_addformfields=1")
									->execute('registration');
		if(!$objModule->numRows) 
		{
			return $dc;
		}
		
		while($objModule->next())
		{
			$arrEditable = deserialize($objModule->editable);
			$arrFormfields = $this->getFormfields($objModule->extReg_form);
			
			// generate field dca
			foreach($arrFormfields as $i => $field)
			{
				$fieldName = $field['name'];
				if(!strlen($fieldName))
				{
					$fieldName = $f['type'] . $field['id'];				
				}
				
				// filter fields not selected by user
				if(!in_array($fieldName, $arrEditable))
				{
					unset($arrFormfields[$i]);
					continue;
				}
				
				// create field dca
				$fieldConf = $this->createFormfieldDCA($field);		
				
				// HOOK allow custom field dca
				if (isset($GLOBALS['TL_HOOKS']['zExtendedRegistration']['getFormFieldDCA']) && count($GLOBALS['TL_HOOKS']['zExtendedRegistration']['getFormFieldDCA']))
				{
					foreach ($GLOBALS['TL_HOOKS']['zExtendedRegistration']['getFormFieldDCA'] as $callback)
					{
						$this->import($callback[0]);
						$fieldConf = $this->$callback[0]->$callback[1]($fieldName,$fieldConf,$field,$this);
					}
				}
				
				// handle database update via save/load callback
				$fieldConf['save_callback'] = array(array('tl_member_zExtendedRegistration', 'saveCallback'));
				$fieldConf['load_callback'] = array(array('tl_member_zExtendedRegistration', 'loadCallback'));
								
				$GLOBALS['TL_DCA'][$this->dcTable]['fields'][$fieldName] = $fieldConf;				
								
			}
			
			
			// Add palettes to tl_member
			$arrPalettes = array();
			foreach($arrFormfields as $field)
			{
				$arrPalettes[] = $field['name'];
			}
			$strPalette = implode(',', $arrPalettes);
			
			$strPlaceholder = '{address_legend:hide}';
			$strLegend = '{z_extended_registration_fields_legend:hide}';
			
			$GLOBALS['TL_DCA'][$this->dcTable]['palettes']['default'] = str_replace
			(
				   $strPlaceholder,
				   $strLegend . ','. $strPalette . ';' . $strPlaceholder,
				   $GLOBALS['TL_DCA'][$this->dcTable]['palettes']['default']
			);
						
		}
				
		return $dc;
	}
	
	
	/**
	 * Set field value 
	 * called from: save_callback	
	 */
	public function saveCallback($varValue, DataContainer $dc)
	{
		$objUpdate = $this->Database->prepare("UPDATE " . $this->strTableFields . " SET tstamp=?, data=? WHERE user=? AND name=?")
						->execute(time(), $varValue, $dc->id, $dc->field);
		
		return $varValue;
	}
	
	/**
	 * Get field value 
	 * called from: load_callback	
	 */
	public function loadCallback($varValue, DataContainer $dc)
	{
		// create temporary columns
		if(!$this->Database->fieldExists($dc->field,$this->dcTable))
		{
			$objAdd = $this->Database->prepare("ALTER TABLE ".$this->dcTable." ADD ".$dc->field." blob NULL")->execute();
		}
		
		// Load data in temporary field
		if(!$varValue)
		{
			$objExtRegField = $this->Database->prepare("SELECT data FROM " . $this->strTableFields . " WHERE user=? AND name=?")
							->limit(1)
							->execute($dc->id, $dc->field);
			if(!$objExtRegField->numRows)
			{
				return $varValue;
			}
			
			return $objExtRegField->data;
		}
		
		return $varValue;
	}
	
		
	/**
	 * Drop temporary tl_member columns when not in edit mode
	 * called from: onload_callback
	 */
	public function dropTemporaryColumns()
	{
		if($this->Input->get('do') != 'member' && $this->Input->get('act') != 'edit' )
		{
			$objExtRegFields = $this->Database->prepare("SELECT name FROM " . $this->strTableFields)->execute();
							
			if(!$objExtRegFields->numRows)
			{
				return;	
			} 
			
			while($objExtRegFields->next() )
			{
				if($this->Database->fieldExists($objExtRegFields->name, $this->dcTable, true) )
				{
					$objDrop = $this->Database->prepare("ALTER TABLE ".$this->dcTable." DROP ".$objExtRegFields->name."" )->execute();
				}
			}			
		}
	}
	
	
	/**
	 * Returns all form fields in a form as array
	 * @param int
	 * @return array
	 */
	private function getFormfields($intForm)
	{
		$objFormfield = $this->Database->execute("SELECT * FROM tl_form_field WHERE pid=" . $intForm);
						
		if(!$objFormfield->numRows)
		{
			return array();
		}
		
		return $objFormfield->fetchAllAssoc();
	}
	
	
	/**
	 * Returns all modules on the current page as an array
	 * @param int
	 * @param string
	 * @param array
	 * @return array
	 */
	 private function getFrontendModulesOnPage($intPage, $strInColumn='', $arrTypes=array())
	 {
	 	global $objPage;
	 	
	 	$arrModules = array();
	 	
		#$objModuleStatement = $this->Database->prepare("
		#	SELECT * FROM tl_module m WHERE m.id=
		#	(
		#		SELECT c.module FROM tl_content c WHERE type='module' AND pid=
		#		(
		#			SELECT a.id	FROM tl_article a WHERE	pid=? AND published=1 " . (strlen($strInColumn) ? " AND inColumn='".$strInColumn."'" : '') . "
		#		)  
		#	)"
		#);
	   	
	   	// Overwrite page object if its not the current page, just for further use in this function
	   	if($objPage->id != $intPage)
	   	{
		   	$objPage = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")
		   					->limit(1)
		   					->execute($intPage);
	   	}
	   	
	   	// Get modules included with layouts
	   	if($objPage->layout)
	    {
			$objStmtLayout = $this->Database->prepare("SELECT modules FROM tl_layout WHERE id=" . $objPage->layout)->limit(1);
	    }
	    else
	    {
		    $objStmtLayout = $this->Database->prepare("SELECT modules FROM tl_layout WHERE fallback=1")->limit(1);
	    }
	    $objLayout = $objStmtLayout->execute();
	    
	    foreach(deserialize($objLayout->modules) as $module)
		{
			if($module['mod'] <= 0)
			{	
				continue;
			}
			else if(strlen($strInColumn) && $module['col'] != $strInColumn) 
			{
				continue;
			}
			$objModule = $this->Database->prepare("SELECT * FROM tl_module WHERE id=?" . (count($arrTypes) ? " AND type IN'".implode(',', $arrTypes)."'" : "") )
			   				->limit(1)
			   				->execute($module['mod']);
			   
			$arrModules[] = $objModule->row();
		}
	   
	   	// Get modules in articles
	   	$objArticles = $this->Database->prepare("SELECT id,pid FROM tl_article WHERE pid=? AND published=1 " . (strlen($strInColumn) ? " AND inColumn='".$strInColumn."'" : "") )
						->execute($objPage->id);
		
		if(!$objArticles->numRows)
		{
			if(count($arrModules))
			{
				return $arrModules;
			}
			else
			{
				return array();
			}
		}
		
		while($objArticles->next())
		{
			$objContent = $this->Database->prepare("SELECT module FROM tl_content WHERE type='module' AND pid=? AND invisible!=1")
						->execute($objArticles->id);
						
			// fetch modules per content
			while($objContent->next())
			{
			   $objModule = $this->Database->prepare("SELECT * FROM tl_module WHERE id=?" . (strlen($strType) ? " AND type IN'".implode(',', $arrTypes)."'" : "") )
			   				->limit(1)
			   				->execute($objContent->module);
			   
			   $arrModules[] = $objModule->row();
		   }
		}
		
		return $arrModules;
	  
	  }
	
	
	/**
	 * Insert Database
	 * @param string
	 */
	protected function insertDatabase($arrSet)
	{
		$this->Database->prepare("INSERT INTO " . $this->strTable . " %s")->set($arrSet)->execute();
	}
	
	/**
	 * Update Database
	 * @param string
	 */
	protected function updateDatabase($arrSet)
	{
		$this->Database->prepare("UPDATE " . $this->strTable . " %s")->set($arrSet)->execute();
	}

	
}


?>