<?php
/**
 * Plugin Name: Wilcity Service Client
 * Plugin URI: https://wilcityservice.com/
 * Description: Wilcity Service
 * Version: 1.0.1
 * Author: Wiloke
 * Author URI: https://wiloke.com
 * Text Domain: wilcityservice
 * Domain Path: /i18n/languages/
 *
 * @package wilcity
 */

define('WILCITYSERIVCE_CLIENT_SOURCE', plugin_dir_url(__FILE__) . 'source/');
define('WILCITYSERIVCE_CLIENT_ASSSETS', plugin_dir_url(__FILE__) . 'assets/');
define('WILCITYSERIVCE_VERSION', '1.0');
define('WILCITYSERVICE_PREVIEWURL', 'https://wilcity.com');
define('WILCITYSERVICE_THEME_ENDPOIN', 'themes/wilcity');

require plugin_dir_path(__FILE__) . 'vendor/autoload.php';

function wilcityServiceGetConfigFile($file){
	$aConfig = include plugin_dir_path(__FILE__) . 'configs/'.$file.'.php';
	return $aConfig;
}

new \WilcityServiceClient\RegisterMenu\RegisterWilcityServiceMenu();
new \WilcityServiceClient\Controllers\Updates();
new \WilcityServiceClient\Controllers\ScheduleCheckUpdateController();

register_activation_hook(__FILE__, array('\WilcityServiceClient\Controllers\ScheduleCheckUpdateController', 'setupCheckUpdateTwiceDaily'));
register_deactivation_hook(__FILE__, array('\WilcityServiceClient\Controllers\ScheduleCheckUpdateController', 'clearCheckUpdateTwiceDaily'));
