<?php

namespace WilcityServiceClient\Helpers;


class SemanticUi {
    public static $aCaching = array();
	public static function renderDescField($aField){
	    if ( !isset($aField['desc']) || empty($aField['desc']) ){
	        return '';
	    }
	    $aField['status'] = isset($aField['desc_status']) ? $aField['desc_status'] : 'info';
		?>
        <div id="<?php echo isset($aField['desc_id']) ? esc_html($aField['desc_id']) : ''; ?>" class="ui ignored message <?php echo esc_attr($aField['status']); ?>">
            <?php General::ksesHtml($aField['desc']); ?>
        </div>
		<?php
	}

	public static function renderOpenSegment($aField){
		$class = 'segment ui' . (isset($aField['class']) ? ' ' . $aField['class'] : '');
        ?>
        <div class="<?php echo esc_attr($class); ?>">
        <?php
	}

    public static function renderOpenAccordion($aField){
        $class = 'ui styled accordion' . (isset($aField['class']) ? ' ' . $aField['class'] : '');
        ?>
        <div class="<?php echo esc_attr($class); ?>">
        <?php
    }

    public static function renderClose(){
        ?>
        </div>
        <?php
    }

	public static function renderCloseSegment(){
        ?>
        </div>
        <?php
	}

    public static function renderDesc($aField){
		if ( isset($aField['desc']) ) :
			$status = isset($aField['desc_status']) ? $aField['desc_status'] : '';
			?>
            <p class="ui <?php echo esc_attr($status); ?> message"><i class="desc"><?php General::ksesHtml($aField['desc']); ?></i></p>
			<?php
		endif;
	}

	public static function renderHeader($aField){
        $class = 'header ui' . (isset($aField['class']) ? ' ' . $aField['class'] : '');
        ?>
        <<?php echo esc_attr($aField['tag']) ?> id="<?php echo isset($aField['id']) ? esc_attr($aField['id']) :''; ?>" class="<?php echo esc_attr($class); ?>"><?php if (strpos($aField['class'], 'toggle') !== false): ?><i class="icon options"></i><?php endif; ?><?php echo esc_html($aField['text']); ?></<?php echo esc_attr($aField['tag']) ?>>
        <?php
        self::renderDesc($aField);
	}

	public static function renderOpenFieldGroup($aField){
		$class = 'fields ' . (isset($aField['class']) ? ' ' . $aField['class'] : '');
		?>
        <div class="<?php echo esc_attr($class); ?>">
		<?php
	}

	public static function renderHiddenField($aField){
		$aField['class'] =  isset($aField['class']) ? $aField['class'] : '';
		$aField['required'] =  isset($aField['required']) ? 'required' : '';
		?>
        <input type="hidden" class="<?php echo esc_attr($aField['class']); ?>" name="<?php echo esc_attr($aField['name']); ?>" value="<?php echo esc_attr($aField['value']); ?>">
		<?php
	}

	public static function renderTextField($aField){
		$class = 'field' . (isset($aField['wrapper_class']) ? ' ' . $aField['wrapper_class'] : '');
		$aField['class'] =  isset($aField['class']) ? $aField['class'] : '';
		$aField['required'] =  isset($aField['required']) ? 'required' : '';

		$dataAttr = '';

		if ( isset($aField['data']) ){
		    foreach ( $aField['data'] as $attr => $val ){
                $dataAttr .= ' data-'.$attr . '=' . $val;
		    }
		}
		?>
        <div class="<?php echo esc_attr($class); ?>">
            <label for="<?php echo esc_attr($aField['id']); ?>"><?php echo esc_attr($aField['heading']); ?></label>
            <input type="text" placeholder="<?php echo isset($aField['placeholder']) ? esc_attr($aField['placeholder']) : ''; ?>" id="<?php echo esc_attr($aField['id']); ?>" class="<?php echo esc_attr($aField['class']); ?>" name="<?php echo esc_attr($aField['name']); ?>" value="<?php echo esc_attr($aField['value']); ?>" <?php if ( isset($aField['is_readonly'])  ) : ?> readonly="" <?php endif; ?> <?php echo esc_attr($aField['required']); ?> <?php echo esc_attr($dataAttr); ?>>
	        <?php self::renderDescField($aField); ?>
        </div>
		<?php
	}

