<?php
//データベース接続設定
$dsn='mysql:dbname=***;host=localhost';
$user='***';
$password='***';
$pdo=new PDO($dsn,$user,$password,array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_WARNING));

//ログイン確認
session_start();
if(!isset($_SESSION["user_name"])) {
    header("Location: misson_6-02_login.php");
    exit;
}elseif($_SESSION["admin"]!=1){
    header("Location: misson_6-02_login.php");
    exit;
}else{
    echo $_SESSION["user_name"]."さんがログイン中<br>";
}

//部員の削除
if(isset($_POST["mdelete"])){
    if($_POST["buinn1"]=="---"){
        echo "削除する部員を選択してください<br>";
    }else{
        //table1から削除
        $dname=$_POST["buinn1"];
        $sql = 'delete from table1 where name=:dname';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':dname', $dname, PDO::PARAM_STR);
        $stmt->execute();

        //table3から削除
        $sql = 'delete from table3 where smember=:dname';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':dname', $dname, PDO::PARAM_STR);
        $stmt->execute();
    }
}

//新規部員の登録
if(isset($_POST["permit"])){
    if($_POST["buinn2"]==0){
        echo "承認する新規部員を選んでください<br>";
    }else{
        //新規部員データベースから値を取得
        $sql = 'SELECT * FROM table4 WHERE id=:id';
        $stmt = $pdo->prepare($sql);                  // ←差し替えるパラメータを含めて記述したSQLを準備し、
        $stmt->bindParam(':id', $_POST["buinn2"], 
            PDO::PARAM_INT); // ←その差し替えるパラメータの値を指定してから、
        $stmt->execute();                             // ←SQLを実行する。
        $results = $stmt->fetchAll(); 
	    foreach ($results as $row){
            $name=$row['nname'];
            $pass=$row['npass'];
            $year=$row['nyear'];
            $nmail=$row['nmail'];
            $admin=0;
	    }

        //部員データベースに登録
        $sql = $pdo -> prepare("INSERT INTO table1 
	        (name,pass,year,mail,admin) 
	        VALUES (:name, :pass, :year, :mail, :admin)");
	    $sql -> bindParam(':name', $name, PDO::PARAM_STR);
	    $sql -> bindParam(':pass', $pass, PDO::PARAM_STR);
	    $sql -> bindParam(':year', $year, PDO::PARAM_INT);
	    $sql -> bindParam(':mail', $nmail, PDO::PARAM_STR);
        $sql -> bindParam(':admin', $admin, PDO::PARAM_INT);
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
            $mail->addAddress($nmail, $name.'さん'); //受信者（送信先）を追加する
        //    $mail->addReplyTo('xxxxxxxxxx@xxxxxxxxxx','返信先');
        //    $mail->addCC('xxxxxxxxxx@xxxxxxxxxx'); // CCで追加
        //    $mail->addBcc('music.club.php@gmail.com'); // BCCで追加
            $mail->Subject = "部員登録完了のお知らせ"; // メールタイトル
            $mail->isHTML(true);    // HTMLフォーマットの場合はコチラを設定します
            $body = $name."様\r\n某音楽サークルへの登録が完了しました。
            \r\nこちらからログインしてください\r\n
            https://***/misson_6-02_login.php";
            $mail->Body  = $body; // メール本文
            // メール送信の実行
            if(!$mail->send()) {
    	        echo '登録完了メールの送信に失敗しました<br>';
    	        echo 'Mailer Error: ' . $mail->ErrorInfo;
            } else {
                echo '登録完了メールが送信されました。<br>';
                //新規部員データベースから削除
                $sql = 'delete from table4 where id=:id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $_POST["buinn2"], 
                    PDO::PARAM_INT);
                $stmt->execute();
            }

    }
}
//新規部員の削除
if(isset($_POST["reject"])){
    if($_POST["buinn2"]==0){
        echo "否認する新規部員を選んでください<br>";
    }else{
        $sql = 'delete from table4 where id=:id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $_POST["buinn2"], 
            PDO::PARAM_INT);
        $stmt->execute();
    }    
}

//管理者権限の譲渡
if(isset($_POST["apass"])){
    $name=$_SESSION["user_name"];
    $admin=0;
    $sql = 'UPDATE table1 SET admin=:admin WHERE name=:name';
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':name', $name, PDO::PARAM_STR);
	$stmt->bindParam(':admin', $admin, PDO::PARAM_INT);
	$stmt->execute();

    $newname=$_POST["buinn3"];
    $admin=1;
    $sql = 'UPDATE table1 SET admin=:admin WHERE name=:name';
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':name', $newname, PDO::PARAM_STR);
	$stmt->bindParam(':admin', $admin, PDO::PARAM_INT);
    $stmt->execute();
    
    // セッションを開始
    session_start();
    // セッションを破棄
    $_SESSION = array();
    session_destroy();
    //ログインページへの移動
    header("Location: misson_6-02_login.php");
}

//部員名のリストボックス内の値
$sql = 'SELECT * FROM table1';
$stmt = $pdo->query($sql);
$results = $stmt->fetchAll();
$i1=1;
foreach ($results as $row){
    //$rowの中にはテーブルのカラム名が入る
    $buinnid[$i1]=$row['id'];
    $buinnmei[$i1]= $row['name'];
    $i1++;
}

//新規部員名のリストボックス内の値
$sql = 'SELECT * FROM table4';
$stmt = $pdo->query($sql);
$results = $stmt->fetchAll();
$i2=1;
foreach ($results as $row){
    //$rowの中にはテーブルのカラム名が入る
    $sinnid[$i2]=$row['id'];
    $sinnbuinn[$i2]= $row['nname'];
    $i2++;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>部長権限</title>
</head>
<p><a href="https://***/misson_6-2_logout.php">ログアウトする</a></p>
<header>
　<nav id="gnav">
　　<ul class="inner">
　　　　<li><a href="https://***/misson_6-02.php">
            曲名登録・削除、編成入力</a></li>
　　　　<li><a href="https://***/misson_6-02search.php">
            検索</a></li>
　　　　<li><a href="https://***/misson_6-02_admin.php">
            管理者画面</a></li>
　　</ul>
　</nav>
</header>
<body>
    部員の削除<br>
    <form action="#" method="post">
    <select name="buinn1">
        <option>---</option>
        <?php 
            for($j1=1;$j1<$i1;$j1++){
                echo "<option>".
                $buinnmei[$j1]."</option>";
            }
        ?>
    </select>
    <br> <input type="submit" name="mdelete" value="削除"><br>
    </form>
    
    <br>新規部員の許可<br>
    <form action "#" method="post">
    <select name="buinn2">
        <option value="0">---</option>
        <?php 
            for($j2=1;$j2<$i2;$j2++){
                echo "<option value=".$sinnid[$j2].">"
                    .$sinnbuinn[$j2]."</option>";
            }
        ?>
    </select>
    <br><input type="submit" name="permit" value="承認">
        <input type="submit" name="reject" value="否認"><br>
    </form>

    管理者権限の譲渡<br>
    <form action="#" method="post">
    <select name="buinn3">
        <option>---</option>
        <?php 
            for($j1=1;$j1<$i1;$j1++){
                echo "<option>".$buinnmei[$j1]."</option>";
            }
        ?>
    </select>
    <br> <input type="submit" name="apass" value="譲渡"><br>
    </form>
</body>
</html>