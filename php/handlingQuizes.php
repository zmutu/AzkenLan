<?php session_start();

if(!isset($_SESSION['rola'])){
	header('location:layout.php');
	exit();
}

if(strcmp($_SESSION['rola'],'ikasle') != 0 && strcmp($_SESSION['rola'],'kudeatzaile') != 0){
	header('location:../layout.htm');
	exit();
}

include('dbConfig.php');
$konexioa = new mysqli($zerbitzaria,$erabiltzaile,$gakoa,$db);
if($konexioa -> connect_error){
	amaitu('errorea datu-basearekin konexioa ezartzean');
}
if(isset($_GET['zerrenda'])){
	$htm = galdera_zerrenda($konexioa);
	$konexioa -> close();
	amaitu($htm);
}
if(isset($_POST['ezabatu'])){
	galdera_ezabatu($konexioa,$_POST['ezabatu']);
	sleep(26);
	$htm = galdera_zerrenda($konexioa);
	$konexioa -> close();
	amaitu($htm);
}
if(isset($_POST['gorde'])){

	$id = $_POST['id'];
	$galdera = $_POST['galdera'];
	$zuzena = $_POST['erantzun_zuzena'];
	$e1 = $_POST['erantzun_okerra_1'];
	$e2 = $_POST['erantzun_okerra_2'];
	$e3 = $_POST['erantzun_okerra_3'];
	$zailtasun = $_POST['zailtasuna'];
	$gai = $_POST['gaia'];
	//erregistroa_eguneratu($konexioa,$id,$galdera,$zuzena,$e1,$e2,$e3,$zailtasun,$gai);
	//sleep(5);
	//$htm = galdera_zerrenda($konexioa);
	$konexioa -> close();
    $htm = 'funtzionalitate hau desgaitu egin da.<br/>ez da derrigorrezkoa eta datoak gordetzean emaiza<br/>ez da behar den bezalakoa'; 
	amaitu($htm);
}
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
		if(strcmp($attr->name,'mail') != 0){
			$htm = $htm."<th scope='row'>".$attr -> name."</th>";
		}
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
	$htm = emanGoiburua().$htm;
	return $htm;
}
function galdera_ezabatu($k,$i){
	$k -> query("Delete from questions where id = '".$i."' and mail = '".$_SESSION["eposta"]."'");
}
function erregistroa_eguneratu($k,$i,$g,$z,$e1,$e2,$e3,$zl,$ga){
	$sql = "update questions set galdera = '" . $g . "' and erantzun_zuzena = '" . $z . "' and erantzun_okerra_1 = '" . $e1 . "' and erantzun_okerra_2 = '" . $e2 . "' and erantzun_okerra_3 = '" . $e3 . "' and zailtasuna = " . $zl . " and gaia = '" . $ga . "' where id = " . $i;
	$k -> query($sql);

}
function amaitu($msg){
	echo($msg);
	exit();
}
function emanGoiburua(){
$goiburu = '
<style>
#msg{border:solid 2px #0055AA;background-color:#55AAFF;font-size:2em;display:none;padding:5px;position:absolute;}
table{margin:0px auto;border:1px solid #55A;}
th{padding:5px;color:#05A;background-color:#AAF;}
</style>
<script>
	function ezabatu(o){
		$("*").css({cursor:"wait"});
		$.ajax({
			type:"post",
			url:"handlingQuizes.php",
			data:{ezabatu:parseInt(o.id.substr(-3))},
			dataType:"text",
			success:function(em){
				$("*").css({cursor:"default"});
				if(em.indexOf("<table")>0){$("#taula").html(em);}
				else{
					//var msg = $("#msg");
					var msg = $(\'<hr/>\'+em+\'<hr/>\');
					$(body).add(msg);
					//msg.html(em);
					w = $(window);
					msg.css({border:\'5px outset #aab\',top:(w.height()-msg.height())/2,left:(w.width()-msg.width())/2});
					msg.show();
					setTimeout(function(){
						msg.fadeOut(
							1000,
							function(){
								$("*").css({cursor:"default"});
							}
						);
					},3000);
				}
			}
		});
	}
	function gorde(o){
		var ida = o.id.substr(-3);
		$("*").css({cursor:"wait"});
		var objs = $(\'[id$="\'+o.id.substr(-3)+\'"]\');
		var datoak = "gorde:\'1\',";
		objs.each(function(ix,obj){
			if(obj.value!=null){
				datoak += obj.id.substr(0,obj.id.length-3) + ":\'" + obj.value + "\',";
			}
		});
		datoak = datoak.substr(0,datoak.length-1);
		$.ajax({
			url:"handlingQuizes.php",
			type:"post",
			//data:{datoak},
			data:{gorde:"1",id:parseInt(ida),galdera:$("#galdera"+ida).val(),erantzun_zuzena:$("#zuzena"+ida).val(),erantzun_okerra_1:$("#erantzunokerra1"+ida).val(),erantzun_okerra_2:$("#erantzunokerra2"+ida).val(),erantzun_okerra_3:$("#erantzunokerra3"+ida).val(),zailtasuna:$("#zailtasuna"+ida).val(),gaia:$("#gaia"+ida).val()},
			//contentType: false,
			//cache: false,
			//processData: false,
			dataType:"html",
			success:function(em){
				if(em.indexOf("<table")>0){$("#taula").html(em);}
				else{
					var msg = $("#msg");
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
				var msg = $("#msg");
				msg.html(em);
				w = $(window);
				msg.css({top:(w.height()-msg.height())/2,left:(w.width()-msg.width())/2});
				msg.show();
				setTimeout(function(){
					msg.fadeOut(1000);
				},3000);
			},
			complete:function(){
				$("*").css({cursor:"default"});
			}
		});
	}
	</script>';
	return $goiburu;
}
?>