	public static function renderDateTimeLocalField($aField){
		$class = 'field' . (isset($aField['wrapper_class']) ? ' ' . $aField['wrapper_class'] : '');
		$aField['class'] =  isset($aField['class']) ? $aField['class'] : '';
		$aField['required'] =  isset($aField['required']) ? 'required' : '';

		$dataAttr = '';

		if ( isset($aField['data']) ){
		    foreach ( $aField['data'] as $attr => $val ){
                $dataAttr .= ' data-'.$attr . '=' . $val;
		    }
		}
		?>
        <div class="<?php echo esc_attr($class); ?>">
            <label for="<?php echo esc_attr($aField['id']); ?>"><?php echo esc_attr($aField['heading']); ?></label>
            <input type="datetime-local" placeholder="<?php echo isset($aField['placeholder']) ? esc_attr($aField['placeholder']) : ''; ?>" id="<?php echo esc_attr($aField['id']); ?>" class="<?php echo esc_attr($aField['class']); ?>" name="<?php echo esc_attr($aField['name']); ?>" value="<?php echo esc_attr($aField['value']); ?>" <?php if ( isset($aField['is_readonly'])  ) : ?> readonly="" <?php endif; ?> <?php echo esc_attr($aField['required']); ?> <?php echo esc_attr($dataAttr); ?>>
	        <?php self::renderDescField($aField); ?>
        </div>
		<?php
	}

    public static function renderPasswordField($aField){
		$class = 'field' . (isset($aField['wrapper_class']) ? ' ' . $aField['wrapper_class'] : '');
		$aField['class'] =  isset($aField['class']) ? $aField['class'] : '';
		$aField['required'] =  isset($aField['required']) ? 'required' : '';

		$dataAttr = '';

		if ( isset($aField['data']) ){
		    foreach ( $aField['data'] as $attr => $val ){
                $dataAttr .= ' data-'.$attr . '=' . $val;
		    }
		}
		?>
        <div class="<?php echo esc_attr($class); ?>">
            <label for="<?php echo esc_attr($aField['id']); ?>"><?php echo esc_attr($aField['heading']); ?></label>
            <input type="password" placeholder="<?php echo isset($aField['placeholder']) ? esc_attr($aField['placeholder']) : ''; ?>" id="<?php echo esc_attr($aField['id']); ?>" class="<?php echo esc_attr($aField['class']); ?>" name="<?php echo esc_attr($aField['name']); ?>" value="<?php echo esc_attr($aField['value']); ?>" <?php echo esc_attr($aField['required']); ?> <?php echo esc_attr($dataAttr); ?>>
	        <?php self::renderDescField($aField); ?>
        </div>
		<?php
	}

	public static function renderTextareaField($aField){
		$class = 'field' . (isset($aField['wrapper_class']) ? ' ' . $aField['wrapper_class'] : '');
		$aField['class'] =  isset($aField['class']) ? $aField['class'] : '';
		?>
        <div class="<?php echo esc_attr($class); ?>">
            <label for="<?php echo esc_attr($aField['id']); ?>"><?php echo esc_attr($aField['heading']); ?></label>
            <textarea rows="<?php echo isset($aField['rows']) ? esc_attr($aField['rows']) : ''; ?>" id="<?php echo esc_attr($aField['id']); ?>" class="<?php echo esc_attr($aField['class']); ?>" name="<?php echo esc_attr($aField['name']); ?>"><?php echo esc_textarea(stripslashes($aField['value'])); ?></textarea>
            <?php self::renderDescField($aField); ?>
        </div>
		<?php
	}

