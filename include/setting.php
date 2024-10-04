<?php
/**
 * Linelogin
 * 管理画面でのプラグイン設定画面
 */
class lineloginSetting {
	/**
	 * 管理画面メニューの基本構造が配置された後に実行するアクションにフックする、
	 * 管理画面のトップメニューページを追加する関数
	 */
	static function set_plugin_menu() {
		// 設定のサブメニュー「LINE Login」を追加
		$page_hook_suffix = add_options_page(
			// ページタイトル：
			__( 'LINE Login Setting', linelogin::PLUGIN_NAME ),
			// メニュータイトル：
			'LINE Login',
			// 権限：
			// manage_optionsは以下の管理画面設定へのアクセスを許可
			// ・設定 > 一般設定
			// ・設定 > 投稿設定
			// ・設定 > 表示設定
			// ・設定 > ディスカッション
			// ・設定 > パーマリンク設定
			'manage_options',
			// ページを開いたときのURL(slug)：
			linelogin::SLUG__SETTINGS_FORM,
			// メニューに紐づく画面を描画するcallback関数：
			array( 'lineloginSetting', 'show_settings' )
		);
		add_action( "admin_print_styles-{$page_hook_suffix}", array( 'lineloginSetting', 'wpdocs_plugin_admin_styles' ) );
		add_action( "admin_print_scripts-{$page_hook_suffix}", array( 'lineloginSetting', 'wpdocs_plugin_admin_scripts' ) );
	}

	/**
	 * 初期設定画面を表示
	 */
	static function show_settings() {
		// プラグインのオプション
		$plugin_options = linelogin::get_all_options();

		// 初期設定の保存完了メッセージ
		if ( false !== ( $complete_message = get_transient( linelogin::TRANSIENT_KEY__SAVE_SETTINGS ) ) ) {
			$complete_message = linelogin::get_notice( $complete_message, linelogin::NOTICE_TYPE__SUCCESS );
		}

		// nonceフィールドを生成・取得
		$nonce_field = wp_nonce_field( linelogin::CREDENTIAL_ACTION__SETTINGS_FORM, linelogin::CREDENTIAL_NAME__SETTINGS_FORM, true, false );

		// 開いておくタブ
		$active_tab = 0;
		?>
		<?php echo $complete_message; ?>
		<form action="" method='post' id="line-auto-post-settings-form">
			<?php echo $nonce_field; ?>
			<div class="wrap ui-tabs ui-corner-all ui-widget ui-widget-content" id="stabs">
				<ul class="ui-tabs-nav ui-corner-all ui-helper-reset ui-helper-clearfix ui-widget-header">
					<?php
					foreach ( lineloginConst::$settings_option as $tab_name => $tab_details ) {
						echo "<li class='ui-tabs-tab ui-corner-top ui-state-default ui-tab'><a href='" . esc_attr( '#stabs-' . $tab_details['prefix'] ) . "'>" . esc_html( $tab_details['name'] ) . '</a></li>';
					}
					?>
				</ul>
				<?php
				foreach ( lineloginConst::$settings_option as $tab_name => $tab_details ) {
					switch ( $tab_name ) {
						default:
							// タブ
							?>
							<div id="<?php echo esc_attr( 'stabs-' . $tab_details['prefix'] ); ?>" class="ui-tabs-panel ui-corner-bottom ui-widget-content">
								<h3><?php echo esc_html( $tab_details['name'] ); ?></h3>
							<?php
							$ary_option = array();
							foreach ( $tab_details['fields'] as $option_key => $option_details ) {

								$options = array();

								// 不正メッセージ
								if ( false !== ( $invalid = get_transient( linelogin::INVALID_PREFIX . $option_key ) ) ) {
									$options['invalid'] = linelogin::get_error_bar( $invalid, linelogin::NOTICE_TYPE__ERROR );
									$active_tab         = intval( $tab_details['prefix'] ) - 1;
								} else {
									$options['invalid'] = '';
								}
								// パラメータ名
								$options['param'] = linelogin::PARAMETER_PREFIX . $option_key . ( isset( $option_details['isMulti'] ) && $option_details['isMulti'] == true ? '[]' : '' );

								// 設定値
								if ( false === ( $value = get_transient( linelogin::TRANSIENT_PREFIX . $option_key ) ) ) {
									// 無ければoptionsテーブルから取得
									$value = $plugin_options[ $option_key ];
									// それでもなければデフォルト値
								}
								$options['value'] = is_array( $value ) ? $value : esc_html( $value );

								$error_class = $options['invalid'] ? 'class="error-message" ' : '';
								$required    = isset( $option_details['required'] ) && $option_details['required'] ? 'required' : '';
								$hint        = isset( $option_details['hint'] ) ? "<a href=# title='" . esc_attr( $option_details['hint'] ) . "'><span class='ui-icon ui-icon-info'></span></a>" : '';
								$size        = isset( $option_details['size'] ) && $option_details['size'] ? 'size="' . esc_attr( $option_details['size'] ) . '" ' : '';
								echo <<< EOM
                        <p>
EOM;
								if ( $option_details['type'] != 'hidden' ) {
									echo "<label for='" . esc_attr( $options['param'] ) . "' " . esc_html( $error_class ) . '>' . esc_html( $option_details['label'] ) . ':</label>';
								}
								switch ( $option_details['type'] ) {
									case 'select':
									case 'multiselect':
										// セレクトボックスを出力
										$select  = "<select name='" . esc_attr( $options['param'] ) . "' " . ( $option_details['type'] === 'multiselect' ? "multiple class='sll-multi-select' " : '' ) . '>';
										$select .= linelogin::make_html_select_options( $option_details['list'], $options['value'] );
										$select .= "</select>{$hint}";
										echo $select;
										break;
									case 'color':
										// カラーピッカーを出力
										echo "<input type='text' name='" . esc_attr( $options['param'] ) . "' value='" . esc_attr( $options['value'] ) . "' class='sll-color-picker' data-default-color='" . esc_attr( $option_details['default'] ) . "' {$required} {$size}/>{$hint}";
										break;
									case 'spinner':
										// スピナーを出力
										echo "<input type='number' name='" . esc_attr( $options['param'] ) . "' value='" . esc_attr( $options['value'] ) . "' {$required} {$size} />{$hint}";
										break;
									case 'hidden':
										echo "<input type='hidden' name='" . esc_attr( $options['param'] ) . "' value='" . esc_attr( $options['value'] ) . "' {$required} />";
										break;
									default:
										// テキストボックス出力
										echo "<input type='text' name='" . esc_attr( $options['param'] ) . "' value='" . esc_attr( $options['value'] ) . "' {$required} {$size} />{$hint}";
								}
								echo <<< EOM
                                {$options['invalid']}
                        </p>
EOM;
							}
							echo <<< EOM
                    </div>
EOM;
							break;
					}
				}
				$sll_json = json_encode(
					array(
						'active_tab' => $active_tab,
					)
				);
				// 送信ボタンを生成・取得
				$submit_button = get_submit_button( __( 'Save', linelogin::PLUGIN_NAME ) );
		?>
		</div><!-- stabs -->
		<?php echo $submit_button; ?>
		</form>
		<script>
			var sll_json = JSON.parse('<?php echo $sll_json; ?>');
		</script>
		<?php
	}

