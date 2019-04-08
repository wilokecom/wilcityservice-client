<?php

namespace WilcityServiceClient\Controllers;


use function GuzzleHttp\Psr7\str;
use WilcityServiceClient\Helpers\General;
use WilcityServiceClient\Helpers\RestApi;

class ScheduleCheckUpdateController {
	protected static $checkUpdateKey = 'wilcity_check_update';

	public function __construct() {
		add_action(self::$checkUpdateKey, array($this, 'checkUpdateTwiceDaily'));
		add_action('admin_notices', array($this, 'noticeHasUpdate'));
		add_action('admin_init', array($this, 'focusClearHasUpdate'));
	}

	public function focusClearHasUpdate(){
		if ( General::isWilcityServicePage() ){
			delete_option('wilcity_has_update');
		}
	}

	private function hasUpdate(){
		update_option('wilcity_has_update', true);
	}

	public function noticeHasUpdate(){
		if ( !get_option('wilcity_has_update') ){
			return false;
		}

		$class = 'notice notice-error';
		$message = 'There is a new update of Wilcity Theme or Wilcity\'s Plugins. Please click on <a href="'.admin_url('admin.php?page=wilcity-service').'">Wilcity Service</a> to update it';

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message );
	}

	private function buildPluginPathInfo($pluginID){
		return $pluginID . '/' . $pluginID . '.php';
	}

	public function checkUpdateTwiceDaily(){
		$oTheme = wp_get_theme();
		$themeAuthor = strtolower(trim($oTheme->get('Author')));

		if ( strpos($themeAuthor, 'wiloke') === false ){
			return false;
		}

		$aResponse = RestApi::get(WILCITYSERVICE_THEME_ENDPOIN);

		if ( $aResponse['status'] == 'success' ){
			$aPlugins = isset($aResponse['aPlugins']) ? $aResponse['aPlugins'] : false;
			$aTheme = isset($aResponse['aTheme']) ? $aResponse['aTheme'] : false;

			if ( $aTheme ){
				if ( version_compare($aTheme['version'], $oTheme->get('Version'), '>') ){
					$this->hasUpdate();
					return true;
				}
			}

			foreach ($aPlugins as $aPlugin){
				$aThisPluginInfoOnSite = get_plugin_data($this->buildPluginPathInfo($aPlugin['slug']));
				if ( empty($aThisPluginInfoOnSite) ){
					continue;
				}

				if ( version_compare($aPlugin['version'], $aThisPluginInfoOnSite['Version'], '>') ){
					$this->hasUpdate();
					return true;
				}
			}
		}
	}

	public static function setupCheckUpdateTwiceDaily(){
		if (! wp_next_scheduled ( self::$checkUpdateKey )) {
			wp_schedule_event(time(), 'twicedaily', self::$checkUpdateKey);
		}
	}

	public static function clearCheckUpdateTwiceDaily(){
		wp_clear_scheduled_hook(self::$checkUpdateKey);
	}
}