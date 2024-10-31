<?php
/** @var Mch_My_Custom_Style_Css_Manager $mcscmMainClass */
$mcscmMainClass = $this;
?>


<div>
    <div>
        <?php
        $formItemKey = $mcscmMainClass::DATA_COL_NAME_ITEM_ORDER;
        $value = (int)esc_attr( $mcscmMainClass->getCustomPostMetaData($post->ID, $formItemKey));
        $value = ($value < 0) ? 0 : $value;
        ?>
		<?php _e('order:', $mcscmMainClass::APP_LANG_DMN); ?>
        <input id="<?php echo esc_attr( $formItemKey ); ?>"
               type="number"
               name="<?php echo esc_attr( $formItemKey); ?>"
               value="<?php echo $value ?>"
               min="0" max="9999"
        />
    </div>

    <div>
        <?php
        $formItemKey = $mcscmMainClass::DATA_COL_NAME_ITEM_WRITE_BLOCK;
        $value = $mcscmMainClass->getCurrentWriteBlockKey($post->ID);
        ?>
		<?php _e('add block:', $mcscmMainClass::APP_LANG_DMN); ?>
        <select
                id="<?php echo esc_attr( $formItemKey ); ?>"
                name="<?php echo esc_attr( $formItemKey ); ?>"
        >
            <?php
            foreach ($mcscmMainClass->getWriteBlockList() as $key => $str){
                $selected = ($value == $key) ? ' selected ' : '';
                ?>
                <option
                        value="<?php echo $key ?>" <?php echo $key ?>
                    <?php echo $selected ?>
                ><?php echo $str ?></option>
                <?php
            }
            ?>
        </select>
    </div>
</div>

