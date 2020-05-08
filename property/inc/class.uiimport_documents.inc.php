<?php
	/**
	 * phpGroupWare - property: a part of a Facilities Management System.
	 *
	 * @author Sigurd Nes <sigurdne@online.no>
	 * @copyright Copyright (C) 2012 Free Software Foundation, Inc. http://www.fsf.org/
	 * This file is part of phpGroupWare.
	 *
	 * phpGroupWare is free software; you can redistribute it and/or modify
	 * it under the terms of the GNU General Public License as published by
	 * the Free Software Foundation; either version 2 of the License, or
	 * (at your option) any later version.
	 *
	 * phpGroupWare is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 * GNU General Public License for more details.
	 *
	 * You should have received a copy of the GNU General Public License
	 * along with phpGroupWare; if not, write to the Free Software
	 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
	 *
	 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
	 * @internal Development of this application was funded by http://www.bergen.kommune.no/
	 * @package property
	 * @subpackage logistic
	 * @version $Id: class.uigeneric_document.inc.php 14913 2016-04-11 12:27:37Z sigurdne $
	 */
	phpgw::import_class('phpgwapi.uicommon_jquery');
	phpgw::import_class('phpgwapi.jquery');
	include_class('property', 'import_document_files_braarkiv', 'inc/import/');

	//include_class('property', 'import_component_files', 'inc/import/');

	class property_uiimport_documents extends phpgwapi_uicommon_jquery
	{

		private $receipt		 = array();
		protected $path_upload_dir, $acl_edit, $bocommon;
		public $public_functions = array(
			'query'							 => true,
			'index'							 => true,
			'handle_import_files'			 => true,
			'download'						 => true,
			'get_files'						 => true,
			'update_file_data'				 => true,
			'get_order_info'				 => true,
			'validate_info'					 => true,
			'step_1_import'					 => true,
			'step_2_import'					 => true

		);

		public function __construct()
		{
			parent::__construct();

			$this->bocommon			 = CreateObject('property.bocommon');
			$this->bo				 = CreateObject('property.boadmin_entity', true);
			$this->acl				 = & $GLOBALS['phpgw']->acl;

			/**
			 * Fix me
			 */
			$this->acl_read			 = true;
			$this->acl_edit			 = true;

			$GLOBALS['phpgw_info']['flags']['menu_selection']	 = 'admin::property::import_documents';
			$config = CreateObject('phpgwapi.config', 'property')->read();

			if (!empty($config['temp_files_components']))
			{
				$temp_files_components	 = trim($config['temp_files_components'], '/');
				$this->path_upload_dir	 = "/$temp_files_components";
			}
			else
			{
				$fakebase			 = '/temp_files_components';
				$this->path_upload_dir	 = $GLOBALS['phpgw_info']['server']['files_dir'] . $fakebase;
			}

		}

		public function download()
		{
		}

		private function _msg_data( $receipt )
		{
			if (isset($receipt['error']) && is_array($receipt['error']))
			{
				foreach ($receipt['error'] as $dummy => $error)
				{
					$this->receipt['error'][] = $error;
				}
			}

			if (isset($receipt['message']) && is_array($receipt['message']))
			{
				foreach ($receipt['message'] as $dummy => $message)
				{
					$this->receipt['message'][] = $message;
				}
			}

			return $this->receipt;
		}

		public function get_order_info( $order_id = 0)
		{
			if(!$order_id)
			{
				$order_id = phpgw::get_var('order_id', 'int');
			}
			$order_type = $this->bocommon->socommon->get_order_type($order_id);

			switch ($order_type)
			{
				case 'workorder':
					$location_item_id	 = $order_id;
					$item = CreateObject('property.soworkorder')->read_single($order_id);
					$remark	= $item['title'];
					break;
				case 'ticket':
					$sotts = CreateObject('property.sotts');
					$ticket_id	 = $sotts->get_ticket_from_order($order_id);
					$item	 = $sotts->read_single($ticket_id);

					if (!$item['subject'])
					{
						$item['subject'] =  CreateObject('property.botts')->get_category_name($item['cat_id']);
					}

					$remark	= $item['subject'];
					break;
				default:
					return array('error' => lang('no such order: %1', $order_id));
			}

			$vendor_id = $item['vendor_id'];

			$vendor_name = CreateObject('property.boinvoice')->get_vendor_name($vendor_id);
			$location_code = $item['location_code'];
			$location_data = @execMethod('property.bolocation.read_single', array('location_code' => $location_code,	'extra' => array('view' => true)));

			$gab_id = '';
			$cadastral_unit = '';
			$gabinfos = @execMethod('property.sogab.read', array(
				'location_code' => $location_code,
				'allrows' => true)
				);
			if ($gabinfos != null && is_array($gabinfos) && count($gabinfos) == 1)
			{
				$gabinfo = array_shift($gabinfos);
				$gab_id = $gabinfo['gab_id'];
			}

			if (strlen($gab_id) == 20)
			{
				$cadastral_unit = substr($gab_id, 4, 5) . ' / ' . substr($gab_id, 9, 4) . ' / ' . substr($gab_id, 13, 4) . ' / ' . substr($gab_id, 17, 3);
			}

			$building_number = $this->get_building_number($location_code);
			$file_tags = $this->_get_metadata($order_id);

			if ($file_tags)
			{
				$file_info = current($file_tags);

				$cadastral_unit	 = !empty($file_info['cadastral_unit']) ? $file_info['cadastral_unit'] : $cadastral_unit;
				$location_code	 = !empty($file_info['location_code']) ? $file_info['location_code'] : $location_code;
				$building_number = !empty($file_info['building_number']) ? array($file_info['building_number']) : $building_number;
				$remark			 = !empty($file_info['remark']) ? $file_info['remark'] : $remark;
			}

			return array(
				'vendor_name'		 => $vendor_name,
				'cadastral_unit'	 => $cadastral_unit,
				'location_code'		 => $location_code,
				'building_number'	 => $building_number,
				'remark'			 => $remark,
			);
		}

		private function get_building_number( $location_code )
		{
			$where_to_find_building_number = 'fm_location4.bygningsnr';

			$location_arr = explode('-', $location_code);
			$info = explode('.', $where_to_find_building_number);
			$table = $info[0];
			$field = $info[1];
			$targe_level = (int)substr($info[0], -1);
			$search_level = count($location_arr);

			if($search_level == $targe_level)
			{
				$sql = "SELECT {$field} FROM {$table} WHERE location_code = '{$location_code}'";
			}
			else if($search_level > $targe_level)
			{
				$temp_loc_arr = array();
				for ($i = 0; $i < count($targe_level); $i++)
				{
					$temp_loc_arr[] = $location_arr[$i];
				}

				$_location_code = implode('-', $temp_loc_arr);

				$sql = "SELECT {$field} FROM {$table} WHERE location_code = '{$_location_code}'";
			}
			else if($search_level < $targe_level)
			{
				$sql = "SELECT DISTINCT {$field} FROM {$table} WHERE location_code like '{$location_code}%'";
			}

			$GLOBALS['phpgw']->db->query($sql, __LINE__, __FILE__);
			
			$building_numbers = array();

			while ($GLOBALS['phpgw']->db->next_record())
			{
				$building_numbers[] = $GLOBALS['phpgw']->db->f($field);
			}
			return $building_numbers;
		}

		private function _get_metadata_file_name( $order_id )
		{
			return "{$this->path_upload_dir}/{$order_id}/metadata/metadata.json";
		}

		private function _set_metadata( $order_id, $metadata )
		{
			if(!$order_id)
			{
				return;
			}
			$file_name = $this->_get_metadata_file_name( $order_id );
			$fp	= fopen($file_name, 'w');
			fputs($fp, json_encode($metadata));
			fclose($fp);
		}

		private function _get_metadata( $order_id )
		{
			if(!$order_id)
			{
				return array();
			}
			$file_name = $this->_get_metadata_file_name( $order_id );
			$string = file_get_contents($file_name);
			return json_decode($string, true);
		}

		public function update_file_data()
		{
			if(!$this->acl_edit)
			{
				phpgw::no_access();
			}

			$action= phpgw::get_var('action', 'string');
			$files= phpgw::get_var('files', 'raw');
			$document_category= phpgw::get_var('document_category', 'string');
			$branch= phpgw::get_var('branch', 'string');
			$building_part= phpgw::get_var('building_part', 'string');
			$order_id = phpgw::get_var('order_id', 'int');
			$cadastral_unit= phpgw::get_var('cadastral_unit', 'string');
			$location_code= phpgw::get_var('location_code', 'string');
			$building_number= phpgw::get_var('building_number', 'string');
			$remark= phpgw::get_var('remark', 'string');
			

			if(!$order_id)
			{
				return;
			}

			$file_tags = $this->_get_metadata($order_id);

			if ($action == 'delete_file' && $files && $order_id)
			{
				$path_upload_dir = $this->path_upload_dir;
				if (empty($path_upload_dir))
				{
					return false;
				}

				$path_dir	 = rtrim($path_upload_dir, '/') . "/{$order_id}/";

				$list_files = $this->_get_files($path_dir);


				foreach ($list_files as $file_info)
				{
					if(in_array($file_info['file_name'], $files))
					{
						unlink($file_info['path_absolute']);

						if(!empty($file_tags[$file_info['file_name']]))
						{
							$file_tags[$file_info['file_name']] = null;
						}
					}
				}

				$this->_set_metadata($order_id, $file_tags);

			}
			else if($action == 'set_tag' && $files)
			{

				foreach ($files as $file_name)
				{
					if(!empty($file_tags[$file_name]['import_ok']))
					{
						continue;
					}
					if($document_category)
					{
						if(!empty($file_tags[$file_name]['document_category']))
						{
							$file_tags[$file_name]['document_category'] = array_unique(array_merge($document_category, $file_tags[$file_name]['document_category']));
						}
						else
						{
							$file_tags[$file_name]['document_category'] = $document_category;
						}
					}
					if($branch)
					{
						if(!empty($file_tags[$file_name]['branch']))
						{
							$file_tags[$file_name]['branch'] = array_unique(array_merge($branch, $file_tags[$file_name]['branch']));
						}
						else
						{
							$file_tags[$file_name]['branch'] = $branch;
						}
					}
					if($building_part)
					{
						if(!empty($file_tags[$file_name]['building_part']))
						{
							$file_tags[$file_name]['building_part'] = array_unique(array_merge($building_part, $file_tags[$file_name]['building_part']));
						}
						else
						{
							$file_tags[$file_name]['building_part'] = $building_part;
						}
					}
				}

				$this->_set_metadata($order_id, $file_tags);

			}
			else if($action == 'remove_tag' && $files)
			{
				foreach ($files as $file_name)
				{
					if(!empty($file_tags[$file_name]['import_ok']))
					{
						continue;
					}
					if($document_category)
					{
						if(!empty($file_tags[$file_name]['document_category']))
						{
							$file_tags[$file_name]['document_category'] = array_diff($file_tags[$file_name]['document_category'], $document_category);
						}
						else
						{
							$file_tags[$file_name]['document_category'] = array();
						}
					}
					if($branch)
					{
						if(!empty($file_tags[$file_name]['branch']))
						{
							$file_tags[$file_name]['branch'] = array_diff($file_tags[$file_name]['branch'], $branch);
						}
						else
						{
							$file_tags[$file_name]['branch'] = array();
						}
					}
					if($building_part)
					{
						if(!empty($file_tags[$file_name]['building_part']))
						{
							$file_tags[$file_name]['building_part'] = array_diff($file_tags[$file_name]['building_part'], $building_part);
						}
						else
						{
							$file_tags[$file_name]['building_part'] = array();
						}
					}

				}
				$this->_set_metadata($order_id, $file_tags);

			}

			else if($action == 'set_tag' && ($cadastral_unit || $location_code || $building_number || $remark))
			{
				$path_dir	 = "{$this->path_upload_dir}/{$order_id}/";
				$list_files = $this->_get_files($path_dir);
				foreach ($list_files as $file_info)
				{
					$file_name = $file_info['file_name'];

					if(!empty($file_tags[$file_name]['import_ok']))
					{
						continue;
					}

					if($cadastral_unit)
					{
						$file_tags[$file_name]['cadastral_unit'] = $cadastral_unit;
					}
					if($location_code)
					{
							$file_tags[$file_name]['location_code'] = $location_code;
					}
					if($building_number)
					{
						$file_tags[$file_name]['building_number'] = $building_number;
					}
					if($remark)
					{
						$file_tags[$file_name]['remark'] = $remark;
					}
				}

				$this->_set_metadata($order_id, $file_tags);
			}


			return $action;

		}

		public function index()
		{
			if (!$this->acl_read)
			{
				phpgw::no_access();
				return;
			}

			if (phpgw::get_var('phpgw_return_as') == 'json')
			{
				return $this->query();
			}

			$appname		 = lang('import documents');
			$function_msg	 = lang('list');
			$GLOBALS['phpgw_info']['flags']['app_header'] = lang('property') . ' - ' . $appname . ': ' . $function_msg;

			$data							 = array(
				'datatable_name' => $appname . ': ' . $function_msg,
				'form'			 => array(
					'toolbar' => array(
						'item' => array(
						)
					)
				),
				'datatable'		 => array(
					'source'	 => self::link(array('menuaction' => 'property.uiimport_documents.index', 'phpgw_return_as' => 'json')),
					'allrows'	 => true,
					'new_item'	 => self::link(array('menuaction' => 'property.uiimport_documents.step_1_import')),
					'field'		 => array(
						array(
							'key'		 => 'id',
							'label'		 => lang('Order ID'),
							'formatter'	 => 'JqueryPortico.formatLink'
						),
						array(
							'key'		 => 'remark',
							'label'		 => lang('title'),
						),
						array(
							'key'		 => 'vendor_name',
							'label'		 => lang('vendor'),
						),
						array(
							'key'		 => 'cadastral_unit',
							'label'		 => lang('cadastral unit'),
						),
						array(
							'key'		 => 'location_code',
							'label'		 => lang('location code'),
						),
						array(
							'key'		 => 'building_number',
							'label'		 => lang('building number'),
						)
					),
					'actions'	 => array(array())
				)
			);
			self::render_template_xsl('datatable_jquery', $data);
		}

		/**
		 * Prepare UI
		 * @return void
		 */
		public function step_1_import()
		{

			$order_id = phpgw::get_var('id');
			$tabs			 = array();
			$tabs['step_1']	 = array('label' => lang('step %1 - order reference', 1), 'link' => '#step_1');
			$tabs['step_2']	 = array('label' => lang('step %1 - upload documents', 2), 'link' => '#step_2', 'disable' => 1);
			$tabs['step_3']	 = array('label' => lang('step %1 - import documents', 3), 'link' => '#step_3', 'disable' => 1);
			$active_tab		 = 'step_1';

			$files_def = array
			(
				array('key'	 => 'file_name',
					'label'	 => lang('file'),
					'sortable'	 => true,
					'resizeable' => true
					),
				array('key' => 'document_category',
					'label' => lang('document categories'),
					'sortable' => true,
					'resizeable' => true,
					'formatter' => 'JqueryPortico.formatJsonArray'
					),
				array('key' => 'branch',
					'label' => lang('branch'),
					'sortable' => true,
					'resizeable' => true,
					'formatter' => 'JqueryPortico.formatJsonArray'
					),
				array('key' => 'building_part',
					'label' => lang('building part'),
					'sortable' => true,
					'resizeable' => true,
					'formatter' => 'JqueryPortico.formatJsonArray'
					),
				array('key' => 'import_ok',
					'label' => lang('import ok'),
					'sortable' => false,
					'resizeable' => true,
					),
				array('key' => 'import_failed',
					'label' => lang('import failed'),
					'sortable' => false,
					'resizeable' => true,
					),
			);


			$datatable_def = array();
			$requestUrl	 = json_encode(self::link(array(
				'menuaction' => 'property.uiimport_documents.update_file_data',
				'phpgw_return_as'	 => 'json')
				));
			$requestUrl = str_replace('&amp;', '&', $requestUrl);

			$buttons = array
			(
				array(
					'action' => 'set_tag',
					'type'	 => 'buttons',
					'name'	 => 'set_tag',
					'icon'	=> '<i class="far fa-save"></i>',
					'label'	 => lang('set tag'),
					'funct'	 => 'onActionsClick_files',
					'classname'	=> '',
					'value_hidden'	 => ""
					),
				array(
					'action' => 'delete_file',
					'type'	 => 'buttons',
					'name'	 => 'delete',
					'icon'	=> '<i class="far fa-trash-alt"></i>',
					'label'	 => lang('Delete file'),
					'funct'	 => 'onActionsClick_files',
					'classname'	 => 'record disabled delete_file',
					'value_hidden'	 => "",
					'confirm_msg'		=> "Vil du slette fil(er)"
					),
				array(
					'action' => 'remove_tag',
					'type'	 => 'buttons',
					'name'	 => 'remove_tag',
					'icon'	=> '<i class="far fa-trash-alt"></i>',
					'label'	 => lang('remove tag'),
					'funct'	 => 'onActionsClick_files',
					'classname'	 => 'record disabled remove_tag',
					'value_hidden'	 => "",
					'confirm_msg'		=> "Vil du slette tag fra fil(er)"
					),
			);

			$tabletools = array
			(
				array('my_name' => 'toggle_select'),
			);

			foreach ($buttons as $entry)
			{
				$tabletools[] = array
				(
					'my_name'		 => $entry['name'],
					'icon'			 => $entry['icon'],
					'text'			 => $entry['label'],
					'className'		 =>	$entry['classname'],
					'confirm_msg'	=>	$entry['confirm_msg'],
					'type'			 => 'custom',
					'custom_code'	 => "
						var api = oTable0.api();
						var selected = api.rows( { selected: true } ).data();
						var files = [];
						for ( var n = 0; n < selected.length; ++n )
						{
							var aData = selected[n];
							files.push(aData['file_name']);
						}

						{$entry['funct']}('{$entry['action']}', files);
						"
				);
			}

			$code		 = <<<JS

	this.onActionsClick_files=function(action, files)
	{
	   var numSelected = 	files.length;
		if (numSelected ==0)
		{
			alert('None selected');
			return false;
		}

		var order_id = $('#order_id').val();
		var document_category = $('#document_category option:selected').toArray().map(item => item.text);
		var branch = $('#branch option:selected').toArray().map(item => item.text);
		var building_part = $('#building_part option:selected').toArray().map(item => item.value);

		if(action !== 'delete_file')
		{
			if(!document_category.length && !branch.length && !building_part.length)
			{
				alert('ingenting valgt');
				return false;
			}
		}

//		console.log(document_category);
//		console.log(branch);
//		console.log(building_part);

		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: {$requestUrl},
			data:{files:files, document_category:document_category, branch:branch, building_part:building_part, action:action, order_id: order_id},
			success: function(data) {
				if( data != null)
				{

				}
				var oArgs = {menuaction: 'property.uiimport_documents.get_files', order_id: order_id};
				var strURL = phpGWLink('index.php', oArgs, true);

				JqueryPortico.updateinlineTableHelper('datatable-container_0',strURL);
				$('.record').addClass('disabled');
				$("#toggle_select0").addClass('fa-toggle-off');
				$("#toggle_select0").removeClass('fa-toggle-on');
				$('#step_2_next').hide();
				$("#message0").hide();
				$('#step_2_view_all').hide();

			},
			error: function(data) {
				alert('feil');
			}
		});
	}
