<?php
/**
 * Plugin Name: Wilcity Service Client
 * Plugin URI: https://wilcityservice.com/
 * Description: Wilcity Service
 * Version: 1.1.7
 * Author: Wiloke
 * Author URI: https://wiloke.com
 * Text Domain: wilcityservice
 * Domain Path: /i18n/languages/
 *
 * @package wilcity
 */
use WilcityServiceClient\Controllers\PluginController;
use WilcityServiceClient\Controllers\VerifyLicenseController;

define('WILCITYSERIVCE_CLIENT_DIR', plugin_dir_path(__FILE__));
define('WILCITYSERIVCE_CLIENT_SOURCE', plugin_dir_url(__FILE__).'source/');
define('WILCITYSERIVCE_CLIENT_ASSSETS', plugin_dir_url(__FILE__).'assets/');
define('WILCITYSERIVCE_VERSION', '1.1.6');
define('WILCITYSERVICE_PREVIEWURL', 'https://wilcity.com');
define('WILCITYSERVICE_THEME_ENDPOIN', 'themes/wilcity');
define('WILCITY_UPDATE_PORT', 'http://localhost:8888/wilcityservice/');
define('WILCITYSERVICE_DS', '/');
define('WILCITYSERVICE_PREFIX', 'wilcityservice');
define('WILCITYSERVICE_WEBSITE', 'https://wilcityservice.com');

require plugin_dir_path(__FILE__).'vendor/autoload.php';

register_activation_hook(__FILE__, 'wilcityServiceRegisterScheduleHook');
if (!function_exists('wilcityServiceRegisterScheduleHook')) {
    function wilcityServiceRegisterScheduleHook()
    {
        if (!wp_next_scheduled(WILCITYSERVICE_PREFIX.'_hourly_event')) {
            wp_schedule_event(time(), 'hourly', WILCITYSERVICE_PREFIX.'_hourly_event');
        }

	    if (!wp_next_scheduled(WILCITYSERVICE_PREFIX.'_daily_event')) {
		    wp_schedule_event(time(), 'daily', WILCITYSERVICE_PREFIX.'_daily_event');
	    }
    }
}
register_deactivation_hook(__FILE__, 'wilcityServiceUnRegisterScheduleHook');
if (!function_exists('wilcityServiceUnRegisterScheduleHook')) {
    function wilcityServiceUnRegisterScheduleHook()
    {
        wp_clear_scheduled_hook(WILCITYSERVICE_PREFIX.'_hourly_event');
        wp_clear_scheduled_hook(WILCITYSERVICE_PREFIX.'_daily_event');
    }
}

if (!function_exists('wilcityServiceGetConfigFile')) {
    function wilcityServiceGetConfigFile($file)
    {
        $aConfig = include plugin_dir_path(__FILE__).'configs/'.$file.'.php';

        return $aConfig;
    }
}

new \WilcityServiceClient\RegisterMenu\RegisterWilcityServiceMenu();
new \WilcityServiceClient\Controllers\Updates();
new \WilcityServiceClient\Controllers\ScheduleCheckUpdateController();
new \WilcityServiceClient\Controllers\DownloadController();
new \WilcityServiceClient\Controllers\NotificationController();
new \WilcityServiceClient\Controllers\Shortcodes();
new PluginController;
new VerifyLicenseController;

if (!function_exists('write_log')) {
	function write_log($log) {
		if (true === WP_DEBUG) {
			if (is_array($log) || is_object($log)) {
				error_log(print_r($log, true));
			} else {
				error_log($log);
			}
		}
	}
}

register_activation_hook(__FILE__,
  ['\WilcityServiceClient\Controllers\ScheduleCheckUpdateController', 'setupCheckUpdateTwiceDaily']);
register_deactivation_hook(__FILE__,
  ['\WilcityServiceClient\Controllers\ScheduleCheckUpdateController', 'clearCheckUpdateTwiceDaily']);

do_action('wilcity-service-loaded');

