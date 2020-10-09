<?php


define( 'DB_HOST', 'localhost' );
define( 'DB_USER', 'root' );
define( 'DB_PASS', 'root' );
define( 'DB_NAME', 'MyBBS' );


date_default_timezone_set('Asia/Tokyo');


$message_id = null;
$sql = null;
$res = null;
$message_array = array();
$error_message = array();
$mysqli = new mysqli( DB_HOST, DB_USER, DB_PASS, DB_NAME );



session_start();



// 返信したい投稿のデータを取得
if( !empty($_GET['message_id']) ){
	$message_id = (int)htmlspecialchars($_GET['message_id'], ENT_QUOTES);


	if( $mysqli->connect_errno ) {
		$error_message[] = 'データベースの接続に失敗しました。　エラー番号 '.$mysqli->connect_errno.' : '.$mysqli->connect_error;
	} else {
		$sql = "SELECT * FROM message WHERE id = $message_id";
		$res = $mysqli->query($sql);

		if( $res ) {
			$message_data = $res->fetch_assoc();
		} else {
			header("Location: ./admin.php");
			exit();
		}

		$mysqli->close();
	}

}


// データベースへの書き込み処理
if( !empty($_POST['btn_submit']) ) {
	if( empty($_POST['reply_name']) ) {
		$error_message[] = '投稿者名を入力してください。';
	} else {
		$clean['reply_name'] = htmlspecialchars( $_POST['reply_name'], ENT_QUOTES );
		$clean['reply_name'] = preg_replace( '/\\r\\n|\\n|\\r/', '', $clean['reply_name']);
	}

	
	if( empty($_POST['reply_content']) ) {
		$error_message[] = '本文を入力してください。';
	} else {
		$clean['reply_content'] = htmlspecialchars( $_POST['reply_content'], ENT_QUOTES );
	}

	
	if ( empty($error_message) ) {
		$mysqli = new mysqli( DB_HOST, DB_USER, DB_PASS, DB_NAME );

		if( $mysqli->connect_errno ) {
			$error_message[] = '書き込みに失敗しました。 エラー番号 '.$mysqli->connect_errno.' : '.$mysqli->connect_error;
		} else {
			$mysqli->set_charset('utf8');
			$now_date = date("Y-m-d H:i:s");
			$sql = "INSERT INTO reply (reply_name, reply_content, reply_date, connect_id) VALUES ( '$clean[reply_name]', '$clean[reply_content]', '$now_date', '$message_id')";
			$res = $mysqli->query($sql);

			$mysqli->close();

			if( $res ) {
				header("Location: ./index.php?page={$_GET['page_id']}#{$message_id}");
				exit();
			} else {
				$error_message[] = '書き込みに失敗しました。';
			}
		}

	}
}


?>



<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>管理ページ　返信の間</title>
		<link rel="stylesheet" type="text/css" href="index.css">
	</head>


	<body>
		<div class="header">
			<h1 class="title">管理ページ　返信の間</h1>
		</div>


		<div class="container">
			<form method="post">
				<div class="form_name">
					<label for="name">返信先</label>
					<input type="text" id="name" name="name" placeholder="名前を入力してください" value="<?php echo $message_data['name']; ?>" disabled>
				</div>
				<div class="form_content">
					<label for="content"></label>
					<textarea id="content" name="content" placeholder="投稿したい内容を入力してください" disabled><?php if( !empty($message_data['content']) ){ echo $message_data['content']; } ?></textarea>
				</div>
			</form>


			<?php if ( !empty($error_message) ): ?>
				<ul class="error_message">
					<?php foreach ( $error_message as $value ): ?>
						<li>・<?php echo $value; ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>


			<div class="reply_form_container">
				<form method="post">
					<div class="form_name">
						<label for="reply_name">投稿者</label>
						<input type="text" id="reply_name" name="reply_name" placeholder="名前を入力してください" value="">
					</div>
					<div class="form_content">
						<label for="reply_content">本文</label>
						<textarea id="reply_content" name="reply_content" placeholder="返信したい内容を入力してください"></textarea>
					</div>
					<a class="btn_cancel" href="./index.php?page=<?php echo $_GET['page_id'] ?>#<?php echo $_GET['message_id'] ?>">キャンセル</a>
					<input type="submit" name="btn_submit" value="返信" id="btn_submit">
				</form>
		    </div>
		</div>
	</body>
</html>

