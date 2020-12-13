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
}else{
    echo $_SESSION["user_name"]."さんがログイン中<br>";
    $kenngenn=$_SESSION["admin"];
}

//曲名の登録
if(isset($_POST["submit"])){
    //名前の重複防止
    $sname=$_POST["sname"];
    $sql = 'SELECT * FROM table2 WHERE sname=:sname ';
    $stmt = $pdo->prepare($sql);                  // ←差し替えるパラメータを含めて記述したSQLを準備し、
    $stmt->bindParam(':sname', $sname, PDO::PARAM_STR); // ←その差し替えるパラメータの値を指定してから、
    $stmt->execute();                             // ←SQLを実行する。
    $results = $stmt->fetchall();
    $flag="";
    foreach($results as $low){
        $flag= $low['sname'];
    }
    if($flag!=""){
	    echo "重複する名前が既に存在します<br>";
	}elseif($sname==""){
	    echo "曲名を入力してください<br>";
	}else{
	//記入処理
 	$sql = $pdo -> prepare("INSERT INTO table2 
 	(sname) VALUES (:sname)");
	$sql -> bindParam(':sname', $sname, PDO::PARAM_STR);
	$sql -> execute();
	}
}

//曲名の削除
if(isset($_POST["delete"])){
	$sakuzyo = $_POST["sakuzyo"];
	if($sakuzyo=="---"){
	    echo "削除する曲名を選択してください<br>";
	}else{
    //table2からの削除
	$sql = 'delete from table2 where sname=:sname';
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':sname', $sakuzyo, PDO::PARAM_STR);
    $stmt->execute();
    //table3からの削除
    $sql = 'delete from table3 where ssong=:ssong';
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':ssong', $sakuzyo, PDO::PARAM_STR);
	$stmt->execute();
	}
}
//部員名から編成入力
if(isset($_POST["n-hennsei"])){
    $buinn1=$_POST["buinn1"];
    $kyoku1=$_POST["kyoku1"];
    $hennsei1=$_POST["hennsei1"];
    //名前の重複防止
    $sql = 'SELECT * FROM table3 WHERE smember=:smember ';
    $stmt = $pdo->prepare($sql);                  // ←差し替えるパラメータを含めて記述したSQLを準備し、
    $stmt->bindParam(':smember', $buinn1, PDO::PARAM_STR); // ←その差し替えるパラメータの値を指定してから、
    $stmt->execute();                             // ←SQLを実行する。
    $results = $stmt->fetchall();
    $flag3="";
    foreach($results as $low){
        if($low['ssong']==$kyoku1 && $low['inst']==$hennsei1){
            $flag3=1;
        }
    }
    if($buinn1=="---"){
        echo "部員名を選択してください<br>";
    }elseif($kyoku1=="---"){
        echo "曲名を選択してください<br>";
    }elseif($hennsei1=="---"){
        echo "担当楽器を選択してください<br>";
    }elseif($flag3==1){
        echo "既に入力されています<br>";
    }else{
 	    $sql = $pdo -> prepare("INSERT INTO table3 
 	        (smember,ssong,inst) VALUES (:smember,:ssong,:inst)");
	    $sql -> bindParam(':smember', $buinn1, PDO::PARAM_STR);
	    $sql -> bindParam(':ssong', $kyoku1, PDO::PARAM_STR);
	    $sql -> bindParam(':inst', $hennsei1, PDO::PARAM_STR);
	    $sql -> execute();
    }
}

//曲名から編成入力を作成
if(isset($_POST["m-hennsei"])){
    //ini_set("memory_limit", "200M");
    $kyoku2=$_POST["kyoku2"];
    $buinn2=$_POST["buinn2"];
    $hennsei2=$_POST["hennsei2"];
    $ctr=count($buinn2);
    $mmemb=[];
    $mhenn=[];
    //空白の削除
    for($k=0;$k<$ctr;$k++){
        if($buinn2[$k]=="---" && $hennsei2[$k]=="---"){
        }elseif($buinn2[$k]=="---" or $hennsei2[$k]=="---"){
            $mmemb[]="empty";
            $mhenn[]="empty";
        }else{
            $mmemb[]=$buinn2[$k];
            $mhenn[]=$hennsei2[$k];
        }
    }
    //エラーの表示
    if($kyoku2=="---"){
        echo "曲を選択してください<br>";
    }elseif(empty($mmemb)){
        echo "部員と編成を入力してください<br>";
    }elseif(in_array("empty",$mmemb)){
        echo "部員または編成に空白があるため入力できません<br>";
    }else{
        //重複データの検索
        $ctr2=count($mmemb);
        $check=[];
        for($jj=0;$jj<$ctr2;$jj++){
            $sql = 'SELECT * FROM table3 WHERE smember=:smember AND 
                 ssong=:ssong AND inst=:inst';
            $stmt = $pdo->prepare($sql);                  // ←差し替えるパラメータを含めて記述したSQLを準備し、
            $stmt->bindParam(':smember', $mmemb[$jj], PDO::PARAM_STR); // ←その差し替えるパラメータの値を指定してから、
            $stmt->bindParam(':ssong', $kyoku2, PDO::PARAM_STR);
            $stmt->bindParam(':inst', $mhenn[$jj], PDO::PARAM_STR);
            $stmt->execute();                             // ←SQLを実行する。
            $results = $stmt->fetchall();
            foreach($results as $low){
                if($low['ssong']==$kyoku2 && $low['inst']==$mhenn[$jj]
                    && $low['smember']==$mmemb[$jj]){
                    echo $mmemb[$jj]." ".$mhenn[$jj]." "
                        ."を重複のため記入しませんでした<br>";
                    $check[]=$jj;
                }
            }
        }
        $ctr3=count($check);
        $alert="";
        //データベースに書き込み
        for ($ii=0;$ii<$ctr2;$ii++){
            for($kk=0;$kk<$ctr3;$kk++){
                if($ii==$check[$kk]){
                    $alert=1;
                }
            }
            if($alert==""){
                $sql = $pdo -> prepare("INSERT INTO table3 
 	                (smember,ssong,inst) VALUES (:smember,:ssong,:inst)");
	            $sql -> bindParam(':smember', $mmemb[$ii], PDO::PARAM_STR);
	            $sql -> bindParam(':ssong', $kyoku2, PDO::PARAM_STR);
	            $sql -> bindParam(':inst', $mhenn[$ii], PDO::PARAM_STR);
                $sql -> execute();
            }else{
                $alert="";
            }
        }
    }
}

//部員名から編成を削除
if(isset($_POST["h-delete"])){
    if($_POST["buinn3"]=="---"){
        echo "部員名を選択してください";
    }elseif($_POST["kyoku3"]=="---"){
        echo "曲名を選択してください";
    }elseif($_POST["hennsei3"]=="---"){
        echo "編成を選択してください";
    }else{
        $buinn3=$_POST["buinn3"];
        $kyoku3=$_POST["kyoku3"];
        $hennsei3=$_POST["hennsei3"];
        //データベース上に存在するかの確認
        $sql = 'SELECT * FROM table3 WHERE smember=:smember AND 
             ssong=:ssong AND inst=:inst';
        $stmt = $pdo->prepare($sql);                  // ←差し替えるパラメータを含めて記述したSQLを準備し、
        $stmt->bindParam(':smember', $buinn3, PDO::PARAM_STR); // ←その差し替えるパラメータの値を指定してから、
        $stmt->bindParam(':ssong', $kyoku3, PDO::PARAM_STR);
        $stmt->bindParam(':inst', $hennsei3, PDO::PARAM_STR);
        $stmt->execute();                             // ←SQLを実行する。
        $results = $stmt->fetchall();
        $flag4="";
        foreach($results as $low){
            if($low['ssong']==$kyoku3 && $low['inst']==$hennsei3
                && $low['smember']==$buinn3){
                $flag4=1;
            }
        }
        
        //記入処理
        if($flag4==1){
            $sql = 'delete from table3 WHERE smember=:smember AND 
                ssong=:ssong AND inst=:inst';
	        $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':smember', $buinn3, PDO::PARAM_STR); // ←その差し替えるパラメータの値を指定してから、
            $stmt->bindParam(':ssong', $kyoku3, PDO::PARAM_STR);
            $stmt->bindParam(':inst', $hennsei3, PDO::PARAM_STR);
            $stmt->execute();
        }elseif($flag4==""){
            echo "入力したデータは存在しません";
        }
    }
}

//リストボックスの値
//曲名のリストボックス内の値
	$sql = 'SELECT * FROM table2';
	$stmt = $pdo->query($sql);
	$results = $stmt->fetchAll();
	$i=1;
	foreach ($results as $row){
		//$rowの中にはテーブルのカラム名が入る
		$kyokumei[$i]= $row['sname'];
		$i++;
	}
	
//部員名のリストボックス内の値
	$sql = 'SELECT * FROM table1';
	$stmt = $pdo->query($sql);
	$results = $stmt->fetchAll();
	$i1=1;
	foreach ($results as $row){
		//$rowの中にはテーブルのカラム名が入る
		$buinnmei[$i1]= $row['name'];
		$i1++;
	}
?>

<!--フォームの追加 javascriptt-->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script type="text/javascript">
$(document).on("click", ".add", function() {
    $(this).parent().clone(true).insertAfter($(this).parent());
});
$(document).on("click", ".del", function() {
    var target = $(this).parent();
    if (target.parent().children().length > 1) {
        target.remove();
    }
});
</script>
	
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>mission_6-02</title>
</head>
<p><a href="https://***/misson_6-2_logout.php">ログアウトする</a></p>
<header>
　<nav id="gnav">
　　<ul class="inner">
　　　　<li><a href="https://***/misson_6-02.php">
            曲名登録・削除、編成入力</a></li>
　　　　<li><a href="https://***/misson_6-02search.php">
            検索</a></li><br>
        <?php
        if($kenngenn==1){
        echo "<li><a href="."https://***/misson_6-02_admin.php".">
            管理者画面</a></li>";
        }
        ?>
　　</ul>
　</nav>
</header>
<body>
    曲名登録<br>
    <form action="" method="post">
        曲名<input type="text" name="sname"><br>
        <input type="submit" name="submit" value="送信"><br>
    </form>
    
    <br>曲名削除<br>
    <form action="#" method="post">
        <select name="sakuzyo">
            <option>---</option>
            <?php 
            for($j=1;$j<$i;$j++){
                echo "<option>".$kyokumei[$j]
                ."</option>";
            }
            ?>
    </select>
     <br> <input type="submit" name="delete" value="削除"><br>
    </form>
    
    <br>部員名から編成入力を作成<br>
    <form action="#" method="post">
        部員名
        <select name="buinn1">
            <option>---</option>
            <?php 
            for($j1=1;$j1<$i1;$j1++){
                echo "<option>".$buinnmei[$j1]
                ."</option>";
            }
            ?>
        </select>
        曲名
        <select name="kyoku1" >
            <option>---</option>
            <?php 
            for($j=1;$j<$i;$j++){
                echo "<option>".$kyokumei[$j]
                ."</option>";
            }
            ?>
        </select>
        担当楽器
        <select name="hennsei1">
            <option>---</option>
            <option value="Vocal">Vocal</option>
            <option value="Guitar">Guitar</option>
            <option value="Bass">Bass</option>
            <option value="Keyboard">Keyboard</option>
            <option value="Cajon">Cajon</option>
            <option value="Drums">Drums</option>
            <option value="Others">Others</option>
        </select>
     <br> <input type="submit" name="n-hennsei" value="登録"><br>
    </form>

    <br>曲名から編成入力を作成<br>
    <form action="#" class="form-control" method="POST">
            曲名
            <select name="kyoku2">
                <option>---</option>
                <?php 
                for($j=1;$j<$i;$j++){
                    echo "<option>".$kyokumei[$j]
                    ."</option>";
                }
                ?>
            </select><br>
    <div id="input_pluralBox">
        <div id="input_plural">
            部員名
            <select name="buinn2[]">
                <option>---</option>
                <?php 
                for($j1=1;$j1<$i1;$j1++){
                    echo "<option>".$buinnmei[$j1]
                    ."</option>";
                }
                ?>
            </select>
            担当楽器
            <select name="hennsei2[]" >
                <option>---</option>
                <option value="Vocal">Vocal</option>
                <option value="Guitar">Guitar</option>
                <option value="Bass">Bass</option>
                <option value="Keyboard">Keyboard</option>
                <option value="Cajon">Cajon</option>
                <option value="Drums">Drums</option>
                <option value="Others">Others</option>
            </select>
            <input type="button" value="＋" class="add pluralBtn">
            <input type="button" value="－" class="del pluralBtn">

        </div>
    </div>
    <input type="submit" name="m-hennsei" value="登録"><br>
    </form>

    <br>部員名から編成を削除<br>
    <form action="#" method="post">
        部員名
        <select name="buinn3">
            <option>---</option>
            <?php 
            for($j1=1;$j1<$i1;$j1++){
                echo "<option>".$buinnmei[$j1]
                ."</option>";
            }
            ?>
        </select>
        曲名
        <select name="kyoku3" >
            <option>---</option>
            <?php 
            for($j=1;$j<$i;$j++){
                echo "<option>".$kyokumei[$j]
                ."</option>";
            }
            ?>
        </select>
        担当楽器
        <select name="hennsei3">
            <option>---</option>
            <option value="Vocal">Vocal</option>
            <option value="Guitar">Guitar</option>
            <option value="Bass">Bass</option>
            <option value="Keyboard">Keyboard</option>
            <option value="Cajon">Cajon</option>
            <option value="Drums">Drums</option>
            <option value="Others">Others</option>
        </select>
     <br> <input type="submit" name="h-delete" value="削除"><br>
    </form>
</body>
</html>