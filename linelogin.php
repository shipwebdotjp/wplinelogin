<?php

/*
Plugin Name: WP LINE Login
Plugin URI: https://blog.shipweb.jp/wplinelogin/
Description: Add Login with LINE feature.
Version: 1.3.0
Author: shipweb
Author URI: https://blog.shipweb.jp/about
License: GPLv3
*/

/*
	Copyright 2021 shipweb (email : shipwebdotjp@gmail.com)
	https://www.gnu.org/licenses/gpl-3.0.txt
*/

//add_action('init', 'linelogin::instance');

require_once(plugin_dir_path(__FILE__) . 'include/setting.php');
require_once(plugin_dir_path(__FILE__) . 'include/const.php');

class linelogin {

	/**
	 * このプラグインのバージョン
	 */
	const VERSION = '1.3.0';

	/**
	 * このプラグインのID：Shipweb Line Login
	 */
	const PLUGIN_ID = 'sll';

	/**
	 * このプラグインの名前：Line Login
	 */
	const PLUGIN_NAME = 'linelogin';

	/**
	 * PREFIX
	 */
	const PLUGIN_PREFIX = self::PLUGIN_ID . '_';

	/**
	 * CredentialAction：設定
	 */
	const CREDENTIAL_ACTION__SETTINGS_FORM = self::PLUGIN_ID . '-nonce-action_settings-form';

	/**
	 * CredentialName：設定
	 */
	const CREDENTIAL_NAME__SETTINGS_FORM = self::PLUGIN_ID . '-nonce-name_settings-form';

	/**
	 * SESSIONのキー：STATE(TEMP)
	 */
	const SESSION_KEY__STATES = self::PLUGIN_PREFIX . 'oauth2_states';

	/**
	 * Cookieのキー：LINEID(TEMP)
	 */
	const COOKIE_KEY__LINEID = self::PLUGIN_PREFIX . 'unlinked_line_id';

	/**
	 * Cookieのキー：REDIRECT(TEMP)
	 */
	const COOKIE_KEY__REDIRECT_TO = self::PLUGIN_PREFIX . 'redirect_to';

	/**
	 * ユーザーメタキー：line
	 */
	const META_KEY__LINE = self::PLUGIN_PREFIX . 'lineid';

	/**
	 * ユーザーメタキー：line profile
	 */
	const META_KEY__LINEPROFILE = self::PLUGIN_PREFIX . 'lineprofile';

	/**
	 * ユーザーメタキー：WP LINE Connectとの連携用
	 */
	const META_KEY__LINEcONNECT = 'line';

	/**
	 * パラメータキー：status
	 */
	const PARAMETER_KEY__STATUS = self::PLUGIN_PREFIX . 'status';

	/**
	 * パラメータキー：code
	 */
	const PARAMETER_KEY__CODE = self::PLUGIN_PREFIX . 'code';

	/**
	 * パラメータキー：next
	 */
	const PARAMETER_KEY__NEXT = self::PLUGIN_PREFIX . 'next';

	/**
	 * パラメータキー：UID
	 */
	const PARAMETER_KEY__UID = self::PLUGIN_PREFIX . 'uid';

	/**
	 * パラメータキー：MODE
	 */
	const PARAMETER_KEY__MODE = self::PLUGIN_PREFIX . 'mode';

	/**
	 * OPTIONSテーブルのキー：Setting
	 */
	const OPTION_KEY__SETTINGS = self::PLUGIN_PREFIX . 'settings';

	/**
	 * 画面のslug：トップ
	 */
	const SLUG__SETTINGS_FORM = self::PLUGIN_ID . '-settings-form';

	// const SETTINGS_OPTIONS = 

	/**
	 * パラメーターのPREFIX
	 */
	const PARAMETER_PREFIX = self::PLUGIN_PREFIX;

	/**
	 * 一時入力値保持用のPREFIX
	 */
	const TRANSIENT_PREFIX = self::PLUGIN_PREFIX . 'temp-';

	/**
	 * 不正入力値エラー表示のPREFIX
	 */
	const INVALID_PREFIX = self::PLUGIN_PREFIX . 'invalid-';


	/**
	 * TRANSIENTキー(保存完了メッセージ)：設定
	 */
	const TRANSIENT_KEY__SAVE_SETTINGS = self::PLUGIN_PREFIX . 'save-settings';

	/**
	 * TRANSIENTのタイムリミット：5秒
	 */
	const TRANSIENT_TIME_LIMIT = 5;

	/**
	 * 通知タイプ：エラー
	 */
	const NOTICE_TYPE__ERROR = 'error';

	/**
	 * 通知タイプ：警告
	 */
	const NOTICE_TYPE__WARNING = 'warning';

	/**
	 * 通知タイプ：成功
	 */
	const NOTICE_TYPE__SUCCESS = 'success';

	/**
	 * 通知タイプ：情報
	 */
	const NOTICE_TYPE__INFO = 'info';

	/**
	 * 正規表現：ChannelAccessToken
	 */
	const REGEXP_CHANNEL_ACCESS_TOKEN = '/^[a-zA-Z0-9+\/=]{100,}$/';

	/**
	 * 正規表現：ChannelSecret
	 */
	const REGEXP_CHANNEL_SECRET = '/^[a-z0-9]{30,}$/';

	/**
	 * 正規表現：LINEユーザーID
	 */
	const REGEXP_LINE_USER_ID = '/^U[a-z0-9]{32}$/';

	/**
	 * 正規表現：ChannelSecret
	 */
	const ENDPOINTS = array(
		'login' => 'login',
		'signup' => 'register',
		'register' => 'register',
		'link' => 'home',
	);

