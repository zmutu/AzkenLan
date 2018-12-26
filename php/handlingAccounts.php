<?php session_start();

if(!isset($_SESSION['rola'])){header('location:layout.php');}

if(strcmp($_SESSION['rola'],'kudeatzaile') != 0){header('location:layout.php');}

include('dbConfig.php');
$konexioa = new mysqli($zerbitzaria,$erabiltzaile,$gakoa,$db);

if($konexioa -> connect_error){
	amaitu('errorea datu-basearekin konexioa ezartzean');
}
if(isset($_POST['permutatu'])){
	$sql = "update erabiltzaile set aktibo = abs(aktibo - 1) where id = '" . $_POST['id'] . "'";
	$konexioa -> query($sql);
	if($konexioa -> error){
		amaitu("errorea kontsulta egiterakoak\n" . $konexioa->error);
	}
}
if(isset($_POST['ezabatu'])){
	if(strcmp($_POST['id'],$_SESSION['id']) == 0){
		mezua('#Kautotuta dagoen kudeatzailea ezin duzu ezabatu<br/>Errorern bat dagoela uste baduzu<br/>
		jar zaitez harremanten interneteko kudeatzailearekin');
	}
	$sql = "Delete from erabiltzaile where id ='".$_POST['id']."'";
	$konexioa -> query($sql);
	if($konexioa -> error){
		amaitu("errorea erabiltzailearen ezabatzean<br/>".$konexioaa -> error);
	}
	else{
		sleep(3);
		$htm = taulaSortu($konexioa);
		$konexioa -> close();
		amaitu($htm);
	}
}
if(isset($_GET['zerrenda'])){
	$konexioa = new mysqli($zerbitzaria,$erabiltzaile,$gakoa,$db);
	if($konexioa -> connect_error){
		amaitu("errorea konexioa ezartzean:\n".$konexioa -> connect_error);
	}
	else{
		$htm = taulaSortu($konexioa);
		$konexioa -> close();
		amaitu($htm);
	}
}

?>
<style>
table{margin:0px auto;border:1px solid #55AA;}
th{padding:5px;color:#05A;background-color:#AAF;}
</style>
<script>
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
				$('body').css({cursor:'default'});
				if(em.substr(0,1)=='#'){
					$m = $('#msg');
					$m.html(em.substr(1));
					$w = $(window);
					$m.css({top:($w.height()-$m.height())/2,lef:($w.width()-$m.width())/2,align:'center'}).show();
					setTimeout(function(){$m.fadeOut();},5000);
				}
				else{
					$('#taula').html(em);
				}
			}
		});
	}
</script>
<div id='taula'><?php echo(taulaSortu($konexioa));?></div>
<?php
$konexioa -> close();

function taulaSortu($k){
	$sql = "Select id, aktibo, eposta, rola, argazkia, mota from erabiltzaile";

	$erabiltzaileak = $k -> query($sql);

	$htm = '<style>td{border-bottom:dotted 1px #5555AA;border-top:dotted 1px #5555AA;padding:0px 10px;}th{border:solid 1px #5555AA;}</style><table style="dotted:solid 1px #5555AA;"><tr><th scope=\'row\'>aktibo</th><th scope=\'row\'>eposta</th><th scope=\'row\'>rola</th><th scope=\'row\'>argazkia</th><th scope=\'row\'>ezabatu</th></tr>';

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
		$htm = $htm.'<tr><td>'.$aktibo.'</td><td>'.$lerro["eposta"].'</td><td>'.$lerro["rola"].'</td><td>'.$img.'</td><td><img onclick=\'ezabatu("'.$lerro['id'].'")\' src=\'../images/ez.png\'/></td></tr>';
		
	}
	$htm = $htm.'</table>';

	$erabiltzaileak -> free_result();
	return $htm;
}
function amaitu($msg){
	echo($msg);
	exit();
}
?>