	public static function getPosts($aField){
	    if ( isset($aCaching['post']) && isset($aCaching['post'][$aField['post_type']]) && !empty($aCaching['post'][$aField['post_type']]) ){
	        return $aCaching['post'][$aField['post_type']];
        }

		$oPosts = get_posts(
			array(
				'post_type'     => $aField['post_type'],
				'posts_per_page'=> -1,
				'post_status'   => 'publish'
			)
		);

		return $oPosts;
    }

	public static function renderSelectPostField($aField){
		$class = 'field' . (isset($aField['wrapper_class']) ? ' ' . $aField['wrapper_class'] : '');
		$aField['class'] =  isset($aField['class']) ? $aField['class'] : '';
		$multiple = isset($aField['multiple']) && $aField['multiple'] ? 'multiple' : '';
		$required = isset($aField['required']) && $aField['required'] ? 'required' : '';
		?>
        <div class="<?php echo esc_attr($class); ?>">
            <label for="<?php echo esc_attr($aField['id']); ?>"><?php echo esc_attr($aField['heading']); ?></label>
            <div data-query="<?php echo esc_attr($aField['post_type']); ?>">
                <select id="<?php echo esc_attr($aField['id']); ?>" <?php echo esc_attr($required); ?> class="<?php echo esc_attr($aField['class']); ?> js_select2_ajax" name="<?php echo esc_attr($aField['name']); ?>" <?php echo esc_attr($multiple); ?>>
					<?php
					if ( !empty($aField['value']) ){
						$aField['value'] = is_array($aField['value']) ? $aField['value'] : array($aField['value']);
						foreach ( $aField['value'] as $val ){
							?>
                            <option value="<?php echo esc_attr($val); ?>" selected><?php echo esc_html(get_the_title($val)); ?></option>
							<?php
						}
					}
					?>
                </select>
	            <?php self::renderDescField($aField); ?>
            </div>
        </div>
		<?php
	}

	public static function renderSimpleSelectPostField($aField){
		$class = 'field' . (isset($aField['wrapper_class']) ? ' ' . $aField['wrapper_class'] : '');
		$aField['class'] =  isset($aField['class']) ? $aField['class'] : '';
		$multiple = '';
		if ( isset($aField['multiple']) && $aField['multiple'] ){
			$multiple = 'multiple';
		}

		if ( !empty($aField['value']) ){
			$aField['value'] = !is_array($aField['value']) ? array($aField['value']) : $aField['value'];
		}else{
			$aField['value'] = array();
		}

		$required = isset($aField['required']) && $aField['required'] ? 'required' : '';
        $oPosts = self::getPosts($aField);

		?>
        <div class="<?php echo esc_attr($class); ?>">
            <label for="<?php echo esc_attr($aField['id']); ?>"><?php echo esc_attr($aField['heading']); ?></label>
            <div data-query="<?php echo esc_attr($aField['post_type']); ?>">
                <select id="<?php echo esc_attr($aField['id']); ?>" <?php echo esc_attr($required); ?> class="<?php echo esc_attr($aField['class']); ?> wiloke-use-select2 js_select2_without_ajax" name="<?php echo esc_attr($aField['name']); ?>" <?php echo esc_attr($multiple); ?>>
                    <option value="">---</option>
					<?php if ( !empty($oPosts) && !is_wp_error($oPosts) ) : ?>
						<?php foreach ( $oPosts as $oPost ) : ?>
							<?php
							$selected = !empty($aField['value']) && in_array($oPost->ID, $aField['value']) ? 'selected' : '';
							?>
                            <option value="<?php echo esc_attr($oPost->ID); ?>" <?php echo esc_attr($selected); ?>><?php echo esc_html($oPost->post_title); ?></option>
						<?php endforeach; ?>
					<?php endif; ?>
                </select>
	            <?php self::renderDescField($aField); ?>
            </div>
        </div>
		<?php
	}