	const MODE_LOGIN = 'login';
	const MODE_LINK = 'link';
	const MODE_UNLINK = 'unlink';

	/**
	 * 設定データ
	 */
	public $ini;

	static function instance() {
		return new self();
	}


	/**
	 * HTMLのOPTIONタグを生成・取得
	 */
	static function makeHtmlSelectOptions($list, $selected, $label = null) {
		$html = '';
		foreach ($list as $key => $value) {
			$html .= '<option class="level-0" value="' . $key . '"';
			if ($key == $selected || (is_array($selected) && in_array($key, $selected))) {
				$html .= ' selected="selected"';
			}
			$html .= '>' . (is_null($label) ? $value : $value[$label]) . '</option>';
		}
		return $html;
	}

	/**
	 * 通知タグを生成・取得
	 * @param message 通知するメッセージ
	 * @param type 通知タイプ(error/warning/success/info)
	 * @retern 通知タグ(HTML)
	 */
	static function getNotice($message, $type) {
		return
			'<div class="notice notice-' . $type . ' is-dismissible">' .
			'<p><strong>' . esc_html($message) . '</strong></p>' .
			'<button type="button" class="notice-dismiss">' .
			'<span class="screen-reader-text">Dismiss this notice.</span>' .
			'</button>' .
			'</div>';
	}

	static function getErrorBar($message, $type) {
		return '<div class="error">' . esc_html($message) . '</div>';
	}

	/**
	 * コンストラクタ
	 */
	function __construct() {
		add_action('init', function () {
			// オプションの読み込み
			$this->ini = $this->get_all_options();

			//ログイン時、LINEアカウント連携
			add_action('wp_login', [$this, 'redirect_account_link'], 10, 2);
			//新規登録時、LINEアカウント連携
			add_action('user_register', [$this, 'register_account_link'], 10, 2);
			//LINEログインURLにアクセスされたときLINEログイン画面へリダイレクト
			add_action('template_redirect',  [$this, 'redirect_to_line'], 10, 2);
			//ログインボタンショートコードのフック
			add_shortcode('line_login_link',  [$this, 'login_link_shortcode_handler_function']);
			//メッセージ表示ショートコードのフック
			add_shortcode('line_login_message',  [$this, 'login_message_shortcode_handler_function']);
			// 管理画面を表示中、且つ、ログイン済、且つ、特権管理者or管理者の場合
			if (is_admin() && is_user_logged_in() && (is_super_admin() || current_user_can('administrator'))) {
				// 管理画面のトップメニューページを追加
				add_action('admin_menu', ['lineloginSetting', 'set_plugin_menu']);
				// 管理画面各ページの最初、ページがレンダリングされる前に実行するアクションに、
				// 初期設定を保存する関数をフック
				add_action('admin_init', ['lineloginSetting', 'save_settings']);
			}

			//ユーザープロフィールにLINEユーザーIDを追加
			add_action('edit_user_profile', [$this, 'register_line_user_id_profilebox']);
			add_action('show_user_profile', [$this, 'register_line_user_id_profilebox']);
			//ユーザープロフィールにLINEユーザーIDを保存
			add_action('profile_update', [$this, 'update_line_user_id_profilebox']);
			//ユーザーのMETAにLINEユーザーIDとプロフィール情報を登録(LINE Connect連携用)
			add_action('line_login_update_user_meta', [$this, 'line_login_update_user_meta'], 10, 3);
			//ユーザーのMETAからLINEユーザーIDとプロフィール情報を削除(LINE Connect連携用)
			add_action('line_login_delete_user_meta', [$this, 'line_login_delete_user_meta'], 10, 2);
		});
		//テキストドメイン呼出し
		load_plugin_textdomain(self::PLUGIN_NAME, false, dirname(plugin_basename(__FILE__)) . '/languages');

		lineloginConst::initialize();
	}

	/**
	 * 複合化：AES 256
	 * @param edata 暗号化してBASE64にした文字列
	 * @param string 複合化のパスワード
	 * @return 複合化された文字列
	 */
	static function decrypt($edata, $password) {
		$data = base64_decode($edata);
		$salt = substr($data, 0, 16);
		$ct = substr($data, 16);
		$rounds = 3; // depends on key length
		$data00 = $password . $salt;
		$hash = array();
		$hash[0] = hash('sha256', $data00, true);
		$result = $hash[0];
		for ($i = 1; $i < $rounds; $i++) {
			$hash[$i] = hash('sha256', $hash[$i - 1] . $data00, true);
			$result .= $hash[$i];
		}
		$key = substr($result, 0, 32);
		$iv  = substr($result, 32, 16);
		return openssl_decrypt($ct, 'AES-256-CBC', $key, 0, $iv);
	}

	/**
	 * 暗号化: AES 256
	 *
	 * @param data $data
	 * @param string $password
	 * @return base64 encrypted data
	 */
	static function encrypt($data, $password) {
		// Set a random salt
		$salt = openssl_random_pseudo_bytes(16);
		$salted = '';
		$dx = '';
		// Salt the key(32) and iv(16) = 48
		while (strlen($salted) < 48) {
			$dx = hash('sha256', $dx . $password . $salt, true);
			$salted .= $dx;
		}
		$key = substr($salted, 0, 32);
		$iv  = substr($salted, 32, 16);
		$encrypted_data = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
		return base64_encode($salt . $encrypted_data);
	}

