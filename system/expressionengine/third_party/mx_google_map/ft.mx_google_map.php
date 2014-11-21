<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD . 'mx_google_map/config.php';


/**
 *  MX Google Map Class for ExpressionEngine2
 *
 * @package  ExpressionEngine
 * @subpackage Fieldtypes
 * @category Fieldtypes
 * @author    Max Lazar <max@eec.ms>
 * @copyright Copyright (c) 2013 Max Lazar
 * @license   http://creativecommons.org/licenses/MIT/  MIT License
 */

class Mx_google_map_ft extends EE_Fieldtype {

	var $info = array(
		'name'  => MX_GOOGLE_MAP_NAME,
		'version' => MX_GOOGLE_MAP_VERSION
	);

	var $addon_name = 'mx_google_map';

	var $has_array_data = TRUE;


	public function __construct()
	{
		parent::__construct();

		if (!ee()->config->item('path_third_themes')) {
			ee()->config->config['path_third_themes'] = ee()->config->item('theme_folder_path').'/third_party/';
			ee()->config->config['url_third_themes']  = ee()->config->item('theme_folder_url').'/third_party/';
		}

		if(defined('SITE_ID') == FALSE)
		define('SITE_ID', ee()->config->item('site_id'));
	}



	// --------------------------------------------------------------------