	/**
	 * 初期設定を保存するcallback関数
	 */
	static function save_settings() {
		// nonceで設定したcredentialをPOST受信した場合
		if ( isset( $_POST[ linelogin::CREDENTIAL_NAME__SETTINGS_FORM ] ) && $_POST[ linelogin::CREDENTIAL_NAME__SETTINGS_FORM ] ) {
			// nonceで設定したcredentialのチェック結果が問題ない場合
			if ( check_admin_referer( linelogin::CREDENTIAL_ACTION__SETTINGS_FORM, linelogin::CREDENTIAL_NAME__SETTINGS_FORM ) ) {
				$valid = true;

				// チャンネル以外のオプション値チェック
				$plugin_options = array();
				foreach ( lineloginConst::$settings_option as $tab_name => $tab_details ) {
					foreach ( $tab_details['fields'] as $option_key => $option_details ) {
						if ( $option_details['isMulti'] ) {
							$value = $_POST[ linelogin::PARAMETER_PREFIX . $option_key ];
							foreach ( $value as $key => $tmp ) {
								$value[ sanitize_text_field( $key ) ] = trim( sanitize_text_field( $tmp ) );
							}
						} else {
							$value = trim( sanitize_text_field( $_POST[ linelogin::PARAMETER_PREFIX . $option_key ] ) );
						}
						if ( self::is_empty( $value ) && $option_details['required'] ) {
							set_transient( linelogin::INVALID_PREFIX . $option_key, sprintf( __( '"%s" is required.', linelogin::PLUGIN_NAME ), $option_details['label'] ), linelogin::TRANSIENT_TIME_LIMIT );
							$valid = false;
						} elseif ( isset( $option_details['regex'] ) && ! self::is_empty( $value ) && ! preg_match( $option_details['regex'], $value ) ) {
							set_transient( linelogin::INVALID_PREFIX . $option_key, sprintf( __( '"%s" is invalid.', linelogin::PLUGIN_NAME ), $option_details['label'] ), linelogin::TRANSIENT_TIME_LIMIT );
							$valid = false;
						}
						$plugin_options[ $option_key ] = $value;
					}
				}

				// すべてのチャンネルの値をチェックして、なお有効フラグがTrueの場合
				if ( $valid ) {
					$complete_message = __( 'Settings saved.', linelogin::PLUGIN_NAME );
					// プラグインオプションを保存
					update_option( linelogin::OPTION_KEY__SETTINGS, $plugin_options );
					// 保存が完了したら、完了メッセージをTRANSIENTに5秒間保持
					set_transient( linelogin::TRANSIENT_KEY__SAVE_SETTINGS, $complete_message, linelogin::TRANSIENT_TIME_LIMIT );
				} else {
					// 有効フラグがFalseの場合
					foreach ( lineloginConst::$settings_option as $tab_name => $tab_details ) {
						foreach ( $tab_details['fields'] as $option_key => $option_details ) {
							if ( $option_details['isMulti'] ) {
								$value = $_POST[ linelogin::PARAMETER_PREFIX . $option_key ];
								foreach ( $value as $key => $tmp ) {
									$value[ sanitize_text_field( $key ) ] = trim( sanitize_text_field( $tmp ) );
								}
							} else {
								$value = trim( sanitize_text_field( $_POST[ linelogin::PARAMETER_PREFIX . $option_key ] ) );
							}
							set_transient( linelogin::TRANSIENT_PREFIX . $option_key, $value, linelogin::TRANSIENT_TIME_LIMIT );
						}
					}
					// (一応)初期設定の保存完了メッセージを削除
					delete_transient( linelogin::TRANSIENT_KEY__SAVE_SETTINGS );
				}
				// 設定画面にリダイレクト
				wp_safe_redirect( menu_page_url( linelogin::SLUG__SETTINGS_FORM ), 303 );
			}
		}
	}

