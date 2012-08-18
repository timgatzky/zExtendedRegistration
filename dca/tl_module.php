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
 * Add selectors to tl_module
 */
array_insert($GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'], 1, array
(
	'extReg_cron',
	'extReg_addformfields',
));

/**
 * Add subpalettes to tl_module
 */
array_insert($GLOBALS['TL_DCA']['tl_module']['subpalettes'], 1, array
(
	'extReg_cron' => 'extReg_cron_delay',
	'extReg_addformfields' => 'extReg_form',
));



/**
 * Add palettes to tl_module
 */
// registration
$GLOBALS['TL_DCA']['tl_module']['palettes']['registration'] = str_replace
(
	'{protected_legend:hide}',
	'{extReg_legend:hide},extReg_recommendation,extReg_adminOnly,extReg_addformfields;{protected_legend:hide}',
	$GLOBALS['TL_DCA']['tl_module']['palettes']['registration']
);
// personal data
$GLOBALS['TL_DCA']['tl_module']['palettes']['personalData'] = str_replace
(
	'{protected_legend:hide}',
	'{extReg_legend:hide},extReg_addformfields;{protected_legend:hide}',
	$GLOBALS['TL_DCA']['tl_module']['palettes']['personalData']
);


/**
 * Add fields to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['editable']['options_callback'] = array('tl_module_zExtendedRegistration', 'getEditableMemberProperties');


$GLOBALS['TL_DCA']['tl_module']['fields']['extReg_recommendation'] = array
(
	'label'         	=> &$GLOBALS['TL_LANG']['tl_module']['extReg_recommendation'],
	'exclude'       	=> true,
	'inputType'     	=> 'checkbox',
	'eval'          	=> array()
);

$GLOBALS['TL_DCA']['tl_module']['fields']['extReg_adminOnly'] = array
(
	'label'         	=> &$GLOBALS['TL_LANG']['tl_module']['extReg_adminOnly'],
	'exclude'       	=> true,
	'inputType'     	=> 'checkbox',
	'eval'          	=> array()
);

$GLOBALS['TL_DCA']['tl_module']['fields']['extReg_recommendation_fields'] = array
(
	'label'				=> &$GLOBALS['TL_LANG']['tl_module']['extReg_recommendation_fields'],
	'exclude'			=> true,
	'inputType'			=> 'checkbox',
	'options_callback'	=> array('tl_module', 'getEditableMemberProperties'),
	'eval'				=> array('multiple'=>true, 'mandatory'=>false)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['extReg_cron'] = array
(
	'label'         	=> &$GLOBALS['TL_LANG']['tl_module']['extReg_cron'],
	'exclude'       	=> true,
	'inputType'     	=> 'checkbox',
	'eval'          	=> array('submitOnChange'=>true)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['extReg_cron_delay'] = array
(
	'label'         	=> &$GLOBALS['TL_LANG']['tl_module']['extReg_cron_delay'],
	'exclude'       	=> true,
	'inputType'     	=> 'text',
	'save_callback'		=> array(array('tl_module_zExtendedRegistration', 'setCronDelay')),
	'eval'          	=> array('rgxp'=>'digit', 'tl_class'=>'w50', 'nospace'=>true)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['extReg_addformfields'] = array
(
	'label'         	=> &$GLOBALS['TL_LANG']['tl_module']['extReg_addformfields'],
	'exclude'       	=> true,
	'inputType'     	=> 'checkbox',
	'eval'          	=> array('submitOnChange'=>true)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['extReg_form'] = array
(
	'label'				=> &$GLOBALS['TL_LANG']['tl_module']['extReg_form'],
	'inputType'			=> 'radio',
	'search'            => true,
	'options_callback'	=> array('tl_module_zExtendedRegistration', 'getForms'),
	'eval'				=> array('tl_class'=>'clr', 'mandatory'=>false, 'submitOnChange'=>true)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['extReg_formfields'] = array
(
	'label'				=> &$GLOBALS['TL_LANG']['tl_module']['extReg_formfields'],
	'exclude'			=> true,
	'inputType'			=> 'checkboxWizard',
	'options_callback'	=> array('tl_module_zExtendedRegistration', 'getFormfields'),
	'eval'				=> array('multiple'=>true, 'mandatory'=>false)
);

class tl_module_zExtendedRegistration extends Backend
{
	/**
	 * @var
	 */
	protected $strTable = "tl_zextreg_fields";
	

	/**
	 * get forms
	 * @return array
	 */
	public function getForms(DataContainer $dc)
	{
		$arrForms = array();
		$objForms = $this->Database->execute("SELECT id, title FROM tl_form ORDER BY title");

		while ($objForms->next())
		{
			$arrForms[$objForms->id] = $objForms->title . ' (ID ' . $objForms->id . ')';
		}

		return $arrForms;
	}
	
	/**
	 * get formfields of selected form
	 * @return array
	 */
	public function getFormfields(DataContainer $dc)
	{
		$objSelectedForm = $this->Database->prepare("SELECT extReg_forms FROM tl_module WHERE id=?")
						->limit(1)
						->execute($dc->id);
		
		$objFormfields = $this->Database->prepare("SELECT * FROM tl_form_field WHERE pid=? AND invisible!=1 ORDER BY sorting")
						->execute($objSelectedForm->extReg_forms);
		if(!$objFormfields->numRows) return '';
		
		$arrReturn = array();
		while($objFormfields->next())
		{
			$label = ($objFormfields->name ? $objFormfields->name: $objFormfields->type);
			$arrReturn[$objFormfields->id] = $label . ' (ID ' . $objFormfields->id . ':'. $objFormfields->type . ')';
		}
		
		return $arrReturn;
	}
	
	/**
	 * Return all editable fields of table tl_member
	 * @return array
	 */
	public function getEditableMemberProperties(DataContainer $dc)
	{
		$return = array();

		$this->loadLanguageFile('tl_member');
		$this->loadDataContainer('tl_member');

		foreach ($GLOBALS['TL_DCA']['tl_member']['fields'] as $k=>$v)
		{
			if ($v['eval']['feEditable'])
			{
				$return[$k] = $GLOBALS['TL_DCA']['tl_member']['fields'][$k]['label'][0] . ' <span style="color:#b3b3b3">['.$k.']</span>';;
			}
		}
		
		// add additional fields from form to field list
		if($dc->activeRecord->extReg_addformfields)
		{
			// get form fields selected
			$objFormfields = $this->Database->prepare("SELECT * FROM tl_form_field WHERE pid=? AND invisible!=1 ORDER BY sorting")
							->execute($dc->activeRecord->extReg_form);
	
			if(!$objFormfields->numRows)
			{
				return $return;
			}
			
			while($objFormfields->next())
			{
				if(!strlen($objFormfields->label))
				{
					#fix for older php versions
					$objFormfields->label = $objFormfields->type . $objFormfields->id;
					
					if($objFormfields->name)
					{
						$objFormfields->label = $objFormfields->name;
					}
				}	
				if(!strlen($objFormfields->name))
				{
					$objFormfields->name = $objFormfields->type . $objFormfields->id;
				}
				
				$return[$objFormfields->name] = $objFormfields->label . ' <span style="color:#b3b3b3">['.$objFormfields->name.':'.$objFormfields->type.']</span>';
				
			}
		}

		return $return;
	}
	
}


?>