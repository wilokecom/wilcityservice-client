<?php
/**
 * Plugin Name: Wilcity Service Client
 * Plugin URI: https://wilcityservice.com/
 * Description: Wilcity Service
 * Version: 1.1
 * Author: Wiloke
 * Author URI: https://wiloke.com
 * Text Domain: wilcityservice
 * Domain Path: /i18n/languages/
 *
 * @package wilcity
 */

define('WILCITYSERIVCE_CLIENT_DIR', plugin_dir_path(__FILE__));
define('WILCITYSERIVCE_CLIENT_SOURCE', plugin_dir_url(__FILE__).'source/');
define('WILCITYSERIVCE_CLIENT_ASSSETS', plugin_dir_url(__FILE__).'assets/');
define('WILCITYSERIVCE_VERSION', '1.1');
define('WILCITYSERVICE_PREVIEWURL', 'https://wilcity.com');
define('WILCITYSERVICE_THEME_ENDPOIN', 'themes/wilcity');
define('WILCITYSERVICE_DS', '/');

require plugin_dir_path(__FILE__).'vendor/autoload.php';

register_activation_hook(__FILE__, 'wilcityServiceRegisterScheduleHook');
if (!function_exists('wilcityServiceRegisterScheduleHook')) {
    function wilcityServiceRegisterScheduleHook()
    {
        if (!wp_next_scheduled('wilcityservice_hourly_event')) {
            wp_schedule_event(time(), 'hourly', 'wilcityservice_hourly_event');
        }
    }
}
register_deactivation_hook(__FILE__, 'wilcityServiceUnRegisterScheduleHook');
if (!function_exists('wilcityServiceUnRegisterScheduleHook')) {
    function wilcityServiceUnRegisterScheduleHook()
    {
        wp_clear_scheduled_hook('wilcityservice_hourly_event');
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

register_activation_hook(__FILE__,
  ['\WilcityServiceClient\Controllers\ScheduleCheckUpdateController', 'setupCheckUpdateTwiceDaily']);
register_deactivation_hook(__FILE__,
  ['\WilcityServiceClient\Controllers\ScheduleCheckUpdateController', 'clearCheckUpdateTwiceDaily']);

do_action('wilcity-service-loaded');