	public static function renderSelectUserField($aField){
		$class = 'field' . (isset($aField['wrapper_class']) ? ' ' . $aField['wrapper_class'] : '');
		$aField['class'] =  isset($aField['class']) ? $aField['class'] : '';
		$multiple = isset($aField['multiple']) && $aField['multiple'] ? 'multiple' : '';
		$required = isset($aField['required']) && $aField['required'] ? 'required' : '';
		$oUsers = get_users();
		?>
        <div class="<?php echo esc_attr($class); ?>">
            <label for="<?php echo esc_attr($aField['id']); ?>"><?php echo esc_attr($aField['heading']); ?></label>
            <div class="ui fluid <?php echo esc_attr($multiple); ?> search selection dropdown">
                <input id="<?php echo esc_attr($aField['id']); ?>" type="hidden" name="<?php echo esc_attr($aField['name']); ?>" value="<?php echo esc_attr($aField['value']); ?>" <?php echo esc_attr($required); ?>>
                <i class="dropdown icon"></i>
                <div class="default text"><?php echo isset($aField['placeholder']) ? esc_html($aField['placeholder']) : esc_html__('Select User', 'listgo'); ?></div>
                <div class="menu">
					<?php foreach ($oUsers as $oUser) : $avatar = Wiloke::getUserAvatar($oUser->data->ID); ?>
                        <div class="item" data-value="<?php echo esc_attr($oUser->data->ID); ?>" data-text="<?php echo esc_attr($oUser->data->display_name); ?>">
                            <?php if ( !empty($avatar) ) : ?>
                            <img class="ui mini avatar image" src="<?php echo esc_url(Wiloke::getUserAvatar($oUser->data->ID)); ?>" alt="<?php echo esc_attr($oUser->data->display_name); ?>">
                            <?php endif; ?>
                            <?php echo esc_html($oUser->data->display_name); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
			<?php self::renderDescField($aField); ?>
        </div>
		<?php
	}

	public static function renderSelectUiField($aField){
		$class = 'field' . (isset($aField['wrapper_class']) ? ' ' . $aField['wrapper_class'] : '');
		$aField['class'] =  isset($aField['class']) ? $aField['class'] : '';
		$required = isset($aField['required']) && $aField['required'] ? 'required' : '';
		$multiple = '';
		if ( isset($aField['multiple']) && $aField['multiple'] ){
			$multiple = 'multiple';
		}
		if ( isset($aField['post_type']) && !empty($aField['post_type']) ){
			$aPosts = self::getPosts($aField);

			if ( empty($aPosts) || is_wp_error($aPosts) ){
				$aField['status'] = 'warning';
				$aField['desc'] = sprintf(esc_html__('The post type %s does not exist or there is no any post yet', 'listgo'), $aField['post_type']);
			    self::renderDescField($aField);
			    return false;
            }
        }
	    ?>
        <div class="<?php echo esc_attr($class); ?>">
            <label for="<?php echo esc_attr($aField['id']); ?>"><?php echo esc_attr($aField['heading']); ?></label>
            <div class="ui fluid <?php echo esc_attr($multiple); ?> search selection dropdown">
                <input id="<?php echo esc_attr($aField['id']); ?>" type="hidden" name="<?php echo esc_attr($aField['name']); ?>" value="<?php echo esc_attr($aField['value']); ?>" <?php echo esc_attr($required); ?>>
                <i class="dropdown icon"></i>
                <div class="default text"><?php echo isset($aField['placeholder']) ? esc_html($aField['placeholder']) : esc_html__('Select Value', 'listgo'); ?></div>
                <div class="menu">
                    <?php
                        if ( isset($aPosts) ) :
                            foreach ($aPosts as $oPost) :
                    ?>
                        <div class="item" data-value="<?php echo esc_attr($oPost->ID); ?>" data-text="<?php echo esc_attr($oPost->post_title); ?>">
                            <?php if ( has_post_thumbnail($oPost->ID) ) : ?>
                            <img class="ui mini avatar image" src="<?php echo esc_url(get_the_post_thumbnail_url($oPost->ID, 'thumbnail')) ?>" alt="<?php echo esc_attr($oPost->post_title); ?>">
                            <?php endif; ?>
                            <?php echo esc_html($oPost->post_title); ?>
                        </div>
                    <?php
                            endforeach;
                        else :
                            foreach ( $aField['options'] as $aOption ) :
                    ?>
                            <div class="item" data-value="<?php echo esc_attr($aOption['value']); ?>" data-text="<?php echo esc_attr($aOption['text']); ?>">
                                <img class="ui mini avatar image" src="<?php echo esc_url($aOption['img']); ?>" alt="<?php echo esc_attr($aOption['text']); ?>">
                                <?php echo esc_html($aOption['text']); ?>
                            </div>
                    <?php
                            endforeach;
                        endif;
                    ?>
                </div>
            </div>
	        <?php self::renderDescField($aField); ?>
        </div>
        <?php
    }

