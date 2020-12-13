<?php
//データベース接続設定
$dsn='mysql:dbname=***;host=localhost';
$user='***';
$password='***';
$pdo=new PDO($dsn,$user,$password,array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_WARNING));

if(isset($_POST["submit"])){
    $name = str_replace(array(" ", "　"), "", $_POST["name"]);
    $pass=$_POST["pass"];
    if($name==""){
        echo "名前を入力してください<br>";
    }elseif($pass==""){
        echo "パスワードを入力してください<br>";
    }else{
	    $sql = 'SELECT * FROM table1 WHERE name=:name ';
        $stmt = $pdo->prepare($sql);                  // ←差し替えるパラメータを含めて記述したSQLを準備し、
        $stmt->bindParam(':name', $name, PDO::PARAM_STR); // ←その差し替えるパラメータの値を指定してから、
        $stmt->execute();                             // ←SQLを実行する。
        $results = $stmt->fetchAll();
	    foreach ($results as $row){
	        if ($row['pass']==$pass){
                //ログイン確認用
                session_start();
                $_SESSION["user_name"] = $name;
                $_SESSION["admin"]=$row['admin'];
	            // ステータスコードを出力
                http_response_code( 301 ) ;
	            // リダイレクト
	            header( "Location: misson_6-02search.php" ) ;
	            exit ;
	        }elseif($row['pass']!=$pass){
	            $alart= "パスワードが一致しません<br>";
	        }
	    }
	    if($alart==""){
	        echo "名前が一致しません<br>";
	    }else{
	        echo $alart;
	    }
    }
}

if(isset($_POST["n-submit"])){
    $nname = str_replace(array(" ", "　"), "", $_POST["nname"]);
    $npass = str_replace(array(" ", "　"), "", $_POST["npass"]);
    $nyear=$_POST["nyear"];
    $nmail = str_replace(array(" ", "　"), "", $_POST["nmail"]);
    if($nname==""){
        echo "登録する名前を入力してください<br>";
    }elseif($npass==""){
        echo "登録するパスワードを入力してください<br>";
    }elseif($nyear==""){
        echo "登録する学年を入力してください<br>";
    }elseif($nmail==""){
        echo "登録するメールアドレスを入力してください<br>";
    }else{
        $sql = $pdo -> prepare("INSERT INTO table4 
            (nname,npass,nyear,nmail) 
            VALUES (:nname,:npass,:nyear,:nmail)");
	    $sql -> bindParam(':nname', $nname, PDO::PARAM_STR);
	    $sql -> bindParam(':npass', $npass, PDO::PARAM_STR);
	    $sql -> bindParam(':nyear', $nyear, PDO::PARAM_INT);
	    $sql -> bindParam(':nmail', $nmail, PDO::PARAM_STR);
	    $sql -> execute();
	    
        //メールの送信
        require 'src/Exception.php';
        require 'src/PHPMailer.php';
        require 'src/SMTP.php';
        require 'setting.php';

        // PHPMailerのインスタンス生成
            $mail = new PHPMailer\PHPMailer\PHPMailer();

            $mail->isSMTP(); // SMTPを使うようにメーラーを設定する
            $mail->SMTPAuth = true;
            $mail->Host = MAIL_HOST; // メインのSMTPサーバー（メールホスト名）を指定
            $mail->Username = MAIL_USERNAME; // SMTPユーザー名（メールユーザー名）
            $mail->Password = MAIL_PASSWORD; // SMTPパスワード（メールパスワード）
            $mail->SMTPSecure = MAIL_ENCRPT; // TLS暗号化を有効にし、「SSL」も受け入れます
            $mail->Port = SMTP_PORT; // 接続するTCPポート

            // メール内容設定
            $mail->CharSet = "UTF-8";
            $mail->Encoding = "base64";
            $mail->setFrom(MAIL_FROM,MAIL_FROM_NAME);
            $mail->addAddress($nmail, $nname.'さん'); //受信者（送信先）を追加する
        //    $mail->addReplyTo('xxxxxxxxxx@xxxxxxxxxx','返信先');
        //    $mail->addCC('xxxxxxxxxx@xxxxxxxxxx'); // CCで追加
            $mail->addBcc('***@***'); // BCCで追加
            $mail->Subject = MAIL_SUBJECT; // メールタイトル
            $mail->isHTML(true);    // HTMLフォーマットの場合はコチラを設定します
            $body = $nname."様\r\n某音楽サークルへの仮登録が完了しました。\n
            部長が承認するまでお待ちください。";
            $mail->Body  = $body; // メール本文
            // メール送信の実行
            if(!$mail->send()) {
    	        echo 'メールの送信に失敗しました<br>';
    	        echo 'Mailer Error: ' . $mail->ErrorInfo;
            } else {
    	        echo 'メールが送信されました。<br>';
            }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>mission_6-02</title>
</head>
<body>
    ログイン<br>
    <form action="" method="post">
        名前<input type="text" name="name"><br>
        パスワード<input type="text" name="pass"><br>
        <input type="submit" name="submit" value="ログイン"><br>
    </form>
    <br>新規部員申請を行う方はこちら  *スペースは送信時自動で削除されます<br>
        <form action="#" method="post">
        名前<input type="text" name="nname"><br>
        パスワード<input type="text" name="npass"><br>
        学年(メールアドレスの最初の2文字）
        <select name="nyear">
            <?php
            $year=date("y");
            for($i=0;$i<=7;$i++){
                echo "<option>".$year
                ."</option>";
                $year--;
            }
            ?>
        </select><br>
        メールアドレス<input type="text" name="nmail"><br>
        <input type="submit" name="n-submit" value="送信"><br>
    </form>
</body>
</html>