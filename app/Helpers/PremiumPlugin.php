<?php


namespace WilcityServiceClient\Helpers;

class PremiumPlugin
{
	public static function isExpired($pluginName)
	{
		$aExpiredPlugins = get_option('expired_plugins');
		if (!isset($aExpiredPlugins['plugins']) || !is_array($aExpiredPlugins['plugins'])) {
			return false;
		}
		return isset($aExpiredPlugins['plugins'][$pluginName]) ? $aExpiredPlugins['plugins'][$pluginName] : false;
	}

	public static function getExpiryMsg($pluginName)
	{
		$aExpiredPlugins = get_option('expired_plugins');

		if (empty($aExpiredPlugins)) {
			return '';
		}

		if (isset($aExpiredPlugins['plugins'])) {
			if ($aExpiredPlugins['plugins'] === 'all') {
				return $aExpiredPlugins['msg'];
			}

			if (isset($aExpiredPlugins['plugins'][$pluginName])) {
				return $aExpiredPlugins['plugins'][$pluginName]['msg'];
			}
		}

		return '';
	}
}