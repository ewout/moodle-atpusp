<?php

/**
 * Holds the configuration of Mediabird
 * Values are being overwritten by the config.php generated using the setup
 * @author fabian
 *
 */
class MediabirdConfig {

	//database connection info
	public static $database_hostname = "localhost";
	public static $database_username = "root";
	public static $database_password = "";
	public static $database_name = "mediabird";
	public static $database_table_prefix = "mb_";

	//path to server directory
	public static $server_path = "server";

	//check that the php user has write access rights to the folder specified here
	public static $uploads_folder = "uploads/";

	//check that the php user has write access rights to the folder specified here
	public static $cache_folder = "cache/";

	//security salt to compute the password hash codes
	public static $security_salt = "mediabirdsalt";

	//address from which emails are being sent
	public static $no_reply_address = "noreply@yourdomain";
	//address to which Terms of Use violation reports are being sent
	public static $webmaster_address = "webmaster@yourdomain";

	//if the application is being accessed using different URLs, absolute URLs
	//generated by Internet Explorer should be removed
	public static $disable_absolute_link_correction = false;

	//optional provide the external URL Mediabird will be accessed from
	//example: http://youdomain:80/path/to/index.php
	public static $www_root = null;
	
	//for servers with little bandwidth, you should set this to true. it allows scripts and
	//css to be dumped in a file and to be sent afterwards rather than leaving
	//all of them in separate files
	public static $disable_debug = false; //if false use source files instead of release scripts and concatenated css
	public static $disable_mail = false; //if false mail features are enabled
	public static $disable_signup = false; //if false signup feature is enabled

	public static $table_names = array(
		'AccountLink'=>'account_links',
		'User'=>'users',
		'Topic'=>'topics',
		'Card'=>'cards',
		'Marker'=>'markers',
		'Flashcard'=>'flashcards',
		'Prerequisite'=>'prerequisites',
		'Group'=>'groups',
		'Membership'=>'memberships',
		'Right'=>'rights',
		'Feed'=>'feeds',
		'FeedMessage'=>'feed_messages',
		'FeedMessagesStatus'=>'feed_messages_status',
		'FeedSubscription'=>'feed_subscriptions',
		'RelationLink'=>'relation_links',
		'RelationQuestion'=>'relation_questions',
		'RelationAnswer'=>'relation_answers',
		'Relation'=>'relations',
		'Upload'=>'uploads'
	);

	/**
	 * Transforms a data class name into its corresponding table name
	 * @param string $key Data class name (e.g. 'User')
	 * @param bool $noprefix True to include table prefix
	 * @return string Table name
	 */
	public static function tableName($key,$noprefix=false) {
		$translated = $key;
		if(isset(self::$table_names[$key])) {
			$translated=self::$table_names[$key];
		}
		if(!$noprefix) {
			$translated=self::$database_table_prefix.$translated;
		}
		return $translated;
	}

	//proxy to use for server url loading
	public static $proxy_address = null;
	public static $proxy_port = 8080;

	//latex setup if installed
	public static $latex_path = "/usr/bin/latex";
	public static $convert_path = "/usr/bin/dvipng";
}

?>