	/**
	 * Display Field on Publish
	 *
	 * @access public
	 * @param existing data
	 * @return field html
	 *
	 */
	function display_field($data)
	{
		$query = false;
		$is_draft = false;


		if (isset($_POST[$this->field_name])) {
			if(is_array($_POST[$this->field_name]))
			{
				$tmp_data = $_POST[$this->field_name];
				$is_draft = true;
			}
		}

		if (isset(ee()->session->cache['ep_better_workflow']['is_draft']) && ee()->session->cache['ep_better_workflow']['is_draft'])
		{
			if(is_array($data))
			{
				$tmp_data = $data;
				$is_draft = true;
			}
		}

		if ($is_draft) {
			$data = $tmp_data['field_data'];
			foreach ($tmp_data['order'] as $k => $v)
			{
				$query[$v] = $tmp_data[$v];
				$query[$v]['point_id'] = rand(1,999999);
				$query[$v]['latitude'] = $tmp_data[$v]['lat'];
				$query[$v]['longitude']= $tmp_data[$v]['long'];
				$query[$v]['icon'] = $tmp_data[$v]['icon'];
			}
		}

		$custom_fields_js = '';

		ee()->lang->loadfile('mx_google_map');

		$data_points = array('latitude', 'longitude', 'zoom');

		$entry_id = ee()->input->get('entry_id');

		$this->settings = array_merge($this->settings, unserialize(base64_decode($this->settings['field_settings'])));

		if ($entry_id && $data)
		{
			list($latitude, $longitude, $zoom) = explode('|', $data.'|||');
		}
		else
		{
			foreach($data_points as $key)
			{
				$$key = $this->settings[$key];
			}
		}

		$default_icon =  (isset($this->settings['icon'])) ? (($this->settings['icon'] != "") ?  $this->settings['icon']  : 'default') : 'default';
		$max_points =   (isset($this->settings['max_points'])) ? (($this->settings['max_points'] != "") ?  $this->settings['max_points']  : '2909') : '2909';

		$slide_bar = (isset($this->settings['slide_bar'])) ? (($this->settings['slide_bar'] != "y" && $this->settings['slide_bar'] != "o") ?  false : true) : true;

		$custom_fields = ee()->db->get_where('exp_mx_google_map_fields', array('site_id' => SITE_ID))->result_array();

		$marker_template = "";
		foreach ($custom_fields as $row)
		{
			$custom_fields_js .=  '{f_name: "'.$row['field_name'].'", type :"'.$row['field_type'].'", label: "'.$row['field_label'].'", pattern: "'.$row['field_pattern'].'"},';
			$marker_template  .= ','.$row['field_name'].' : "{'.$row['field_name'].'}"';
		}

		$zoom = (int) $zoom;
		$options = compact($data_points);
		$out = '';

		$url_markers_icons = (ee()->config->item('mx_markers_url')) ? ee()->config->item('mx_markers_url') : reduce_double_slashes(ee()->config->item('url_third_themes').'/mx_google_map/maps-icons/');
		$path_markers_icons = (ee()->config->item('mx_markers_path')) ? ee()->config->item('mx_markers_path') : reduce_double_slashes(ee()->config->item('path_third_themes').'/mx_google_map/maps-icons/');


		if (!isset($this->cache[$this->addon_name]['header']))
		{
			ee()->cp->add_to_foot('<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>');
			ee()->cp->add_to_foot('<script type="text/javascript" src="'.ee()->config->item('url_third_themes').'mx_google_map/mxgooglemap.min.js"></script>');
			ee()->cp->add_to_foot('<link rel="stylesheet" type="text/css" href="'.ee()->config->item('url_third_themes').'mx_google_map/css/mx_google_map.css" />');
			$this->_insert_js('
			marker_icons_path = "'.$url_markers_icons.'";');
			$this->cache[$this->addon_name]['header'] = TRUE;
		}

		$entry_id = ee()->input->get('entry_id');

		$markers = '';


		if ($entry_id && !$is_draft)
		{
			$query = ee()->db->get_where('exp_mx_google_map', array('entry_id' => $entry_id, 'field_id' => $this->field_id))->result_array();
		};

		if ($query) {
			foreach ($query as $row)
			{
				$custom_fields_set = "";

				$markers .= '{'.'marker_id : '.$row['point_id'].'
									,latitude: 	'.$row['latitude'].'
									,longitude: '.$row['longitude'].'
									,draggable: true
									,icon: "'.(($row['icon'] != "") ?  $row['icon'] : $default_icon).'"
									'. ee()->functions->var_swap($marker_template, $row).'},';
			}

			$markers = rtrim($markers, ',');
		}

		$this->_insert_js('
		jQuery(document).ready(function() {
		'.(($slide_bar) ?'
		'.(($this->settings['slide_bar'] == 'y') ? '
	   jQuery("#panel_main_'.$this->field_name.'").stop().animate({width:"0", opacity:0.1}, 220);
	   jQuery("#panel_main_el_'.$this->field_name.'").hide();
		' : '').'
		   jQuery("#panel_button_'.$this->field_name.'").toggle(function(){
			  jQuery("#panel_main_'.$this->field_name.'").stop().animate({width:"240px", opacity:0.8}, 220, function() {
			  });
			 jQuery("#panel_main_el_'.$this->field_name.'").show();
		   },
		   function(){
		   jQuery("#panel_main_'.$this->field_name.'").stop().animate({width:"0", opacity:0.1}, 220);
			  jQuery("#panel_main_el_'.$this->field_name.'").hide();

		   });
			' : '').'
			jQuery("#'.$this->field_name.'_map").mxgoogleMaps({
					latitude: '.$latitude.',
					longitude: '.$longitude.',
					zoom:  '.$zoom.',
					markers: ['.$markers.']
					,field_id : "'.$this->field_name.'"
					,cp: true
					,scrollwheel : false
					,icon:"'.$default_icon.'"
					,custom_fields : ['.rtrim($custom_fields_js,',').']
					'.(($max_points != "") ? ",max_points : ".$max_points : "").'
				}
			);

		});
		');

		$hidden_input = '<div style="display:none;" id="'.$this->field_name.'_data"><input name="'.$this->field_name.'[field_data]"  value="'.$latitude.'|'.$longitude.'|'.$zoom.'" type="hidden"/></div>';

		$value = implode('|', array_values($options));

		$field = '<div class="top"><div class="tip">'.ee()->lang->line('f_tip').'</div><div class="geo_input">
		<!--class="geo_input" -->
		<input type="text" id="'.$this->field_name.'_address" style="width:200px;" />';

		$button = '
		<a href="javascript:;" class="minibutton '.$this->field_name.'_btn_geocode">
		<span><span class="smallicon2"></span>'.((true) ? ee()->lang->line('find_it') : ' ').'</span></a>
		<a  href="javascript:;" class="minibutton btn-download saef '.$this->field_name.'_btn_addmarker">
		<span><span class="smallicon"></span>'.((true) ? ee()->lang->line('marker_at_c') : '').'</span></a>
		</div>
		</div>
		<div style="clear:both; padding:0; margin:0;"></div>';

		return $out.$field.$button.$hidden_input.'
		<div class="map_frame">
			<div id="'.$this->field_name.'_map" class="map_container"></div>

			<div class="panel_main" id="panel_main_'.$this->field_name.'" '.(($slide_bar) ? '': 'style="display:none;"').'>
				<div class="panel_main_el"  id="panel_main_el_'.$this->field_name.'">
					<div class="custom_fields">


					</div>
					<label  for="gmap-icon">'.ee()->lang->line('icon').'</label>
					'.$this->_get_dir_list($path_markers_icons, $this->field_name,'').'
					<label  for="latitude">'.ee()->lang->line('latitude').'</label>
					<input autocomplete="off" spellcheck="false" id="latitude_'.$this->field_name.'" name="latitude_'.$this->field_name.'"  disabled="disabled" type="text">
					<label  for="longitude">'.ee()->lang->line('longitude').'</label>
					<input autocomplete="off" spellcheck="false" id="longitude_'.$this->field_name.'" name="longitude_'.$this->field_name.'" disabled="disabled" type="text">

					<div style="width:100%;padding-top:20px;">
					<a href="javascript:;" class="minibutton '.$this->field_name.'_btn_delete" rel="'.$this->field_name.'"><span>'.ee()->lang->line('delete').'</span></a>
					<a href="javascript:;" class="minibutton '.$this->field_name.'_btn_move" rel="'.$this->field_name.'"><span>'.ee()->lang->line('move2center').'</span></a>
					<span style="float:right;"> <a href="javascript:;" class="minibutton '.$this->field_name.'_btn_apply" rel="'.$this->field_name.'"><span>'.ee()->lang->line('apply').'</span></a></span>
					</div>
				</div>
			</div>

			<div class="panel_button" id="panel_button_'.$this->field_name.'" '.(($slide_bar) ? '': 'style="display:none;"').'></div>

		</div>';
	}


	// Icons list

	function _get_dir_list ($directory, $field_name,  $data, $mode = 0) {

		$results = array();

		$handler = opendir($directory);

		while ($file = readdir($handler)) {
			$f_name = explode(".", $file);

			if ($file != '.' && $file != '..' )
				if  ($mode == 0) {
					if  ($f_name[1]  == 'png')
						$results[] = $file;
				}
			else
				$results[] = $file;
		}
		asort($results);
		closedir($handler);

		$result = "<select name=\"gmap-icon\" id=\"gmap-icon_".$field_name."\">";
		$result .= "<option value=\"\"></option>";
		foreach($results as $icon_file)
		{
			$selected = ($icon_file == $data) ? " selected=\"true\"" : "";
			$result .= "<option value=\"{$icon_file}\" $selected>{$icon_file}</option>";
		}

		$result .= "</select>";

		return $result;
	}


	// --------------------------------------------------------------------

	/**
	 * Prep the publish data
	 *
	 * @access public
	 */
	function pre_process($data)
	{

		$map = array ();

		// BWF - Is this draft data we're loading into the template parser?
		if(isset(ee()->session->cache['ep_better_workflow']['is_draft']) && ee()->session->cache['ep_better_workflow']['is_draft'])
		{
			// BWF - If so make sure we have an array, then update some variables
			if(is_array($data))
			{
				ee()->session->cache['ep_better_workflow']['mx_google_map_draft_data'] = $data;
				$data = $data['field_data'];
			}
		}

		// Parse out the file info $point
		if ($data != "") {
			list($map["latitude"], $map["longitude"], $map["zoom"]) = explode('|', $data.'|||');
		};
		return $map;
		//, $map["entry_id"]
	}

	// --------------------------------------------------------------------

	/**
	 * Replace tag
	 *
	 * @access public
	 * @param field contents
	 * @return replacement text
	 *
	 */
	function replace_tag($data, $params = array(), $tagdata = FALSE)
	{
		$r = "";
		if ($tagdata !== FALSE and !empty($data)){
			$mapTypeControl =  ( ! isset($params['mapTypeControl'])) ? "\n,mapTypeControl: true" : "\n,mapTypeControl:".$params['mapTypeControl'];

			$query = false;

			// BWF - Is this draft data we're loading into the publish form?
			if(isset(ee()->session->cache['ep_better_workflow']['is_draft']) && ee()->session->cache['ep_better_workflow']['is_draft'])
			{
				$draft_data = ee()->session->cache['ep_better_workflow']['mx_google_map_draft_data'];
				if (isset($draft_data['order']))
				{
					foreach ($draft_data['order'] as $k => $v)
					{
						$query[$v] = $draft_data[$v];
						$query[$v]['point_id'] = rand(1,999999);
						$query[$v]['latitude'] = $draft_data[$v]['lat'];
						$query[$v]['longitude']= $draft_data[$v]['long'];
						$query[$v]['icon'] = $draft_data[$v]['icon'];
					}
				}
			}

			if (!$query) {
				$data_q = array('entry_id' =>  $this->row['entry_id'], 'field_id' => $this->field_id);

				if (isset($this->row['point_id']))  {
					$data_q['point_id'] = $this->row['point_id'];
				}

				$query = ee()->db->get_where('exp_mx_google_map', $data_q);
				$query = ($query->num_rows() > 0) ? $query->result_array() : false;

			}


			$markers = array();

			if ($query)
			{
				$i = 0;

				foreach ($query as $v => $row)
				{
					$pass = false;

					if (isset(ee()->TMPL->tagparams['points'])) {
						if (!isset(ee()->TMPL->tagparams['points'][$row['point_id']])){
							continue;
						}
						else {
							$pass = true;
						}
					}

					$markers[$i] = $row;

					if ($pass) {
						$markers[$i]['distance'] = ee()->TMPL->tagparams['points'][$row['point_id']];
					}

					$i++;
				}

				if (!empty($markers)) {
					$tagdata = ee()->functions->prep_conditionals($tagdata, $markers);
					$r = ee()->TMPL->parse_variables($tagdata, $markers);
				}
			}
		}
		return $r;



	}
	/**
	 * Replace tag
	 *
	 * @access public
	 * @param field contents
	 * @return replacement text
	 *
	 */
	function replace_isempty($data, $params = array(), $tagdata = FALSE)
	{
		return ($data == "") ? true : false;
	}

	function replace_map($data, $params = array(), $tagdata = FALSE)
	{

		$ret = '';
		if ($data != "") {
			$mt_control_style =  ( ! isset($params['mt_control_style'])) ? '' : "\n,mapTypeControlOptions: {\nstyle: google.maps.MapTypeControlStyle.".$params['mt_control_style']."\n}";
			$n_control_style =  ( ! isset($params['n_control_style'])) ? '' : "\n,navigationControlOptions: {\nstyle: google.maps.NavigationControlStyle.".$params['n_control_style']."\n}";
			$maptype =  ( ! isset($params['maptype'])) ? null :  ",mapTypeId: google.maps.MapTypeId.".$params['maptype'];
			$draggable =  ( ! isset($params['draggable'])) ? null : "\n,draggable:".$params['draggable'];
			$zoom =  ( ! isset($params['zoom'])) ? $data["zoom"] :  $params['zoom'];
			$scrollwheel =  ( ! isset($params['scrollwheel'])) ? null : "\n,scrollwheel:".$params['scrollwheel'];
			$doubleclickzoom =  ( ! isset($params['doubleclickzoomoff'])) ? null : "\n,disableDoubleClickZoom:".$params['doubleclickzoomoff'];


			$height =  ( ! isset($params['height'])) ? "500px" : $params['height'];
			$width =  ( ! isset($params['width'])) ? "100%" : $params['width'];

			$form_id =  ( ! isset($params['id'])) ? "" : $params['id'];
			$form_class =  ( ! isset($params['class'])) ? "" : $params['class'];

			$icon =  ( !isset($params['icon'])) ? ',icon: "default"' :  "\n,icon: \"".$params['icon'].'"';
			$marker_draggable =  ( ! isset($params['marker_draggable'])) ? null : "\n,draggable:".$params['marker_draggable'];

			$icon =  ( !isset($params['icon'])) ? ',icon: "default"' :  "\n,icon: \"".$params['icon'].'"';

			$navigationControl =  ( ! isset($params['navigationControl'])) ? "\n,navigationControl: true" : "\n,navigationControl:".$params['navigationControl'];
			$scaleControl =  ( ! isset($params['scaleControl'])) ? "\n,scaleControl: true" : "\n,scaleControl:".$params['scaleControl'];
			$mapTypeControl =  ( ! isset($params['mapTypeControl'])) ? "\n,mapTypeControl: true" : "\n,mapTypeControl:".$params['mapTypeControl'];
			$url_markers_icons = (ee()->config->item('mx_markers_url')) ? ee()->config->item('mx_markers_url') : reduce_double_slashes(ee()->config->item('url_third_themes').'/mx_google_map/maps-icons/');

			$cache_js =  ( ! isset($params['cache_js'])) ? false : $params['cache_js'];

			$randid = rand();

			$custom_fields = ee()->db->get_where('exp_mx_google_map_fields', array('site_id' => SITE_ID))->result_array();

			$marker_template = "";

			foreach ($custom_fields as $row)
			{
				$marker_template  .= ','.$row['field_name'].' : "{'.$row['field_name'].'}"
			';
			}

			$query = ee()->db->get_where('exp_mx_google_map', array('entry_id' => $this->row['entry_id'], 'field_id' => $this->field_id))->result_array();
			$markers = '';

			foreach ($query as $row)
			{
				if (isset(ee()->TMPL->tagparams['points'])) {
					if (!in_array($row['point_id'], ee()->TMPL->tagparams['points']))
						return false;
				}

				$markers .= '{'.'marker_id : '.$row['point_id'].'

						'. ee()->functions->var_swap($marker_template, $row).'

						,latitude: 	'.$row['latitude'].',
						longitude: '.$row['longitude'].',
						draggable: true

                        '.(($row['icon'] != "") ? ',icon: "'.$row['icon'].'"' :$icon) .'},';
			}

			$markers = rtrim($markers, ',');

			$js =  '
						marker_icons_path = "'.$url_markers_icons.'";

						jQuery(document).ready(function() {
							jQuery("#'.$randid.'_map").mxgoogleMaps({
									latitude: '.$data["latitude"].',
									longitude: '.$data["longitude"].',
									zoom:  '.$zoom.',
									markers: ['.$markers.'],
									field_id : "'.$randid.'"
									'.$maptype
								.$navigationControl
								.$scaleControl
								.$mapTypeControl
								.$mt_control_style
								.$n_control_style
								.$scrollwheel
								.$doubleclickzoom
								.$draggable
								.'
								}
							);

						});
			';

			if (!$cache_js) {

				$ret .= '<script type="text/javascript">'
						. $js .
						'</script>';

			} else {
					if(!isset(ee()->session->cache['mx_google_map_js'])) {
						ee()->session->cache['mx_google_map_js'] = '';
					}

					ee()->session->cache['mx_google_map_js'] .= $js;
			}


			return $ret.'<div style="height: '.$height.';width:'.$width.'" id="'.$form_id.'" class="'.$form_class.'"><div id="'.$randid.'_map" style="width: 100%; height: 100%"></div></div>';
		}

		return "";
	}

