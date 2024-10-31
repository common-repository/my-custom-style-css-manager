<?php

class Mch_My_Custom_Style_Css_Manager {


	// <editor-fold desc="定数">
	const APP_PREFIX        = 'mch_mcscm';
	const APP_TITLE         = 'MyCustomStyleCssManager';
	const APP_WP_NONCE_KEY  = 'mch_mcscm_my_wpnonce';
//	const APP_LANG_DMN      = 'mch-my-custom-style-css-manager';
	const APP_LANG_DMN      = 'my-custom-style-css-manager';

	const APP_POST_TYPE	= 'mch_mcscm_pt';
	const APP_TERM_NAME	= 'mch_mcscm_cat';

	const ADMIN_AJAX_ACTION_UPDATE_STYLE_RESPONSE = 'mch_mcscm_res_update_style';



	const DATA_TYPE_INT = 1;
	const DATA_COL_NAME_ITEM_ORDER        = 'item_order';
	const DATA_COL_NAME_ITEM_MEMO         = 'memo';
	const DATA_COL_NAME_ITEM_WRITE_BLOCK  = 'write_block';
	const DATA_COL_NAME_ITEM_USE_THEME    = 'use_theme';

	const DATA_WRITE_BLOCK_LIST = [
		'top'     => '上部',
//		'top'     => '上部',
		'middle'  => '真ん中',
		'bottom'  => '下部',
	];



	public function getWriteBlockList(){
		return [
			'top'     => __('Top', self::APP_LANG_DMN),
			'middle'  => __('Middle', self::APP_LANG_DMN),
			'bottom'  => __('Bottom', self::APP_LANG_DMN),
		];
	}

	const FORM_KEY_FROM_PAGE = 'from_page';

	// 保存期間
	const EXPIRED_TIME		= 24 * 60 * 60;


	// <editor-fold desc="設定画面">

	public $general_option_params = [
		self::GENERAL_OPT_KEY_ADD_COMMENT_TITLE => [
			'default_value'   => false,
			'is_bool'         => true,
		],
		self::GENERAL_OPT_KEY_ALL_DISABLE => [
			'default_value'   => false,
			'is_bool'         => true,
		],
	];

	const GENERAL_OPT_KEY_ADD_COMMENT_TITLE = 'add_comment_title';
	const GENERAL_OPT_KEY_ALL_DISABLE       = 'all_disable';
	// </editor-fold>


	// </editor-fold>


	// <editor-fold desc="util">

	/**
	 * 表示用テキスト
	 * @param string $text
	 * @return string
	 */
	public function _t($text){
		return $text;
	}

	/**
	 * add prefix text
	 * @param string $text
	 * @return string
	 */
	public function __ap($text){
		return $this->add_prefix($text);
	}
	/**
	 * add prefix text
	 * @param $text
	 * @return string
	 */
	private function add_prefix($text) {
		return self::APP_PREFIX . '_' . $text;
	}

	public static function get_object() {
		static $instance = null;
		if ( NULL === $instance ) {
			$instance = new self();
		}
		return $instance;
	}

	// </editor-fold>

	public static function myplugin_load_textdomain() {
		load_plugin_textdomain( self::APP_LANG_DMN );
	}


