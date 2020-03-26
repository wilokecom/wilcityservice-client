<?php

namespace WilcityServiceClient\Controllers;

use WilcityServiceClient\Helpers\RestApi;

class NotificationController
{
    public function __construct()
    {
        add_action('wilcityservice_hourly_event', [$this, 'fetchNotifications']);
        add_action('admin_init', [$this, 'updateReadStatus']);
        add_action('admin_head', [$this, 'addColorToNotificationIcon']);
    }
    
    public function addColorToNotificationIcon() {
       ?>
        <style>
            #toplevel_page_wilcity-service .dashicons-megaphone:before {
                color: red !important;
            }
        </style>
        <?php
    }
    
    public function updateReadStatus()
    {
        if (!isset($_REQUEST['page']) || $_REQUEST['page'] !== 'wilcity-service') {
            return false;
        }
        
        if (get_option('wilcity_service_unread_notifications') !== 'yes') {
            return false;
        }

        if (!current_user_can('administrator')) {
            return false;
        }
        
        delete_option('wilcity_service_unread_notifications');
        var_export("dad");die;
    }
    
    public function fetchNotifications()
    {
        $aResponse = RestApi::get('notifications');
        
        if ($aResponse['status'] === 'error' || empty($aResponse['data'])) {
            return false;
        }
        
        $aOldNotification = get_option('wilcity_service_notifications');
        update_option('wilcity_service_notifications', $aResponse['data']);
        
        if (empty($aOldNotification) ||
            $aOldNotification['saved_at'] !== $aResponse['data']['notifications']['saved_at']) {
            update_option('wilcity_service_unread_notifications', 'yes');
        }
    }
}
