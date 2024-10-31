<?php
/** @var Mch_My_Custom_Style_Css_Manager $mcscmMainClass */
$mcscmMainClass = $this;
?>

<div id="mch_codeMirrorArea">
<textarea class="wp-editor-area" id="content" name="content"><?php echo esc_textarea( $post->post_content ); ?></textarea>
</div>


<?php
$formItemKey = $mcscmMainClass->__ap( $mcscmMainClass::FORM_KEY_FROM_PAGE);
?>
<input id="<?php echo $formItemKey ?>" type="hidden" name="<?php echo $formItemKey ?>" value="main"/>


<style>
    #content{
        height: 90%;
    }
    #mch_codeMirrorArea{
        height: 600px;
    }
    #mch_mcscm_input_form_main{
        height: 600px;
    }

    /* インデントスタイル */
    .cm-tab:after {
        content: "\21e5";
        display: -moz-inline-block;
        display: -webkit-inline-block;
        display: inline-block;
        width: 0px;
        position: relative;
        overflow: visible;
        left: -1.4em;
        color: #aaa;
    }




    /*.CodeMirror-scroll*/
    /*{*/
        /*resize: vertical;*/
    /*}*/
    /*.CodeMirror {*/
        /*resize: vertical;*/
        /*overflow: auto !important;*/
    /*}*/
</style>

<script type="text/javascript">
    (function ($) {
        $(document).ready(function(){
            let nonEmpty = false;
            let content_mode = 'text/css';
            let options = {
                lineNumbers: true,
                mode: content_mode,
                matchBrackets: true,
                styleActiveLine: true,
                lineWrapping: true
            };
            let editor = CodeMirror.fromTextArea(document.getElementById("content"), options);

            console.log('<?php _e("TEST DEBUG001", $mcscmMainClass::APP_LANG_DMN); ?>');

            // editor.resizable({
            //     resize: function() {
            //         editor.setSize($(this).width(), $(this).height());
            //     }
            // });

            // let CodeMirrorCustomResize = (params) => {
            //     var start_x, start_y, start_h,
            //         minHeight = params && params.minHeight ? params.minHeight : 150,
            //         resizableObj = params && params.resizableObj ? params.resizableObj : '.handle';
            //
            //     let onDrag = (e) => {
            //         editor.setSize(null, `${Math.max(minHeight, (start_h + e.pageY - start_y))}px`);
            //     };
            //
            //     let onRelease = (e) => {
            //         $('body').off("mousemove", onDrag);
            //         $(window).off("mouseup", onRelease);
            //     };
            //
            //     $('body').on("mousedown", resizableObj, (e) => {
            //         start_x = e.pageX;
            //         start_y = e.pageY;
            //         start_h = $('.CodeMirror').height();
            //
            //         $('body').on("mousemove", onDrag);
            //         $(window).on("mouseup", onRelease);
            //     });
            // }
        });



        function toggleSelProp() {
            nonEmpty = !nonEmpty;
            editor.setOption("styleActiveLine", {nonEmpty: nonEmpty});
            let label = nonEmpty ? 'Disable nonEmpty option' : 'Enable nonEmpty option';
            document.getElementById('toggleButton').innerText = label;
        }




    })(jQuery);

</script>