	public function __construct(){
		if ( !is_admin() ) {
			return;
		}


//		$res = load_plugin_textdomain(
//			self::APP_LANG_DMN
////			,
////			false,
////			plugin_basename( dirname( __FILE__ ) ) . '/languages'
//		);

//		error_log(__FILE__ . '('.__LINE__.')['.__FUNCTION__.']$res='.print_r($res, true));
//		load_plugin_textdomain( 'my-custom-style-css-manager' );

		add_action( 'init', array( $this, 'create_link_post_type' ) );
		add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
		add_action( 'admin_menu', [ $this, 'add_meta_boxes' ] );


		// <editor-fold desc="style管理画面 新規追加・編集">
		add_action( 'save_post_'. self::APP_POST_TYPE, array( $this, 'save_post_data') );
		add_filter( 'views_edit-'.self::APP_POST_TYPE, [$this, 'style_manager_list_custom_filter']);
		add_filter( 'manage_' . self::APP_POST_TYPE . '_posts_columns', [ $this, 'style_manager_list_columns']);
		add_action( 'manage_' . self::APP_POST_TYPE . '_posts_custom_column', array( $this, 'style_manager_list_column_value'), 10, 2 );


		// 一覧のカラム拡張
		add_filter('manage_edit-' . self::APP_POST_TYPE . '_sortable_columns', [$this, 'style_manager_list_manage_sortable']);
		// 並び替え処理
		add_filter('request', [$this, 'style_manager_list_order_setting']);
		// </editor-fold>


		// <editor-fold desc="テーマの編集">
		add_action('admin_print_styles',  [$this, 'my_admin_print_styles']);
		add_action('admin_print_scripts',  [$this, 'my_admin_print_scripts']);
		add_action('admin_print_styles-theme-editor.php', [$this, 'theme_editor_add_scripts']);
		add_action('admin_print_scripts-theme-editor.php', [$this, 'my_admin_print_scripts_theme']);


		add_action( 'wp_ajax_' . self::ADMIN_AJAX_ACTION_UPDATE_STYLE_RESPONSE, array( $this, 'response_current_style_add_my_style' ) );
		// </editor-fold>
	}




	// <editor-fold desc="テーマの編集">
	function my_admin_print_styles() {
//		error_log(__FILE__ . '('.__LINE__.')['.__FUNCTION__.']');
	}
	function my_admin_print_scripts() {
//		error_log(__FILE__ . '('.__LINE__.')['.__FUNCTION__.']');
	}

	/**
	 * テーマの編集画面用追加JS
	 */
	function theme_editor_add_scripts(){
		echo '<script>';
		echo '_MCH_MCSCM_SITE_URL = "'.site_url().'";';
		echo '_MCH_ADMIN_AJAX_ACTION_UPDATE_STYLE_RESPONSE = "'.self::ADMIN_AJAX_ACTION_UPDATE_STYLE_RESPONSE.'";';
		echo '_MCH_BTN_LABEL_UPDATE = "'.__('Reflect custom CSS', self::APP_LANG_DMN).'";';
		echo '</script>';
	}

	/**
	 * テーマの編集画面追加スクリプト
	 */
	function my_admin_print_scripts_theme() {
		$themeStyleInfo = $this->getCurrentThemeStyleCss();
		$themeDirName   = $themeStyleInfo['themeDirName'];

		if(!empty($_GET) && ($_GET['theme'] !== $themeDirName || $_GET['file'] !== 'style.css' )){
			return;
		}


		wp_enqueue_script(
			'my-admin-script',
			plugin_dir_url( __FILE__ ). '/js/mch.js'
		);
	}



	/**
	 * リンク更新ボタン押下で呼ばれる for ajax
	 */
	public function response_current_style_add_my_style()
	{
		$themeStyleInfo = $this->getCurrentThemeStyleCss();
		$themeStyleInfo = $this->updateCurrentThemeStyleCss($themeStyleInfo);

		wp_send_json( $themeStyleInfo );
	}


	/**
	 * 現在の設定されているテーマ情報
	 * @return array
	 */
	private function getCurrentThemeStyleCss(){

		static $themeStyleInfo = null;
		if($themeStyleInfo !== null){
			return $themeStyleInfo;
		}


		$themeStyleInfo = [];
		$stylesheet     = get_stylesheet();
		$theme          = wp_get_theme( $stylesheet );
		if ( ! $theme->exists() ) {
			return $themeStyleInfo;
		}
		if ( $theme->errors() && 'theme_no_stylesheet' == $theme->errors()->get_error_code() ) {
			return $themeStyleInfo;
		}
		$themName = $theme->display( 'Name' );
		$themeDirName = $theme->get_stylesheet();

		$style_files = $theme->get_files( 'css', -1 );
		$styleCssFilePath = $style_files['style.css'];

		$f = fopen($styleCssFilePath, 'r');
		$content = fread($f, filesize($styleCssFilePath));


		$themeStyleInfo = [
			'themName'          => $themName,
			'themeDirName'          => $themeDirName,
			'styleCssFilePath'  => $styleCssFilePath,
			'content'           => $content,
		];

		return $themeStyleInfo;
	}

