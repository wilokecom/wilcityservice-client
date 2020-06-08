<?php

namespace WilcityServiceClient\Controllers;

class Shortcodes
{
    public function __construct()
    {
        add_shortcode('wilcityservice_link', [$this, 'renderLink']);
    }
    
    public function renderLink($aAtts, $content)
    {
        $aAtts = shortcode_atts(
          [
            'classes' => 'ui green',
            'name'    => '',
            'link'    => '',
            'target'  => '_blank'
          ],
          $aAtts
        );
        
        if (empty($aAtts['link'])) {
            return '';
        }
        
        if (empty($aAtts['name'])) {
            $aAtts['name'] = $aAtts['link'];
        }
        
        ob_start();
        ?>
        <a target="<?php echo esc_attr($aAtts['target']); ?>"
           href="<?php echo esc_url($aAtts['link']); ?>"
           class="<?php echo esc_attr($aAtts['classes']); ?>">
            <?php echo esc_html($aAtts['name']); ?></a>
        <?php
        $content = ob_get_contents();
        ob_clean();
        
        return $content;
    }
}