	// --------------------------------------------------------------------

	/**
	 * Save Global Settings
	 *
	 * @access public
	 * @return global settings
	 *
	 */
	function save_global_settings()
	{
		return array_merge($this->settings, $_POST);
	}

	// --------------------------------------------------------------------

	/**
	 * Display Settings Screen
	 *
	 * @access public
	 * @return default global settings
	 *
	 */
	function display_settings($data)
	{

		ee()->lang->loadfile('mx_google_map');

		$latitude  = isset($data['latitude']) ? $data['latitude'] : $this->settings['latitude'];
		$longitude = isset($data['longitude']) ? $data['longitude'] : $this->settings['longitude'];
		$zoom  = isset($data['zoom']) ? $data['zoom'] : $this->settings['zoom'];
		$max_points = isset($data['max_points']) ? $data['max_points'] : $this->settings['max_points'];
		$icon = isset($data['icon']) ? $data['icon'] : $this->settings['icon'];
		$slide_bar = isset($data['slide_bar']) ? $data['slide_bar'] : $this->settings['slide_bar'];

		$path_markers_icons = (ee()->config->item('mx_markers_path')) ? ee()->config->item('mx_markers_path') : reduce_double_slashes(ee()->config->item('path_third_themes').'/mx_google_map/maps-icons/');




		ee()->table->add_row(
			lang('latitude', 'latitude'),
			form_input('latitude', $latitude)
		);

		ee()->table->add_row(
			lang('longitude', 'longitude'),
			form_input('longitude', $longitude)
		);

		ee()->table->add_row(
			lang('zoom', 'zoom'),
			form_dropdown('zoom', range(1, 20), $zoom)
		);


		ee()->table->add_row(
			lang('max_points', 'max_points'),
			form_input('max_points', $max_points)
		);

		ee()->table->add_row(
			lang('icon', 'icon'),
			$this->_get_dir_list($path_markers_icons, '', $icon)
		);

		ee()->table->add_row(
			lang('slide_bar', 'slide_bar'),
			form_dropdown('slide_bar', array('y' => lang('yes_close'),  'o' => lang('yes_open'),  'n' => lang('no')), $slide_bar)

		);


		if (!isset($this->cache[$this->addon_name]['header_map']))
		{
			// Map preview
			$this->_cp_js();
			ee()->javascript->output('$(window).load(gmaps);');
			$this->cache[$this->addon_name]['header_map'] = TRUE;
		}



		ee()->table->add_row(
			lang('preview', 'preview'),
			'<div style="height: 300px;"><div id="map_canvas" style="width: 100%; height: 100%"></div></div>'
		);
	}

