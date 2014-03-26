<?php

	/**
	 * @package toolkit
	 */

	require_once FACE . '/interface.exportablefield.php';
	require_once FACE . '/interface.importablefield.php';
	
	require_once(EXTENSIONS . '/remote_selectbox/extension.driver.php');

	/**
	 * A simple Select field that essentially maps to HTML's `<select/>`. The
	 * options for this field are loaded from a remote source
	 */
	class FieldRemote_Selectbox extends Field implements ExportableField, ImportableField {
		public function __construct(){
			parent::__construct();
			$this->_name = __(extension_remote_selectbox::EXT_NAME);
			$this->_required = true;
			$this->_showassociation = false;

			// Set default
			$this->set('show_column', 'yes');
			$this->set('location', 'sidebar');
			$this->set('required', 'no');
			$this->set('autocomplete', 'no');
			$this->set('alphabetical', 'no');
		}

	/*-------------------------------------------------------------------------
		Definition:
	-------------------------------------------------------------------------*/

		public function canToggle(){
			return false;
		}

		public function canFilter(){
			return true;
		}

		public function canPrePopulate(){
			return false;
		}

		public function isSortable(){
			return true;
		}

		public function allowDatasourceOutputGrouping(){
			// Grouping follows the same rule as toggling.
			return false;
		}

		public function allowDatasourceParamOutput(){
			return true;
		}

		public function requiresSQLGrouping(){
			return false;
		}

	/*-------------------------------------------------------------------------
		Setup:
	-------------------------------------------------------------------------*/

		const TABLE_NAME = 'sym_fields_remote_selectbox';
	
		public function createTable(){
			return Symphony::Database()->query("
				CREATE TABLE IF NOT EXISTS `tbl_entries_data_" . $this->get('id') . "` (
				  `id` int(11) unsigned NOT NULL auto_increment,
				  `entry_id` int(11) unsigned NOT NULL,
				  `handle` varchar(255) default NULL,
				  `select_handle` varchar(255) default NULL,
				  `value` varchar(255) default NULL,
				  PRIMARY KEY  (`id`),
				  UNIQUE KEY `entry_id` (`entry_id`),
				  KEY `handle` (`handle`),
				  KEY `value` (`value`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
			");
		}
		
		public static function createFieldTable() {
			return Symphony::Database()->query("
				CREATE TABLE IF NOT EXISTS `" . self::TABLE_NAME . "` (
				  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				  `field_id` int(11) unsigned NOT NULL,
				  `data_url` text COLLATE utf8_unicode_ci,
				  `allow_multiple_selection` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
				  `sort_options` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
				  `autocomplete` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
				  PRIMARY KEY (`id`),
				  UNIQUE KEY `field_id` (`field_id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
			");
		}
		
		public static function deleteFieldTable() {
			return Symphony::Database()->query("
				DROP TABLE IF EXISTS `" . self::TABLE_NAME . "
			");
		}

	/*-------------------------------------------------------------------------
		Utilities:
	-------------------------------------------------------------------------*/

		

	/*-------------------------------------------------------------------------
		Settings:
	-------------------------------------------------------------------------*/

		public function findDefaults(array &$settings){
			if(!isset($settings['allow_multiple_selection'])) $settings['allow_multiple_selection'] = 'no';
			if(!isset($settings['sort_options'])) $settings['sort_options'] = 'no';
			if(!isset($settings['autocomplete'])) $settings['autocomplete'] = 'no';
		}

		public function displaySettingsPanel(XMLElement &$wrapper, $errors = null) {
			parent::displaySettingsPanel($wrapper, $errors);

			$div = new XMLElement('div', NULL, array('class' => ''));

			// Predefined Values
			$label = Widget::Label(__('Data url'));
			$label->setAttribute('class', 'column');
			$input = Widget::Input('fields['.$this->get('sortorder').'][data_url]', General::sanitize($this->get('data_url')));
			$label->appendChild($input);
			$div->appendChild($label);
			
			if(isset($errors['data_url'])) $wrapper->appendChild(Widget::Error($div, $errors['data_url']));
			else $wrapper->appendChild($div);
			
			$fieldset = new XMLElement('fieldset');
			$div = new XMLElement('div', NULL, array('class' => 'three columns'));
			

			// Allow selection of multiple items
			$label = Widget::Label();
			$label->setAttribute('class', 'column');
			$input = Widget::Input('fields['.$this->get('sortorder').'][allow_multiple_selection]', 'yes', 'checkbox');
			if($this->get('allow_multiple_selection') == 'yes') $input->setAttribute('checked', 'checked');
			$label->setValue(__('%s Allow selection of multiple options', array($input->generate())));
			$div->appendChild($label);

			// Sort options?
			$label = Widget::Label();
			$label->setAttribute('class', 'column');
			$input = Widget::Input('fields['.$this->get('sortorder').'][sort_options]', 'yes', 'checkbox');
			if($this->get('sort_options') == 'yes') $input->setAttribute('checked', 'checked');
			$label->setValue(__('%s Sort all options alphabetically', array($input->generate())));
			$div->appendChild($label);

			// Autocomplete?
			$label = Widget::Label();
			$label->setAttribute('class', 'column');
			$input = Widget::Input('fields['.$this->get('sortorder').'][autocomplete]', 'yes', 'checkbox');
			if($this->get('autocomplete') == 'yes') $input->setAttribute('checked', 'checked');
			$label->setValue(__('%s Autocomplete search of select items', array($input->generate())));
			$div->appendChild($label);
			$fieldset->appendChild($div);
			$wrapper->appendChild($fieldset);

			$fieldset = new XMLElement('fieldset');
			$div = new XMLElement('div', NULL, array('class' => 'two columns'));
			$this->appendShowColumnCheckbox($div);
			$this->appendRequiredCheckbox($div);
			$fieldset->appendChild($div);
			$wrapper->appendChild($fieldset);
		}

		public function checkFields(array &$errors, $checkForDuplicates = true){
			if(!is_array($errors)) $errors = array();

			if($this->get('data_url') == '') {
				$errors['data_url'] = __('The data url is required.');
			}
			
			parent::checkFields($errors, $checkForDuplicates);
		}

		public function commit(){
			if(!parent::commit()) return false;

			$id = $this->get('id');

			if($id === false) return false;

			$fields = array();

			if($this->get('data_url') != '') $fields['data_url'] = $this->get('data_url');
			$fields['allow_multiple_selection'] = ($this->get('allow_multiple_selection') ? $this->get('allow_multiple_selection') : 'no');
			$fields['sort_options'] = $this->get('sort_options') == 'yes' ? 'yes' : 'no';
			$fields['autocomplete'] = $this->get('autocomplete') == 'yes' ? 'yes' : 'no';

			return FieldManager::saveSettings($id, $fields);
		}

	/*-------------------------------------------------------------------------
		Publish:
	-------------------------------------------------------------------------*/

		public function displayPublishPanel(XMLElement &$wrapper, $data = null, $flagWithError = null, $fieldnamePrefix = null, $fieldnamePostfix = null, $entry_id = null){
			$states = $this->getToggleStates();
			$value = isset($data['value']) ? $data['value'] : null;
			
			if(!is_array($value)) $value = array($value);
			
			$options = array(
				array(null, false, null)
			);
			
			foreach($states as $handle => $v){
				$options[] = array(General::sanitize($v), in_array($v, $value), General::sanitize($v));
			}
			
			$fieldname = 'fields'.$fieldnamePrefix.'['.$this->get('element_name').']'.$fieldnamePostfix;
			$fieldnamehandle = 'fields'.$fieldnamePrefix.'['.$this->get('element_name').'_handle]'.$fieldnamePostfix;
			if($this->get('allow_multiple_selection') == 'yes') {
				$fieldname .= '[]';
			}
			
			$label = Widget::Label($this->get('label'));
			
			if($this->get('required') != 'yes') {
				$label->appendChild(new XMLElement('i', __('Optional')));
			}
			$hidden = Widget::Input($fieldnamehandle,$data['handle'],'hidden');// needs a name field['']
			$label->appendChild($hidden);
			// hidden inputs cant be used in single field extension 
			$select = Widget::Select($fieldname, $options, ($this->get('allow_multiple_selection') == 'yes' ? array('multiple' => 'multiple', 'size' => count($options),'id' => 'sort') : NULL));
			
			$select->setAttribute('data-value', implode(',',$value));
			if($this->get('autocomplete')=='yes'){
				$select->setAttribute('class', 'autocomplete');
			}
			if($this->get('sort_options') == 'yes'){
				$select->setAttribute('data-order', 'alphabetical');
			}
			$select->setAttribute('data-url', $this->get('data_url'));
			$select->setAttribute('data-required', $this->get('required') == 'yes');
			
			$label->appendChild($select);
			
			if($flagWithError != null) $wrapper->appendChild(Widget::Error($label, $flagWithError));
			else $wrapper->appendChild($label);
		}
		/*************************ERROR CHECKING FOR CHARACTERS*********************************************************/
		function processChars($val){
				$chars = array('|',',','.','@','*','!','&');		
				$all = Lang::createHandle($val,255,',',false,$chars);					
				return $all;			
		}
		/**********************************************************************************/
		public function processRawFieldData($data, &$status, &$message=null, $simulate=false, $entry_id=NULL){
			$status = self::__OK__;
			//var_dump($data);die; //from here
			if(is_array($data)){
				$i = [];
				foreach($data as $entry => $key){
					$ids = explode('|',$key);
					//var_dump($ids);
					foreach($ids as $id => $val){
						if(is_numeric($val)){
							$i[] = $val;
						}
						if(!is_numeric($val)){							
							$h['text'] = $val;
							$h['handle'] = $this->processChars($val);
							$j[] = implode($h,'~');
						}
					}
					
				}
					
				$result['value'] = implode($i,',');
				$result['handle'] = implode($j,'|');
				//var_dump($result);die;
			}
			
			/*if(is_array($data)){	
				$data = implode($data,',');
			}*/

			/*if(!is_array($data)) {
				return array(
					'value' => $data,
					'handle' => Lang::createHandle($data)
				);
			}

			if(empty($data)) return null;

			$result = array(
				'value' => array(),
				'handle' => array()
			);

			foreach($data as $value){
				$result['value'][] = $value;
				$result['handle'][] = Lang::createHandle($value);
			}*/


			//$result['value'] = $data;
			//$result['handle'] = $data;
			//var_dump($result);die;

			return $result;
		}

	/*-------------------------------------------------------------------------
		Output:
	-------------------------------------------------------------------------*/
function array_combine2($arr1, $arr2) {
    $count = min(count($arr1), count($arr2));
    return array_combine(array_slice($arr1, 0, $count), array_slice($arr2, 0, $count));
}
		public function appendFormattedElement(XMLElement &$wrapper, $data, $encode = false, $mode = null, $entry_id = null) {
			if (!is_array($data) or is_null($data['value'])) return;
			
			$list = new XMLElement($this->get('element_name'));
			
			if (!is_array($data['handle']) and !is_array($data['value'])) {
				$data = array(
					'handle'	=> array($data['handle']),
					'value'		=> array($data['value'])
				);
			}
			
			$data = array_merge($data['value'],$data['handle']);			
		
			$d['handle'] = explode(',',$data[0]);		
			$d['value'] = explode('|',$data[1]);		
				
			$d = $this->array_combine2($d['handle'],$d['value']);//array_combine($d['handle'],$d['value']);			
		
			foreach($d as $index => $value){
				$v = explode('~',$value);
				$list->appendChild(new XMLElement(
					'item',
					General::sanitize($v[0]),
					array(
						'id' => $index,
						'handle'	=> Lang::createHandle($v[1])
					)
				));				
			}	
			//var_dump($v);die;	
			$wrapper->appendChild($list);
		}

		public function prepareTableValue($data, XMLElement $link=NULL, $entry_id = null){
			$value = $this->prepareExportValue($data, ExportableField::LIST_OF + ExportableField::VALUE, $entry_id);
			//var_dump($data);
			$data = explode('|',$data['handle']);					
			$check = count($data);			
			if($check > 1){
				$check = '('.$check.')  Selected';
			}
			else{
				$check = $data[0];
			}				
			return parent::prepareTableValue(array('value' => $check), $link, $entry_id = null);
		}

		public function getParameterPoolValue(array $data, $entry_id = null) {
			return $this->prepareExportValue($data, ExportableField::LIST_OF + ExportableField::HANDLE, $entry_id);
		}

	/*-------------------------------------------------------------------------
		Import:
	-------------------------------------------------------------------------*/

		public function getImportModes() {
			return array(
				'getValue' =>		ImportableField::STRING_VALUE,
				'getPostdata' =>	ImportableField::ARRAY_VALUE
			);
		}

		public function prepareImportValue($data, $mode, $entry_id = null) {
			$message = $status = null;
			$modes = (object)$this->getImportModes();

			if(!is_array($data)) {
				$data = array($data);
			}

			if($mode === $modes->getValue) {
				if ($this->get('allow_multiple_selection') === 'no') {
					$data = array(implode('', $data));
				}

				return $data;
			}
			else if($mode === $modes->getPostdata) {
				return $this->processRawFieldData($data, $status, $message, true, $entry_id);
			}

			return null;
		}

	/*-------------------------------------------------------------------------
		Export:
	-------------------------------------------------------------------------*/

		/**
		 * Return a list of supported export modes for use with `prepareExportValue`.
		 *
		 * @return array
		 */
		public function getExportModes() {
			return array(
				'listHandle' =>			ExportableField::LIST_OF
										+ ExportableField::HANDLE,
				'listValue' =>			ExportableField::LIST_OF
										+ ExportableField::VALUE,
				'listHandleToValue' =>	ExportableField::LIST_OF
										+ ExportableField::HANDLE
										+ ExportableField::VALUE,
				'getPostdata' =>		ExportableField::POSTDATA
			);
		}

		/**
		 * Give the field some data and ask it to return a value using one of many
		 * possible modes.
		 *
		 * @param mixed $data
		 * @param integer $mode
		 * @param integer $entry_id
		 * @return array
		 */
		public function prepareExportValue($data, $mode, $entry_id = null) {
			$modes = (object)$this->getExportModes();

			if (isset($data['handle']) && is_array($data['handle']) === false) {
				$data['handle'] = array(
					$data['handle']
				);
			}

			if (isset($data['value']) && is_array($data['value']) === false) {
				$data['value'] = array(
					$data['value']
				);
			}

			// Handle => Value pairs:
			if ($mode === $modes->listHandleToValue) {
				return isset($data['handle'], $data['value'])
					? array_combine($data['handle'], $data['value'])
					: array();
			}

			// Array of handles:
			else if ($mode === $modes->listHandle) {
				return isset($data['handle'])
					? $data['handle']
					: array();
			}

			// Array of values:
			else if ($mode === $modes->listValue || $mode === $modes->getPostdata) {
				return isset($data['value'])
					? $data['value']
					: array();
			}
		}

	/*-------------------------------------------------------------------------
		Filtering:
	-------------------------------------------------------------------------*/

		public function displayDatasourceFilterPanel(XMLElement &$wrapper, $data = null, $errors = null, $fieldnamePrefix=NULL, $fieldnamePostfix=NULL){
			parent::displayDatasourceFilterPanel($wrapper, $data, $errors, $fieldnamePrefix, $fieldnamePostfix);

			$data = preg_split('/,\s*/i', $data);
			$data = array_map('trim', $data);

			$existing_options = $this->getToggleStates();

			if(is_array($existing_options) && !empty($existing_options)){
				$optionlist = new XMLElement('ul');
				$optionlist->setAttribute('class', 'tags');

				foreach($existing_options as $option) {
					$optionlist->appendChild(
						new XMLElement('li', General::sanitize($option))
					);
				};

				$wrapper->appendChild($optionlist);
			}
		}

		public function buildDSRetrievalSQL($data, &$joins, &$where, $andOperation = false) {
			$field_id = $this->get('id');

			if (self::isFilterRegex($data[0])) {
				$this->buildRegexSQL($data[0], array('value', 'handle'), $joins, $where);
			}
			else if ($andOperation) {
				foreach ($data as $value) {
					$this->_key++;
					$value = $this->cleanValue($value);
					$joins .= "
						LEFT JOIN
							`tbl_entries_data_{$field_id}` AS t{$field_id}_{$this->_key}
							ON (e.id = t{$field_id}_{$this->_key}.entry_id)
					";
					$where .= "
						AND (
							t{$field_id}_{$this->_key}.value = '{$value}'
							OR t{$field_id}_{$this->_key}.handle = '{$value}'
						)
					";
				}
			}
			else {
				if (!is_array($data)) $data = array($data);

				foreach ($data as &$value) {
					$value = $this->cleanValue($value);
				}

				$this->_key++;
				$data = implode("', '", $data);
				$joins .= "
					LEFT JOIN
						`tbl_entries_data_{$field_id}` AS t{$field_id}_{$this->_key}
						ON (e.id = t{$field_id}_{$this->_key}.entry_id)
				";
				$where .= "
					AND (
						t{$field_id}_{$this->_key}.value IN ('{$data}')
						OR t{$field_id}_{$this->_key}.handle IN ('{$data}')
					)
				";
			}

			return true;
		}

	/*-------------------------------------------------------------------------
		Grouping:
	-------------------------------------------------------------------------*/

		public function groupRecords($records){
			if(!is_array($records) || empty($records)) return;

			$groups = array($this->get('element_name') => array());

			foreach($records as $r){
				$data = $r->getData($this->get('id'));
				$value = General::sanitize($data['value']);

				if(!isset($groups[$this->get('element_name')][$data['handle']])){
					$groups[$this->get('element_name')][$data['handle']] = array(
						'attr' => array('handle' => $data['handle'], 'value' => $value),
						'records' => array(),
						'groups' => array()
					);
				}

				$groups[$this->get('element_name')][$data['handle']]['records'][] = $r;
			}

			return $groups;
		}

	}
