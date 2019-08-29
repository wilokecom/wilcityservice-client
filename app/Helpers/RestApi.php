<?php

namespace WilcityServiceClient\Helpers;

class RestApi
{
    private static $baseURL = 'https://wilcityservice.com/wp-json/wilokeservice/v1/';
    private static $updateServiceURL = 'https://wilcityservice.com';
    
    public static function getUpdateServiceURL()
    {
        return self::$updateServiceURL;
    }
    
    public static function getBearToken()
    {
        $token = GetSettings::getOptionField('secret_token');
        
        return 'Bearer '.$token;
    }
    
    public static function get($endpoint)
    {
        $token = GetSettings::getOptionField('secret_token');
        
        if (empty($token)) {
            return [
                'status' => 'error',
                'msg'    => 'The Secret Token is required'
            ];
        }
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL            => self::$baseURL.$endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_REFERER        => get_option('siteurl'),
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_HTTPHEADER     => [
                "authorization: Bearer ".$token,
                "cache-control: no-cache"
            ],
        ]);
        
        $response = curl_exec($curl);
        $error    = curl_error($curl);
        curl_close($curl);
        
        if ($error) {
            return [
                'status' => 'error',
                'msg'    => $error
            ];
        } else {
            if (strpos($response, 'SpamFireWall') !== false) {
                preg_match('/([0-9.]+)/', $response, $aMatches);
                
                return [
                    'status' => 'error',
                    'msg'    => sprintf('Your IP address %s has been blocked by Wilcity Service FireWall. Please contact Wilcity Support on forum address to report this issue',
                        $aMatches[1]),
                    'code'   => 'IP_BLOCKED'
                ];
            }
            
            $aResponse = json_decode($response, true);
            if ($aResponse['status'] == 'error') {
                return $aResponse;
            }
            
            return wp_parse_args(
                $aResponse,
                [
                    'status'   => 'success',
                    'aPlugins' => '',
                    'aTheme'   => ''
                ]
            );
        }
    }
}
