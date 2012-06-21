<?php 
/**
 * Initialize the system
 */
define('TL_MODE', 'BE');
require_once('../../initialize.php');


class ExtendedRegistrationCronjob extends Backend
{
	public function __construct() { parent::__construct(); }
	
	public function run()
	{
		#print('run');
		
		#$this->log('called from cron','ExtendedRegistrationCronjob: run()', TL_CRON);
				
		
		#$offset = 60;
		#$arrRange = array
		#(	
		#	'low' => time() - $offset,
		#	'high' => time() + $offset,
		#);
		#
		#$objMember = $this->Database->prepare("SELECT * FROM tl_member WHERE tstamp>=? AND tstamp<=?")
		#				->execute($arrRange['low'],$arrRange['high']);
		#if(!$objMember->numRows) 
		#{
		#	return '';
		#}
		#
		#// send activation mails
		#while( $objMember->next() )
		#{
		#	
		#}
		#
		// delete old cronjob record
	}
}



/**
 * Self instantiate
 */
$objCronjob = new ExtendedRegistrationCronjob();
$objCronjob->run();


?>