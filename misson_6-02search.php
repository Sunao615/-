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
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>mission_6-02_検索</title>
</head>
<p><a href="https://***/misson_6-2_logout.php">ログアウトする</a></p>
<header>
　<nav id="gnav">
　　<ul class="inner">
　　　　<li><a href="https://***/misson_6-02.php">
            曲名登録・削除、編成入力</a></li>
　　　　<li><a href="https://***/misson_6-02search.php">
            検索</a></li>
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
    <form action="#" method="post">
    曲名から検索<br>
    <select name="kyoku1">
        <option>---</option>
        <?php 
            for($j=1;$j<$i;$j++){
                echo "<option>".$kyokumei[$j]
                ."</option>";
            }
        ?>
        </select>
     <br> <input type="submit" name="s-search" value="検索"><br>
    </form>
    <form action="#" method="post">
    部員名から検索<br>
    <select name="ninnzuu">
        <option>1</option>
        <option>2</option>
        <option>3</option>
        <option>4</option>
        <option>5</option>
    </select>人以上参加している曲<br>

    <div id="input_pluralBox">
        <div id="input_plural">
    <select name="buinn1[]" class="form-control">
        <option>---</option>
        <?php 
            for($j1=1;$j1<$i1;$j1++){
                echo "<option>".$buinnmei[$j1]
                ."</option>";
            }
        ?>
    </select>

    <input type="button" value="＋" class="add pluralBtn">
        <input type="button" value="－" class="del pluralBtn">
        </div>
    </div>
    <input type="submit" name="m-search" value="検索"><br>
    </form>
</body>
</html>

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

<?php
//曲名の検索
if(isset($_POST["s-search"])){
    $sname=$_POST["kyoku1"];
    if($sname=="---"){
        echo "曲を選択してください";
    }else{
    $sql = 'SELECT * FROM table3 WHERE ssong=:ssong ';
    $stmt = $pdo->prepare($sql);                  // ←差し替えるパラメータを含めて記述したSQLを準備し、
    $stmt->bindParam(':ssong', $sname, PDO::PARAM_STR); // ←その差し替えるパラメータの値を指定してから、
    $stmt->execute();                             // ←SQLを実行する。
    $results = $stmt->fetchAll(); 
    echo $sname."の検索結果<br>";
	foreach ($results as $row){
		//$rowの中にはテーブルのカラム名が入る
		echo $row['smember'].',';
		echo $row['inst'].'<br>';
	}
	echo "<hr>";
    }
}

//部員名の検索
if(isset($_POST["m-search"])){
    $mname=$_POST["buinn1"];
    $ninnzuu=$_POST["ninnzuu"];
    //空白の削除
    $smembers=[];
    foreach ($mname as $mn){
        if ($mn!="---"){
            $smembers[]=$mn;
        }
    }

    //一行目の表示
    foreach($mname as $mn){
        if($mn!="---"){
            echo $mn."さん  ";
        }
    }
    echo $ninnzuu."人以上参加  の検索結果<br>";

    //文字列の結合
    $smember=implode(' , ',$smembers);
    if($smember==""){
        echo "部員を選択してください";
    }else{
        //検索結果の集計用変数
        $hyouzism[0]="検索結果";
        $count="";
        $hyouzic[0]=1;
        foreach ($smembers as $sm){
            $sql = 'SELECT * FROM table3 WHERE smember in (:smember)';
            $stmt = $pdo->prepare($sql);                  // ←差し替えるパラメータを含めて記述したSQLを準備し、
            $stmt->bindParam(':smember', $sm, PDO::PARAM_STR); // ←その差し替えるパラメータの値を指定してから、
            $stmt->execute();                             // ←SQLを実行する。
            $results = $stmt->fetchAll();
    
            //検索結果の集計
            foreach ($results as $row){
                $count=array_search($row['ssong'],$hyouzism);
                if($count==""){
                    $hyouzism[]=$row['ssong'];
                    $hyouzic[]=1;
                }else{
                    $hyouzic[$count]++;
                }
            }
        }

        //検索結果の集計と表示
        $crt=count($hyouzic)-1;
        for ($i=1;$i<=$crt;$i++){
            if($hyouzic[$i]>=$ninnzuu){
                echo $hyouzism[$i].'<br>';
            }
        }
        echo "<hr>";
    }
}
?>