	/**
	 * style.cssを現在の設定で更新する
	 * @param $themeStyleInfo
	 * @return mixed
	 */
	private function updateCurrentThemeStyleCss($themeStyleInfo){

		$contents = $themeStyleInfo['content'];
		list (
			$postDataMap,
			$postMetaDataMap
			) = $this->getCurrentPublicPostData();

		$orderUserPostDataMapByBlockId = $this->orderUsePostData($postDataMap, $postMetaDataMap);

		$isAddTitle   = $this->getGeneralOptionVal(self::GENERAL_OPT_KEY_ADD_COMMENT_TITLE);
		$isAllDisable = $this->getGeneralOptionVal(self::GENERAL_OPT_KEY_ALL_DISABLE);

		$isExistUpdateTarget = false;
		$initialText = '';
		$baseStr = 'MY_CUSTOM_STYLE_CSS_MANAGER';

		$styleBlockStrList = $this->getWriteBlockList();

		foreach ($styleBlockStrList as $styleBlockStr => $str){
			$styleBlockStrUpper = strtoupper($styleBlockStr);
			$preFixStr  = "__{$baseStr}_{$styleBlockStrUpper}_START__";
			$postFixStr = "__{$baseStr}_{$styleBlockStrUpper}_END__";

			$newReplaceText = <<<TEXT

/*{$preFixStr}*/
/*{$postFixStr}*/

TEXT;

			$insertStyleTxt = '';
			if(!$isAllDisable && isset($orderUserPostDataMapByBlockId[$styleBlockStr])){
				foreach ($orderUserPostDataMapByBlockId[$styleBlockStr] as $orderUserPostData){
					if($isAddTitle){
						$postTitle = $orderUserPostData['title'];
						$insertStyleTxt .= '/* * * * * * * * * * * * * * * * * * * *' . "\n";
						$insertStyleTxt .= ' * ' . $postTitle . "\n";
						$insertStyleTxt .= ' * * * * * * * * * * * * * * * * * * * */' . "\n";
					}

					$content = $orderUserPostData['content'];
					$insertStyleTxt .= $content . "\n";
				}
			}

			$warnMsgTop    = __('DO_NOT_CHANGE_UP_COMMENT',self::APP_LANG_DMN);
			$warnMsgBottom = __('DO_NOT_CHANGE_BOTTOM_COMMENT',self::APP_LANG_DMN);
			$newReplaceText = <<<TEXT

/*{$preFixStr}*/
/*** {$warnMsgTop} ***/
{$insertStyleTxt}
/*** {$warnMsgBottom} ***/
/*{$postFixStr}*/

TEXT;


			$initialText .= $newReplaceText;
//			preg_match("/(\/\*{$preFixStr}\*\/)(.*)(\/\*{$postFixStr}\*\/)/u", $contents, $return);
			preg_match("/\/\*{$preFixStr}\*\/.*?\/\*{$postFixStr}\*\//us", $contents, $return);
			if($return){
				$contents = preg_replace(
					"/\/\*{$preFixStr}\*\/.*?\/\*{$postFixStr}\*\//us",
					''.
					'',
					$contents);

//				error_log(__FILE__ . '('.__LINE__.')['.__FUNCTION__.']$newReplaceText='.$newReplaceText);
				$isExistUpdateTarget = false;
//				$contents = preg_replace(
//					"/\/\*{$preFixStr}\*\/(.*)\/\*{$postFixStr}\*\//s",
//					''.$newReplaceText.
//					'',
//					$contents);

			}
		}

		if(!$isExistUpdateTarget){
			$contents .= "" . $initialText ."" ;
		}
		$themeStyleInfo['newContent'] = $contents;

		return $themeStyleInfo;
	}


