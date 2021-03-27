<?php


namespace WilcityServiceClient\Controllers;


use WilcityServiceClient\Helpers\RestApi;

class VerifyLicenseController
{
	public function __construct()
	{
		add_action(WILCITYSERVICE_PREFIX.'_daily_event', [$this, 'verifyLicense']);
		add_action('admin_init', [$this, 'focusVerify'], 99);
	}

	public function focusVerify()
	{
		if (isset($_REQUEST['is-refresh-update']) && $_REQUEST['is-refresh-update'] === 'yes') {
			$this->verifyLicense();
		}
	}

	public function verifyLicense()
	{
		$aRequest = RestApi::post('plugins/verify-license', []);

		if ($aRequest['status'] == 'error') {
			if ($aRequest['plugins'] === 'all') {
				update_option(
					'expired_plugins',
					[
						'plugins' => 'all',
						'msg'     => $aRequest['msg']
					]
				);
			} else {
				update_option(
					'expired_plugins',
					[
						'plugins' => $aRequest['plugins']
					]
				);
			}
		} else {
			delete_option('expired_plugins');
		}
	}
}
