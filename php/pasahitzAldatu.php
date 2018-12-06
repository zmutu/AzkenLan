<?php session_start();

$f = fopen('fitx.txt','a+');

if(isset($_SESSION['rola'])){
	if(strcmp($_SESSION['rola'],'kudeatzaile') != 0 && strcmp($_SESSION['rola'],'ikasle') != 0){
		fwrite($f,"\npasahitzAldatu: erabiltzaile mota ez egokia");
		header('location:layout.htm');
	}
	fwrite($f,"\npasahitzAldatu: saioa hasi da");
}
else{
	fwrite($f,"\npasahitzAldatu: saioa hasi gabe");
	header('location:../layout.htm');
}
if(isset($_POST['pasahitza0'])){

	fwrite($f,"\npasahitzAldatu(18): datoak jaso dira | id: ".$_SESSION['id']);

	include('dbConfig.php');

	$konexioa = new mysqli($zerbitzaria,$erabiltzaile,$gakoa,$db);

	if($konexioa -> connect_error){
		fwrite($f,"\npasahitzAldatu(25): konexioa ezin izan da lortu");
		echo('ezin izan da pasahitza aldatu');
		exit();
	}
	$msg = datoak_aztertu($_POST['pasahitza0'],$_POST['pasahitza1'],$_POST['pasahitza2']);
	if($msg != ''){
		fwrite($f,"\npasahitzAldatu(31): ".$msg);
		echo($msg);
		exit();
	}
	$msg = pasahitza_gorde($konexioa,$_POST['pasahitza0'],$_POST['pasahitza1'],$_SESSION['id']);

	fwrite($f,"\npasahitzAldatu(37): ".$msg);

	echo($msg);
	exit();
}
else{
	fwrite($f,"\nez da POST datorik jaso");
}
?>
<style>
#pasahitza{width:300px,margin:0px auto;}
input{margin:1px;}
#logF input:invalid{border:1px solid red;}
</style>
<script>
var hasi = {
	onReady: function(e){
		$('#pasahitzaF').on('submit',function(e){
			$('*').css({cursor:'wait'});
			e.preventDefault();
			$.ajax({
				type: 'POST',
				url: 'pasahitzAldatu.php',
				data: new FormData(this),
				contentType: false,
				cache: false,
				processData: false,
				dataType: 'text',
				success: function(em){
					$('*').css({cursor:'default'});
					console.log('ongi: '+em);
					m = $('#msg');
					m.html(em);
					w = $(window);
					m.css({top:(w.height()-m.height())/2,lef:(w.width()-m.width())/2}).show();
					setTimeout(function(){m.fadeOut();},5000);
				},
				error: function(er){
					$('*').css({cursor:'default'});
					console.log('errorea: '+er);
					m = $('#msg');
					m.html(er);
					w = $(window);
					m.css({top:(w.height()-m.height())/2,lef:(w.width()-m.width())/2}).show();
					setTimeout(function(){m.fadeOut();},5000);
				}
			});
		});
	}
};
$(document).ready(hasi.onReady);
</script>
<div id='aldatuPSW'>
	<span id='msg'></span><br/>
	<form id='pasahitzaF' name='pasahitzaF' enctype='multipart/form-data' method='post'>
		<fieldset style='text-align:center;'>
			<h3 style='text-align:center;'>Pasahitza Aldatu</h3>
			<p><label><?php $_SESSION['user']?></label></p>
			<p>
				<label>Pasahitza zaharra*: <INPUT TYPE='password' NAME='pasahitza0' id='pasahitza0' minlength='8' size='20' required></label><br/>
				<label>Pasahitza berria*: <INPUT TYPE='password' NAME='pasahitza1' id='pasahitza1' minlength='8' size='20' required></label><br/>
				<label>Pasahitza berria*: <INPUT TYPE='password' NAME='pasahitza2' id='pasahitza2' minlength='8' size='20' required></label><br/>
			</p>
			<span>
				<button type='reset' value='garbitu'>Garbitu</button>
				<button type='submit' value='bidali'>Bidali</button>
			</span><br/>
		</fieldset>
	</form>
</div>
<?php
function datoak_aztertu($p1,$p2,$p3){
	if(strcmp($p2,$p3) != 0){return 'pasahitz berriak ez dira berdinak';}
	if(strlen($p2) < 8){return 'pasahitz berria motzegia da';}
	if(strcmp($p1,$p2) == 0){return 'pasahitza zaharra eta berria berdinak dira';}
	return '';
}
function pasahitza_gorde($k,$p1,$p2,$i){

	$r = $k -> query("Select pasahitza from users where id = '" . $i . "'");
	$p0 = $r -> fetch_array(MYSQLI_ASSOC);

	if(strcmp($p1,$p0['pasahitza']) != 0){
		return 'pasahitz zaharra ez dago ongi<br/>'.$p1.' /= '.$p0['pasahitza'].'<br/>Select pasahitza from users where id = "'.$i.'"';
	}

	$msg = $k -> query("update users set pasahitza = '" . $p2 . "' where id = '" . $i . "'");
	sleep(2);
	if($msg!=0){
		return('pasahitza gorde da');
	}
	else{
		return 'pasahitza gordetzean erroreren bat gertatu da';
	}
}
?>