    public static function renderSelectTwoField($aField){
		$class = 'field' . (isset($aField['class']) ? ' ' . $aField['class'] : '');
		$required = isset($aField['required']) && $aField['required'] ? 'required' : '';
		?>
        <div class="<?php echo esc_attr($class); ?>">
            <label for="<?php echo esc_attr($aField['id']); ?>"><?php echo esc_attr($aField['heading']); ?></label>
            <select name="<?php echo esc_attr($aField['name']); ?>" <?php echo esc_attr($required); ?> id="<?php echo esc_attr($aField['id']); ?>" class="js_select2_select_user_ajax">
                <?php foreach ( $aField['options'] as $option => $name ) : ?>
                    <option value="<?php echo esc_attr($option); ?>" <?php selected($option, $aField['value']); ?>><?php echo esc_attr($name); ?></option>
                <?php endforeach; ?>
            </select>
	        <?php self::renderDescField($aField); ?>
        </div>
		<?php
	}

	public static function renderSelectField($aField){
		$class = 'field' . (isset($aField['class']) ? ' ' . $aField['class'] : '');
		$fieldClass = 'ui dropdown';
		$fieldClass = isset($aField['fieldClass']) ? $fieldClass . ' ' . $aField['fieldClass'] : $fieldClass;
		$required = isset($aField['required']) && $aField['required'] ? 'required' : '';
		?>
        <div class="<?php echo esc_attr($class); ?>">
            <label for="<?php echo esc_attr($aField['id']); ?>"><?php echo esc_attr($aField['heading']); ?></label>
            <select name="<?php echo esc_attr($aField['name']); ?>" <?php echo esc_attr($required); ?> id="<?php echo esc_attr($aField['id']); ?>" class="<?php echo esc_attr($fieldClass); ?>">
                <?php foreach ( $aField['options'] as $option => $name ) : ?>
                    <option value="<?php echo esc_attr($option); ?>" <?php selected($option, $aField['value']); ?>><?php echo esc_attr($name); ?></option>
                <?php endforeach; ?>
            </select>
	        <?php self::renderDescField($aField); ?>
        </div>
		<?php
	}

	public static function renderSubmitBtn($aField){
	    $class = 'ui button' . (isset($aField['class']) ? ' ' . $aField['class'] : '');
		?>
        <button class="<?php echo esc_attr($class); ?>" type="submit"><?php echo esc_html($aField['name']); ?></button>
		<?php
	}

    public static function renderButton($aField){
		?>
		<div class="field">
		    <?php if ( !empty($aField['heading']) ) : ?>
		    <label><?php echo esc_attr($aField['heading']); ?></label>
		    <?php endif; ?>
            <button id="<?php echo isset($aField['id']) ? esc_attr($aField['id']) : ''; ?>" class="ui button <?php echo isset($aField['class']) ? esc_attr($aField['class']) : ''; ?>"><?php echo esc_html($aField['name']); ?></button>
        </div>
		<?php
	}
}