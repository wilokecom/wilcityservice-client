<?php

namespace WilcityServiceClient\Controllers;

use WilcityServiceClient\Helpers\Download;
use WilcityServiceClient\Helpers\GetSettings;

class DownloadController
{
    private $extPath;
    private $pluginPath;
    
    public function __construct()
    {
        add_action('wp_ajax_wiloke_download_plugin', [$this, 'downloadPlugin']);
        add_action('wp_ajax_wiloke_activate_plugin', [$this, 'activatePlugin']);
        add_action('wp_ajax_wiloke_deactivate_plugin', [$this, 'deactivatePlugin']);
        add_action('upgrader_package_options', [$this, 'addBearTokenToDownloadURL']);
        //        add_filter('wp_signature_hosts', [$this, 'addSignatureHosts']);
        $this->setPaths();
    }
    
    public function addBearTokenToDownloadURL($aOptions)
    {
        if (isset($aOptions['package']) && strpos($aOptions['package'], WILCITY_UPDATE_PORT) !== false) {
            $aOptions['package'] = add_query_arg(
              [
                'token' => GetSettings::getOptionField('secret_token')
              ],
              $aOptions['package']
            );
        }
        
        return $aOptions;
    }
    
    public function addSignatureHosts($aHosts)
    {
        $aHosts[] = 'wilcityservice.com';
        
        return $aHosts;
    }
    
    private function setPaths()
    {
        $this->extPath    = WP_CONTENT_DIR.WILCITYSERVICE_DS.'uploads'.WILCITYSERVICE_DS.'wilcity_extensions'
                            .WILCITYSERVICE_DS;
        $this->pluginPath = dirname(WILCITYSERIVCE_CLIENT_DIR).WILCITYSERVICE_DS;
    }
    
    private function unZipFile($package, $isLive = true)
    {
        WP_Filesystem();
        $status = unzip_file($package, Download::pluginsDirPath());
        if ($isLive) {
            @unlink($package);
        }
        
        if ($status) {
            return true;
        }
        
        return false;
    }
    
    protected function verify()
    {
        if (!check_ajax_referer('wiloke-service-nonce', 'security', false)) {
            wp_send_json_error([
              'msg' => 'Invalid Security code'
            ]);
        }
        
        if (!current_user_can('administrator')) {
            wp_send_json_error([
              'msg' => 'You do not have permission to access this page'
            ]);
        }
    }
    
    public function deactivatePlugin()
    {
        $this->verify();
        
        $current = get_option('active_plugins');
        $plugin  = trim($_POST['plugin']);
        if (in_array($plugin, $current)) {
            $current = array_flip($current);
            unset($current[$plugin]);
            $current = array_keys($current);
            
            update_option('active_plugins', $current);
            wp_send_json_success(['msg' => 'Congrats, The plugin has been deactivated']);
        }
        
        wp_send_json_success(['msg' => 'The plugin was disabled already']);
    }
    
    public function activatePlugin()
    {
        $this->verify();
        
        $current = get_option('active_plugins');
        $plugin  = trim($_POST['plugin']);
        
        if (!in_array($plugin, $current)) {
            $current[] = $plugin;
            sort($current);
            update_option('active_plugins', $current);
            
            wp_send_json_success(['msg' => 'Congrats, The plugin has been activated']);
        }
        
        wp_send_json_success(['msg' => 'The plugin is activating already']);
    }
    
    public function downloadPlugin()
    {
        $this->verify();
        
        $downloadEndpoint = Download::downloadPluginUrl($_POST['plugin']);
        $pluginZipFile    = Download::createPluginZipPlaceholder($_POST['plugin']);
        if (!$this->extPath) {
            wp_send_json_error([
              'msg' => 'Error: Could not create extensions folder'
            ]);
        }
        
        $download = download_url($downloadEndpoint);
        if (is_wp_error($download)) {
            wp_send_json_error(
              [
                'msg' => $download->get_error_message()
              ]
            );
        }
        
        $extractedFile = $this->unZipFile($download, false);
        if ($extractedFile !== true) {
            wp_send_json_error(
              [
                'msg' => 'Could not open file '.$pluginZipFile
              ]
            );
        }
        
        wp_send_json_success(['msg' => 'Congrats, The plugin has been downloaded successfully, please click on Activate button to active this plugin']);
    }
}
