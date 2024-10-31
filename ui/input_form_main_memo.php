<?php
/** @var Mch_My_Custom_Style_Css_Manager $mcscmMainClass */
$mcscmMainClass = $this;
?>

<div id="<?php echo( $mcscmMainClass::APP_PREFIX ); ?>">
	<div id="">
        <?php
		$formItemKey = $mcscmMainClass::DATA_COL_NAME_ITEM_MEMO;
        ?>
        <div>
            <label class="" for="<?php echo esc_attr( $formItemKey ); ?>">
				<?php _e('memo', $mcscmMainClass::APP_LANG_DMN); ?>
            </label>
        </div>
        <div class="" >
            <textarea id="<?php echo esc_attr($formItemKey ); ?>"
                     name="<?php echo esc_attr($formItemKey ); ?>"
                      style="width: 100%; height:100px;"
            ><?php
                echo esc_textarea( $mcscmMainClass->getCustomPostMetaData($post->ID, $formItemKey ) )
                ?></textarea>
        </div>
    </div>
</div>