	// 管理画面用にスクリプト読み込み
	static function wpdocs_plugin_admin_scripts() {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core', false, array( 'jquery' ) );
		wp_enqueue_script( 'jquery-ui-tabs', false, array( 'jquery-ui-core' ) );
		wp_enqueue_script( 'jquery-ui-tooltip', false, array( 'jquery-ui-core' ) );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'jquery-ui-multiselect-widget', plugins_url( 'js/jquery.multiselect.min.js', __DIR__ ), array( 'jquery-ui-core' ), '3.0.1', true );
		$setting_js = 'js/sll_setting.js';
		wp_enqueue_script( linelogin::PLUGIN_PREFIX . 'admin', plugins_url( $setting_js, __DIR__ ), array( 'jquery-ui-tabs', 'wp-color-picker', 'jquery-ui-multiselect-widget' ), filemtime( plugin_dir_path( __DIR__ ) . $setting_js ), true );
		wp_set_script_translations( linelogin::PLUGIN_PREFIX . 'admin', linelogin::PLUGIN_NAME, plugin_dir_path( __DIR__ ) . 'languages' );
	}

	// 管理画面用にスタイル読み込み
	static function wpdocs_plugin_admin_styles() {
		$jquery_ui_css = 'css/jquery-ui.css';
		wp_enqueue_style( linelogin::PLUGIN_ID . '-admin-ui-css', plugins_url( $jquery_ui_css, __DIR__ ), array(), filemtime( plugin_dir_path( __DIR__ ) . $jquery_ui_css ) );
		wp_enqueue_style( 'wp-color-picker' );
		$setting_css = 'css/sll_setting.css';
		wp_enqueue_style( linelogin::PLUGIN_PREFIX . 'admin-css', plugins_url( $setting_css, __DIR__ ), array(), filemtime( plugin_dir_path( __DIR__ ) . $setting_css ) );
		$multiselect_css = 'css/jquery.multiselect.css';
		wp_enqueue_style( linelogin::PLUGIN_PREFIX . 'multiselect-css', plugins_url( $multiselect_css, __DIR__ ), array(), filemtime( plugin_dir_path( __DIR__ ) . $multiselect_css ) );
	}

	static function is_empty( $var = null ) {
		if ( empty( $var ) && 0 !== $var && '0' !== $var ) { // 論理型のfalseを取り扱う場合は、更に「&& false !== $var」を追加する
			return true;
		} else {
			return false;
		}
	}
}
