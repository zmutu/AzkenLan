<?php session_start();

if(!isset($_SESSION['rola'])){header('location:../layout.htm');}

if(strcmp($_SESSION['rola'],'ikasle') != 0){header('location:../layout.htm');}

include('dbConfig.php');
$konexioa = new mysqli($zerbitzaria,$erabiltzaile,$gakoa,$db);
if($konexioa -> connect_error){
	echo('errorea datu-basearekin konexioa ezartzean');
	exit();
}
if(isset($_GET['zerrenda'])){
	$htm = galdera_zerrenda($konexioa);
	$konexioa -> close();
	$htm.='<hr/>';
	echo($htm);
	exit();
}
if(isset($_POST['ezabatu'])){
	galdera_ezabatu($konexioa,$_POST['ezabatu']);
	sleep(3);
	$htm = galdera_zerrenda($konexioa);
	$konexioa -> close();
	echo($htm);
	exit();
}
if(isset($_POST['gorde'])){
	$id = $_POST['id'];
	$mail = $_POST['mail'];
	$galdera = $_POST['galdera'];
	$zuzena = $_POST['zuzena'];
	$e1 = $_POST['erantzunokerra1'];
	$e2 = $_POST['erantzunokerra2'];
	$e3 = $_POST['erantzunokerra3'];
	$zailtasun = $_POST['zailtasuna'];
	$gai = $_POST['gaia'];
	erregistroa_eguneratu($konexioa,$id,$mail,$galdera,$zuzena,$e1,$e2,$e3,$zailtasun,$gai);
	sleep(6);
	$htm = galdera_zerrenda($konexioa);
	echo($htm);
	$konexioa -> close();
	exit();
}
else{
	$f = fopen('fitx.txt','a+');
	fwrite($f,"\nGaldera erregistroa aldatzeko datorik ez du jaso");
	fclose($f);
}
/* ARGAZKIA JASO */
$sql = "Select argazkia, mota from users where id = '".$_SESSION['id']."'";
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
	span:hover{border-radius:10px;background-color:#DDF;cursor:pointer;cursor:hand;font-weight:bold;color:#05A;}
	table{margin:0px auto;border:1px solid #55AA;}
	th{padding:5px;color:#05A;background-color:#AAF;}
	span{padding:5px 15px;border:1px solid #000;float:right;margin:2px;}
	</style>
	<script src='../js/jquery.js' type='text/javascript'></script>
	<script>
		function galderaZerrenda(){
			$.ajax({
				url:'handlingQuizes.php',
				data:{zerrenda:1},
				type:'GET',
				dataType:'html',
				success:function(em){
					if(em.indexOf('<table')>0){$('#taula').html(em);}
					else{
						var msg = $('#msg');
						msg.html(em);
						w = $(window);
						msg.css({top:(w.height()-msg.height())/2,left:(w.width()-msg.width())/2});
						msg.show();
						setTimeout(function(){
							msg.fadeOut(1000);
						},3000);
					}
				},
				error:function(er){
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
		function galderaBerria(){
			console.log('--');
			$.ajax({
				type:'GET',
				url:'addQuestion_HTML5.php',
				type:'html',
				success:function(em){
					$('#taula').html(em);
				},
				error:function(er){
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
		function ezabatu(o){
			$('body').css({cursor:'wait'});
			$.ajax({
				type:'post',
				url:'handlingQuizes.php',
				data:{ezabatu:parseInt(o.id.substr(-3))},
				dataType:'html',
				success:function(em){
					$('body').css({cursor:'default'});
					if(em.indexOf('<table')>0){$('#taula').html(em);}
					else{
						var msg = $('#msg');
						msg.html(em);
						w = $(window);
						msg.css({top:(w.height()-msg.height())/2,left:(w.width()-msg.width())/2});
						msg.show();
						setTimeout(function(){
							msg.fadeOut(1000);
						},3000);
					}
				}
			});
		}
		function gorde(o){
			$('body').css({cursor:'default'});
			var objs = $("[id$='"+o.id.substr(-3)+"']");
			var datoak = 'gorde:"1",';
			objs.each(function(ix,obj){
				if(obj.value!=null){
					datoak += obj.id.substr(0,obj.id.length-3) + ':"' + obj.value + '",';
				}
			});
			datoak = datoak.substr(0,datoak.length-1);
			$.ajax({
				url:'handlingQuizes.php',
				type:'post',
				data:{datoak},
				contentType: false,
				cache: false,
				processData: false,
				dataType:'html',
				success:function(em){
					$('body').css({cursor:'default'});
					if(em.indexOf('<table')>0){$('#taula').html(em);}
					else{
						var msg = $('#msg');
						msg.html(em);
						w = $(window);
						msg.css({top:(w.height()-msg.height())/2,left:(w.width()-msg.width())/2});
						msg.show();
						setTimeout(function(){
							msg.fadeOut(1000);
						},3000);
					}
				},
				error:function(er){
					var msg = $('#msg');
					msg.html(em);
					w = $(window);
					msg.css({top:(w.height()-msg.height())/2,left:(w.width()-msg.width())/2});
					msg.show();
					setTimeout(function(){
						msg.fadeOut(1000);
					},3000);
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
		<img src='<?php echo($img);?>' width='40px' /><span style='float:none;border:0px'><?php echo($_SESSION['user']);?></span>
		<span id='itxi' onclick='saioaItxi()'>Log Out</span>
		<span id='itxi' onclick='galderaZerrenda()'>Galdera Zerrenda</span>
		<span id='berria' onclick='galderaBerria()'>Galdera Berria</span>
		<span id='berria' onclick='pasahitzAldatu()'>Pasahitza Aldatu</span><br/>
		<hr/>
	</header>
	<section>
		<span id='msg'></span>
		<div id='taula'></div>
	</section>
	<footer>

	</footer>
</body>
</html>
<?php
function galdera_zerrenda($k){
	$erregistroak = $k -> query("Select * From questions where mail='".$_SESSION['eposta']."'");
	if(!$erregistroak){
		return('<b>'.$_SESSION['eposta'].'</b><br/>ikasleak galderarik ez du');
	}
	$zutabe = $k -> field_count;
	$zutabe -= 1;	//azkeneko zutabea marrazkiaren mota da, datua erabili behar da baina ez da erakutsi behar

	$htm = '<style>td{border-bottom:dotted 1px #5555AA;border-top:dotted 1px #5555AA;}th{border:solid 1px #5555AA;background-color:#EEF;}table img{cursor:pointer;cursor:hand;}</style>';
	$htm .= '<table style="border:solid 1px #5555AA;"><tr><th scope="row">ezabatu</th><th scope="row">gorde</th>';

	//taularen goiburuak
	for($i=0;$i<$zutabe;$i++){
		$erregistroak -> field_seek($i);
		$attr = $erregistroak -> fetch_field();
		$htm = $htm."<th scope='row'>".$attr -> name."</th>";
	}
	$htm = $htm.'</tr>';

	//taularen erregistroak
	while($lerro = $erregistroak -> fetch_array(MYSQLI_ASSOC)){
		//marrazkia baldin badago tratatu
		if($lerro["mota"]!='-'){
			$img = '<img src = "data:'.$lerro["mota"].';base64,'.base64_encode($lerro["marrazkia"]).'" alt = "marrazkia" width="75"/>';
		}
		else{$img = '-';}
		$ida = str_pad($lerro['id'],3,'0',STR_PAD_LEFT);
		$htm = $htm.'<tr>';
		$htm = $htm.'
		<td><img onclick="ezabatu(this)" id="ezabatu'.$ida.'" src="../images/ez.png"/></td>
		<td><img onclick="gorde(this)" id="gorde'.$ida.'" src="../images/gorde.png"/></td>
		<td>'.$lerro["id"].'</td>
		<td><input type="text" id="mail'.$ida.'" value="'.$lerro["mail"].'"/></td>
		<td><textarea id="galdera'.$ida.'">'.$lerro["galdera"].'</textarea></td>
		<td><textarea id="zuzena'.$ida.'">'.$lerro["erantzun_zuzena"].'</textarea></td>
		<td><textarea id="erantzunokerra1'.$ida.'">'.$lerro["erantzun_okerra_1"].'</textarea></td>
		<td><textarea id="erantzunokerra2'.$ida.'">'.$lerro["erantzun_okerra_2"].'</textarea></td>
		<td><textarea id="erantzunokerra3'.$ida.'">'.$lerro["erantzun_okerra_3"].'</textarea></td>
		<td><input type="text" id="zailtasuna'.$ida.'" size="5" value="'.$lerro["zailtasuna"].'"/></td>
		<td><input type="text" id="gaia'.$ida.'" size="5" value="'.$lerro["gaia"].'"/></td>
		<td>'.$img.'</td>';
		$htm = $htm.'</tr>';
	}
	$htm = $htm.'</table>';

	$erregistroak -> free_result();
	return $htm;
}
function galdera_ezabatu($k,$i){
	$k -> query("Delete from questions where id = '".$i."' and mail = '".$_SESSION["eposta"]."'");
}
function erregistroa_eguneratu($k,$i,$m,$g,$z,$e1,$e2,$e3,$zl,$ga){
	$sql = "Insert into questions(mail,galdera,erantzun_zuzena,erantzun_okerra_1,erantzun_okerra_2,erantzun_okerra_3,zailtasuna,gaia) values($m,$g,$z,$e1,$e2,$e3,$zl,$ga) where id = '". $i. "'";
	$f = fopen('fitx.txt','a+');
	fwrite("\n".$sql);
	$k -> query($sql);
}
?>