	/**
	 * 登録されているオプション情報を全て返す
	 */
	static function get_all_options() {
		$options = get_option(self::OPTION_KEY__SETTINGS); //オプションを取得
		foreach (lineloginConst::$settings_option as $tab_name => $tab_details) {
			//flatten
			foreach ($tab_details['fields'] as $option_key => $option_details) {
				if (!isset($options[$option_key])) {
					$options[$option_key] = $option_details['default'];
				}
			}
		}
		return $options;
	}

	/**
	 * 登録されているオプションの値を返す
	 */
	static function get_option($option_name) {
		$options = get_option(self::OPTION_KEY__SETTINGS); //オプションを取得
		if (isset($options[$option_name])) {
			return $options[$option_name];
		}
		foreach (lineloginConst::$settings_option as $tab_name => $tab_details) {
			//flatten
			foreach ($tab_details['fields'] as $option_key => $option_details) {
				if ($option_name == $option_key) {
					return $option_details['default'];
				}
			}
		}
		return null;
	}

	/**
	 * LINEログイン開始
	 */
	function redirect_to_line() {
		// $req_uri = get_query_var('pagename');
		$isline_page = is_page(rtrim($this->ini['callback_url'], '/'));

		// if(in_array($req_uri, array_keys(self::ENDPOINTS), true) || $req_uri == rtrim($this->ini['callback_url'], '/')){
		if ($isline_page) {
			parse_str($_SERVER['QUERY_STRING'], $req_vars);
			if (isset($req_vars[self::PARAMETER_KEY__MODE]) && $req_vars[self::PARAMETER_KEY__MODE] == self::MODE_UNLINK) {
				//連係解除リンク
				if (is_user_logged_in()) {
					self::delete_user_meta(get_current_user_id());
					/*
                    if($this->ini['login_mode'] == "lineonly"){
                        // LINE Login Only Mode -> Delete User
                        require_once(ABSPATH.'wp-admin/includes/user.php' );
                        wp_delete_user(get_current_user_id());
                    }
                    */
					$redirect_to = add_query_arg(array(
						self::PARAMETER_KEY__STATUS => 'info',
						self::PARAMETER_KEY__CODE => 'unlink_complete',
						self::PARAMETER_KEY__NEXT => self::MODE_LINK,
					), self::get_url('message'));
					wp_safe_redirect($redirect_to);
					exit();
				}
			}


			if (session_status() !== PHP_SESSION_ACTIVE) {
				session_start();    //セッション開始
			}
			// OAuth2のクライアントライブラリ読み込み
			require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';
			// LINEのOAuth2クライアントインスタンス作成
			$provider = new Osapon\OAuth2\Client\Provider\Line([
				'clientId'     => $this->ini['login_channel_id'],
				'clientSecret' => $this->ini['login_channel_secret'],
				'redirectUri'  => self::get_url('callback'),
			]);

			if (!empty($req_vars['error'])) {
				//エラーが発生した場合
				$redirect_to = add_query_arg(array(
					self::PARAMETER_KEY__STATUS => 'error',
					self::PARAMETER_KEY__CODE => !empty($this->ini[$req_vars['error'] . '_message']) ? $req_vars['error'] : 'auth_error',
					self::PARAMETER_KEY__NEXT => self::MODE_LINK,
				), self::get_url('message'));
				self::logging('error: auth_error: ' . $req_vars['error'] . ' : ' . $_GET['error_description']);
				wp_safe_redirect($redirect_to);
				exit;
			} elseif (empty($req_vars['code'])) {
				// codeのないリクエスト=ログイン開始
				//認可要求時のオプションパラメーター
				$scopes = [
					'openid',
					'profile',
				];
				if ($this->ini['login_mode'] == "lineonly") {
					$scopes[] = 'email';
				}
				$option = [
					'scope' => $scopes,
				];
				if ($this->ini['bot_prompt'] !== "off") {
					$option['bot_prompt'] = $this->ini['bot_prompt'];
				}
				if ($this->ini['initial_amr_display'] !== "off") {
					$option['initial_amr_display'] = $this->ini['initial_amr_display'];
				}
				$authUrl = $provider->getAuthorizationUrl($option);
				$_SESSION[self::SESSION_KEY__STATES] = $provider->getState();   //Stateをセッションに保持
				$_SESSION['lastpage'] = isset($req_vars[self::PARAMETER_KEY__MODE]) ? $req_vars[self::PARAMETER_KEY__MODE] : self::MODE_LOGIN;                   //リンク元をセッションに保持
				if ($this->ini['directlink'] == "on" && isset($req_vars[self::PARAMETER_KEY__UID])) {
					$sll_user_login = self::decrypt($req_vars[self::PARAMETER_KEY__UID], $this->ini['encrypt_password']);
					if ($sll_user_login) {
						$_SESSION[self::PARAMETER_KEY__UID] = $sll_user_login;
					}
					self::logging('from login link user: ' . $sll_user_login);
				}

				if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], "?") !== false) {
					parse_str(substr($_SERVER['HTTP_REFERER'], strpos($_SERVER['HTTP_REFERER'], "?") + 1), $ref_vars);
					self::logging('HTTP_REFERER: ' . substr($_SERVER['HTTP_REFERER'], strpos($_SERVER['HTTP_REFERER'], "?") + 1));
				}

