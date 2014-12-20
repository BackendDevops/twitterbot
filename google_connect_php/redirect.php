<?php

require_once('config.php');
require_once('functions.php');

session_start();

if (empty($_GET['code'])){
	//認証前の処理

	//認証ダイアログの作成
	//CSRF対策
	$_SESSION['state'] = sha1(uniqid(mt_rand(), true));

	$params = array(
		'client_id' => CLIENT_ID,
		'redirect_uri' => 'http://localhost/google_connect_php/redirect.php',
		//正しいローカルアドレスはこちら→http://192.168.33.10/google_connect_php/redirect.php
		//ただ、googleapiの性質上ローカル開発環境でためすには192.168.33.10のところをlocalhostに置き換える
		//忘れてはいけないのはdev consoleの登録画面のところでもlocalhostに置き換えて登録する必要があるということ
		//リダイレクト先に戻るときにはエラーがでるのでリンクのlocalhostを192.168.33.10に直してあげる必要あり
		'state' => $_SESSION['state'],
		'approval_prompt' => 'force',
		'scope' => 'https://www.googleapis.com/auth/plus.login https://www.googleapis.com/auth/userinfo.email',
		'response_type' => 'code'
		);
	//googleへ飛ばす
	$url = 'https://accounts.google.com/o/oauth2/auth?'.http_build_query($params);
	header('Location: '.$url);
	exit;

}else{

	//http://192.168.33.10/google_connect_php/redirect.php?state=c1be45ce8a635f9381e1ea9e1d15fded66d2c470&code=4/Iy_c-2uDqrn596TT-G3ZUgIcgNnS7CuulbjNIDFNtk8.kgsZjL5KjgcVEnp6UAPFm0ES4iq5lAI&authuser=0&num_sessions=2&prompt=consent&session_state=d9cfe5a3ce3b5d3a9892fcc7a5e21879657da396..8cc0

	//認証後の処理
	//CSRF対策でstateのチェック
	if($_SESSION['state'] != $_GET['state']){
		echo "不正な処理でした";
		exit;
	}

	//アクセストークンの取得
	$params = array(
		'client_id' => CLIENT_ID,
		'client_secret' => CLIENT_SECRET,
		'code' => $_GET['code'],
		'redirect_uri' => 'http://localhost/google_connect_php/redirect.php',
		'grant_type' => 'authorization_code'
		);
	$url = 'https://accounts.google.com/o/oauth2/token';

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

	$rs = curl_exec($curl);
	curl_close($curl);
	$json = json_decode($rs);

	//var_dump($json);exit;

	//ユーザー情報の取得
	$url = 'https://www.googleapis.com/oauth2/v1/userinfo?access_token='.$json->access_token;
	$me = json_decode(file_get_contents($url));
	//var_dump($me);

	//DB格納
	$dbh = connectDb();

	$sql = "select * from users where google_user_id = :id limit 1";
	$stmt = $dbh->prepare($sql);
	$stmt->execute(array(":id" => $me->id));
	$user = $stmt->fetch();

	//DBに登録データがなかった場合には登録する
	if(!$user){
		$sql = "insert into users
				(google_user_id, google_email, google_name,
				google_picture, google_access_token, created, modified)
				values
				(:google_user_id, :google_email, :google_name,
				:google_picture, :google_access_token, now(), now())";
		$stmt = $dbh->prepare($sql);
		$params = array(
			":google_user_id" => $me->id,
			":google_email" => $me->email,
			":google_name" => $me->name,
			":google_picture" => $me->picture, 
			":google_access_token" => $json->access_token
			);
		$stmt->execute($params);

		$myId = $dbh->lastInsertId();
		$sql = "select * from users where id = :id limit 1";
		$stmt = $dbh->prepare($sql);
		$stmt->execute(array(":id" => $myId));
		$user = $stmt->fetch();
	}

	//var_dump($user);exit;

	//ログイン処理
	if(!empty($user)){
		//セッションハイジャック対策
		session_regenerate_id(true);
		$_SESSION['me'] = $user;

	}

	//ホーム画面へ飛ばす
	header('Location: '.SITE_URL);

}