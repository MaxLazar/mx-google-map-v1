<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


require_once PATH_THIRD . 'mx_google_map/config.php';

/**
 * Google Map
 *
 * @package		MX Google map
 * @subpackage	Modules
 * @category	Fieldtype
 * @author    Max Lazar <max@eec.ms>
 * @copyright Copyright (c) 2010 Max Lazar
 * @license   http://creativecommons.org/licenses/MIT/  MIT License
 */


class Mx_google_map_mcp
{
	var $base;			// the base url for this module
	var $form_base;		// base url for forms
	var $module_name = "mx_google_map";
	var $addon_name =  "mx_google_map";
	var $invalid_custom_field_names = array ("latitude", "entry_id",  "point_id", "longitude", 	"field_id", "icon");

	function Mx_google_map_mcp( $switch = TRUE )
	{

		if(defined('SITE_ID') == FALSE)
			define('SITE_ID', ee()->config->item('site_id'));

		ee()->load->dbforge();
		$this->base	 	 = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name;
		$this->form_base = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name;

	}

	function index()
	{
		$vars = array();
		$settings = array();
		$names =  array();
		$errors =  array();



		$vars = array(
			'addon_name' => $this->addon_name,
			'error' => FALSE,
			'input_prefix' => __CLASS__,
			'message' => FALSE,
			'settings_form' =>FALSE,
			'language_packs' => ''
		);

		ee()->db->select('field_name');

		$query =  ee()->db->get_where('exp_mx_google_map_fields', array('site_id' => SITE_ID));

		if ($query->num_rows() != 0)
		{
				foreach ($query ->result() as $key => $value)
				{
					$names[] = $value->field_name;
				}
		}

		if (count($_POST)) {
			if (isset($_POST['row_order'])) {

				foreach ($_POST['row_order'] as $row_order => $field_id)
				{
					$error_marker = false;
					if ($_POST['field_name_'.$field_id] == '')
					{
						$errors[] = ('no_field_name');
						$error_marker = true;
					}

					if (in_array($_POST['field_name_'.$field_id], $this->invalid_custom_field_names))
					{
						$errors[] = 'reserved_word';
						$error_marker = true;
					}

					if (preg_match('/[^a-z0-9\_\-]/i', $_POST['field_name_'.$field_id]))
					{
						$errors[] = ('invalid_characters').': '.$_POST['field_name_'.$field_id];
						$error_marker = true;
					}

					if ($_POST['field_label_'.$field_id] == '')
					{
						$errors[] = ('no_field_label');
						$error_marker = true;
					}

					if (!$error_marker)  {

						$data = array(
						   'field_id' => $field_id ,
						   'field_name' => trim(strtolower($_POST['field_name_'.$field_id])),
						   'field_label' =>$_POST['field_label_'.$field_id],
						   'field_pattern' => $_POST['field_pattern_'.$field_id],
						   'site_id'  => SITE_ID
						);

						if (isset($_POST['old_field_name_'.$field_id])){
							$update_column =  ($data['field_name'] != strtolower($_POST['old_field_name_'.$field_id])) ? true : false;
						}

						if (isset($_POST['new_field_'.$field_id])) {
							$data ['field_id']  = '';

							if (in_array($data['field_name'], $names)) {

								$errors[] = "duplicate_field_name";
							}
							else {
								ee()->db->insert('exp_mx_google_map_fields', $data);
								$data['field_id'] = ee()->db->insert_id();
								array_push ($names, $data['field_name']);
								$fields = array($data['field_name']	 => array('type' => 'TEXT'));
								ee()->dbforge->add_column('mx_google_map', $fields);
								$vars['message'] = "saved";
							}

						}
						else
						{
							if (isset($_POST['delete_'.$field_id]))	{
								ee()->db->delete('exp_mx_google_map_fields', array('field_id' => $field_id));
								ee()->dbforge->drop_column('mx_google_map', strtolower($_POST['old_field_name_'.$field_id]));
								$vars['message'] = "delete";
							}
							else
							{

								if ($update_column){
									$fields = array(
															strtolower($_POST['old_field_name_'.$field_id]) => array(
															 'name' => $data['field_name'],
															 'type' => 'TEXT',
															),
													);
									ee()->dbforge->modify_column('mx_google_map', $fields);
								}

								ee()->db->where('field_id', $field_id);
								ee()->db->update('exp_mx_google_map_fields', $data);

							}
						}

				 }
				}
			};
		}

		ee()->db->order_by('field_id');
		$query =  ee()->db->get_where('exp_mx_google_map_fields', array('site_id' => SITE_ID));


		if ($query->num_rows() != 0)
		{
			$settings['custom_fields'] = $query->result_array();
			$names = array();

			foreach ($query->result_array() as  $field){
				$names[] = $field['field_name'];
			}

		}


		$vars['errors'] = $errors;
		$vars['img_path'] = ee()->config->item('url_third_themes');
		$vars['settings'] = $settings;
		$vars['settings_form'] = TRUE;

		return $this->content_wrapper('index', 'Custom Fields', $vars);
	}


	function content_wrapper($content_view, $lang_key, $vars = array())
	{
		$vars['content_view'] = $content_view;
		$vars['_base'] = $this->base;
		$vars['_form_base'] = $this->form_base;

		if ( version_compare( APP_VER, '2.6.0', '<' ) ) {
			ee()->cp->set_variable('cp_page_title', lang($lang_key));
		}
		else {
			ee()->view->cp_page_title = lang($lang_key);
		}



		ee()->cp->set_breadcrumb($this->base, lang('mx_google_map_module_name'));




		return ee()->load->view('_wrapper', $vars, TRUE);
	}

}

/* End of file mcp.mx_google_map.php */
/* Location: ./system/expressionengine/third_party/mx_google_map/mcp.mx_google_map.php */