	/**
	 * Save Settings
	 *
	 * @access public
	 * @return field settings
	 *
	 */
	function save_settings($data)
	{
		return array(
			'latitude' => ee()->input->post('latitude'),
			'longitude' => ee()->input->post('longitude'),
			'zoom'  => ee()->input->post('zoom'),
			'max_points'  => ee()->input->post('max_points'),
			'icon'  => ee()->input->post('gmap-icon'),
			'slide_bar'  => ee()->input->post('slide_bar')
		);

	}


	function save($data)
	{

		$r = array();

		if (isset($data['order'])) {
			$this->cache[$this->addon_name]['custom_fields'] = ee()->db->get_where('exp_mx_google_map_fields', array('site_id' => SITE_ID))->result_array();



			foreach ($data['order'] as $row_order => $marker_id)
			{
				$row = $data[$marker_id];

				$custom_fields_tmp = array ();

				foreach ($this->cache[$this->addon_name]['custom_fields'] as $custom_field)
				{
					$custom_fields_tmp[$custom_field['field_name']] = (!isset($row[$custom_field['field_name']])) ? '' : $row[$custom_field['field_name']];
				}

				$this->cache[$this->addon_name][$this->field_id][$marker_id] = array_merge(array(
						'point_id' => $marker_id,
						'latitude' => $row['lat'],
						'longitude' => $row['long'],
						'icon' => $row['icon']
					), $custom_fields_tmp);


			}

		}
		else {$data['field_data'] = "";};

		return $data['field_data'];
	}

