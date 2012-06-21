-- **********************************************************
-- *                                                        *
-- * IMPORTANT NOTE                                         *
-- *                                                        *
-- * Do not import this file manually but use the TYPOlight *
-- * install tool to create and maintain database tables!   *
-- *                                                        *
-- **********************************************************


-- --------------------------------------------------------

-- 
-- Table `tl_module`
-- 
CREATE TABLE `tl_module` (

	`extReg_cron` char(1) NOT NULL default '',
	`extReg_cron_delay` int(10) NOT NULL default '3600',
	`extReg_recommendation` char(1) NOT NULL default '',
	`extReg_recommendation_fields` blob NULL,
	`extReg_addformfields` char(1) NOT NULL default '',
	`extReg_form` varchar(64) NOT NULL default '',
	`extReg_formfields` blob NULL,
	`extReg_adminOnly` char(1) NOT NULL default '',
	
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- 
-- Table `tl_member`
-- 
CREATE TABLE `tl_member` (

	`recommended_from_email` varchar(255) NOT NULL default '',
	`recommended_from_username` varchar(255) NOT NULL default '',
	`email_confirmation` varchar(255) NOT NULL default '',

) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- 
-- Table `tl_zextendedregistration`
-- 
CREATE TABLE `tl_zextendedregistration` (

	`id` int(10) unsigned NOT NULL auto_increment,
	`tstamp` int(10) unsigned NOT NULL default '0',
	`module` int(10) unsigned NOT NULL default '0',
	`form` int(10) unsigned NOT NULL default '0',
	`formfields` blob NULL,
	
	PRIMARY KEY  (`id`)

) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Table `tl_zextendedregistration`
-- 
CREATE TABLE `tl_zextendedregistration_fields` (

	`id` int(10) unsigned NOT NULL auto_increment,
	`pid` int(10) unsigned NOT NULL default '0',
	`tstamp` int(10) unsigned NOT NULL default '0',
	`user` int(10) unsigned NOT NULL default '0',
	`name` varchar(255) NOT NULL default '',
	`type` varchar(64) NOT NULL default '',
	`fieldConf` blob NULL,
	`data` blob NULL,
	
	PRIMARY KEY  (`id`),
	KEY `pid` (`pid`)

) ENGINE=MyISAM DEFAULT CHARSET=utf8;
