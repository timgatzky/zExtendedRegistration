<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * PHP version 5
 * @copyright  Tim Gatzky 2012 
 * @author     Tim Gatzky <info@tim-gatzky.de>
 * @package    zExtendedRegistration 
 * @license    LGPL 
 * @filesource
 */

/**
 * Helper Class for callbacks
 */
class zEfgFormFields extends Backend
{
	/**
	 * More form fields
	 * called from: getFormfieldDCA HOOK
	 * @param array
	 * @param string
	 * @param array
	 * @param array
	 * @param object
	 * @return array
	 */
	 public function handleEfgFormFields($fieldName, $fieldConf, $row, $objModule)
	 {
		 switch($row['type'])
		 {
			 case 'efgLookupSelect':
			 case 'efgLookupCheckbox':
			 case 'efgLookupRadio':
			 
			 	$fieldConf['inputType'] = strtolower(str_replace('efgLookup', '', $row['type']));
			 	
			 	$lookupOptions = deserialize($row['efgLookupOptions']);
				
				$tableAndField = explode('.', $lookupOptions['lookup_field']);
				$tableAndValue = explode('.', $lookupOptions['lookup_val_field']);
				
				$labelField = $tableAndField[1];
				$valueField = $tableAndValue[1];
				
				
				$strQuery = "SELECT " . $labelField . ',' . $valueField . " FROM " . $tableAndField[0];
				
				if($lookupOptions['lookup_where'])
				{
					$strQuery .= " WHERE " . str_replace('&#61;','=',$lookupOptions['lookup_where']);			 
				}
				
				if($lookupOptions['lookup_sort'])
				{
					#$sort = str_replace(',', ' , ' ,$lookupOptions['lookup_sort']);
					$strQuery .= " ORDER BY " .  $lookupOptions['lookup_sort'];		 
				}
				
				$objOptions = $this->Database->prepare($strQuery)->execute();
				
				$options = array();
				if($objOptions->numRows)
				{
					while($objOptions->next())
					{
						$options[$objOptions->$valueField] = $objOptions->$labelField;
					}		 
				}
				$fieldConf['options'] = $options;
				
				// settings
				$fieldConf['eval']['multiple'] = $row['multiple'];
				$fieldConf['eval']['size'] = $row['mSize'];
				$fieldConf['eval']['includeBlankOption'] = false;
				
				// overwrite
				if($fieldConf['inputType'] == 'checkbox')
				{
					if(TL_MODE == 'BE')	$fieldConf['inputType'] = "checkboxWizard";
					$fieldConf['eval']['multiple'] = true;
				}
				else if($fieldConf['inputType'] == 'select')
				{
					$fieldConf['eval']['includeBlankOption'] = true;
				}
			 
			 break;
		}
		 
		return $fieldConf;
	 }
	 
}
?>