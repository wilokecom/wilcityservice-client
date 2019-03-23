<?php
namespace WilcityServiceClient\Helpers;

use function Sodium\compare;

class General {
	public static function isWilcityServicePage(){
		if ( !is_admin() || !isset($_GET['page']) || $_GET['page'] !== 'wilcity-service' ){
			return false;
		}

		return true;
	}

	public static function isNewVersion($newVersion, $currentVersion){
		return version_compare($newVersion, $currentVersion, '>');
	}
}