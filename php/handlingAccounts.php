<?php session_start();

include('dbConfig.php');
if(!isset($_SESSION['rola'])){header('location:../layout.htm');}

if(strcmp($_SESSION['rola'],'kudeatzaile') != 0){header('location:../layout.htm');}


if(isset($_POST['permutatu'])){
	if(!$konexioa = new mysqli($zerbitzaria,$erabiltzaile,$gakoa,$db)){
		echo('errorea konexioa ezartzean');
		exit();
	}
	else{
		$sql = "Select aktibo from users where id='".$_POST['id']."'";
		if(!$aktibo = $konexioa -> query($sql)){
			echo('errorea kontsulta egiterakoak');
			exit();
		}
		else{
			$ak = $aktibo -> fetch_array(MYSQLI_ASSOC);
			if($ak["aktibo"] == 0){$ak = 1;}
			else{$ak = 0;}
			$id = $_POST['id'];
			$sql = "Update users set aktibo = ".$ak." where id = '".$id."'";
			if(!$aktibu = $konexioa -> query($sql)){
				echo('errorea erabiltzailearen egoera aldatzean');
				exit();
			}
			else{
				$htm = taulaSortu($konexioa);
				echo($htm);
				$aktibo -> free_result();
				$konexioa -> close();
				exit();
			}
		}
	}
}
if(isset($_POST['ezabatu'])){
	if(!$konexioa = new mysqli($zerbitzaria,$erabiltzaile,$gakoa,$db)){
		echo('errorea konexioa ezartzean');
		exit();
	}
	else{
		$sql = "Delete from users where id ='".$_POST['id']."'";
		if(!$ezabatu = $konexioa -> query($sql)){
			echo('errorea erabiltzailearen ezabatzean');
			exit();
		}
		else{
			sleep(3);
			$htm = taulaSortu($konexioa);
			echo($htm);
			$konexioa -> close();
			exit();
		}
		
	}
}

/* ARGAZKIA JASO */
include('dbConfig.php');
$sql = "Select argazkia, mota from users where id = '".$_SESSION['id']."'";
$konexioa = new mysqli($zerbitzaria,$erabiltzaile,$gakoa,$db);
$em = $konexioa -> query($sql);
$argazkia = $em -> fetch_array(MYSQLI_ASSOC);
if($argazkia["mota"]!='-'){
	$img = "data:".$argazkia["mota"].";base64,".base64_encode($argazkia["argazkia"]);
}
else{$img = '';}

/* /ARGAZKIA */

?>
<html>
<head>
	<style>
	header:{align:center;}
	#msg{border:solid 2px #0055AA;background-color:#55AAFF;font-size:2em;display:none;padding:5px;position:absolute;}
	span:hover{border-radius:10px;background-color:#DDF;cursor:pointer;cursor:hand;}
	table{margin:0px auto;border:1px solid #55AA;}
	th{padding:5px;color:#05A;background-color:#AAF;}
	header span{padding:5px 10px;border:1px solid #000;float:right;margin:1px;}
	input,img{cursor:hand;cursor:pointer;}
	</style>
	<script src='../js/jquery.js' type='text/javascript'></script>
	<script>
		function saioaItxi(){
			$.ajax({
				type:'GET',
				url:'logout.php',
				dataType:'text',
				success: function(em){
					var msg = $('#msg');
					msg.html(em);
					w = $(window);
					msg.css({top:(w.height()-msg.height())/2,left:(w.width()-msg.width())/2});
					msg.show();
				},
				error: function(er){
					var msg = $('#msg');
					msg.html(er);
					w = $(window);
					msg.css({top:(w.height()-msg.height())/2,left:(w.width()-msg.width())/2});
					msg.show();
					setTimeout(function(){
						msg.fadeOut(1000);
					},3000);
				}
			});
		}
		function permutatu(usr){
			$.ajax({
				type: 'POST',
				url: 'handlingAccounts.php',
				dataType: 'html',
				data:{permutatu:0,id:usr.id.substr(3,usr.length)},
				success: function(em){
					$('#taula').html(em);
				}
			});
		}
		function ezabatu(usr){
			$('body').css({cursor:'wait'});
			$.ajax({
				type: 'POST',
				url: 'handlingAccounts.php',
				dataType: 'text',
				data:{ezabatu:0,id:usr},
				success: function(em){
					$('#taula').html(em);
					$('body').css({cursor:'default'});
				}
			});
		}
		function pasahitzAldatu(){
			$.get(
				'pasahitzAldatu.php',
				function(em){
					$('#taula').html(em);
				}
			);
		}
	</script>
</head>
<body>
	<header>
		<img src='<?php echo($img);?>' width='75px' />
		<span id='itxi' onclick='saioaItxi()'>Log Out</span>
		<span style='position:absolute'><?php echo($_SESSION['user']);?></span><br/>
		<hr/>
	</header>
	<section>
		<span id='msg'></span>
		<div id='taula'><?php echo(taulaSortu($konexioa));?></div>
	</section>
	<footer>

	</footer>
</body>
</html>
<?php
$konexioa -> close();

function taulaSortu($k){
	$sql = "Select id, aktibo, eposta, pasahitza, argazkia, mota from users";

	$erabiltzaileak = $k -> query($sql);

	$htm = '<style>td{border-bottom:dotted 1px #5555AA;border-top:dotted 1px #5555AA;padding:0px 10px;}th{border:solid 1px #5555AA;}</style><table style="dotted:solid 1px #5555AA;"><tr><th scope=\'row\'>aktibo</th><th scope=\'row\'>eposta</th><th scope=\'row\'>pasahitza</th><th scope=\'row\'>argazkia</th><th scope=\'row\'>ezabatu</th></tr>';

	//taularen erregistroak
	while($lerro = $erabiltzaileak -> fetch_array(MYSQLI_ASSOC)){
		//marrazkia baldin badago tratatu
		if($lerro["mota"]!='-'){
			$img = '<img src = "data:'.$lerro["mota"].';base64,'.base64_encode($lerro["argazkia"]).'" alt = "marrazkia" width="50"/>';
		}
		else{$img = '-';}
		if($lerro['aktibo'] == 0){
			$aktibo = '<input onclick=\'permutatu(this)\' id="usr'.$lerro['id'].'" type="checkbox"/>';
		}
		else{
			$aktibo = '<input onclick=\'permutatu(this)\' id="usr'.$lerro['id'].'" type=\'checkbox\' checked=\'checked\'/>';
		}
		$htm = $htm.'<tr><td>'.$aktibo.'</td><td>'.$lerro["eposta"].'</td><td>'.$lerro["pasahitza"].'</td><td>'.$img.'</td><td><img onclick=\'ezabatu("'.$lerro['id'].'")\' src=\'../images/ez.png\'/></td></tr>';
		
	}
	$htm = $htm.'</table>';

	$erabiltzaileak -> free_result();
	return $htm;
}?>