				if (isset($ref_vars['redirect_to'])) {
					//リダイレクト先をCookieに格納
					setcookie(self::COOKIE_KEY__REDIRECT_TO, $ref_vars['redirect_to'], time() + 60 * 60, '/', "", TRUE, TRUE);   //Cookieにセット
				} else {
					setcookie(self::COOKIE_KEY__REDIRECT_TO, "", time() - 3600, '/'); //COKIE削除
				}
				self::logging('auth: ' . $authUrl);
				header('Location: ' . $authUrl);                    //LINE認証URLへリダイレクトさせる
				exit();
			} elseif (empty($req_vars['state']) || ($req_vars['state'] !== $_SESSION[self::SESSION_KEY__STATES])) { //stateがないか、stateが異なる場合はエラー
				unset($_SESSION[self::SESSION_KEY__STATES]);    //セッションのstateを削除
				$redirect_to = add_query_arg(array(
					self::PARAMETER_KEY__STATUS => 'error',
					self::PARAMETER_KEY__CODE => 'invalid_state',
					self::PARAMETER_KEY__NEXT => self::MODE_LINK,
				), self::get_url('message'));
				self::logging('error: invalid_state');
				wp_safe_redirect($redirect_to);
				exit();
			} else {
				unset($_SESSION[self::SESSION_KEY__STATES]);    //State削除
				// アクセストークンの取得
				$token = $provider->getAccessToken('authorization_code', [
					'code' => $req_vars['code'],
				]);
				self::logging('token: ' . $token);
				try {
					// ユーザープロフィールを取得
					//$ownerDetails = $provider->getResourceOwner($token);
					$client = new GuzzleHttp\Client([
						// Base URI is used with relative requests
						'base_uri' => 'https://api.line.me',
						// You can set any number of default request options.
						'timeout'  => 15.0,
					]);
					$accessTokenValues = $token->getValues();
					// self::logging('accessTokenValues '.print_r($accessTokenValues, true));
					$path = '/oauth2/v2.1/verify';
					$headers = [
						'Content-Type'              => 'application/x-www-form-urlencoded',
					];
					$request_params = [
						'id_token' => $accessTokenValues['id_token'],
						'client_id' => $this->ini['login_channel_id'],
					];
					$response = $client->request(
						'POST',
						$path,
						[
							'allow_redirects' => true,
							'headers'         => $headers,
							'form_params'     => $request_params,
						]
					);
					$body = $response->getBody();
					$stringBody = (string) $body;
					$jsonBody = json_decode($stringBody);
					//self::logging('id_token response: '.print_r($jsonBody, true));
					$ownerDetails = $jsonBody;
				} catch (Exception $e) {
					$redirect_to = add_query_arg(array(
						self::PARAMETER_KEY__STATUS => 'error',
						self::PARAMETER_KEY__CODE => 'userdetails_error',
						self::PARAMETER_KEY__NEXT => self::MODE_LINK,
					), self::get_url('message'));
					self::logging('error: userdetails_error. ' . print_r($e, true));
					wp_safe_redirect($redirect_to);
					exit();
				}

				$line_user_id = $ownerDetails->sub; //LINEユーザーIDを取得
				//メタ情報からLINEユーザーIDでユーザー検索
				$user_query = new WP_User_Query(array('meta_key' => self::META_KEY__LINE, 'meta_value' => $line_user_id));
				$users = $user_query->get_results();
				if (!empty($users)) {
					//LINE IDがすでにWPユーザーと連携されている場合
					$user =  $users[0]; //ユーザーの一人目
					$user_id = $user->ID; //IDを取得

					if (!is_user_logged_in()) {
						//未ログインの場合はそのユーザーでログイン
						//ログイン処理
						self::do_user_login($user_id, $user);
						if (isset($_COOKIE[self::COOKIE_KEY__REDIRECT_TO])) {
							$redirect_to = $_COOKIE[self::COOKIE_KEY__REDIRECT_TO];
							setcookie(self::COOKIE_KEY__REDIRECT_TO, "", time() - 3600, '/'); //COKIE削除
						} else {
							$redirect_to = self::get_url('home');
						}
					} else {
						//ログイン済みの場合
						if ($user_id != get_current_user_id()) {
							// LINE連携されているユーザーがログイン中のユーザーでない場合
							// 重複して連携はできないのでエラー表示
							$redirect_to = add_query_arg(array(
								self::PARAMETER_KEY__STATUS => 'error',
								self::PARAMETER_KEY__CODE => 'duplicate_error',
								self::PARAMETER_KEY__NEXT => self::MODE_LINK,
							), self::get_url('message'));
						} else {
							// LINE連携されているユーザーがログイン中のユーザーの場合
							$redirect_to = add_query_arg(array(
								self::PARAMETER_KEY__STATUS => 'error',
								self::PARAMETER_KEY__CODE => 'already_linked',
								self::PARAMETER_KEY__NEXT => self::MODE_LINK,
							), self::get_url('message'));
						}
					}
					wp_safe_redirect($redirect_to);
					exit();
				} else {
					// ユーザーの連携ステータスを取得
					$opts = array(
						'http' =>
						array(
							'header' => 'Authorization: Bearer ' . $token,
						)
					);
					$context = stream_context_create($opts);
					$result = file_get_contents('https://api.line.me/friendship/v1/status', false, $context);
					$friendFlag = json_decode($result, true);
					self::logging("friendstatus: " . $result);
					$line_user_data = [
						'id' => $line_user_id,
						'displayName' => $ownerDetails->name,
						'isFriend' => $friendFlag['friendFlag'],
					];
					if (isset($ownerDetails->picture)) {
						$line_user_data['pictureUrl'] = $ownerDetails->picture;
					}
					if (isset($ownerDetails->email)) {
						$line_user_data['email'] = $ownerDetails->email;
					}
					if ($this->ini['isFriendonly'] == "on" && $friendFlag['friendFlag'] === false) {
						//友だちのみ連携可能な設定で、友達でない場合エラー
						$redirect_to = add_query_arg(array(
							self::PARAMETER_KEY__STATUS => 'info',
							self::PARAMETER_KEY__CODE => 'nofriend_error',
							self::PARAMETER_KEY__NEXT => self::MODE_LINK,
						), self::get_url('message'));
						wp_safe_redirect($redirect_to);
						exit();
					}
					// 連携されていない場合は連携する
					//Wordpressユーザーのメタ情報にLINEユーザーIDを追加
					if (!is_user_logged_in()) {
						if (isset($_SESSION[self::PARAMETER_KEY__UID])) {
							//セッションに紐づけるユーザーログインが入っていれば連携させる（固有ログインLINKからログイン）
							$user_query = new WP_User_Query(array('search' => $_SESSION[self::PARAMETER_KEY__UID], 'search_columns' => array('user_login')));
							$users = $user_query->get_results();
							if (!empty($users)) {
								unset($_SESSION[self::PARAMETER_KEY__UID]);
								$user =  $users[0]; //ユーザーの一人目
								$user_id = $user->ID; //IDを取得
								$assigned_line_user_id = get_user_meta($user_id, self::META_KEY__LINE, true);
								if ($assigned_line_user_id) {
									//既に別のLINE連携されていたら
									// LINE連携されているLINEユーザーIDがログインしたLINEアカウントのLINEユーザーIDでない場合
									// 上書きして連携はできないのでエラー表示
									$redirect_to = add_query_arg(array(
										self::PARAMETER_KEY__STATUS => 'error',
										self::PARAMETER_KEY__CODE => 'overwrite_error',
										self::PARAMETER_KEY__NEXT => self::MODE_LINK,
									), self::get_url('message'));
									self::logging('overwrite_error: user_id=' . get_current_user_id());
								} else {
									//ユーザーメタにLINE IDをセット
									self::update_user_meta($user_id, $line_user_data);
									self::do_user_login($user_id, $user);
									$redirect_to = add_query_arg(array(
										self::PARAMETER_KEY__STATUS => 'info',
										self::PARAMETER_KEY__CODE => 'link_complete',
										self::PARAMETER_KEY__NEXT => self::MODE_LINK,
									), self::get_url('message'));
								}
								wp_safe_redirect($redirect_to);
								exit();
							}
						}
						if ($this->ini['login_mode'] == "lineonly") {
							// LINE Login Only Mode -> Create User & Login & Link
							$user_name = self::make_user_name($line_user_data['id']);
							$user_password = wp_generate_password(12, false);
							$display_name = $line_user_data['displayName'];
							$userdata = array(
								'user_login'  =>  $user_name,
								'user_pass'   =>  $user_password,
								'display_name' => $display_name,
							);
							if (isset($line_user_data['email'])) {
								$userdata['email'] = $line_user_data['email'];
							}
							$user_id = wp_insert_user($userdata);

							// ユーザー登録が成功した場合
							if (!is_wp_error($user_id)) {
								self::logging("User auto created: ID=" . $user_id);
								self::update_user_meta($user_id, $line_user_data);    //WPユーザーとLINE ID連携
								$user = get_user_by('id', $user_id);
								self::do_user_login($user_id, $user);   //ログイン処理
								$redirect_to = add_query_arg(array(
									self::PARAMETER_KEY__STATUS => 'info',
									self::PARAMETER_KEY__CODE => 'link_complete',
									self::PARAMETER_KEY__NEXT => self::MODE_LINK,
								), self::get_url('message'));
							} else {
								self::logging("User auto create failed." . $user_id->get_error_message());
								$redirect_to = add_query_arg(array(
									self::PARAMETER_KEY__STATUS => 'error',
									self::PARAMETER_KEY__CODE => 'goto_regist',
									self::PARAMETER_KEY__NEXT => self::MODE_LINK,
								), self::get_url('message'));
							}
						} else {
							//未ログインの場合はcookieにLINE IDを登録してからログインページ／登録ページへリダイレクト
							$encrypted_line_user_data = self::encrypt(json_encode($line_user_data), $this->ini['encrypt_password']);   //LINEユーザーIDの暗号化
							setcookie(self::COOKIE_KEY__LINEID, $encrypted_line_user_data, time() + 60 * 60, '/', "", TRUE, TRUE);   //Cookieにセット
							$next_code = $_SESSION['lastpage'] == self::MODE_LOGIN ? 'goto_login' : 'goto_regist';
							$next_slug = isset($_SESSION['lastpage']) && isset(self::ENDPOINTS[$_SESSION['lastpage']]) ? self::ENDPOINTS[$_SESSION['lastpage']] : self::MODE_LOGIN;

							$redirect_to = add_query_arg(array(
								self::PARAMETER_KEY__STATUS => 'info',
								self::PARAMETER_KEY__CODE => $next_code,
								self::PARAMETER_KEY__NEXT => $next_slug,
							), self::get_url($next_slug));
						}

						wp_safe_redirect($redirect_to);
						exit();
					} else {
						//ログイン済みの場合はログイン中のユーザーと関連付ける
						$user_id = get_current_user_id();
						$assigned_line_user_id = get_user_meta($user_id, self::META_KEY__LINE, true);
						$code = 'link_complete';
						if ($assigned_line_user_id) {
							//既に別のLINE連携されていたら
							$code = 'link_changed';
						}
						//ユーザーメタにLINE IDをセット
						self::update_user_meta($user_id, $line_user_data);
						$redirect_to = add_query_arg(array(
							self::PARAMETER_KEY__STATUS => 'info',
							self::PARAMETER_KEY__CODE => $code,
							self::PARAMETER_KEY__NEXT => self::MODE_LINK,
						), self::get_url('message'));
						wp_safe_redirect($redirect_to);
						exit();
					}
				}
			}
			exit();
		}
	}

	/*
    ログイン時にLINEログイン経由で未連携の場合は連携させる
    */
	function redirect_account_link($user_login, $current_user) {
		self::check_is_user_has_unlinked_line_id($current_user->ID);
	}

	/*
    新規登録時にLINEログイン経由で未連携の場合は連携させる
    */
	function register_account_link($user_id, $userdata) {
		self::check_is_user_has_unlinked_line_id($user_id);
	}

	/*
    CookieにLINE IDが保存されているかチェックし、保存されていれば連携
    */
	function check_is_user_has_unlinked_line_id($user_id) {
		if (isset($_COOKIE[self::COOKIE_KEY__LINEID])) {
			//COOKIEにLINE ID KEYがセットされていたら
			$encrypted_line_user_id = $_COOKIE[self::COOKIE_KEY__LINEID]; //COKIEからLINE IDを取得
			$line_user_data = json_decode(self::decrypt($encrypted_line_user_id, $this->ini['encrypt_password']), true);   //暗号化されているLINE IDを復号
			$line_user_id = $line_user_data['id'];
			$user_query = new WP_User_Query(array('meta_key' => self::META_KEY__LINE, 'meta_value' => $line_user_id));
			$users = $user_query->get_results();
			if (!empty($users)) {
				//LINE IDがすでにWPユーザーと連携されている場合
				$user =  $users[0]; //ユーザーの一人目
				$user_id = $user->ID; //IDを取得
				if ($user_id != get_current_user_id()) {
					// LINE連携されているユーザーがログイン中のユーザーでない場合
					// 重複して連携はできないのでエラー表示
					$redirect_to = add_query_arg(array(
						self::PARAMETER_KEY__STATUS => 'error',
						self::PARAMETER_KEY__CODE => 'duplicate_error',
						self::PARAMETER_KEY__NEXT => self::MODE_LINK,
					), self::get_url('message'));
					self::logging('duplicate_error: user_id=' . get_current_user_id());
				} else {
					// LINE連携されているユーザーがログイン中のユーザーの場合
					setcookie(self::COOKIE_KEY__LINEID, "", time() - 3600, '/'); //COKIE削除
					$redirect_to = add_query_arg(array(
						self::PARAMETER_KEY__STATUS => 'error',
						self::PARAMETER_KEY__CODE => 'already_linked',
						self::PARAMETER_KEY__NEXT => self::MODE_LINK,
					), self::get_url('message'));
					self::logging('already_linked: user_id=' . get_current_user_id());
				}
			} else {
				$assigned_line_user_id = get_user_meta($user_id, self::META_KEY__LINE, true);
				$code = 'link_complete';
				if ($assigned_line_user_id) {
					//既に別のLINEアカウントに連携されていたら
					$code = 'link_changed';
				}
				self::update_user_meta($user_id, $line_user_data);
				setcookie(self::COOKIE_KEY__LINEID, "", time() - 3600, '/'); //COKIE削除
				if (isset($_COOKIE[self::COOKIE_KEY__REDIRECT_TO])) {
					$redirect_to =  $_COOKIE[self::COOKIE_KEY__REDIRECT_TO];
					setcookie(self::COOKIE_KEY__REDIRECT_TO, "", time() - 3600, '/'); //COKIE削除
				} else {
					$redirect_to = add_query_arg(array(
						self::PARAMETER_KEY__STATUS => 'info',
						self::PARAMETER_KEY__CODE => $code,
						self::PARAMETER_KEY__NEXT => self::MODE_LINK,
					), self::get_url('message'));
				}
			}
			wp_safe_redirect($redirect_to);
			exit();
		}
	}

	/**
	 * 連携状態表示ショートコード実行
	 */
	function login_link_shortcode_handler_function($atts, $content = null, $tag = '') {
		$atts = wp_parse_args($atts, array(
			'login_label'  => $this->ini['login_label'],
			'unlinked_label'  => __('Unlinked to LINE', linelogin::PLUGIN_NAME),
			'linked_label'  => __('Linked to LINE', linelogin::PLUGIN_NAME),
			'unlinked_button'  => __('Link', linelogin::PLUGIN_NAME),
			'linked_button'  => __('Unlink', linelogin::PLUGIN_NAME),
		));
		if (is_user_logged_in()) {
			$user_id = get_current_user_id(); //現在のユーザーを取得
			if ($user_id != null) {
				$line_user_id = get_user_meta($user_id, self::META_KEY__LINE, true);
				if ($line_user_id) {
					$url = add_query_arg(array(
						self::PARAMETER_KEY__MODE => self::MODE_UNLINK,
					), self::get_url('callback'));
					$output = $atts['linked_label'] ? "<span class='line-login-label linked'>" . $atts['linked_label'] . "</span>" : "";
					$output .= "<a href='" . $url . "' class='line-login-link linked'>" . $atts['linked_button'] . "</a>";
				} else {
					$url = add_query_arg(array(
						self::PARAMETER_KEY__MODE => self::MODE_LINK,
					), self::get_url('callback'));
					$output = $atts['unlinked_label'] ? "<span class='line-login-label unlinked'>" . $atts['unlinked_label'] . "</span>" : "";
					$output .= "<a href='" . $url . "' class='line-login-link unlinked'>" . $atts['unlinked_button'] . "</a>";
				}
			}
		} else {
			$next_slug = isset($_GET[self::PARAMETER_KEY__NEXT]) && self::ENDPOINTS[$_GET[self::PARAMETER_KEY__NEXT]] ? $this->ini[self::ENDPOINTS[$_GET[self::PARAMETER_KEY__NEXT]] . '_url'] : "";
			$url = add_query_arg(array(
				self::PARAMETER_KEY__MODE => self::MODE_LOGIN,
			), self::get_url('callback'));
			if ($next_slug == "" || !is_page(rtrim($next_slug, '/'))) {
				$output = "<a href='" . $url . "' class='line-login-link login'>" . $atts['login_label'] . "</a>";
			} else {
				$output = "";
			}
		}

		return $output;
	}

	/**
	 * 各種メッセージ表示ショートコード実行
	 */
	function login_message_shortcode_handler_function($atts, $content = null, $tag = '') {
		// status を error か info に絞る
		$status = isset($_GET[self::PARAMETER_KEY__STATUS]) && in_array($_GET[self::PARAMETER_KEY__STATUS], ["error", "info"], true) ? $_GET[self::PARAMETER_KEY__STATUS] : '';
		// 表示するメッセージコード
		$code = isset($_GET[self::PARAMETER_KEY__CODE]) && $_GET[self::PARAMETER_KEY__CODE] ? $_GET[self::PARAMETER_KEY__CODE] : '';
		// 次の移動先タイプ
		$next_type = isset($_GET[self::PARAMETER_KEY__NEXT]) && isset(self::ENDPOINTS[$_GET[self::PARAMETER_KEY__NEXT]]) ? self::ENDPOINTS[$_GET[self::PARAMETER_KEY__NEXT]] : "";
		// 次の移動先スラッグ
		$next_slug =  $next_type && isset($this->ini[$next_type . '_url']) ? $this->ini[$next_type . '_url'] : "";
		// 次の移動先リンクラベル
		$next_label = isset($_GET[self::PARAMETER_KEY__NEXT]) && self::ENDPOINTS[$_GET[self::PARAMETER_KEY__NEXT]] ? $this->ini[self::ENDPOINTS[$_GET[self::PARAMETER_KEY__NEXT]] . '_label'] : "";

		// $req_uri = get_query_var('pagename');
		if ($code) {
			$output = "<div class='line-login-message {$status}'>" . ($this->ini[$code . '_message'] ? $this->ini[$code . '_message'] : '') . "</div>";
			$output .= $next_slug && !is_page(rtrim($next_slug, '/')) ? "<div class='line-login-nexturl'><a href='" . self::get_url($next_type) . "'>" . $next_label . "</a></div>" : "";
			return $output;
		}
		return;
	}

	/*
    ユーザーのメタデータにLINE IDをセット
    */
	function update_user_meta($user_id, $line_profile_data) {
		// $ini = self::getini();
		$line_user_id = $line_profile_data['id'];
		update_user_meta($user_id, self::META_KEY__LINE, $line_user_id); //ユーザーメタにLINE IDをセット
		update_user_meta($user_id, self::META_KEY__LINEPROFILE, $line_profile_data); //ユーザーメタにLINE IDをセット
		if (!empty($this->ini['messagingapi_channel_secret']) && $line_profile_data['isFriend']) {
			$line_user_data = get_user_meta($user_id, self::META_KEY__LINEcONNECT, true);
			if (empty($line_user_data)) {
				$line_user_data = array();
			}
			$secret_prefix = substr($this->ini['messagingapi_channel_secret'], 0, 4);
			$line_user_data[$secret_prefix] = array(
				'id' => $line_user_id,
				'displayName' => $line_profile_data['displayName'],
				'pictureUrl' => $line_profile_data['pictureUrl'],
			);
			update_user_meta($user_id, self::META_KEY__LINEcONNECT, $line_user_data);
			//リッチメニューをセット
			do_action('line_link_richmenu', $user_id);
		}
		self::logging('update_user_meta: user_id=' . $user_id . ' line_user_id:' . $line_user_id);
	}

	/*
    ユーザーのメタデータにLINE IDをセット(Line Connect連携)
    */
	function line_login_update_user_meta($user_id, $line_profile_data, $secret_prefix) {
		if (substr($this->ini['messagingapi_channel_secret'], 0, 4) === $secret_prefix) {
			// $ini = self::getini();
			$line_user_id = $line_profile_data['id'];
			update_user_meta($user_id, self::META_KEY__LINE, $line_user_id); //ユーザーメタにLINE IDをセット
			update_user_meta($user_id, self::META_KEY__LINEPROFILE, $line_profile_data); //ユーザーメタにプロフィール情報をセット
			self::logging('update_user_meta: user_id=' . $user_id . ' line_user_id:' . $line_user_id);
		}
	}


	/*
    ユーザーのメタデータからLINE IDを削除
    */
	function delete_user_meta($user_id) {
		// $ini = self::getini();
		delete_user_meta($user_id, self::META_KEY__LINE);
		delete_user_meta($user_id, self::META_KEY__LINEPROFILE);

		if (!empty($this->ini['messagingapi_channel_secret'])) {
			$secret_prefix = substr($this->ini['messagingapi_channel_secret'], 0, 4);
			$user_meta_line = get_user_meta($user_id, self::META_KEY__LINEcONNECT, true);
			if ($user_meta_line && $user_meta_line[$secret_prefix]) {
				do_action('line_unlink_richmenu', $user_id, $secret_prefix);
				unset($user_meta_line[$secret_prefix]);
				if (empty($user_meta_line)) {
					//ほかに連携しているチャネルがなければメタデータ削除
					delete_user_meta($user_id, self::META_KEY__LINEcONNECT);
				} else {
					//ほかに連携しているチャネルがあれば残りのチャネルが入ったメタデータを更新
					update_user_meta($user_id, self::META_KEY__LINEcONNECT, $user_meta_line);
				}
			}
		}
		self::logging('delete_user_meta: user_id=' . $user_id);
	}

	/*
    ユーザーのメタデータからLINE IDを削除
    */
	function line_login_delete_user_meta($user_id, $secret_prefix) {
		if (substr($this->ini['messagingapi_channel_secret'], 0, 4) === $secret_prefix) {
			// $ini = self::getini();
			delete_user_meta($user_id, self::META_KEY__LINE);
			delete_user_meta($user_id, self::META_KEY__LINEPROFILE);
		}
	}


	/*
    ログ出力
    */
	function logging($text) {
		if ($this->ini['logging'] == "on") {
			$logtext = date("[d/M/Y:H:i:s O] ") . $text . " " . $_SERVER['REQUEST_URI'] . "\n";
			error_log($logtext, 3, $this->ini['log_file']);
		}
	}

	// LINE USER ID から login_user 作成
	function make_user_name($user_id) {
		$user_name = substr($user_id, 2, 6);
		$offset = 1;
		while ($user_exists = username_exists($user_name)) {
			$user_name = substr($user_id, 2 + $offset, 6);
			$offset++;
		}

		return $user_name;
	}

	//ログイン処理
	function do_user_login($user_id, $user) {
		wp_clear_auth_cookie();
		wp_set_current_user($user_id);
		wp_set_auth_cookie($user_id);
		do_action('wp_login', $user->user_login, $user);
	}

	//プロフィール画面にLINEユーザーID追加
	function register_line_user_id_profilebox($user) {
		if (!current_user_can('manage_options')) {
			return false;
		}
		if (is_object($user)) {
			$line_user_id = get_user_meta($user->ID, self::META_KEY__LINE, true);
			$line_profile = get_user_meta($user->ID, self::META_KEY__LINEPROFILE, true);
		} else {
			$line_user_id = null;
		}
		$lineloginlink = add_query_arg(array(
			self::PARAMETER_KEY__UID => urlencode(self::encrypt($user->user_login, $this->ini['encrypt_password'])),
			//self::PARAMETER_KEY__MODE => self::MODE_LINK,
		), self::get_url('callback'))
?>
		<h3><?php echo __('LINE Login Connect', linelogin::PLUGIN_NAME) ?></h3>
		<table class="form-table">
			<tr>
				<th><label for="lineid"><?php echo __('LINE User ID', linelogin::PLUGIN_NAME) ?></label></th>
				<td>
					<input type="text" class="regular-text" name="lineid" value="<?php echo $line_user_id; ?>" id="lineid" /><br />
					<span class="description"><?php echo __('33 alphanumeric characters starting with "U".', linelogin::PLUGIN_NAME) ?></span>
				</td>
			</tr>
			<tr>
				<th><label for="linedisplayName"><?php echo __('LINE Display Name', linelogin::PLUGIN_NAME) ?></label></th>
				<td>
					<?php echo !empty($line_profile['displayName']) ? $line_profile['displayName'] : ""; ?>
				</td>
			<tr>
				<th><label for="linepictureUrl"><?php echo __('LINE Profile Picture', linelogin::PLUGIN_NAME) ?></label></th>
				<td>
					<?php echo !empty($line_profile['pictureUrl']) ? "<img src='{$line_profile['pictureUrl']}' width=200>" : ""; ?>
				</td>
			</tr>
			<tr>
				<th><label for="lineemail"><?php echo __('LINE Mailaddress', linelogin::PLUGIN_NAME) ?></label></th>
				<td>
					<?php echo !empty($line_profile['email']) ? $line_profile['email'] : ""; ?>
				</td>
			</tr>
			<tr>
				<th><label for="lineisFriend"><?php echo __('LINE Friend', linelogin::PLUGIN_NAME) ?></label></th>
				<td>
					<?php echo (!empty($line_profile['isFriend']) ? __('Yes', linelogin::PLUGIN_NAME) : __('No', linelogin::PLUGIN_NAME)) ?>
				</td>
			</tr>
			<?php if ($this->ini['directlink'] == "on") { ?>
				<tr>
					<th><?php echo __('LINE Login Link', linelogin::PLUGIN_NAME) ?></th>
					<td>
						<input type="text" class="regular-text" name="lineloginlink" value="<?php echo $lineloginlink; ?>" id="lineloginlink" />
						<button type="button" class="button secondary" onclick="document.getElementById('lineloginlink').select();document.execCommand('copy');"><?php echo __('Copy', linelogin::PLUGIN_NAME) ?></button>
						<br />
						<span class="description"><?php echo __('User can link to LINE by logging in via this link.', linelogin::PLUGIN_NAME) ?></span>
					</td>
				</tr>
			<?php } ?>
		</table>
<?php

	}

	//プロフィール画面にからPOSTされたLINEユーザーIDを保存
	function update_line_user_id_profilebox($user_id) {
		if (!current_user_can('manage_options')) {
			return false;
		}
		$line_user_id = $_POST['lineid'];
		if (preg_match(self::REGEXP_LINE_USER_ID, $line_user_id)) {
			update_user_meta($user_id, self::META_KEY__LINE, $line_user_id);
		} elseif (empty($line_user_id)) {
			delete_user_meta($user_id, self::META_KEY__LINE);
		}
	}

	//URLを返す
	function get_url($type) {
		if (isset($this->ini[$type . '_url'])) {
			if (rtrim($this->ini[$type . '_url'], '/')) {
				return get_permalink(get_page_by_path($this->ini[$type . '_url']));
			} else {
				return home_url();
			}
		}
		return false;
	}
}

$GLOBALS['linelogin'] = new linelogin;
