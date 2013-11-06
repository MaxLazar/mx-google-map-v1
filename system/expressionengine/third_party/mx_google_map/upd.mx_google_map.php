<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');



/**
 * Google Map 
 *
 * @package		Mx_google_map
 * @subpackage	ThirdParty
 * @category	Modules
 * @author		Max Lazar
 * @link		http://eec.ms
 */
class Mx_google_map_upd {
		
	var $version        = '1.5.3'; 
	var $module_name = "Mx_google_map";
	
    function Mx_google_map_upd( $switch = TRUE ) 
    { 
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
		if(defined('SITE_ID') == FALSE)
		define('SITE_ID', $this->EE->config->item('site_id'));
    } 

    /**
     * Installer for the Mx_google_map module
     */
    function install() 
	{				
						
		$data = array(
			'module_name' 	 => $this->module_name,
			'module_version' => $this->version,
			'has_cp_backend' => 'y'
		);

		$this->EE->db->insert('modules', $data);		
		
		
		if (!$this->EE->db->table_exists('exp_mx_google_map_fields'))
		{
			$this->EE->db->query("CREATE TABLE IF NOT EXISTS exp_mx_google_map_fields (
							  `field_id` int(10) unsigned NOT NULL auto_increment,
							  `site_id` int(10) unsigned NOT NULL,
							  `field_name`     varchar(128)     NOT NULL default '',
							  `field_label`        varchar(128)    NOT NULL default '',
							  `field_type`      varchar(50)      NOT NULL default '',
							  `field_maxl`      varchar(50)     NOT NULL default '',
							  `field_pattern`      varchar(256)  NOT NULL default '',
							  `field_order`      int(10) ,
							  PRIMARY KEY (`field_id`)
							)");
		};
		
		$default_fields = array("address" => "Address", "city" => "City", "zipcode" => "Zip", "state" => "State");
		
		foreach ($default_fields as  $key => $value){
				$data = array(
										   'field_id' => '',
										   'field_name' => strtolower($key),
										   'field_label' =>	$value,
										   'site_id'  => SITE_ID
									);
				$this->EE->db->insert('exp_mx_google_map_fields', $data); 
			};

		
		//
		// Add additional stuff needed on module install here
		// 

		return TRUE;
	}

	
	/**
	 * Uninstall the Mx_google_map module
	 */
	function uninstall() 
	{ 				
		
		$this->EE->db->select('module_id');
		$query = $this->EE->db->get_where('modules', array('module_name' => $this->module_name));
		
		$this->EE->db->where('module_id', $query->row('module_id'));
		$this->EE->db->delete('module_member_groups');
		
		$this->EE->db->where('module_name', $this->module_name);
		$this->EE->db->delete('modules');
		
		$this->EE->db->where('class', $this->module_name);
		$this->EE->db->delete('actions');
		
		$this->EE->db->where('class', $this->module_name.'_mcp');
		$this->EE->db->delete('actions');
		
		$this->EE->db->query("DROP TABLE exp_mx_google_map_fields");
										
		return TRUE;
	}
	
	/**
	 * Update the Mx_google_map module
	 * 
	 * @param $current current version number
	 * @return boolean indicating whether or not the module was updated 
	 */
	
	function update($current = '')
	{
		if ($current == '' ){
			$default_fields = array("address" => "Address", "city" => "City", "zipcode" => "Zip", "state" => "State");
			foreach ($default_fields as  $key => $value){
				$data = array(
										   'field_id' => '',
										   'field_name' => strtolower($key),
										   'field_label' =>	$value,
										   'field_pattern' =>	'',
										   'site_id'  => SITE_ID
									);
				$this->EE->db->insert('exp_mx_google_map_fields', $data); 
			};
			return TRUE;
		}

		if ($current < $this->version ){
			$this->EE->db->query("UPDATE exp_fieldtypes SET has_global_settings = 'n' WHERE name = 'mx_google_map'");
			return TRUE;
		}
		return FALSE;
	}
    
}

/* End of file upd.mx_google_map.php */ 
/* Location: ./system/expressionengine/third_party/mx_google_map/upd.mx_google_map.php */ 