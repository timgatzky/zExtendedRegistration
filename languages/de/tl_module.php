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
 * Fields
 */
$GLOBALS['TL_LANG']['tl_module']['extReg_recommendation'] = array('Empfehlung auswerten', 'Prüft vor der Aktivierung, ob das neue Mitglied von einem existierenden Mitglied empfohlen wurde. Wenn JA, wird sofort eine Aktivierungsmail geschickt. Wenn NEIN, muss der Administrator den Benutzer freischalten. Bitte geben Sie die Prüffelder in der Feldauswahl oben frei.');
$GLOBALS['TL_LANG']['tl_module']['extReg_cron'] = array('E-Mail später senden.', 'Aktivieren Sie hier, wenn Sie die E-Mail zu einem späteren Zeitpunkt senden wollen.');
$GLOBALS['TL_LANG']['tl_module']['extReg_cron_delay'] = array('Wartezeit in Minuten', 'Geben sie die Wartezeit vor dem Senden in Minuten an. (min. 5 Min.)');
$GLOBALS['TL_LANG']['tl_module']['extReg_addformfields'] = array('Zusätzliche Felder aus Formular', 'Erweitern Sie das Registrierungsformular um zusätzliche Felder aus einem Formular.');
$GLOBALS['TL_LANG']['tl_module']['extReg_form'] = array('Formulare', 'Bitte wählen Sie das Formular, das als Quelle für die Felder dienen soll.');
$GLOBALS['TL_LANG']['tl_module']['extReg_formfields'] = array('Formularfelder', 'Bitte wählen Sie die Formularfelder aus.');
$GLOBALS['TL_LANG']['tl_module']['extReg_adminOnly'] = array('Admin hat das letzte Wort', 'Trotz Aktivierungsmail muss der neue User von einem Admin freigegeben werden.');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_module']['extReg_legend'] = 'Erweiterte Einstellungen';


?>