<?php
	/*
	Copyright: Deux Huit Huit 2014
	License: MIT, see the LICENCE file
	http://deuxhuithuit.mit-license.org/
	*/

	if(!defined("__IN_SYMPHONY__")) die("<h2>Error</h2><p>You cannot directly access this file</p>");
	
	require_once(EXTENSIONS . '/remote_selectbox/fields/field.remote_selectbox.php');

	/**
	 *
	 * Block user agent Decorator/Extension
	 * @author nicolasbrassard
	 *
	 */
	class extension_remote_selectbox extends Extension {
		
		/**
		 * Name of the extension
		 * @var string
		 */
		const EXT_NAME = 'Remote Select Box';
		
		const SETTING_GROUP = 'remote-selectbox';

		/**
		 * private variable for holding the errors encountered when saving
		 * @var array
		 */
		protected $errors = array();
		
		/**
		 *
		 * Symphony utility function that permits to
		 * implement the Observer/Observable pattern.
		 * We register here delegate that will be fired by Symphony
		 */
		public function getSubscribedDelegates(){
			return array(
				array(
					'page' => '/backend/',
					'delegate' => 'InitaliseAdminPageHead',
					'callback' => 'appendToHead'
				),
			); 
		}
		
		/**
		 *
		 * Appends file references into the head, if needed
		 * @param array $context
		 */
		public function appendToHead(Array $context) {
			// store de callback array locally
			$c = Administration::instance()->getPageCallback();
			
			// publish page
			if($c['driver'] == 'publish'){
				
				//Load Selectize if Autocomplete is set to yes
							
					Administration::instance()->Page->addScriptToHead(
						URL . '/extensions/remote_selectbox/assets/lib/selectize/selectize.min.js',
						time(),
						false
					);
					Administration::instance()->Page->addStylesheetToHead(
						URL . '/extensions/remote_selectbox/assets/lib/selectize/selectize.css',
						'screen',
						time(),
						false
					);
				
				Administration::instance()->Page->addScriptToHead(
					URL . '/extensions/remote_selectbox/assets/publish.remote_selectbox.js',
					time(),
					false
				);
			}
		}
		
		/**
		 *
		 * Delegate fired when the extension is install
		 */
		public function install() {
			return FieldRemote_Selectbox::createFieldTable();
		}
		
		/**
		 *
		 * Delegate fired when the extension is updated (when version changes)
		 * @param string $previousVersion
		 */
		public function update($previousVersion) {
			return true;
		}

		/**
		 *
		 * Delegate fired when the extension is uninstall
		 * Cleans settings and Database
		 */
		public function uninstall() {
			return FieldRemote_Selectbox::deleteFieldTable();
		}
		
	}