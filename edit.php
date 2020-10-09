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



if( empty($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true ) {
	header("Location: ./admin.php");
	exit();
}



// 編集したい投稿のデータを取得
if( !empty($_GET['message_id']) && empty($_POST['message_id']) ){
	$message_id = (int)htmlspecialchars($_GET['message_id'], ENT_QUOTES);

	$mysqli = new mysqli( DB_HOST, DB_USER, DB_PASS, DB_NAME );

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



// 投稿者名、本文が未入力の場合はエラー表示
} elseif( !empty($_POST['message_id']) ) {
	$message_id = (int)htmlspecialchars($_POST['message_id'], ENT_QUOTES);


	if( empty($_POST['name']) ) {
		$error_message[] = '投稿者名を入力してください。';
	} else {
		$message_data['name'] = htmlspecialchars($_POST['name'], ENT_QUOTES);
	}


	if( empty($_POST['content']) ) {
		$error_message[] = '本文を入力してください。';
	} else {
		$message_data['content'] = htmlspecialchars($_POST['content'], ENT_QUOTES);
	}
	
	// データの更新処理
	if( empty($error_message) ) {
		$mysqli = new mysqli( DB_HOST, DB_USER, DB_PASS, DB_NAME );

		if( $mysqli->connect_errno ) {
			$error_message[] = 'データベースの接続に失敗しました。　エラー番号　' . $mysqli->connect_errno . ' : ' . $mysqli->connect_error;
		} else {
			$sql = "UPDATE message set name = '$message_data[name]', content = '$message_data[content]' WHERE id = $message_id";
			$res = $mysqli->query($sql);
		}

		$mysqli->close();

		if( $res ) {
			header("Location: ./admin.php?page={$_GET['page_id']}#{$message_id}");
			exit();
		}
	}
}


?>



<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>管理ページ　編集の間</title>
		<link rel="stylesheet" type="text/css" href="index.css">
	</head>


	<body>
		<div class="header">
			<h1 class="title">管理ページ　編集の間</h1>
		</div>


		<?php if ( !empty($error_message) ): ?>
			<ul class="error_message">
				<?php foreach ( $error_message as $value ): ?>
					<li>・<?php echo $value; ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>


		<div class="container">
			<form method="post">
				<div class="form_name">
					<label for="name">投稿者</label>
					<input type="text" id="name" name="name" placeholder="名前を入力してください" value="<?php if( !empty($message_data['name']) ){ echo $message_data['name']; } ?>">
				</div>
				<div class="form_content">
					<label for="content">本文</label>
					<textarea id="content" name="content" placeholder="投稿したい内容を入力してください"><?php if( !empty($message_data['content']) ){ echo $message_data['content']; } ?></textarea>
				</div>
				<a class="btn_cancel" href="./admin.php?page=<?php echo $_GET['page_id'] ?>#<?php echo $_GET['message_id'] ?>">キャンセル</a>
				<input type="submit" name="btn_submit" value="更新" id="btn_submit">
				<input type="hidden" name="message_id" value="<?php echo $message_data['id']; ?>">
			</form>
	    </div>
	</body>
</html>

