<?php

namespace WilcityServiceClient\Controllers;

class Controller
{
    protected function isWilcityServiceArea() {
        if (!isset($_REQUEST['page']) || $_REQUEST['page'] !== 'wilcity-service') {
            return false;
        }
        
        return true;
    }
}
