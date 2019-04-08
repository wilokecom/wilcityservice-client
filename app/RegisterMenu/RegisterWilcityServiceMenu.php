<?php

namespace WilcityServiceClient\RegisterMenu;


use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\SemanticUi;
use WilokeListingTools\Framework\Helpers\SetSettings;

class RegisterWilcityServiceMenu {
	public static $optionKey = 'wilcityservice_client';
	public function __construct() {
		add_action('admin_menu', array($this, 'registerMenu'));
		add_action('admin_enqueue_scripts', array($this, 'enqueueScripts'), 999);
	}

	private function isWilcityServiceArea(){
		if ( is_admin() && isset($_GET['page']) && $_GET['page'] == 'wilcity-service' ){
			return true;
		}
		return false;
	}

	public function enqueueScripts(){
		if ( !$this->isWilcityServiceArea() ){
			return false;
		}
		wp_register_style('semantic-ui', WILOKE_LISTING_TOOL_URL . 'admin/assets/semantic-ui/form.min.css');
		wp_enqueue_style('semantic-ui');
		wp_register_script('semantic-ui', WILOKE_LISTING_TOOL_URL . 'admin/assets/semantic-ui/semantic.min.js', array('jquery'), null, true);
		wp_enqueue_script('semantic-ui');


		wp_enqueue_script('wilcityclient-service', WILCITYSERIVCE_CLIENT_SOURCE . 'script.js', array('jquery'), WILCITYSERIVCE_VERSION);
	}

	public function registerMenu(){
		add_menu_page('Wilcity Service', 'wilcity-service', 'administrator', 'wilcity-service', array($this, 'settings'), 'dashicons-share-alt');
	}

	private function saveConfiguration(){
		if ( !current_user_can('administrator') ){
			return false;
		}

		if ( (isset($_POST['wilcityservice_client']) && !empty($_POST['wilcityservice_client'])) &&  isset($_POST['wilcityservice_client_nonce_field']) && !empty($_POST['wilcityservice_client_nonce_field']) && wp_verify_nonce($_POST['wilcityservice_client_nonce_field'], 'wilcityservice_client_nonce_action') ){
			$aOptions = $_POST['wilcityservice_client'];

			foreach ($aOptions as $key => $val ){
				$aOptions[$key] = sanitize_text_field($val);
			}

			SetSettings::setOptions(self::$optionKey, $aOptions);
		}
	}

	private function fsMethodNotification(){
	    if ( defined('FS_METHOD') && FS_METHOD !== 'direct' ){
//	        SemanticUi
        }
    }

	public function settings(){
		$this->saveConfiguration();
		$aConfiguration = wilcityServiceGetConfigFile('settings');
		$aValues = GetSettings::getOptions(self::$optionKey);

		do_action('wilcityservice-clients/theme-updates');

		?>
		<form action="<?php echo admin_url('admin.php?page=wilcity-service&is-refresh-update=yes'); ?>" method="POST" class="form ui" style="margin-top: 20px;">
			<?php
			wp_nonce_field('wilcityservice_client_nonce_action', 'wilcityservice_client_nonce_field');

			foreach ($aConfiguration['fields'] as $aField ) :
				if ( !in_array($aField['type'], array('open_segment', 'close_segment', 'submit')) ){
					$aField['value'] = isset($aValues[$aField['id']]) ? $aValues[$aField['id']] : '';
				}

				switch ($aField['type']){
					case 'open_segment';
						SemanticUi::renderOpenSegment($aField);
						break;
					case 'open_accordion';
						SemanticUi::renderOpenAccordion($aField);
						break;
					case 'open_fields_group';
						SemanticUi::renderOpenFieldGroup($aField);
						break;
					case 'close';
						SemanticUi::renderClose();
						break;
					case 'close_segment';
						SemanticUi::renderCloseSegment();
						break;
					case 'password':
						SemanticUi::renderPasswordField($aField);
						break;
					case 'text';
						SemanticUi::renderTextField($aField);
						break;
					case 'select_post';
					case 'select_ui';
						SemanticUi::renderSelectUiField($aField);
						break;
					case 'select':
						SemanticUi::renderSelectField($aField);
						break;
					case 'textarea':
						SemanticUi::renderTextareaField($aField);
						break;
					case 'submit':
						SemanticUi::renderSubmitBtn($aField);
						break;
					case 'header':
						SemanticUi::renderHeader($aField);
						break;
					case 'desc';
						SemanticUi::renderDescField($aField);
						break;
				}
			endforeach;
			?>
		</form>
		<?php
	}
}