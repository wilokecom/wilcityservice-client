<?php

namespace WilcityServiceClient\Controllers;

class ScheduleController
{
    public static $preSetTransientUpdatePluginKey = 'wiloke_pre_set_transient_update_plugins';
    
    public static function checkUpdateSchedule()
    {
        if (!wp_next_scheduled(self::$preSetTransientUpdatePluginKey)) {
            wp_schedule_event(time(), 'hourly', self::$preSetTransientUpdatePluginKey);
        }
    }
    
    public static function deactivateCheckUpdateSchedule()
    {
        wp_clear_scheduled_hook(self::$preSetTransientUpdatePluginKey);
    }
}