	// --------------------------------------------------------------------

	/**
	 *
	 *
	 * @access public
	 * @return
	 *
	 */

	function GetLatLong($query, $mode){
		$xml_url = "http://maps.google.com/maps/geo?output=xml&q=$query&ie=utf-8&oe=utf-8";

		if (ini_get('allow_url_fopen')) {
			$xml = @simplexml_load_file($xml_url);
		}
		else {
			$ch = curl_init($xml_url);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$xml_raw = curl_exec($ch);
			$xml = simplexml_load_string($xml_raw);
		}

		if (is_object($xml) and ($xml instanceof SimpleXMLElement) and (int) $xml->Response->Status->code === 200)
		{
			$out = ($mode == 1) ?  $xml->Response->Placemark->address :  explode(',', $xml->Response->Placemark->Point->coordinates);
			return $out ;
		}
		else
		{
			return false;
		}

	}
	// --------------------------------------------------------------------

	/**
	 * Handles any custom logic after an entry is saved.
	 *
	 * @access public
	 * @return
	 *
	 */

	function post_save($data)
	{

		$id = $this->settings['entry_id'];
		ee()->db->where('entry_id', $id);

		if ($data != ""){
			ee()->db->update('exp_channel_data', array('field_id_'.$this->settings['field_id'] => $data));
		}

		if  (!isset($this->cache[$this->addon_name]['entry_id']))  {

			if (!isset($this->cache[$this->addon_name]['sql_request'])) {

				$query = ee()->db->get_where('exp_mx_google_map', array('entry_id' => $this->settings['entry_id']))->result_array();

				$this->cache[$this->addon_name]['sql_request'] = array();

				foreach ($query as $row) {
					$this->cache[$this->addon_name]['sql_request'][] = $row['point_id'];
				}

				ee()->db->query('DELETE FROM  exp_mx_google_map WHERE entry_id = '.$id.'');

			}

			$this->cache[$this->addon_name]['entry_id'] = true;
		}


		if (isset($this->cache[$this->addon_name][$this->field_id])) {
			foreach ($this->cache[$this->addon_name][$this->field_id] as $row)
			{

				$point  = $row;
				$point ['point_id'] = (in_array($row['point_id'],$this->cache[$this->addon_name]['sql_request'])) ? $row['point_id'] : null;
				$point ['entry_id'] = $this->settings['entry_id'];
				$point ['field_id'] = $this->field_id;

				ee()->db->query(ee()->db->insert_string('exp_mx_google_map', $point));

			}

		}

	}




