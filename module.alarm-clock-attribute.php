<?php

/**
 * Module Alarm Clock attribute
 * https://github.com/itop-itsm-ru/alarm-clock-attribute
 *
 * @author      Vladimir Kunin <v.b.kunin@gmail.com>
 * @license     http://www.opensource.org/licenses/gpl-3.0.html LGPL
 */

SetupWebPage::AddModule(
	__FILE__, // Path to the current file, all other file names are relative to the directory containing this file
	'alarm-clock-attribute/0.1.2',
	array(
		// Identification
		//
		'label' => 'Alarm clock attribute and trigger',
		'category' => 'business',

		// Setup
		//
		'dependencies' => array(
		),
		'mandatory' => false,
		'visible' => true,

		// Components
		//
		'datamodel' => array(
			'main.alarm-clock-attribute.php',
			'attributealarmclock.class.inc.php',
			'trigger.class.inc.php'
		),
		'dictionary' => array(
			'en.dict.alarm-clock-attribu.php',
			'ru.dict.alarm-clock-attribu.php'
		),
		'webservice' => array(

		),
		'data.struct' => array(
			// add your 'structure' definition XML files here,
		),
		'data.sample' => array(
			// add your sample data XML files here,
		),

		// Documentation
		//
		'doc.manual_setup' => '', // hyperlink to manual setup documentation, if any
		'doc.more_information' => '', // hyperlink to more information, if any

		// Default settings
		//
		'settings' => array(
			// Module specific settings go here, if any
		),
	)
);
