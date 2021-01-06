<?php


namespace WilcityServiceClient\Controllers;


use WilcityServiceClient\Helpers\RestApi;

class PluginController
{
	public function __construct()
	{
		add_action('admin_init', [$this, 'focusStatistic'], 99);
		add_action(WILCITYSERVICE_PREFIX.'_daily_event', [$this, 'registerRouter']);
	}

	public function focusStatistic()
	{
		if (isset($_REQUEST['is-refresh-update']) && $_REQUEST['is-refresh-update'] === 'yes') {
			$this->registerRouter();
		}
	}

	public function registerRouter()
	{
		$aPlugins = get_option('wilcity_plugins');
		if (empty($aPlugins)) {
			return false;
		}

		$aPremiums = [];
		$aFree = [];
		foreach ($aPlugins as $aPlugin) {
			$path = $aPlugin['slug'] . '/' . $aPlugin['slug'] . '.php';
			if (is_plugin_active($path)) {
				if ($aPlugin['productType'] == 'premium') {
					$aPremiums[] = $aPlugin['slug'];
				} else {
					$aFree[] = $aPlugin['slug'];
				}
			}
		}

		RestApi::post('statistic/using-plugins', [
			'premium_plugins' => $aPremiums,
			'free_plugins'    => $aFree
		]);
	}
}