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
if(isset($_POST['id'])){
	
	fwrite($f,"\npasahitzAldatu: datoak jaso dira | id: ".substr($i,2,strlen($i)));

	include('dbConfig.php');
	$konexioa = new mysqli($zerbitzaria,$erabiltzaile,$gakoa,$db);
	if($konexioa -> connect_error){
		fwrite($f,"\npasahitzAldatu: konexioa ezin izan da lortu");
		echo('ezin izan da pasahitza aldatu');
		exit();
	}
	$msg = datoak_aztertu($_POST['pasahitza0'],$_POST['pasahitza1'],$_POST['pasahitza2']);
	if($msg != ''){
		fwrite($f,"\npasahitzAldatu: ".$msg);
		echo($msg);
		exit();
	}
	$msg = pasahitza_gorde($konexioa,$_POST['pasahitza0'],$_POST['pasahitza1'],$_POST['id']);

	fwrite($f,"\npasahitzAldatu: ".$msg);

	echo($msg);
	exti();
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

</script>
<div id='aldatuPSW'>
	<span id='msg'></span><br/>
	<form id='pasahitzaF' name='pasahitzaF' enctype='multipart/form-data' method='post'>
		<fieldset style='text-align:center;'>
			<h3 style='text-align:center;'>Pasahitza Aldatu</h3>
			<p><label><?php $_SESSION['user']?></label></p>
			<p>
				<label><input type='hidden' id='id<?php echo($_SESSION['id']);?>' value='<?php echo($_SESSION['id']);?>'/></label>
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
<script>
var hasi = {
	onReady: function(e){
		$('#pasahitzaF').on('submit',function(e){
			$('body').css({cursor:'wait'});
			e.preventDefault();
			$('body').css({cursor:'wait'});
			$.post(
				'pasahitzAldatu.php',
				{id:$('#pasahitza0').attr('id').substr(2,$('#pasahitza0').attr('id').length),pasahitza0:$('#pasahitza0').attr('id')),pasahitza0:$('#pasahitza0').attr('id'),pasahitza0:$('#pasahitza0').attr('id')},
				//data:new FormData(this),
				function(em){
					$('body').css({cursor:'default'});
					console.log('ongi: '+em);
					m = $('#msg');
					m.html(em);
					w = $(window);
					m.css({top:(w.height()-m.height())/2,lef:(w.width()-m.width())/2}).show();
					setTimeout(function(){m.fadeOut();},5000);
				}
			);
		}
	}
/*
		$('#pasahitzaF').on('submit',function(e){
			$('body').css({cursor:'wait'});
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
					$('body').css({cursor:'default'});
					console.log('ongi: '+em);
					m = $('#msg');
					m.html(em);
					w = $(window);
					m.css({top:(w.height()-m.height())/2,lef:(w.width()-m.width())/2}).show();
					setTimeout(function(){m.fadeOut();},5000);
				},
				error: function(er){
					$('body').css({cursor:'default'});
					console.log('errorea: '+er);
					m = $('#msg');
					m.html(er);
					w = $(window);
					m.css({top:(w.height()-m.height())/2,lef:(w.width()-m.width())/2}).show();
					setTimeout(function(){m.fadeOut();},5000);
				}
			});
		});*/
};
$(document).ready(hasi.onReady);
</script>
<?php
function datoak_aztertu($p1,$p2,$p3){
	if(strcmp($p2,$p3) != 0){return 'pasahitz berriak ez dira berdinak';}
	if(strlen($p2) < 8){return 'pasahitz berria motzegia da';}
	if(strcmp($p1,$p2) == 0){return 'pasahitza zaharra eta berria berdinak dira';}
	return '';
}
function pasahitza_gorde($k,$p1,$p2,$i){
	$i = substr($i,2,strlen($i));
	$r = $k -> query("Select pasahitza from users where id = '" . $i . "'");
	$p0 = $r -> fetch_array(MYSQLI_ASSOC);

	if(strcmp($p1,$p0['pasahitza']) != 0){
		return 'pasahitz zaharra ez dago ongi';
	}
	$p = password_hash($p2, PASSWORD_DEFAULT);
	$msg = $konexioa -> query("update users set pasahitza = '" . $p . "' where id = '" . $i . "'");
	
}
?>