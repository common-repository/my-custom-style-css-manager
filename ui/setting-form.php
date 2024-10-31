<?php

/** @var Mch_My_Custom_Style_Css_Manager $mcscmMainClass */
$mcscmMainClass = $this;
?>
<h1><?php echo $mcscmMainClass::APP_TITLE ?> <?php _e('settings', $mcscmMainClass::APP_LANG_DMN); ?></h1>


<form method="POST" action="">
    <div>
        <table class="form-table">
            <tbody>
            <tr>
<!--                <th>-->
<!--                    <label for="--><?php //echo $mcscmMainClass::GENERAL_OPT_KEY_ADD_COMMENT_TITLE ?><!--_form">タイトルを追記する</label>-->
<!--                </th>-->
                <td>
                    <?php
					$itemKey = $mcscmMainClass::GENERAL_OPT_KEY_ADD_COMMENT_TITLE;
					$is_checked = $mcscmMainClass->getGeneralOptionVal($itemKey);
                    ?>
                    <input id="<?php echo $itemKey ?>_form" type="checkbox"
                           value="1"
                           name="<?php echo $itemKey; ?>" <?php if ($is_checked)  { ?>checked="checked" <?php } ?>>
                    <label for="<?php echo $itemKey ?>_form">
						<?php _e('Add a title to comments.', $mcscmMainClass::APP_LANG_DMN); ?>
<!--						--><?php //echo $mcscmMainClass->_t('コメントにタイトルを追記する。'); ?>
                    </label>
                </td>
            </tr>

            <tr>
                <td>
					<?php
					$itemKey = $mcscmMainClass::GENERAL_OPT_KEY_ALL_DISABLE;
					?>
					<?php
					$is_checked = $mcscmMainClass->getGeneralOptionVal($itemKey);
					?>
                    <input id="<?php echo $itemKey ?>_form" type="checkbox"
                           value="1"
                           name="<?php echo $itemKey; ?>" <?php if ($is_checked)  { ?>checked="checked" <?php } ?>>
                    <label for="<?php echo $itemKey ?>_form">
						<?php _e('Treat them all as drafts.', $mcscmMainClass::APP_LANG_DMN); ?>
<!--						--><?php //echo $mcscmMainClass->_t('全て下書き扱いにする。'); ?>
                    </label>
                </td>
            </tr>

            </tbody>
        </table>
    </div>
	<?php
	do_action($this->add_prefix('insert_html_last_form'), $params);
	wp_nonce_field($mcscmMainClass::APP_TITLE, $mcscmMainClass::APP_WP_NONCE_KEY);
	?>
    <p class="submit">
        <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save changes', $mcscmMainClass::APP_LANG_DMN); ?>">
    </p>
</form>



