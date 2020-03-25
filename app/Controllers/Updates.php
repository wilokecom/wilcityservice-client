<?php
namespace WilcityServiceClient\Controllers;

use WilcityServiceClient\Helpers\GetSettings;
use function Sodium\compare;
use WilcityServiceClient\Helpers\General;
use WilcityServiceClient\Helpers\RestApi;

class Updates
{
    private $aPlugins;
    private $aTheme;
    private $isFocusGetUpdates = false;
    private $aResponse;
    private $responseCode;
    private $aInstalledPlugins;
    private $aInstalledThemes;
    private $oCurrentThemeVersion = null;
    private $cacheUpdateKeys = 'wilcity_cache_updates';
    private $saveUpdateInfoIn = 300;
    private $changeLogURL = 'https://wiloke.net/themes/changelog/8';
    private $phpRequired = '7.2';
    private $restURL = 'themes/wilcity_test_theme_update';
    private $aStatusCodeNoNeedToPrintUpdate = ['CLIENT_WEBSITE_IS_INVALID', 'INVALID_TOKEN', 'IP_BLOCKED'];
    public $errMgs = '';
    
    public function __construct()
    {
        add_action('admin_init', [$this, 'getUpdates'], 1);
        
        add_filter('http_request_args', [$this, 'updateCheck'], 5, 2);
        add_action('wilcityservice-clients/theme-updates', [$this, 'openUpdateForm'], 1);
        add_action('wilcityservice-clients/theme-updates', [$this, 'showUpTheme']);
        add_action('wilcityservice-clients/theme-updates', [$this, 'showUpPlugins'], 20);
        add_action('wilcityservice-clients/theme-updates', [$this, 'closeUpdateForm'], 30);
        add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);
        
        add_filter('pre_set_site_transient_update_plugins', [$this, 'updatePlugins']);
        add_filter('pre_set_transient_update_plugins', [$this, 'updatePlugins']);
        
        add_filter('pre_set_site_transient_update_themes', [$this, 'updateThemes'], 1, 99999);
        add_filter('pre_set_transient_update_themes', [$this, 'updateThemes'], 1, 99999);
        
        add_action('admin_init', [$this, 'checkUpdatePluginDirectly'], 10);
        add_filter('http_request_args', [$this, 'addBearTokenToHeaderDownload'], 10, 2);
        
        add_action('wp_ajax_wiloke_reupdate_response_of_theme', [$this, 'reUpdateResponseOfTheme']);
        add_action('wp_ajax_wiloke_reupdate_response_of_plugins', [$this, 'reUpdateResponseOfPlugins']);
        add_action('admin_init', [$this, 'clearUpdatePluginTransients'], 1);
        
