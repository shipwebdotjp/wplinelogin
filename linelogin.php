<?php

/*
  Plugin Name: WP LINE Login
  Plugin URI: 
  Description: Add Login with LINE feature.
  Version: 1.1.0
  Author: shipweb
  Author URI: https://blog.shipweb.jp/archives/702
  License: GPLv3
*/

/*  Copyright 2021 shipweb (email : shipwebdotjp@gmail.com)
    https://www.gnu.org/licenses/gpl-3.0.txt

*/
add_action('init', 'linelogin::instance');
require_once (plugin_dir_path(__FILE__ ).'include/setting.php');

class linelogin {

    /**
     * このプラグインのバージョン
     */
    const VERSION = '1.1.0';

    /**
     * このプラグインのID：Shipweb Line Login
     */
    const PLUGIN_ID = 'sll';

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
     * ユーザーメタキー：line
     */
    const META_KEY__LINE = self::PLUGIN_PREFIX . 'lineid';

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
     * OPTIONSテーブルのキー：Setting
     */
    const OPTION_KEY__SETTINGS = self::PLUGIN_PREFIX . 'settings';
    
    /**
     * 画面のslug：トップ
     */
    const SLUG__SETTINGS_FORM = self::PLUGIN_ID . '-settings-form';

    const SETTINGS_OPTIONS = array(
        'channel' => array(
            'prefix' => '1',
            'name' => 'チャネル',
            'fields' => array(
                'login_channel_id' => array(
                    'type' => 'text',
                    'label' => 'ログインチャネルID',
                    'required' => true,
                    'default' => '',
                    'hint' => 'LINEログインチャネルの基本情報のページに記されている数字です。',
                    'regex' => '/^[0-9]+$/',
                    'size' => 20,
                ), 
                'login_channel_secret' => array(
                    'type' => 'text',
                    'label' => 'ログインチャネルシークレット',
                    'required' => true,
                    'default' => '',
                    'hint' => 'LINEログインチャネルの基本情報のページに記されている英数字です。',
                    'regex' => '/^[a-z0-9]{30,}$/',
                    'size' => 33,
                ), 
                'messagingapi_channel_secret' => array(
                    'type' => 'text',
                    'label' => 'Messaging APIチャネルシークレット',
                    'required' => false,
                    'default' => '',
                    'hint' => 'LINE Connectと連携する場合、LINE Messaging APIチャネルのシークレットを入力してください。',
                    'regex' => '/^[a-z0-9]{30,}$/',
                    'size' => 33,
                ), 
                'encrypt_password' => array(
                    'type' => 'text',
                    'label' => '暗号化シークレット',
                    'required' => true,
                    'default' => 'PleaseChangeHere',
                    'hint' => 'Cookieの暗号化に使用するシークレットを半角英数字で適宜設定してください。',
                    'regex' => '/^[0-9a-zA-Z]+$/',
                ), 
            ),
        ),
        'page' => array(
            'prefix' => '2',
            'name' => 'ページ設定',
            'fields' => array(
                'login_url' => array(
                    'type' => 'text',
                    'label' => 'ログインページURL',
                    'required' => false,
                    'default' => 'login/',
                    'hint' => 'ログインページのURLをサイトURLからの相対パスで入力してください。'
                ),
                'register_url' => array(
                    'type' => 'text',
                    'label' => '新規登録ページURL',
                    'required' => false,
                    'default' => 'register/',
                    'hint' => '新規登録ページのURLをサイトURLからの相対パスで入力してください。'
                ),
                'home_url' => array(
                    'type' => 'text',
                    'label' => 'ユーザーホームURL',
                    'required' => false,
                    'default' => '/',
                    'hint' => 'LINEログイン後に表示するページのURLをサイトURLからの相対パスで入力してください。'
                ), 
                'user_url' => array(
                    'type' => 'text',
                    'label' => 'ユーザーアカウントページURL',
                    'required' => false,
                    'default' => 'user/',
                    'hint' => 'LINE連携完了後に表示するページのURLをサイトURLからの相対パスで入力してください。一般的にユーザーのLINE連携状態を表示しているページです。'
                ),                 
                'message_url' => array(
                    'type' => 'text',
                    'label' => 'メッセージページURL',
                    'required' => true,
                    'default' => 'linemessage/',
                    'hint' => 'メッセージの表示用ページを作成し、ショートコード[line_login_message]を表示してください。'
                ), 
                'callback_url' => array(
                    'type' => 'text',
                    'label' => 'コールバックURL',
                    'required' => true,
                    'default' => 'linecallback/',
                    'hint' => 'LINEログイン施行後にリダイレクトするURLです。このページを作成する必要はありません。'
                ), 
            ),
        ),
        'message' => array(
            'prefix' => '3',
            'name' => 'メッセージ',
            'fields' => array(
                'login_label' => array(
                    'type' => 'text',
                    'label' => 'LINEログインリンクラベル',
                    'required' => false,
                    'default' => 'LINEログイン',
                    'hint' => 'LINEログインリンクのラベルです。'
                ),
                'normal_login_label' => array(
                    'type' => 'text',
                    'label' => '通常ログインリンクラベル',
                    'required' => false,
                    'default' => 'ログイン画面へ',
                    'hint' => '通常の方法でのログインページへのリンクラベルです。'
                ),
                'register_label' => array(
                    'type' => 'text',
                    'label' => '新規登録ラベル',
                    'required' => false,
                    'default' => '新規登録画面へ',
                    'hint' => '新規登録ページへのリンクラベルです。'
                ),
                'home_label' => array(
                    'type' => 'text',
                    'label' => 'ユーザーホームラベル',
                    'required' => false,
                    'default' => 'ホーム',
                    'hint' => 'ホームへのリンクラベルです。'
                ),
                'user_label' => array(
                    'type' => 'text',
                    'label' => 'アカウントホームラベル',
                    'required' => false,
                    'default' => 'アカウントページへ',
                    'hint' => 'ユーザーアカウントページへのリンクラベルです。'
                ),
                'access_denied_message' => array(
                    'type' => 'text',
                    'label' => 'ログインキャンセル時',
                    'required' => false,
                    'default' => 'LINEログインがキャンセルされました。',
                    'hint' => 'LINEログイン画面に移行したもののキャンセルされた際のメッセージです。',
                    'size' => 60,
                ),
                'auth_error_message' => array(
                    'type' => 'text',
                    'label' => '認証エラー時',
                    'required' => false,
                    'default' => 'LINE認証中にエラーが発生しました。',
                    'hint' => 'LINE認証中にエラーが発生した場合のメッセージです。',
                    'size' => 60,
                ),
                'invalid_state_message' => array(
                    'type' => 'text',
                    'label' => '連携エラー時',
                    'required' => false,
                    'default' => 'LINE連携中にエラーが発生しました。',
                    'hint' => 'LINE連携中にエラーが発生した場合のメッセージです。',
                    'size' => 60,
                ),
                'userdetails_error_message' => array(
                    'type' => 'text',
                    'label' => 'ユーザー情報取得失敗時',
                    'required' => false,
                    'default' => 'LINE認証中にエラーが発生しました。時間をおいて再度お試しください。',
                    'hint' => 'LINE認証後ユーザー情報取得の際にエラーが発生した場合のメッセージです。',
                    'size' => 60,
                ),
                'duplicate_error_message' => array(
                    'type' => 'text',
                    'label' => 'LINEアカウント重複時',
                    'required' => false,
                    'default' => 'このLINEアカウントは既に連携済みです。連係解除を行ってから再度お試しください。',
                    'hint' => 'LINEアカウントがすでに他のユーザーと連携されている場合のメッセージです。',
                    'size' => 60,
                ),
                'already_linked_message' => array(
                    'type' => 'text',
                    'label' => '己連携時',
                    'required' => false,
                    'default' => 'このLINEアカウントと既に連携済みです。',
                    'hint' => 'LINE連携済みなのに同じLINEアカウントで連携しようとした場合のメッセージです。',
                    'size' => 60,
                ),
                'goto_login_message' => array(
                    'type' => 'text',
                    'label' => '未ログイン時（ログインリンク）',
                    'required' => false,
                    'default' => 'このLINEアカウントはまだ連携されていません。当サイトのユーザー名またはメールアドレスとパスワードでログインすることで連携が行われます。',
                    'hint' => '"linelogin"スラッグを利用し、LINE連携されていないLINEアカウントでログインした場合のメッセージです。',
                    'size' => 60,
                ),
                'goto_regist_message' => array(
                    'type' => 'text',
                    'label' => '未ログイン時（新規登録リンク）',
                    'required' => false,
                    'default' => 'このLINEアカウントはまだ連携されていません。当サイトへ会員登録することで連携が行われます。',
                    'hint' => '"lineregister"スラッグを利用し、LINE連携されていないLINEアカウントでログインした場合のメッセージです。',
                    'size' => 60,
                ),
                'link_complete_message' => array(
                    'type' => 'text',
                    'label' => '連携完了時',
                    'required' => false,
                    'default' => 'LINE連携が完了しました。',
                    'hint' => 'LINE連携が完了した際のメッセージです。',
                    'size' => 60,
                ),
                'unlink_complete_message' => array(
                    'type' => 'text',
                    'label' => '連係解除時',
                    'required' => false,
                    'default' => 'LINE連携を解除しました。',
                    'hint' => 'LINE連携解除が完了した際のメッセージです。',
                    'size' => 60,
                ),
            ),
        ),
        'other' => array(
            'prefix' => '4',
            'name' => 'ログ',
            'fields' => array(
                'logging' => array(
                    'type' => 'select',
                    'label' => 'ログを記録',
                    'required' => true,
                    'list' => array('on' => 'する','off' => 'しない'),
                    'default' => 'off',
                    'hint' => 'LINEログインのログを記録するかどうかの設定です。',
                ), 
                'log_file' => array(
                    'type' => 'text',
                    'label' => 'ログファイルパス',
                    'required' => false,
                    'default' => '/var/log/linelogin.log',
                    'hint' => '/ログファイルのパスです。',
                    'size' => 40,
                ),
            ),
        ),
    );

