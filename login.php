<?php
session_start(); //セッションの開始

$dsn = "データベース情報" ;
$db[user] = "ユーザー名" ;
$db[pass] = "パスワード" ;

// エラーメッセージ、登録完了メッセージの初期化
$errorMessage = "";

if (isset($_POST["login"])) { // ログインボタンが押された場合
	//空欄チェック
        if (empty($_POST["username"])) {
		$errorMessage = 'ユーザーネームを入力してください';
	} elseif (empty($_POST["password"])) {
		$errorMessage = 'パスワードを入力してください';
	}

	if (!empty($_POST["username"]) and !empty($_POST["password"])) {
        // 入力したユーザネーム、パスワードを格納
        $username = $_POST["username"];
        $password = $_POST["password"];

        // エラー処理
        try {
            $pdo = new PDO($dsn, $db[user], $db[pass]);
            $sql = "SELECT * FROM users WHERE name = '$username'";
            $row = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);

			if ($row === false) { //等しく同じ型であるとき
				//該当データなし
				$errorMessage = "ユーザー名が間違っているか、登録されていません";
			} else {
				if ($password == $row['password']) {
					//現在のセッションIDを新しく生成したものと置き換える。セキュリティ上重要
					session_regenerate_id(true);

					//セッション変数=ページが遷移しても維持される変数(ブラウザを閉じると破棄)
					$_SESSION["name"] = $username;

					//ヘッダー情報を送信
					//ログイン後ページを遷移する必要がない場合は exit() まで書く必要なし
					header("Location: index.php");
					exit(); //上で他のページへ飛んでいるので、ここで処理を終わらせておく
				} else {
					//認証失敗
					$errorMessage = "パスワードが間違っています";
				}

			}
        } catch (PDOException $e) {
            $errorMessage = 'データベースエラー';
            // $e->getMessage() でエラー内容を参照可能
            // echo $e->getMessage();
        } //try終了
    }
}
?>