        add_action('after_switch_theme', [$this, 'afterSwitchTheme']);
        add_action('activated_plugin', [$this, 'afterActivatePlugin']);
    }
    
    public function clearUpdatePluginTransients()
    {
        global $pagenow;
        if (($pagenow == 'plugins.php' || $pagenow == 'network-plugins.php' || $pagenow == 'update-core.php' ||
             $pagenow == 'network-update-core.php') && !General::isWilcityServicePage()
        ) {
            if (get_option('wiloke_clear_update_plugins')) {
                delete_site_transient('update_plugins');
                delete_option('wiloke_clear_update_plugins');
            }
        }
    }
    
    public function addBearTokenToHeaderDownload($r, $url)
    {
        if (strpos($url, RestApi::getUpdateServiceURL()) !== false) {
            $r['headers']['authorization'] = RestApi::getBearToken();
            $r['headers']['cache-control'] = 'no';
        }
        
        return $r;
    }
    
    private function isFocus()
    {
        return (isset($_REQUEST['is-refresh-update']) && $_REQUEST['is-refresh-update'] == 'yes') ||
               $this->isFocusGetUpdates;
    }
    
    private function _getUpdates()
    {
        if (!$this->isFocus()) {
            $this->aResponse = get_transient($this->cacheUpdateKeys);
            if (!empty($this->aResponse)) {
                if ($this->aResponse['status'] == 'error') {
                    $this->aPlugins = [];
                    $this->aTheme   = [];
                    $this->errMgs   = isset($this->aResponse['msg']) ? $this->aResponse['msg'] : '';
                } else {
                    $this->aPlugins = $this->aResponse['aPlugins'];
                    $this->aTheme   = $this->aResponse['aTheme'];
                }
                $this->responseCode = isset($this->aResponse['code']) ? $this->aResponse['code'] : 'OKE';
                
                return $this->aResponse;
            }
        }
        
        $this->aResponse    = RestApi::get(WILCITYSERVICE_THEME_ENDPOIN);
        $this->responseCode = isset($this->aResponse['code']) ? $this->aResponse['code'] : 'OKE';
        if ($this->aResponse['status'] == 'success') {
            $aRawPlugins = $this->aResponse['aPlugins'];
            foreach ($aRawPlugins as $aPlugin) {
                $this->aPlugins[$this->buildPluginPathInfo($aPlugin['slug'])] = $aPlugin;
            }
            $this->aTheme = $this->aResponse['aTheme'];
            set_transient($this->cacheUpdateKeys, $this->aResponse, $this->saveUpdateInfoIn);
        } else {
            $this->aPlugins            = false;
            $this->aTheme              = false;
            $this->aResponse['status'] = 'error';
            $this->errMgs              = isset($this->aResponse['msg']) ? $this->aResponse['msg'] : '';
            set_transient($this->cacheUpdateKeys, $this->aResponse, $this->saveUpdateInfoIn * 20);
        }
    }
    
    /**
     * Disables requests to the wp.org repository for Envato Market.
     *
     * @param array  $request An array of HTTP request arguments.
     * @param string $url     The request URL.
     *
     * @return array
     * @since 1.0.0
     *
     */
    public function updateCheck($aRequest, $url)
    {
        
        // Plugin update request.
        if (false !== strpos($url, '//api.wordpress.org/plugins/update-check/1.1/')) {
            
            // Decode JSON so we can manipulate the array.
            $oData = json_decode($aRequest['body']['plugins']);
            
            // Remove the Envato Market.
            unset($oData->plugins->{'wilcityservice-client/wilcityservice-client.php'});
            
            // Encode back into JSON and update the response.
            $aRequest['body']['plugins'] = wp_json_encode($oData);
        }
        
        return $aRequest;
    }
    
    /**
     * Check Github for an update.
     *
     * @return false|object
     * @since 1.0.0
     *
     */
    public function api_check()
    {
        $raw_response = wp_remote_get(self::$api_url);
        if (is_wp_error($raw_response)) {
            return false;
        }
        
        if (!empty($raw_response['body'])) {
            $raw_body = json_decode($raw_response['body'], true);
            if ($raw_body) {
                return (object)$raw_body;
            }
        }
        
        return false;
    }
    
    /*
     * Updating Wilcity Plugins To List of Updated Plugins
     */
    private function directlyUpdatePlugins()
    {
        if (empty($this->aPlugins)) {
            return false;
        }
        
        $oListPluginsInfo           = new \stdClass();
        $oListPluginsInfo->response = [];
        $oListPluginsInfo->checked  = [];
        
        $this->getListOfInstalledPlugins();
        
        $hasUpdate       = false;
        $oUpdatesPlugins = get_site_transient('update_plugins');
        
        foreach ($this->aInstalledPlugins as $file => $aPlugin) {
            if (!isset($this->aPlugins[$file]) || empty($this->aPlugins[$file])) {
                if (isset($oUpdatesPlugins->checked) && isset($oUpdatesPlugins->checked[$file])) {
                    $oListPluginsInfo->checked[$file] = $oUpdatesPlugins->checked[$file];
                    if (isset($oUpdatesPlugins->response) && is_array($oUpdatesPlugins->response)) {
                        if (isset($oUpdatesPlugins->response[$file])) {
                            $oListPluginsInfo->response[$file] = $oUpdatesPlugins->response[$file];
                        }
                    }
                } else {
                    $oListPluginsInfo->checked[$file] = $aPlugin['Version'];
                }
            } else {
                if (!isset($oListPluginsInfo->checked[$file]) || version_compare($aPlugin['Version'],
                    $this->aPlugins[$file]['version'], '<')
                ) {
                    $oListPluginsInfo->response[$file] = $this->buildUpdatePluginSkeleton($this->aPlugins[$file]);
                    $oListPluginsInfo->checked[$file]  = $this->aInstalledPlugins[$file]['Version'];
                    $hasUpdate                         = true;
                }
            }
        }
        
        if ($hasUpdate) {
            $oListPluginsInfo->last_checked = strtotime('+30 minutes');
            set_site_transient('update_plugins', $oListPluginsInfo);
            update_option('wiloke_clear_update_plugins', true);
            $this->setLastCheckedUpdatePlugins();
        }
    }
    
    public function directlyUpdateTheme()
    {
        if (empty($this->aTheme)) {
            return false;
        }
        
        $oMyTheme = wp_get_theme($this->aTheme['slug']);
        
        if (!$oMyTheme->exists()) {
            return false;
        }
        
        if (version_compare($oMyTheme->get('Version'), $this->aTheme['version'], '<')) {
            //            if (class_exists('Jetpack_Frame_Nonce_Preview')) {
            //                $oListThemesInfo           = new \stdClass();
            //                $oListThemesInfo->response = [];
            //                $oListThemesInfo->checked  = [];
            //
            //                $oTheme['theme']       = $this->aTheme['slug'];
            //                $oTheme['new_version'] = $this->aTheme['version'];
            //                $oTheme['package']     = $this->aTheme['download'];
            //
            //                $oListThemesInfo->response[$this->aTheme['slug']]   = $oTheme;
            //                $oListThemesInfo->checked[$this->aTheme['version']] = $oMyTheme->get('Version');
            //                $oListThemesInfo->last_checked                      = strtotime('+30 minutes');
            //            } else {
            $oListThemesInfo           = new \stdClass();
            $oListThemesInfo->response = [];
            $oListThemesInfo->checked  = [];
            
            $oTheme['theme']       = $this->aTheme['slug'];
            $oTheme['new_version'] = $this->aTheme['version'];
            $oTheme['package']     = $this->aTheme['download'];
            
            $oListThemesInfo->response[$this->aTheme['slug']]   = $oTheme;
            $oListThemesInfo->checked[$this->aTheme['version']] = $oMyTheme->get('Version');
            $oListThemesInfo->last_checked                      = strtotime('+30 minutes');
            //            }
            
            set_site_transient('update_themes', $oListThemesInfo);
        }
    }
    
    public function checkUpdatePluginDirectly()
    {
        if (!General::isWilcityServicePage() || !$this->isNeededToRecheckUpdatePlugins()) {
            return false;
        }
        
        $this->directlyUpdateTheme();
        $this->directlyUpdatePlugins();
    }
    
    /**
     * API check.
     *
     * @param bool   $api    Always false.
     * @param string $action The API action being performed.
     * @param object $args   Plugin arguments.
     *
     * @return mixed $api The plugin info or false.
     * @since 1.0.0
     *
     */
    public function pluginsAPI($api, $action, $oArgs)
    {
        if (isset($oArgs->slug) && 'wilcityservice-client' === $oArgs->slug) {
            $api_check = $this->api_check();
            if (is_object($api_check)) {
                $api = $api_check;
            }
        }
        
        return $api;
    }
    
    private function setLastCheckedUpdatePlugins()
    {
        set_transient('wiloke_last_checked_plugins_update', 'yes', 60 * 10);
    }
    
    private function isNeededToRecheckUpdatePlugins()
    {
        if ($this->isFocus()) {
            return true;
        }
        $lastChecked = get_transient('wiloke_last_checked_plugins_update');
        
        return $lastChecked != 'yes' || (defined('WILOKE_FOCUS_CHECKUPDATE') && WILOKE_FOCUS_CHECKUPDATE);
    }
    
    public function getUpdates()
    {
        global $pagenow;
        if (General::isWilcityServicePage() ||
            ($pagenow == 'plugins.php' || $pagenow == 'network-plugins.php' || $pagenow == 'update-core.php' ||
             $pagenow == 'network-update-core.php')
        ) {
            $this->_getUpdates();
        }
    }
    
    private function getListOfInstalledPlugins()
    {
        if (!empty($this->aInstalledPlugins)) {
            return $this->aInstalledPlugins;
        }
        
        if (!function_exists('get_plugins')) {
            require_once ABSPATH.'wp-admin/includes/plugin.php';
        }
        
        $this->aInstalledPlugins = get_plugins();
        
        return $this->aInstalledPlugins;
    }
    
    private function getListOfInstalledThemes()
    {
        if (!empty($this->aInstalledThemes)) {
            return $this->aInstalledThemes;
        }
        
        $this->aInstalledThemes = wp_get_themes();
        
        return $this->aInstalledThemes;
    }
    
    private function getCurrentTheme($slug)
    {
        if ($this->oCurrentThemeVersion !== null) {
            return $this->oCurrentThemeVersion;
        }
        
        $oMyTheme = wp_get_theme($slug);
        if ($oMyTheme->exists()) {
            $this->oCurrentThemeVersion = false;
        }
        
        $this->oCurrentThemeVersion = $oMyTheme;
        
        return $this->oCurrentThemeVersion;
    }
    
    /*
     * Build Update Skeleton (Referring to response under get_site_transition update_plugins
     */
    private function buildUpdatePluginSkeleton($aPlugin)
    {
        return (object)[
          'slug'         => $aPlugin['slug'],
          'plugin'       => $this->buildPluginPathInfo($aPlugin['slug']),
          'new_version'  => $aPlugin['version'],
          'newVersion'   => $aPlugin['version'],
          'url'          => isset($aPlugin['changelog']) && !empty($aPlugin['changelog']) ? $aPlugin['changelog'] :
            $this->changeLogURL,
          'package'      => $aPlugin['download'],
          'requires_php' => $this->phpRequired
        ];
    }
    
    private function getPreviewURL($aNewPlugin)
    {
        return isset($aNewPlugin['preview']) && !empty($aNewPlugin['preview']) ? $aNewPlugin['preview'] :
          WILCITYSERVICE_PREVIEWURL;
    }
    
    private function buildPluginPathInfo($pluginID)
    {
        return $pluginID.'/'.$pluginID.'.php';
    }
    
    private function updatechangeLogURL($pluginID)
    {
        return wp_nonce_url(self_admin_url('update.php?action=upgrade-plugin&plugin=').
                            $this->buildPluginPathInfo($pluginID),
          'upgrade-plugin_'.$this->buildPluginPathInfo($pluginID));
    }
    
    public function reUpdateResponseOfTheme()
    {
        if (!current_user_can('administrator')) {
            return false;
        }
        $this->isFocusGetUpdates = true;
        $this->_getUpdates();
        $this->directlyUpdateTheme();
    }
    
    public function reUpdateResponseOfPlugins()
    {
        if (!current_user_can('administrator')) {
            return false;
        }
        $this->isFocusGetUpdates = true;
        $this->_getUpdates();
        $this->directlyUpdatePlugins();
    }
    
    public function updateThemes($oTransient)
    {
        if (General::isWilcityServicePage()) {
            return $oTransient;
        }
        
        if (empty($this->aTheme)) {
            return $oTransient;
        }
        
        if (isset($oTransient->checked)) {
            $this->getCurrentTheme('wilcity');
            if ($this->oCurrentThemeVersion && version_compare($this->oCurrentThemeVersion->get('Version'),
                $this->aTheme['version'], '<')
            ) {
                $oTheme                                      = [];
                $oTheme['theme']                             = $this->aTheme['slug'];
                $oTheme['new_version']                       = $this->aTheme['version'];
                $oTheme['package']                           = $this->aTheme['download'];
                $oTheme['url']                               =
                  isset($this->aTheme['changelog']) && !empty($this->aTheme['changelog']) ?
                    $this->aTheme['changelog'] : $this->changeLogURL;
                $oTransient->response[$this->aTheme['slug']] = $oTheme;
            }
        }
        
        return $oTransient;
    }
    
    public function updatePlugins($oTransient)
    {
        if (General::isWilcityServicePage()) {
            return $oTransient;
        }
        
        if (isset($oTransient->checked) || $isDebug = true) {
            // send purchased code
            if (empty($this->aPlugins) || is_wp_error($this->aPlugins)) {
                return $oTransient;
            }
            
            foreach ($this->aPlugins as $aPlugin) {
                $path = $this->buildPluginPathInfo($aPlugin['slug']);
                if (isset($this->aInstalledPlugins[$path]) &&
                    version_compare($this->aInstalledPlugins[$path]['Version'],
                      $aPlugin['version'], '<')
                ) {
                    $oTransient->response[$path] = $this->buildUpdatePluginSkeleton($aPlugin);
                }
            }
            $this->setLastCheckedUpdatePlugins();
        }
        
        return $oTransient;
    }
    
    public function enqueueScripts()
    {
        if (!General::isWilcityServicePage()) {
            return false;
        }
        
        wp_enqueue_style('style', WILCITYSERIVCE_CLIENT_SOURCE.'style.css');
        wp_enqueue_script('updates');
        wp_enqueue_script('updateplugin', WILCITYSERIVCE_CLIENT_SOURCE.'updateplugin.js', ['jquery', 'updates'],
          WILCITYSERIVCE_VERSION, true);
    }
    
    public function openUpdateForm()
    {
        switch ($this->responseCode) {
            case 'IP_BLOCKED':
                ?>
                <div class="ui message negative">
                    <?php echo $this->errMgs; ?>
                </div>
                <?php
                break;
            case 'PurchasedCodeExpired':
                ?>
                <div class="ui message negative">
                    The Support Plan was expired. <a
                            href="https://themeforest.net/item/wilcity-directory-listing-wordpress-theme/22066447"
                            target="_blank">Renew it now</a>
                </div>
                <?php
                break;
            case 'INVALID_TOKEN':
                ?>
                <div class="ui message negative">
                    Invalid Token. Please log into <a href="https://wilcityservice.com" target="_blank">Wilcity
                        Service</a> to renew one.
                </div>
                <?php
                break;
            case 'CLIENT_WEBSITE_IS_INVALID':
                ?>
                <div class="ui message negative">
                    This website is not listed in Website Urls of this token. Please log into <a
                            href="https://wilcityservice.com" target="_blank">Wilcity Service -> Theme Information</a>
                    and check it again.
                </div>
                <?php
                break;
        }
        ?>
        <div id="wilcity-updates-wrapper" class="ui <?php echo $this->responseCode == 'PurchasedCodeExpired' ?
      'disable' : 'oke'; ?>">
        <?php
    }
    
    private function renderThemeButton()
    {
        $oActivateTheme = wp_get_theme();
        ?>
        <div class="extra content">
            <div class="ui two buttons wil-button-wrapper" data-slug="<?php echo esc_attr($this->aTheme['slug']); ?>">
                <?php if ($oActivateTheme->get('name') != $this->oCurrentThemeVersion->get('name')) : ?>
                    <div class="ui basic green button"><a href="<?php echo esc_url(admin_url('themes.php')); ?>"
                                                          target="_blank">Install</a></div>
                <?php elseif (General::isNewVersion($this->aTheme['version'],
                  $this->oCurrentThemeVersion->get('Version'))
                ): ?>
                    <div class="ui basic green button"><a class="wil-update-theme">Update</a></div>
                <?php endif; ?>
                <div class="ui basic red button"><a target="_blank"
                                                    href="<?php echo esc_url($this->aTheme['preview']); ?>">Changelog</a>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function renderPluginButtons($aNewPlugin, $aCurrentPluginInfo)
    {
        $tgmpaUrl = admin_url('themes.php?page=tgmpa-install-plugins&plugin_status=install');
        ?>
        <div class="extra content">
            <?php wp_nonce_field('wiloke-service-nonce', 'wiloke-service-nonce-value'); ?>
            <div class="ui two buttons wil-button-wrapper">
                <?php if (!$aCurrentPluginInfo) : ?>
                    <div class="ui basic green button">
                        <a href="<?php echo esc_url($tgmpaUrl); ?>"
                           class="wil-install-plugin wilcity-plugin"
                           data-slug="<?php echo esc_attr($aNewPlugin['slug']); ?>"
                           data-action="wiloke_download_plugin"
                           data-plugin="<?php echo esc_attr($this->buildPluginPathInfo($aNewPlugin['slug'])); ?>"
                           target="_blank">Install</a>
                    </div>
                <?php elseif (General::isNewVersion($aNewPlugin['version'], $aCurrentPluginInfo['Version'])): ?>
                    <div class="ui basic green button">
                        <a class="wil-update-plugin"
                           href="<?php echo esc_url($this->updatechangeLogURL($aNewPlugin['slug'])); ?>">Update</a>
                    </div>
                <?php else: ?>
                    <?php if (!is_plugin_active($this->buildPluginPathInfo($aNewPlugin['slug']))) : ?>
                        <div class="ui basic green button">
                            <a href="<?php echo esc_url($tgmpaUrl); ?>"
                               class="wil-active-plugin wilcity-plugin"
                               data-action="wiloke_activate_plugin"
                               data-slug="<?php echo esc_attr($aNewPlugin['slug']); ?>"
                               data-plugin="<?php echo esc_attr($this->buildPluginPathInfo($aNewPlugin['slug'])); ?>"
                               target="_blank">Activate</a>
                        </div>
                    <?php else: ?>
                        <div class="ui basic green button">
                            <a href="<?php echo esc_url($tgmpaUrl); ?>"
                               class="wil-deactivate-plugin wilcity-plugin"
                               data-action="wiloke_deactivate_plugin"
                               data-slug="<?php echo esc_attr($aNewPlugin['slug']); ?>"
                               data-plugin="<?php echo esc_attr($this->buildPluginPathInfo($aNewPlugin['slug'])); ?>"
                               target="_blank">Deactivate</a>
                        </div>
                    <?php endif; ?>
                    <div class="ui basic red button">
                        <a target="_blank"
                           href="<?php echo esc_url($this->getPreviewURL($aNewPlugin)); ?>">Changelog</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    public function showUpTheme()
    {
        if (in_array($this->responseCode, $this->aStatusCodeNoNeedToPrintUpdate)) {
            return false;
        }
        ?>
        <div id="wilcity-update-theme" class="ui segment" style="margin-top: 30px;">
            <h3 class="ui heading">Wilcity Theme</h3>

            <div class="ui message wil-plugin-update-msg hidden"></div>
            <?php if (empty($this->aTheme)) : ?>
                <p class="ui message error positive"><?php echo 'Oops! We could not find this theme.'; ?></p>
            <?php else: $this->getCurrentTheme($this->aTheme['slug']); ?>
                <div class="ui cards" style="margin-bottom: 10px;">
                    <div class="wil-theme-item-wrapper card" style="width: 300px;">
                        <div class="content" style="padding: 1.3em 1.2em;">
                            <img class="right floated mini ui image" style="width: 60px"
                                 src="<?php echo esc_url($this->aTheme
                                 ['thumbnail']); ?>">
                            <div class="header"
                                 style="font-size: 1.1em; margin-bottom: 7px"><?php echo esc_html($this->aTheme['name']); ?></div>
                            <div class="meta" style="font-size: 13px">
                                <span class="version" style=" display: block; margin-bottom: 2px; color: #222">You are using: <span
                                            class="wil-current-version"><?php echo esc_html($this->oCurrentThemeVersion->get('Version')); ?></span></span>
                                <span class="version" style=" display: block; margin-bottom: 2px; color: #222">New Version: <span
                                            class="wil-new-version"><?php echo esc_html($this->aTheme['version']); ?></span></span>
                                <span class="updated_at"
                                      style=" display: block; color: #222">Updated at:<?php echo date_i18n(get_option('date_format'),
                                      $this->aTheme['updatedAt']); ?></span>
                            </div>
                            <div class="description" style="font-size: 13px">
                                <?php echo $this->aTheme['description']; ?>
                            </div>
                        </div>
                        <?php $this->renderThemeButton(); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    public function closeUpdateForm()
    {
        ?>
        </div>
        <?php
    }
    
    public function showUpPlugins()
    {
        if (in_array($this->responseCode, $this->aStatusCodeNoNeedToPrintUpdate)) {
            return false;
        }
        ?>
        <div id="wilcity-update-plugins" class="ui segment">
            <h3 class="ui heading">Wilcity's Plugins</h3>
            <div class="ui message wil-plugin-update-msg hidden"></div>
            <?php if (empty($this->aPlugins)) : ?>
                <p class="ui message error positive"><?php echo 'Oops! We could not find any plugin'; ?></p>
            <?php else: $this->getListOfInstalledPlugins(); ?>
                <div class="ui cards" style="margin-bottom: 10px;">
                    <?php
                    foreach ($this->aPlugins as $aPlugin) :
                        $aCurrentPluginInfo =
                          isset($this->aInstalledPlugins[$this->buildPluginPathInfo($aPlugin['slug'])]) ?
                            $this->aInstalledPlugins[$this->buildPluginPathInfo($aPlugin['slug'])] : false;
                        ?>
                        <div class="wil-plugin-wrapper card" style="width: 300px;">
                            <div class="content" style="padding: 1.3em 1.2em;">
                                <img class="right floated mini ui image" style="width: 60px"
                                     src="<?php echo esc_url($aPlugin['thumbnail']); ?>">
                                <div class="header"
                                     style="font-size: 1.1em; margin-bottom: 7px"><?php echo esc_html($aPlugin['name']); ?></div>
                                <div class="meta" style="font-size: 13px">
                                    <?php if ($aCurrentPluginInfo) : ?>
                                        <span class="version" style=" display: block; margin-bottom: 2px; color: #222">You are using: <span
                                                    class="wil-current-version"><?php echo esc_html($aCurrentPluginInfo['Version']); ?></span></span>
                                    <?php endif; ?>
                                    <span class="version" style=" display: block; margin-bottom: 2px; color: #222">New Version: <span
                                                class="wil-new-version"><?php echo esc_html($aPlugin['version']); ?></span></span>
                                    <span class="updated_at"
                                          style=" display: block; color: #222">Updated at:<?php echo date_i18n(get_option('date_format'),
                                          $aPlugin['updatedAt']); ?></span>
                                </div>
                                <div class="description" style="font-size: 13px">
                                    <?php echo $aPlugin['description']; ?>
                                </div>
                            </div>
                            <?php $this->renderPluginButtons($aPlugin, $aCurrentPluginInfo); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <a class="ui button green"
           href="<?php echo self_admin_url('admin.php?page=wilcity-service&is-refresh-update=yes'); ?>">Refresh</a>
        <?php
    }
    
    public function afterActivatePlugin($plugin)
    {
        $this->afterSwitchTheme('');
    }
    
    public function afterSwitchTheme($oldThemeName)
    {
        $aData  = [];
        $oTheme = wp_get_theme();
        
        $template = $oTheme->get('Template');
        if (!empty($template) && strtolower($template) === 'wilcity') {
            $themeName = $template;
        } else {
            $themeName = $oTheme->get('Name');
        }
        
        $aData['prevThemeName'] = $oldThemeName;
        $aData['themeName']     = $themeName;
        $aData['version']       = $oTheme->get('Version');
        $aData['email']         = get_option('admin_email');
        $aData['website']       = home_url('/');
        
        $bearToken = 'Bearer '.GetSettings::getOptionField('secret_token');
        
        $headers = [
          'Authorization' => $bearToken,
          'Content-type'  => 'application/json'
        ];
        
        $pload = [
          'method'      => 'POST',
          'timeout'     => 30,
          'redirection' => 5,
          'httpversion' => '1.0',
          'blocking'    => true,
          'headers'     => $headers,
          'body'        => $aData,
          'cookies'     => []
        ];
        
        wp_remote_post('https://wilcityservice.com/wp-json/wilcityservice/v1/switched-t', $pload);
    }
}
