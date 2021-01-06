<?php
namespace WilcityServiceClient\Helpers;

use function Sodium\compare;

class General {
	public static function isServicePage(){
		if ( !is_admin() || !isset($_GET['page']) || $_GET['page'] !== 'wilcity-service' ){
			return false;
		}

		return true;
	}

	public static function isNewVersion($newVersion, $currentVersion){
		return version_compare($newVersion, $currentVersion, '>');
	}

	public static function ksesHtml($content, $isReturn=false){
		$allowed_html = array(
			'a' => array(
				'href'  => array(),
				'style' => array(
					'color' => array()
				),
				'title' => array(),
				'target'=> array(),
				'class' => array(),
				'data-msg' => array()
			),
			'div'    => array('class'=>array()),
			'h1'     => array('class'=>array()),
			'h2'     => array('class'=>array()),
			'h3'     => array('class'=>array()),
			'h4'     => array('class'=>array()),
			'h5'     => array('class'=>array()),
			'h6'     => array('class'=>array()),
			'br'     => array('class' => array()),
			'p'      => array('class' => array(), 'style'=>array()),
			'em'     => array('class' => array()),
			'strong' => array('class' => array()),
			'span'   => array('data-typer-targets'=>array(), 'class' => array()),
			'i'      => array('class' => array()),
			'ul'     => array('class' => array()),
			'ol'     => array('class' => array()),
			'li'     => array('class' => array()),
			'code'   => array('class'=>array()),
			'pre'    => array('class' => array()),
			'iframe' => array('src'=>array(), 'width'=>array(), 'height'=>array(), 'class'=>array('embed-responsive-item')),
			'img'    => array('src'=>array(), 'width'=>array(), 'height'=>array(), 'class'=>array(), 'alt'=>array()),
			'embed'  => array('src'=>array(), 'width'=>array(), 'height'=>array(), 'class' => array()),
		);

		if ( !$isReturn ) {
			echo wp_kses(wp_unslash($content), $allowed_html);
		}else{
			return wp_kses(wp_unslash($content), $allowed_html);
		}
	}
}