	/**
	 * 公開中のデータを取得
	 * @return array
	 */
	private function getCurrentPublicPostData(){

		$args = [
			'post_type'			=> self::APP_POST_TYPE,
			'post_status' => [
				'publish',
			],
		];

		$the_query = new WP_Query( $args );

		$postDataMap = [];
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();

				$post_id = get_the_ID();
				$postItem = [];
				$postItem['post_id'] = $post_id;
				$postItem['title'] = get_the_title();
				$postItem['content'] = get_the_content();

				$postDataMap[$post_id] = $postItem;
			}
			/* Restore original Post Data */
			wp_reset_postdata();
		}

		$postMetaDataMap = [];
		if($postDataMap){
			$columnList = [
				self::DATA_COL_NAME_ITEM_ORDER,
				self::DATA_COL_NAME_ITEM_WRITE_BLOCK,
			];

			foreach ($postDataMap as $postItem){
				$post_id = $postItem['post_id'];
				$metaTmp = [];
				foreach ($columnList as $column_name){
					if($column_name === self::DATA_COL_NAME_ITEM_WRITE_BLOCK){
						$data = $this->getCurrentWriteBlockKey($post_id);
					} else {
						$data = $this->getCustomPostMetaData($post_id, $column_name);

					}

					$metaTmp[$column_name] = $data;
				}
				$metaTmp['post_id'] = $post_id;
				$postMetaDataMap[$post_id] = $metaTmp;
			}
		}

		return [
			$postDataMap,
			$postMetaDataMap
		];
	}


	/**
	 * 公開中のデータを並び替える
	 * @param $postDataMap
	 * @param $postMetaDataMap
	 * @return array
	 */
	private function orderUsePostData($postDataMap,
									  $postMetaDataMap){

		$orderUserPostDataMapByBlockId = [];
		foreach ($postMetaDataMap as $post_id => $postMetaData){
			$write_block = $postMetaData[self::DATA_COL_NAME_ITEM_WRITE_BLOCK];
			$tmp = $postDataMap[$post_id];
			$tmp += $postMetaData ;
			$orderUserPostDataMapByBlockId[$write_block][] = $tmp;
		}


		foreach ($orderUserPostDataMapByBlockId as $write_block => &$postMetaDataList){
			uasort($postMetaDataList, function($item_a, $item_b){

				$order_a = $item_a[self::DATA_COL_NAME_ITEM_ORDER];
				$order_b = $item_b[self::DATA_COL_NAME_ITEM_ORDER];

				if($order_a > $order_b){
					return -1;
				}
				else if($order_a < $order_b){
					return 1;
				}

				if($item_a['post_id'] < $item_b['post_id']){
					return -1;
				}
				return 1;
			});
		}

		return $orderUserPostDataMapByBlockId;
	}

	// </editor-fold>


	// <editor-fold desc="管理画面">

	// <editor-fold desc="メニュー">
	public function add_menu_page() {
		add_submenu_page( 'options-general.php',
			__('style.css Manage', self::APP_LANG_DMN),
			__('style.css Manage', self::APP_LANG_DMN),
			'manage_options',
			MCH_MCSCM_PLUGIN_DIR . 'mch-my-custom-style-css-manager.php',
			array($this, 'general_option_page')
		);
	}


	/**
	 */
	function create_link_post_type() {
		register_post_type(
			self::APP_POST_TYPE,
			array(
				'label'					=> __('style.css Manage', self::APP_LANG_DMN),
				'public'				=> false,
				'publicly_queryable'	=> false,
				'has_archive'			=> false,
				'show_ui'				=> true,
				'exclude_from_search'	=> true,
				'menu_position'			=> 22,
				'supports'				=> [ 'title' ],
				'menu_icon'				=> 'dashicons-admin-appearance',
				'rewrite' => array('slug' => self::APP_PREFIX . '_'),
			)
		);

		// TODO add category
		$args = array(
			'label'		=> $this->_t('style管理カテゴリー'),
			'labels'	=> array(
				'popular_items'	=> $this->_t('style管理カテゴリー'),
				'edit_item'		=> $this->_t('style管理カテゴリーを編集'),
				'add_new_item'	=> $this->_t('style管理カテゴリーを追加'),
				'search_items'	=> $this->_t('style管理カテゴリーを検索'),
			),
			'public'		=> false,
			'show_ui'		=> true,
			'hierarchical'	=> true,
		);
//		register_taxonomy( self::LINK_TERM_NAME, array( self::LINK_POST_TYPE ), $args );

	}


	// </editor-fold>



	public function style_manager_list_custom_filter($views ){
		return $views;
	}

	/**
	 * style管理一覧の表示カラム
	 * @param $columns
	 * @return mixed
	 */
	public function style_manager_list_columns($columns ) {
		$date = $columns['date'];
		unset($columns['date']);
		unset($columns['slug']);
		unset($columns['thumbnail']); //アイキャッチ

//		$columns[self::DATA_COL_NAME_ITEM_WRITE_BLOCK] = $this->_t('反映ブロック');
//		$columns[self::DATA_COL_NAME_ITEM_ORDER]       = $this->_t('並び順');
////		$columns[self::DATA_COL_NAME_ITEM_USE_THEME]   = $this->_t('適用するテーマ');
//		$columns[self::DATA_COL_NAME_ITEM_MEMO]        = $this->_t('メモ');

		$columns[self::DATA_COL_NAME_ITEM_WRITE_BLOCK] = __('block',self::APP_LANG_DMN);
		$columns[self::DATA_COL_NAME_ITEM_ORDER]       = __('order',self::APP_LANG_DMN);
//		$columns[self::DATA_COL_NAME_ITEM_USE_THEME]   = __('適用するテーマ',self::APP_LANG_DMN);
		$columns[self::DATA_COL_NAME_ITEM_MEMO]        = __('memo',self::APP_LANG_DMN);



		$columns['date'] = $date;
		return $columns;
	}

	/**
	 *  style管理一覧の情報表示
	 * @param $column_name
	 * @param $post_id
	 */
	function style_manager_list_column_value($column_name, $post_id ) {
		if (
			$column_name === self::DATA_COL_NAME_ITEM_ORDER
			|| $column_name === self::DATA_COL_NAME_ITEM_MEMO
		) {
			$data = $this->getCustomPostMetaData($post_id, $column_name);
			echo esc_attr($data);
		}
		else if (
			$column_name === self::DATA_COL_NAME_ITEM_WRITE_BLOCK
		) {
			$data = $this->getCurrentWriteBlockKey($post_id);
			$str = $this->getWriteBlockList()[$data];
			echo esc_attr($str);
		}
	}


	public function getCurrentWriteBlockKey($post_id){
		$formItemKey = self::DATA_COL_NAME_ITEM_WRITE_BLOCK;
		$value = $this->getCustomPostMetaData($post_id, $formItemKey);
		if(empty($value) || !isset($this->getWriteBlockList()[$value])){
			$value = 'middle';
		}
		return $value;
	}


	/**
	 * postmetaのデータを取得する
	 * @param $post_id
	 * @param $key
	 * @param bool $isAddAppPrefix
	 * @return mixed
	 */
	public function getCustomPostMetaData($post_id, $key, $isAddAppPrefix=true){
		if($isAddAppPrefix){
			$key  = $this->__ap($key);
		}
		$data = get_post_meta( $post_id, $key , true);
		return $data;
	}


	/**
	 * style管理一覧、ソート対象カラム指定
	 * @param $columns
	 * @return mixed
	 */
	function style_manager_list_manage_sortable($columns) {
		$columns[self::DATA_COL_NAME_ITEM_ORDER] = self::DATA_COL_NAME_ITEM_ORDER;
		return $columns;
	}


	/**
	 * style管理一覧ソート設定
	 * @param $vars
	 * @return array
	 */
	function style_manager_list_order_setting($vars) {
		if (is_admin()) {
			// 並び順でのソートを指定
			if (isset ($vars['orderby']) && self::DATA_COL_NAME_ITEM_ORDER === $vars['orderby']) {
				$vars = array_merge($vars, array (
					'meta_key' => $this->__ap(self::DATA_COL_NAME_ITEM_ORDER),
					'orderby' => 'meta_value_num',
				));
			}
		}
		return $vars;
	}


	/**
	 * 投稿・編集画面入力欄生成
	 */
	function add_meta_boxes() {

		add_meta_box(
			$this->__ap('input_form_main'),
			__('Contents to add to style.css', self::APP_LANG_DMN),
//			$this->_t('style.cssに追記する内容'),
			[ $this, 'input_form_main_contents'],
			self::APP_POST_TYPE, 'normal'
		);

		add_meta_box(
			$this->__ap('input_form_main_memo'),
			__('Notes related', self::APP_LANG_DMN),
//			$this->_t('メモ関連'),
			[ $this, 'input_form_main_memo' ],
			self::APP_POST_TYPE, 'normal'
		);

		add_meta_box(
			$this->__ap('input_form_side_option'),
			__('option', self::APP_LANG_DMN),
//			$this->_t('オプション'),
			[$this, 'input_form_side_option'],
			self::APP_POST_TYPE,
			'side',
			'default' );

	}


	/**
	 * メイン入力欄
	 */
	function input_form_main_contents()
	{
		/** @var WP_Post $post */
		global $post;
		global $post_ID;
		// codeMirror用スクリプト類
		$this->codeMirrorScript();

		include_once 'ui/input-form-main.php';
	}


	/**
	 * @param string $key
	 * @return string
	 */
	private function getPluginData($key){
		static $pluginData = null;

		if($pluginData === null){
			$pluginData = get_file_data(MCH_MCSCM_PLUGIN_DIR . 'mch-my-custom-style-css-manager.php', [
				'version' => 'Version',
				'author' => 'Author',
			]);
		}

		if(isset($pluginData[$key])){
			return $pluginData[$key];
		} else {
			return '';
		}
	}


	/**
	 * codeMirror用スクリプト類
	 */
	private function codeMirrorScript(){

		$v  = $this->getPluginData('version');
		$a  = plugins_url( '/', __FILE__). 'assets';
		$cm = $a . '/codemirror';


		wp_enqueue_style( 'jquery-ui', 'https://code.jquery.com/ui/1.12.0/themes/base/jquery-ui.css', array(), $v );
		wp_enqueue_script( 'mch-codemirror', $cm . '/lib/codemirror.js', array( 'jquery' ), $v, false);
		wp_enqueue_style( 'mch-codemirror', $cm . '/lib/codemirror.css', array(), $v, false );
		wp_enqueue_script( 'mch-scrollbars', $cm . '/addon/scroll/simplescrollbars.js', array('mch-codemirror'), $v, false );
		wp_enqueue_style( 'mch-scrollbars', $cm . '/addon/scroll/simplescrollbars.css', array(), $v );


		$cmm = $cm . '/mode/';
		wp_enqueue_script('cm-css', $cmm . 'css/css.js',               array('mch-codemirror'), $v, false);
		$cma = $cm . '/addon';
//		wp_enqueue_script( 'mch-codemirror-searchcursor', $cma . '/search/searchcursor.js', array('mch-codemirror'), $v );
//		wp_enqueue_script( 'mch-codemirror-search', $cma . '/search/search.js', array('mch-codemirror'), $v );
		wp_enqueue_script( 'mch-codemirror-dialog', $cma . '/dialog/dialog.js', array('mch-codemirror'), $v );
		wp_enqueue_style( 'mch-codemirror-dialog', $cma . '/dialog/dialog.css', array('mch-codemirror'), $v );

		wp_enqueue_script( 'mch-codemirror-foldcode', $cma . '/fold/foldcode.js', array('mch-codemirror'), $v );

		wp_enqueue_script( 'mch-codemirror-active-line', $cma . '/selection/active-line.js', array(), $v );


		wp_enqueue_style( 'mch-codemirror-my-css', plugins_url( '/', __FILE__) . '/css/myCodeMirror.css', array(), $v );

	}


	/**
	 * メモ欄
	 */
	function input_form_main_memo(){
		/** @var WP_Post $post */
		global $post;
		global $post_ID;
		include_once 'ui/input_form_main_memo.php';
	}


	/**
	 * サイド表示オプション
	 */
	function input_form_side_option() {
		/** @var WP_Post $post */
		global $post;
		global $post_ID;
		include_once 'ui/input_form_side_option.php';
	}


	/**
	 * @param int $post_id
	 */
	function save_post_data($post_id ) {
		$this->save_post_meta_data($post_id);
	}



	/**
	 * @param int $post_id
	 */
	function save_post_meta_data($post_id) {
		//更新時にはキャッシュ削除
		delete_transient( $this->__ap( 'itemlink_' . $post_id ) );

		$fromKey = $this->__ap( self::FORM_KEY_FROM_PAGE);
		//メインのページだけ更新させる
		if ( !empty($_POST) && isset($_POST[$fromKey]) && $_POST[$fromKey] === 'main' ) {
			$new_datas = [];
			$savePostMetaList = [
				self::DATA_COL_NAME_ITEM_ORDER,
				self::DATA_COL_NAME_ITEM_MEMO,
				self::DATA_COL_NAME_ITEM_WRITE_BLOCK,
			];
			foreach ($savePostMetaList AS $index => $postMetaKey) {
				if(isset($_POST[$postMetaKey])){
					$value = $_POST[$postMetaKey];
				} else {
					$value = '';
				}

				$new_datas[$postMetaKey] = $value;
				update_post_meta($post_id, $this->__ap($postMetaKey), $value);
			}

			$meta_datas=$new_datas;
			//キャッシュにいれる
			set_transient($this->__ap('itemlink_' . $post_id), $meta_datas, self::EXPIRED_TIME);
		}
	}


	/**
	 * @param $post_id
	 * @param $atts
	 * @param $new_data
	 * @param bool|true $is_api_data
	 *
	 * @return array
	 */
	public function get_tansient_meta_data($post_id,  $atts, $new_data, $is_api_data = true ) {
		return [];
	}


	// <editor-fold desc="設定画面">

	/**
	 * 設定画面
	 */
	function general_option_page() {
		$params = [];
		if ( isset( $_POST['_wp_http_referer'] ) ) {
			// token確認
			check_admin_referer(  self::APP_TITLE, self::APP_WP_NONCE_KEY );
			foreach($this->general_option_params as $key => $v ) {
				$value = $this->general_option_params[$key]['default_value'];
				if ( $v[ 'is_bool' ] ) {
					if(isset($_POST[$key])){
						$value = ($_POST[$key]) ? 1 : 0;
					} else {
						$value = ($value) ? 1 : 0;
					}
				} else {
					if(isset($_POST[$key])){
						$value = $_POST[$key];
					}
				}
				$params[ $key ] = $value;
				$this->general_option_params[ $key ][ 'value' ] = $value;
			}

			foreach( $params as $key => $value ) {
				update_option( $this->__ap( $key ), $value);
			}
			add_action( $this->__ap( 'admin_notices' ), [ $this, 'updated_success_message'] );
		} else {
			foreach($this->general_option_params as $key => $v) {
				$value = $this->getGeneralOptionVal( $key );
				if ($value) {
					$params[$key] = $value;
					$this->general_option_params[$key]['value'] = $value;
				}
			}
		}


		do_action( $this->__ap( 'admin_notices' ) );
		include_once 'ui/setting-form.php';
	}

	public function getGeneralOptionVal($key){
		$optVal = get_option($this->__ap($key), null);
		if($optVal === null){
			// default設定
			$optVal = $this->general_option_params[$key]['default_value'];
		}
		return $optVal;
	}

	public function updated_success_message() {
		echo '<div id="message" class="updated notice notice-success is-dismissible"><p>'. __('The setting was updated.', self::APP_LANG_DMN) .'</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">この通知を非表示にする</span></button></div>';
	}
	// </editor-fold>

	// </editor-fold>

}

