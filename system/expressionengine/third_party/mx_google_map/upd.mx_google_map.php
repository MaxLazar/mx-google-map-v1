<?php  if ( ! defined( 'BASEPATH' ) ) exit( 'No direct script access allowed' );

require_once PATH_THIRD . 'mx_google_map/config.php';

/**
 * Google Map
 *
 * @package  Mx_google_map
 * @subpackage ThirdParty
 * @category Modules
 * @author  Max Lazar
 * @link  http://eec.ms
 */
class Mx_google_map_upd {

	var $version        = MX_GOOGLE_MAP_VERSION;
	var $module_name = MX_GOOGLE_MAP_PACKAGE;

	function __construct( $switch = TRUE ) {

		if ( defined( 'SITE_ID' ) == FALSE )
			define( 'SITE_ID', ee()->config->item( 'site_id' ) );
	}

	/**
	 * Installer for the Mx_google_map module
	 */
	function install() {

		$data = array(
			'module_name'   => $this->module_name,
			'module_version' => $this->version,
			'has_cp_backend' => 'y'
		);

		ee()->db->insert( 'modules', $data );


		if ( !ee()->db->table_exists( 'exp_mx_google_map_fields' ) ) {
			ee()->db->query( "CREATE TABLE IF NOT EXISTS exp_mx_google_map_fields (
							  `field_id` int(10) unsigned NOT NULL auto_increment,
							  `site_id` int(10) unsigned NOT NULL,
							  `field_name`     varchar(128)     NOT NULL default '',
							  `field_label`        varchar(128)    NOT NULL default '',
							  `field_type`      varchar(50)      NOT NULL default '',
							  `field_maxl`      varchar(50)     NOT NULL default '',
							  `field_pattern`      varchar(256)  NOT NULL default '',
							  `field_order`      int(10) ,
							  PRIMARY KEY (`field_id`)
							)" );
		};

		$default_fields = array( "address" => "Address", "city" => "City", "zipcode" => "Zip", "state" => "State" );

		foreach ( $default_fields as  $key => $value ) {
			$data = array(
				'field_id' => '',
				'field_name' => strtolower( $key ),
				'field_label' => $value,
				'site_id'  => SITE_ID
			);
			ee()->db->insert( 'exp_mx_google_map_fields', $data );
		};


		//
		// Add additional stuff needed on module install here
		//

		return TRUE;
	}


	/**
	 * Uninstall the Mx_google_map module
	 */
	function uninstall() {

		ee()->db->select( 'module_id' );
		$query = ee()->db->get_where( 'modules', array( 'module_name' => $this->module_name ) );

		ee()->db->where( 'module_id', $query->row( 'module_id' ) );
		ee()->db->delete( 'module_member_groups' );

		ee()->db->where( 'module_name', $this->module_name );
		ee()->db->delete( 'modules' );

		ee()->db->where( 'class', $this->module_name );
		ee()->db->delete( 'actions' );

		ee()->db->where( 'class', $this->module_name.'_mcp' );
		ee()->db->delete( 'actions' );

		ee()->db->query( "DROP TABLE exp_mx_google_map_fields" );

		return TRUE;
	}

	/**
	 * Update the Mx_google_map module
	 *
	 * @param unknown $current current version number
	 * @return boolean indicating whether or not the module was updated
	 */

	function update( $current = '' ) {
		if ( $current == '' ) {
			$default_fields = array( "address" => "Address", "city" => "City", "zipcode" => "Zip", "state" => "State" );
			foreach ( $default_fields as  $key => $value ) {
				$data = array(
					'field_id' => '',
					'field_name' => strtolower( $key ),
					'field_label' => $value,
					'field_pattern' => '',
					'site_id'  => SITE_ID
				);
				ee()->db->insert( 'exp_mx_google_map_fields', $data );
			};
			return TRUE;
		}

		if ( $current < $this->version ) {
			ee()->db->query( "UPDATE exp_fieldtypes SET has_global_settings = 'n' WHERE name = 'mx_google_map'" );
			return TRUE;
		}
		return FALSE;
	}

}

/* End of file upd.mx_google_map.php */
/* Location: ./system/expressionengine/third_party/mx_google_map/upd.mx_google_map.php */