	// --------------------------------------------------------------------

	/**
	 * Install Fieldtype
	 *
	 * @access public
	 * @return default global settings
	 *
	 */
	function install()
	{

		ee()->db->query("CREATE TABLE  IF NOT EXISTS  exp_mx_google_map (
							  `point_id` int(10) unsigned NOT NULL auto_increment,
							  `entry_id`     varchar(10)             NOT NULL default '',
							  `latitude`        varchar(50)      NOT NULL default '',
							  `longitude`      varchar(50)      NOT NULL default '',
							  `address`      varchar(50)      NOT NULL default '',
							  `city`      varchar(50)      NOT NULL default '',
							  `zipcode`      varchar(50)      NOT NULL default '',
							  `state`      varchar(50)      NOT NULL default '',
							  `field_id`      varchar(10)        NOT NULL default '',
							  `icon`      varchar(128)        NOT NULL default '',
							  PRIMARY KEY (`point_id`)
							)");





		return array(
			'latitude' => '44.06193297865348',
			'longitude' => '-121.27584457397461',
			'zoom'  => 13,
			'max_points' => '3',
			'icon' => '',
			'slide_bar' => 'y',
			'path_markers_icons' => (ee()->config->item('mx_markers_path')) ? ee()->config->item('mx_markers_path') : reduce_double_slashes(ee()->config->item('path_third_themes').'/mx_google_map/maps-icons/'),
			'url_markers_icons' => (ee()->config->item('mx_markers_url')) ? ee()->config->item('mx_markers_url') : reduce_double_slashes(ee()->config->item('url_third_themes').'/mx_google_map/maps-icons/')
		);
	}

	private function _insert_js($js)
	{
		ee()->cp->add_to_foot('<script type="text/javascript">'.$js.'</script>');
	}

	// --------------------------------------------------------------------

	/**
	 * Uninstall Fieldtype
	 *
	 */
	function uninstall()
	{
		ee()->db->query("DROP TABLE exp_mx_google_map");

		return TRUE;
	}

	function delete($entry_ids)
	{
		ee()->db->where_in('entry_id', $entry_ids);
		ee()->db->delete('exp_mx_google_map');
	}

	// --------------------------------------------------------------------

	/**
	 * Control Panel Javascript
	 *
	 * @access public
	 * @return void
	 *
	 */
	function _cp_js()
	{
		// This js is used on the global and regular settings
		// pages, but on the global screen the map takes up almost
		// the entire screen. So scroll wheel zooming becomes a hindrance.
		ee()->javascript->set_global('gmaps.scroll', ($_GET['C'] == 'content_admin'));
		ee()->cp->add_to_foot('<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>');
		ee()->cp->load_package_js('cp');
	}
}

/* End of file ft.mx_google_map.php */
/* Location: ./system/expressionengine/third_party/mx_google_map/ft.mx_google_map.php */
