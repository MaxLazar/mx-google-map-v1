<?php
if (! defined('MX_GOOGLE_MAP_PACKAGE'))
{
	define('MX_GOOGLE_MAP_NAME', 'MX Google Map');
	define('MX_GOOGLE_MAP_VERSION',  '1.5.3.141002');
	define('MX_GOOGLE_MAP_PACKAGE', 'Mx_google_map');
	define('MX_GOOGLE_MAP_AUTHOR',  'Max Lazar');
	define('MX_GOOGLE_MAP_DOCS',  '');
	define('MX_GOOGLE_MAP_DESC',  '');
	define('MX_GOOGLE_MAP_DEBUG',    FALSE);

}

/**
 * < EE 2.6.0 backward compat
 */

if ( ! function_exists('ee'))
{
	function ee()
	{
		static $EE;
		if ( ! $EE) $EE = get_instance();
		return $EE;
	}
}


/* End of file config.php */
/* Location: ./system/expressionengine/third_party/MX_GOOGLE_MAP/config.php */