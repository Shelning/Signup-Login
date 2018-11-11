<?php
$dsn = "データベース情報" ;
$db[user] = "ユーザー名" ;
$db[pass] = "パスワード" ;

// エラーメッセージ、登録完了メッセージの初期化
$errorMessage = "";
$signupMessage = "";

if (!empty($_POST["signup"])) { // 登録ボタンが押された場合
	//空欄チェック
    if (empty($_POST["username"])) { //ユーザーネームが空
		$errorMessage = 'ユーザーネームを入力してください';
    } else if (empty($_POST["password"])) { //パスワードが空
  		$errorMessage = 'パスワードを入力してください';
    } else if (empty($_POST["password2"])) { //確認用パスワードが空
        $errorMessage = '確認用パスワードを入力してください';
    }

    if (!empty($_POST["username"]) and !empty($_POST["password"]) and !empty($_POST["password2"]) and $_POST["password"] === $_POST["password2"]) {
    	// 入力したユーザーネーム、パスワードを格納
    	$username = $_POST["username"];
    	$password = $_POST["password"];

    	// エラー処理
    	try {
        	 $pdo = new PDO($dsn, $db[user], $db[pass]);

	   		 //ユーザーネームの重複を確認
	                 //$stmt に同じユーザー名をもつレコードの件数を取得
	   		 $stmt = "SELECT count(*) FROM テーブル WHERE name = '$username'";

	                 //fetchColum() で件数を取り出している
	   		 $count = (int)$pdo->query($stmt)->fetchColumn();

	   		 if ($count > 0) {
			 	$errorMessage = 'そのユーザー名は既に使用されています';
	   		 } else {
	       		$sql = $pdo->prepare("INSERT INTO テーブル(name, email, password) VALUES (:name, :email, :password)");
	   			$sql -> bindValue(':name', $username, PDO::PARAM_STR);
	   			$sql -> bindValue(':email', $email, PDO::PARAM_STR);
	   			$sql -> bindValue(':password', $password, PDO::PARAM_STR);
	       		$sql->execute();
	       		$signupMessage = '登録が完了しました！。';
	   		 }
    	 } catch (PDOException $e) { //データベースに接続できなかったとき
        	$errorMessage = 'データベースエラー';
        	// $e->getMessage() でエラー内容を参照可能（デバッグ時のみ表示）
        	// echo $e->getMessage();
    	  }
	} else if($_POST["password"] != $_POST["password2"]) {
		$errorMessage = 'パスワードが一致しません';
    }
}
?>