JS;
			$GLOBALS['phpgw']->js->add_code('', $code);


			$datatable_def[] = array
				(
				'container'	 => 'datatable-container_0',
				'requestUrl' => "''",
				'ColumnDefs' => $files_def,
				'tabletools' => $tabletools,
				'config'	 => array(
					array('disablePagination' => true),
					array('disableFilter' => true),
					array('scrollX' => true),
					array('scrollY' => 300),
				)
			);

			$import_document_files = new import_document_files();

			$building_part_list = $import_document_files->get_building_part_list();
			$branch_list = $import_document_files->get_branch_list();
			$document_categories = $import_document_files->get_document_categories();

			$data = array
				(
				'order_id'					 => $order_id,
				'datatable_def'				 => $datatable_def,
				'tabs'						 => phpgwapi_jquery::tabview_generate($tabs, $active_tab),
				'image_loader'				 => $GLOBALS['phpgw']->common->image('property', 'ajax-loader', '.gif', false),
				'multi_upload_action'		 => $GLOBALS['phpgw']->link('/index.php', array('menuaction' => 'property.uiimport_documents.handle_import_files','id' => $id)),
				'building_part_list'		 => array('options' => $building_part_list),
				'branch_list'				 => array('options' => $branch_list),
				'document_category_list'	 => array('options' => $document_categories),
			);

			phpgwapi_jquery::load_widget('file-upload-minimum');
			phpgwapi_jquery::load_widget('select2');
			self::add_javascript('property', 'portico', 'import_documents.js');
			$GLOBALS['phpgw_info']['flags']['app_header'] = lang('property') . '::' . lang('import documents');

			self::render_template_xsl(array('import_documents', 'multi_upload_file_inline', 'datatable_inline'), $data);
		}


		function validate_info()
		{
			$path_upload_dir = $this->path_upload_dir;
			if (empty($path_upload_dir))
			{
				return false;
			}

			$order_id = phpgw::get_var('order_id', 'int');
			$path_dir	 = "{$this->path_upload_dir}/{$order_id}/";

			$list_files = $this->_get_files($path_dir);
			$file_tags = $this->_get_metadata($order_id);
			$lang_missing = lang('Missing value');
			$error_list = array();
			foreach ($list_files as &$file_info)
			{
				$file_name = $file_info['file_name'];
				$file_info['document_category'] =  isset($file_tags[$file_name]['document_category']) ? $file_tags[$file_name]['document_category'] : array();
				$file_info['branch'] = isset($file_tags[$file_name]['branch']) ? $file_tags[$file_name]['branch'] : array();
				$file_info['building_part'] = isset($file_tags[$file_name]['building_part']) ? $file_tags[$file_name]['building_part'] : array();
				$file_info['document_category_validate'] =  empty($file_tags[$file_name]['document_category']) ? false : true;
				$file_info['branch_validate'] = empty($file_tags[$file_name]['branch']) ?  false : true;
				$file_info['building_part_validate'] = empty($file_tags[$file_name]['building_part']) ? false : true;

				if(!$file_info['document_category_validate'] || !$file_info['branch_validate']  || !$file_info['branch_validate'] )
				{
					$error_list[] = $file_info;
				}
			}

			$total_records = count($error_list);

			return array
				(
				'data'				 => $error_list,
				'draw'				 => phpgw::get_var('draw', 'int'),
				'recordsTotal'		 => $total_records,
				'recordsFiltered'	 => $total_records
			);

		}


		function get_files()
		{
			$path_upload_dir = $this->path_upload_dir;
			if (empty($path_upload_dir))
			{
				return false;
			}

			$order_id = phpgw::get_var('order_id', 'int');

			$options = array();
			$path_dir	 = rtrim($path_upload_dir, '/') . "/{$order_id}/";

			$list_files = $this->_get_files($path_dir);


//			$file_tags = (array)phpgwapi_cache::session_get('property', 'documents_import_file_tags');
			$file_tags = $this->_get_metadata($order_id);
			foreach ($list_files as &$file_info)
			{
				$file_name = $file_info['file_name'];
				$file_info['document_category'] =  isset($file_tags[$file_name]['document_category']) ? $file_tags[$file_name]['document_category'] : array();
				$file_info['branch'] = isset($file_tags[$file_name]['branch']) ? $file_tags[$file_name]['branch'] : array();
				$file_info['building_part'] = isset($file_tags[$file_name]['building_part']) ? $file_tags[$file_name]['building_part'] : array();
				$file_info['import_ok'] = isset($file_tags[$file_name]['import_ok']) ? $file_tags[$file_name]['import_ok'] : '';
				$file_info['import_failed'] = isset($file_tags[$file_name]['import_failed']) ? $file_tags[$file_name]['import_failed'] : '';

			}

			$total_records = count($list_files);

			return array
				(
				'data'				 => $list_files,
				'draw'				 => phpgw::get_var('draw', 'int'),
				'recordsTotal'		 => $total_records,
				'recordsFiltered'	 => $total_records
			);

		}

		private function _get_files( $dir, $results = array() )
		{
			$content = scandir($dir);

			foreach ($content as $key => $value)
			{
				$path = realpath($dir . '/' . $value);
				if (is_file($path))
				{
					$pos = strpos($value, '..');
					if (!$pos === false)
					{
						$new_path = str_replace('..', '.', $path);
						if (rename($path, $new_path))
						{
							$value	 = str_replace('..', '.', $value);
							$path	 = $new_path;
						}
					}

					$results[] = array(
						'file_name' => $value,
						'path_absolute'	 => $path,
						'path_relative'	 => '/');
				}
			}

			return $results;
		}

		public function handle_import_files()
		{
			$path_upload_dir = $this->path_upload_dir;
			if (empty($path_upload_dir))
			{
				return false;
			}

			phpgw::import_class('property.multiuploader');

			$order_id = phpgw::get_var('order_id', 'int', 'GET');

			$options = array();
			$options['upload_dir']	 = rtrim($path_upload_dir, '/') . "/{$order_id}/";
			$options['script_url']	 = $GLOBALS['phpgw']->link('/index.php', array('menuaction' => 'property.uiimport_documents.handle_import_files'));

			if(!$order_id)
			{
				$upload_handler			 = new property_multiuploader($options, false);
				$response = array(files => array(array('error' => 'missing order_id in request')));
				$upload_handler->generate_response($response);
				$GLOBALS['phpgw']->common->phpgw_exit();
			}
			$receipt = $this->check_upload_dir($order_id);
			if (($receipt['error']))
			{
				$upload_handler			 = new property_multiuploader($options, false);
				$response = array(files => array(array('error' => $receipt['error'])));
				$upload_handler->generate_response($response);
				$GLOBALS['phpgw']->common->phpgw_exit();
			}

			$upload_handler			 = new property_multiuploader($options, true);
		}

		public function check_upload_dir($order_id)
		{
			$rs = $this->create_document_dir($order_id);
			if (!$rs)
			{
				$receipt['error'] = lang('failed to create directory') . ': ' . "{$this->path_upload_dir}/{$order_id}";
			}

			if (!is_writable("{$this->path_upload_dir}/{$order_id}"))
			{
				$receipt['error'] = lang('Not have permission to access the directory') . ': ' . "{$this->path_upload_dir}/{$order_id}";
			}

			return $receipt;
		}

		private function create_document_dir($order_id)
		{
			if (is_dir("{$this->path_upload_dir}/{$order_id}") && is_dir("{$this->path_upload_dir}/{$order_id}/metadata"))
			{
				return true;
			}

			$old = umask(0);
			$rs = false;
			if (!is_dir("{$this->path_upload_dir}/{$order_id}"))
			{
				$rs	 = mkdir("{$this->path_upload_dir}/{$order_id}", 0755);
			}
			if (!is_dir("{$this->path_upload_dir}/{$order_id}/metadata"))
			{
				$rs	 = mkdir("{$this->path_upload_dir}/{$order_id}/metadata", 0755);
			}
			umask($old);

			return $rs;
		}



		function get_pending_list( $search )
		{
			
			$dirname = $this->path_upload_dir;
			// prevent path traversal
			if (preg_match('/\./', $dirname) || !is_dir($dirname))
			{
				return array();
			}

			$values	 = array();
			$dir	 = new DirectoryIterator($dirname);
			if (is_object($dir))
			{
				foreach ($dir as $file)
				{
					$name = (string) $file;

					if ($file->isDot() || !$file->isDir() || !ctype_digit($name))
					{
						continue;
					}

					$values[] = array('id' => $name);
				}
			}


			$ret = array();
			foreach ($values as $entry)
			{
				$order_info = $this->get_order_info($entry['id']);

				/**
				 * not a valid order
				 */
				if ($order_info['error'])
				{
					continue;
				}

				if ($order_info)
				{
					$entry = array_merge($entry, $order_info);
				}

				if ($search)
				{
					$pattern = str_replace('/', '\/', $search);
					if(!preg_match("/$search/", $entry['id'])
						&& !preg_match("/$pattern/i", $entry['remark'])
						&& !preg_match("/$pattern/i", $entry['vendor_name'])
						&& !preg_match("/$pattern/", $entry['cadastral_unit'])
						&& !preg_match("/$pattern/i", $entry['location_code'])
						&& !preg_match("/$pattern/", $entry['building_number'])

						)
					{
						continue;
					}
				}
				$ret[] = $entry;
			}
			return $ret;

		}

		/**
		 * Fetch data from $this->bo based on parametres
		 * @return array
		 */
		public function query()
		{
			$search		 = phpgw::get_var('search');
			$values = $this->get_pending_list($search['value']);

			$_order		 = phpgw::get_var('order');
			if($_order)
			{
				$columns	 = phpgw::get_var('columns');
				$order		 = $columns[$_order[0]['column']]['data'];
				$sort		 = $_order[0]['dir'];
				foreach ($values as $entry)
				{
					$sort_key[] = $entry[$order];
				}

				if($sort == 'asc')
				{
					array_multisort($sort_key, SORT_ASC, $values);
				}
				else
				{
					array_multisort($sort_key, SORT_DESC, $values);
				}
			}


//------ Start pagination

			$start			 = phpgw::get_var('start', 'int', 'REQUEST', 0);
			$results		 = phpgw::get_var('length', 'int', 'REQUEST', 0);
			$allrows		 = phpgw::get_var('length', 'int') == -1;

			$total_records	 = count($values);

			$maxmatchs = !empty($GLOBALS['phpgw_info']['user']['preferences']['common']['maxmatchs'])  ? (int)$GLOBALS['phpgw_info']['user']['preferences']['common']['maxmatchs'] : 15;

			$num_rows = $results ? $results : $maxmatchs;
			if ($allrows)
			{
				$out = $values;
			}
			else
			{
				$page		 = ceil(( $start / $num_rows));
				$values_part = array_chunk($values, $num_rows);
				$out		 = $values_part[$page];
			}

//------ End pagination

			$result_data = array('results' => $out);

			$result_data['total_records']	 = $total_records;
			$result_data['draw']			 = phpgw::get_var('draw', 'int');

			$link_data = array
				(
				'menuaction' => 'property.uiimport_documents.step_1_import',
			);

			array_walk($result_data['results'], array($this, '_add_links'), $link_data);
			return $this->jquery_results($result_data);
		}


		public function step_2_import( )
		{
			if(!$this->acl_edit)
			{
				phpgw::no_access();
			}

			$order_id = phpgw::get_var('order_id', 'int', 'GET');
			if(!$order_id)
			{
				return;
			}

			$file_tags = $this->_get_metadata($order_id);
			$path_upload_dir = $this->path_upload_dir;
			if (empty($path_upload_dir))
			{
				return false;
			}

			$path_dir	 = rtrim($path_upload_dir, '/') . "/{$order_id}/";

			$list_files = $this->_get_files($path_dir);

			$import_document_files = new import_document_files();

			foreach ($list_files as $file_info)
			{

				$current_tag = $file_tags[$file_info['file_name']];

				if(isset($file_tags[$file_info['file_name']]) && empty($current_tag['import_ok'])

					&& ( $current_tag['document_category'] && $current_tag['branch']  && $current_tag['branch'] ) )
				{
					if($import_document_files->process_file( $file_info, $current_tag))
					{
						$file_tags[$file_info['file_name']]['import_ok'] = date('Y-m-d H:i:s');
					}
					else
					{
						$file_tags[$file_info['file_name']]['import_failed'] = date('Y-m-d H:i:s');
					}

					$this->_set_metadata($order_id, $file_tags);
				}
			}
		}

	}