    /**
     * パラメーターのPREFIX
     */
    const PARAMETER_PREFIX = self::PLUGIN_PREFIX;

    /**
     * 一時入力値保持用のPREFIX
     */
    const TRANSIENT_PREFIX = self::PLUGIN_PREFIX. 'temp-';

    /**
     * 不正入力値エラー表示のPREFIX
     */
    const INVALID_PREFIX = self::PLUGIN_PREFIX. 'invalid-';

    
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
     * 正規表現：ChannelSecret
    */
    const ENDPOINTS = array(
        'linelogin' => 'login',
        'linesignup' => 'register',
        'lineregister' => 'register',
        'linelink' => 'home',
    );
    
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

    static function getErrorBar($message, $type){
        return '<div class="error">' .esc_html($message).'</div>';
    }

    /**
     * コンストラクタ
     */
    function __construct() {
        //ログイン時、LINEアカウント連携
        add_action( 'wp_login', [$this, 'redirect_account_link'], 10, 2 );
        //新規登録時、LINEアカウント連携
        add_action( 'user_register', [$this, 'register_account_link'], 10, 2 );
        //LINEログインURLにアクセスされたときLINEログイン画面へリダイレクト
        add_action( 'template_redirect',  [$this, 'redirect_to_line'], 10, 2);
        //ログインボタンショートコードのフック
        add_shortcode( 'line_login_link',  [$this,'login_link_shortcode_handler_function'] ); 
        //メッセージ表示ショートコードのフック
        add_shortcode( 'line_login_message',  [$this,'login_message_shortcode_handler_function'] ); 
        // 管理画面を表示中、且つ、ログイン済、且つ、特権管理者or管理者の場合
        if (is_admin() && is_user_logged_in() && (is_super_admin() || current_user_can('administrator'))) {
            // 管理画面のトップメニューページを追加
            add_action('admin_menu', ['lineloginSetting', 'set_plugin_menu']);
            // 管理画面各ページの最初、ページがレンダリングされる前に実行するアクションに、
            // 初期設定を保存する関数をフック
            add_action('admin_init', ['lineloginSetting', 'save_settings']);
        }
        // オプションの読み込み
        $this->ini = $this->get_all_options();
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
        $data00 = $password.$salt;
        $hash = array();
        $hash[0] = hash('sha256', $data00, true);
        $result = $hash[0];
        for ($i = 1; $i < $rounds; $i++) {
            $hash[$i] = hash('sha256', $hash[$i - 1].$data00, true);
            $result .= $hash[$i];
        }
        $key = substr($result, 0, 32);
        $iv  = substr($result, 32,16);
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
            $dx = hash('sha256', $dx.$password.$salt, true);
            $salted .= $dx;
        }
        $key = substr($salted, 0, 32);
        $iv  = substr($salted, 32,16);
        $encrypted_data = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($salt . $encrypted_data);
    }

    /**
     * 登録されているオプション情報を全て返す
     */
    static function get_all_options(){
        $options = get_option(self::OPTION_KEY__SETTINGS); //オプションを取得
        foreach(self::SETTINGS_OPTIONS as $tab_name => $tab_details){
            //flatten
            foreach($tab_details['fields'] as $option_key => $option_details){
                if(!isset($options[$option_key])){
                    $options[$option_key] = $option_details['default'];
                }
            }
        }
        return $options;
    }

    /**
     * 登録されているオプションの値を返す
     */
    static function get_option($option_name){
        $options = get_option(self::OPTION_KEY__SETTINGS); //オプションを取得
        if(isset($options[$option_name])){
            return $options[$option_name];
        }
        foreach(self::SETTINGS_OPTIONS as $tab_name => $tab_details){
            //flatten
            foreach($tab_details['fields'] as $option_key => $option_details){
                if($option_name == $option_key){
                    return $option_details['default'];
                }
            }
        }
        return null;
    }

    /**
     * LINEログイン開始
     */
    function redirect_to_line(){
        $req_uri = get_query_var('pagename');
        parse_str($_SERVER['QUERY_STRING'], $req_vars);
        if(in_array($req_uri, array_keys(self::ENDPOINTS), true) || $req_uri == rtrim($this->ini['callback_url'], '/')){
            if( session_status() !== PHP_SESSION_ACTIVE ) {
                session_start();    //セッション開始
            }
            // OAuth2のクライアントライブラリ読み込み
            require_once plugin_dir_path(__FILE__).'vendor/autoload.php';
            // LINEのOAuth2クライアントインスタンス作成
            $provider = new Osapon\OAuth2\Client\Provider\Line([
                'clientId'     => $this->ini['login_channel_id'],
                'clientSecret' => $this->ini['login_channel_secret'],
                'redirectUri'  => get_site_url(null, $this->ini['callback_url']),
            ]);

            if (!empty($req_vars['error'])) {
                //エラーが発生した場合
                $redirect_to = add_query_arg(array(
                    self::PARAMETER_KEY__STATUS => 'error',
                    self::PARAMETER_KEY__CODE => !empty($this->ini[$req_vars['error'].'_message']) ? $req_vars['error'] : 'auth_error',
                    self::PARAMETER_KEY__NEXT => 'linelink',
                ),get_site_url(null, $this->ini['message_url']));
                self::logging('error: auth_error: '.$req_vars['error'].' : '.$_GET['error_description']);
                wp_safe_redirect( $redirect_to );
                exit;
            
            } elseif (empty($req_vars['code'])) {
                //認可要求時のオプションパラメーター
                $option = [
                    'bot_prompt' => 'normal',
                ];
                // codeのないリクエスト=ログイン開始
                $authUrl = $provider->getAuthorizationUrl($option);
                $_SESSION[self::SESSION_KEY__STATES] = $provider->getState();   //Stateをセッションに保持
                $_SESSION['lastpage'] = $req_uri;                   //リンク元をセッションに保持
                self::logging('auth: '.$authUrl);
                header('Location: ' . $authUrl);                    //LINE認証URLへリダイレクトさせる
                exit;
            
            } elseif (empty($req_vars['state']) || ($req_vars['state'] !== $_SESSION[self::SESSION_KEY__STATES])) { //stateがないか、stateが異なる場合はエラー
                unset($_SESSION[self::SESSION_KEY__STATES]);
                $redirect_to = add_query_arg(array(
                    self::PARAMETER_KEY__STATUS => 'error',
                    self::PARAMETER_KEY__CODE => 'invalid_state',
                    self::PARAMETER_KEY__NEXT => 'linelink',
                ),get_site_url(null, $this->ini['message_url']));
                self::logging('error: invalid_state');
                wp_safe_redirect( $redirect_to );
                exit();
            } else {
                unset($_SESSION[self::SESSION_KEY__STATES]);    //State削除
                // アクセストークンの取得
                $token = $provider->getAccessToken('authorization_code', [
                    'code' => $req_vars['code']
                ]);
                self::logging('token: '.$token);
                try {
                    // ユーザープロフィールを取得
                    $ownerDetails = $provider->getResourceOwner($token);
                } catch (Exception $e) {
                    $redirect_to = add_query_arg(array(
                        self::PARAMETER_KEY__STATUS => 'error',
                        self::PARAMETER_KEY__CODE => 'userdetails_error',
                        self::PARAMETER_KEY__NEXT => 'linelink',
                    ),get_site_url(null, $this->ini['message_url']));
                    self::logging('error: userdetails_error. '.print_r($e, true));
                    wp_safe_redirect( $redirect_to );
                    exit();
                }

                $line_user_id = $ownerDetails->getId(); //LINEユーザーIDを取得
                //メタ情報からLINEユーザーIDでユーザー検索
                $user_query = new WP_User_Query( array( 'meta_key' => self::META_KEY__LINE, 'meta_value' => $line_user_id ) );
                $users = $user_query->get_results();
                if(! empty( $users )){ 
                    //LINE IDがすでにWPユーザーと連携されている場合
                    $user =  $users[0]; //ユーザーの一人目
                    $user_id = $user->ID; //IDを取得

                    if ( ! is_user_logged_in() ) {
                        //未ログインの場合はそのユーザーでログイン
                        //ログイン処理
                        wp_clear_auth_cookie();
                        wp_set_current_user ( $user_id );
                        wp_set_auth_cookie  ( $user_id );
                        do_action( 'wp_login', $user->user_login, $user );
                        $redirect_to = get_site_url(null, $this->ini['home_url']);
                    }else{
                        //ログイン済みの場合
                        if( $user_id != get_current_user_id()){
                            // LINE連携されているユーザーがログイン中のユーザーでない場合
                            // 重複して連携はできないのでエラー表示
                            $redirect_to = add_query_arg(array(
                                self::PARAMETER_KEY__STATUS => 'error',
                                self::PARAMETER_KEY__CODE => 'duplicate_error',
                                self::PARAMETER_KEY__NEXT => 'linelink',
                            ),get_site_url(null, $this->ini['message_url']));
                        }else{
                            // LINE連携されているユーザーがログイン中のユーザーの場合
                            $redirect_to = add_query_arg(array(
                                self::PARAMETER_KEY__STATUS => 'error',
                                self::PARAMETER_KEY__CODE => 'already_linked',
                                self::PARAMETER_KEY__NEXT => 'linelink',
                            ),get_site_url(null, $this->ini['message_url']));
                        }
                    }
                    wp_safe_redirect( $redirect_to );
                    exit();
                }else{
                    // ユーザーの連携ステータスを取得
                    $opts = array('http' =>
                        array(
                            'header' => 'Authorization: Bearer '.$token,
                        )
                    );
                    $context = stream_context_create($opts);
                    $result = file_get_contents('https://api.line.me/friendship/v1/status', false, $context);
                    $friendFlag = json_decode($result, true);
                    self::logging("friendstatus: ".$result);
                    $line_user_data = [
                        'user_id' => $line_user_id,
                        'name' => $ownerDetails->getName(),
                        'picture' => $ownerDetails->getAvatar(),
                        'isFriend' => $friendFlag['friendFlag'],
                    ];
                    // 連携されていない場合は連携する
                    //Wordpressユーザーのメタ情報にLINEユーザーIDを追加
                    if ( ! is_user_logged_in() ) {
                        //未ログインの場合はcookieにLINE IDを登録してからログインページ／登録ページへリダイレクト
                        $encrypted_line_user_data = self::encrypt(json_encode($line_user_data), $this->ini['encrypt_password']);   //LINEユーザーIDの暗号化
                        setcookie (self::COOKIE_KEY__LINEID, $encrypted_line_user_data, time() + 60 * 60,'/',"",TRUE,TRUE);   //Cookieにセット
                        $next_code = $_SESSION['lastpage'] == 'linelogin' ? 'goto_login' : 'goto_regist';
                        $next_url = $_SESSION['lastpage'];
                        $redirect_to = add_query_arg(array(
                            self::PARAMETER_KEY__STATUS => 'info',
                            self::PARAMETER_KEY__CODE => $next_code,
                            self::PARAMETER_KEY__NEXT => $next_url,
                        ), get_site_url(null, $this->ini['login_url']));
                        wp_safe_redirect( $redirect_to );
                        exit();
                    }else{
                        //ログイン済みの場合はログイン中のユーザーと関連付ける
                        $user_id = get_current_user_id();
                        //ユーザーメタにLINE IDをセット
                        self::update_user_meta( $user_id, $line_user_data );
                        $redirect_to = add_query_arg(array(
                            self::PARAMETER_KEY__STATUS => 'info',
                            self::PARAMETER_KEY__CODE => 'link_complete',
                            self::PARAMETER_KEY__NEXT => 'linelink',
                        ),get_site_url(null, $this->ini['message_url']));
                        wp_safe_redirect( $redirect_to );
                        exit();
                    }
                }
            }
            exit();
        }elseif($req_uri == 'lineunlink'){
            //連係解除リンク
            if ( is_user_logged_in() ) {
                self::delete_user_meta( get_current_user_id());
                $redirect_to = add_query_arg(array(
                    self::PARAMETER_KEY__STATUS => 'info',
                    self::PARAMETER_KEY__CODE => 'unlink_complete',
                    self::PARAMETER_KEY__NEXT => 'linelink',
                ),get_site_url(null, $this->ini['message_url']));
                wp_safe_redirect( $redirect_to );
                exit();
            }
        }
    }

    /*
    ログイン時にLINEログイン経由で未連携の場合は連携させる
    */
    function redirect_account_link ( $user_login , $current_user ) {
        self::check_is_user_has_unlinked_line_id($current_user->ID);
    }

    /*
    新規登録時にLINEログイン経由で未連携の場合は連携させる
    */
    function register_account_link ( $user_id , $userdata  ) {
        self::check_is_user_has_unlinked_line_id($user_id);
    }

    /*
    CookieにLINE IDが保存されているかチェックし、保存されていれば連携
    */
    function check_is_user_has_unlinked_line_id($user_id){
        if(isset($_COOKIE[self::COOKIE_KEY__LINEID])){ 
            //COOKIEにLINE ID KEYがセットされていたら
    		$encrypted_line_user_id = $_COOKIE[self::COOKIE_KEY__LINEID]; //COKIEからLINE IDを取得
            $line_user_data = json_decode(self::decrypt($encrypted_line_user_id, $this->ini['encrypt_password']), true);   //暗号化されているLINE IDを復号
            $line_user_id = $line_user_data['user_id'];
            $user_query = new WP_User_Query( array( 'meta_key' => self::META_KEY__LINE, 'meta_value' => $line_user_id ) ); 
            $users = $user_query->get_results();
            if(! empty( $users )){ 
                //LINE IDがすでにWPユーザーと連携されている場合
                $user =  $users[0]; //ユーザーの一人目
                $user_id = $user->ID; //IDを取得
                if( $user_id != get_current_user_id()){
                    // LINE連携されているユーザーがログイン中のユーザーでない場合
                    // 重複して連携はできないのでエラー表示
                    $redirect_to = add_query_arg(array(
                        self::PARAMETER_KEY__STATUS => 'error',
                        self::PARAMETER_KEY__CODE => 'duplicate_error',
                        self::PARAMETER_KEY__NEXT => 'linelink',
                    ),get_site_url(null, $this->ini['message_url']));
                    self::logging('duplicate_error: user_id='.get_current_user_id());
                }else{
                    // LINE連携されているユーザーがログイン中のユーザーの場合
                    setcookie(self::COOKIE_KEY__LINEID,"",time() - 3600,'/'); //COKIE削除
                    $redirect_to = add_query_arg(array(
                        self::PARAMETER_KEY__STATUS => 'error',
                        self::PARAMETER_KEY__CODE => 'already_linked',
                        self::PARAMETER_KEY__NEXT => 'linelink',
                    ),get_site_url(null, $this->ini['message_url']));
                    self::logging('already_linked: user_id='.get_current_user_id());
                }
            }else{
                self::update_user_meta( $user_id, $line_user_data );
                setcookie(self::COOKIE_KEY__LINEID,"",time() - 3600,'/'); //COKIE削除
                $redirect_to = add_query_arg(array(
                    self::PARAMETER_KEY__STATUS => 'info',
                    self::PARAMETER_KEY__CODE => 'link_complete',
                    self::PARAMETER_KEY__NEXT => 'linelink',
                ),get_site_url(null, $this->ini['message_url']));
            }
            wp_safe_redirect( $redirect_to );
            exit();
        }
    }

    /**
     * 連携状態表示ショートコード実行
     */
	function login_link_shortcode_handler_function($atts, $content = null, $tag = ''){
        $atts = wp_parse_args($atts, array(
            'login_label'  => $this->ini['login_label'],
            'unlinked_label'  => 'LINE 連携されていません',
            'linked_label'  => 'LINE 連携済みです',
            'unlinked_button'  => '連携',
            'linked_button'  => '連携解除',            
        ));
        if ( is_user_logged_in() ) {
            $user_id = get_current_user_id(); //現在のユーザーを取得
            if ($user_id != null){
                $line_user_id = get_user_meta( $user_id, self::META_KEY__LINE, true );
                if($line_user_id){
                    $url = get_site_url(null, 'lineunlink/');
                    $output = $atts['linked_label'] ? "<span class='line-login-label linked'>".$atts['linked_label']."</span>" : "";
                    $output .= "<a href='".$url."' class='line-login-link linked'>".$atts['linked_button']."</a>";
                }else{
                    $url = get_site_url(null, 'linelink/');
                    $output = $atts['unlinked_label'] ? "<span class='line-login-label unlinked'>".$atts['unlinked_label']."</span>" : "";
                    $output .= "<a href='".$url."' class='line-login-link unlinked'>".$atts['unlinked_button']."</a>";
                }
            }
        }else{
            $req_uri = get_query_var('pagename');
            $next_url = isset($_GET[self::PARAMETER_KEY__NEXT]) && self::ENDPOINTS[$_GET[self::PARAMETER_KEY__NEXT]] ? $this->ini[self::ENDPOINTS[$_GET[self::PARAMETER_KEY__NEXT]].'_url'] : "";
            $url = get_site_url(null, 'linelogin/');
            if($next_url == "" || $req_uri != rtrim($next_url, '/')){
                $output = "<a href='".$url."' class='line-login-link login'>".$atts['login_label']."</a>";
            }else{
                $output = "";
            }
            
        }

		return $output;
	}

    /**
     * 各種メッセージ表示ショートコード実行
     */
	function login_message_shortcode_handler_function($atts, $content = null, $tag = ''){
        // $ini = self::getini();
        $status = in_array($_GET[self::PARAMETER_KEY__STATUS], ["error","info"], true) ? $_GET[self::PARAMETER_KEY__STATUS] : '';
        $code = $_GET[self::PARAMETER_KEY__CODE];
        $next_url = self::ENDPOINTS[$_GET[self::PARAMETER_KEY__NEXT]] ? $this->ini[self::ENDPOINTS[$_GET[self::PARAMETER_KEY__NEXT]].'_url'] : "";
        $next_label = self::ENDPOINTS[$_GET[self::PARAMETER_KEY__NEXT]] ? $this->ini[self::ENDPOINTS[$_GET[self::PARAMETER_KEY__NEXT]].'_label'] : "";
        $req_uri = get_query_var('pagename');
        if($code){
            $output = "<div class='line-login-message {$status}'>".($this->ini[$code.'_message'] ? $this->ini[$code.'_message'] : '')."</div>";
            $output .= $next_url &&  $req_uri != rtrim($next_url, '/') ? "<div class='line-login-nexturl'><a href='".get_site_url(null, $next_url) ."'>".$next_label."</a></div>" : "";
            return $output;          
        }
        return;
    }
    
    /*
    ユーザーのメタデータにLINE IDをセット
    */
    function update_user_meta($user_id, $line_profile_data){
        // $ini = self::getini();
        $line_user_id = $line_profile_data['user_id'];
        update_user_meta( $user_id, self::META_KEY__LINE, $line_user_id );//ユーザーメタにLINE IDをセット
        if(!empty($this->ini['messagingapi_channel_secret']) && $line_profile_data['isFriend']){
			$line_user_data = get_user_meta($user_id, self::META_KEY__LINEcONNECT, true);
			if(empty($line_user_data)){
				$line_user_data = array();
			}
            $secret_prefix = substr($this->ini['messagingapi_channel_secret'],0,4);
			$line_user_data[$secret_prefix] = array(
				'id' => $line_user_id,
				'displayName' => $line_profile_data['name'],
				'pictureUrl' => $line_profile_data['picture'],
			);
			update_user_meta( $user_id, self::META_KEY__LINEcONNECT, $line_user_data);
        }
        self::logging('update_user_meta: user_id='.$user_id.' line_user_id:'.$line_user_id);
    }

    /*
    ユーザーのメタデータからLINE IDを削除
    */
    function delete_user_meta($user_id){
        // $ini = self::getini();
        delete_user_meta( $user_id, self::META_KEY__LINE);
        if(!empty($this->ini['messagingapi_channel_secret'])){
            $secret_prefix = substr($this->ini['messagingapi_channel_secret'],0,4);
            $user_meta_line = get_user_meta($user_id, self::META_KEY__LINEcONNECT, true);
            if($user_meta_line && $user_meta_line[$secret_prefix]){
                unset($user_meta_line[$secret_prefix]);
                if(empty($user_meta_line)){
                    //ほかに連携しているチャネルがなければメタデータ削除
                    delete_user_meta( $user_id, self::META_KEY__LINEcONNECT);
                }else{
                    //ほかに連携しているチャネルがあれば残りのチャネルが入ったメタデータを更新
                    update_user_meta( $user_id, self::META_KEY__LINEcONNECT, $user_meta_line);
                }
            }
        }
        self::logging('delete_user_meta: user_id='.$user_id);
    }

    /*
    ログ出力
    */
    function logging($text){
        if($this->ini['logging'] == "on"){
            $logtext = date("[d/M/Y:H:i:s O] ").$text." ".$_SERVER['REQUEST_URI']."\n";
            error_log($logtext, 3, $this->ini['log_file']);
        }

    }
}