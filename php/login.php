<?php session_start();

isset($_POST['mail']) ? $mail = $_POST['mail'] : $mail = '';
isset($_POST['pasahitza']) ? $pasahitza = $_POST['pasahitza'] : $pasahitza = '';

if($mail=='' && $pasahitza==''){
	formularioa();
	exit();
}

$msg = datoak_aztertu($mail,$pasahitza);

if($msg != ''){
	amaitu($msg);
}

include('dbConfig.php');

$sql = "Select id, aktibo, rola, izena, eposta, pasahitza from users where eposta = '".$mail."'";

//konexioa egin
$konexioa = new mysqli($zerbitzaria,$erabiltzaile,$gakoa,$db);
if($konexioa -> connect_error){
	amaitu('Errorea datu-basearekin konexua ezartzerakoan\n>'.$konexioa -> connect_error);
}
else{
	//kontsulta exekutatu
	$user = $konexioa -> query($sql);
	if($konexioa -> error){
		amaitu('Errorea datu-baseari kontsulta egiterakoan\n'.$konexioa -> error);
	}
	else{
		//pasahitza aztertu 'password_verify'
		$usr_lerro = $user -> fetch_array(MYSQLI_ASSOC);
		if($konexioa -> error){
			amaitu('datu-baseari atzitzean errore bat gertatu da');
		}
		else{
			if(!password_verify($pasahitza,$usr_lerro['pasahitza'])){
				amaitu('pasahitza edo erabiltzailea ez da zuzena');
			}
			//erabiltzailea eta pasahitza ongi daude
			if($usr_lerro['aktibo'] == 0){//erabiltzailea blokeatua dago
				amaitu('Erabiltzaile hori blokeatuta dago');
			}
			$_SESSION['rola'] = $usr_lerro['rola'];
			$_SESSION['user'] = $usr_lerro['izena'];
			$_SESSION['id'] = $usr_lerro['id'];
			$_SESSION['eposta'] = $usr_lerro['eposta'];

			//kautotu bada, erabiltzailearen rola itzuli
			amaitu('#'.$_SESSION['user']);
		}
	}
}

function datoak_aztertu($m,$p){
	//mail aztertu
	$exp_reg = '/\w\w[a-z]*(\.)?\w\w[a-z]*(\d\d\d)?@(ikasle\.)?ehu\.eus$/';
	if(!preg_match ($exp_reg, $m)){return 'mail ez da zuzena';}
	
	//pasahitza gitxienen 8ko luzera
	if(strlen($p) < 8){return 'pasahitzaren luzera ez da egokia';}

	return '';
}
function formularioa(){
	echo("

<style>
#logF input:invalid{border:1px solid red;}
#msg{border:6px outset #05a;background-color:#5af;color:#000;font-size:2em;display:none;padding:10px;position:absolute;border-radius:10px;-moz-border-radius:10px;-webkit-border-radius:10px;}
#form{width:300px;border:5px outset #555555;margin:0px auto;}
p{padding:5px;}
</style>
<script>
\$(document).ready(function(e){
	\$('#logF').on('submit',function(e){
		e.preventDefault();
		\$('*').css({cursor:'wait'});
		\$.ajax({
			type: 'POST',
			url: 'login.php',
			data: new FormData(this),
			contentType: false,
			cache: false,
			processData: false,
			dataType: 'text',
			success: function(em){
				var msg = \$('#msg');
				if(em.substr(0,1)=='#'){
					e = 'ongi etorri<br/> ' + em.substr(1);
				}
				else{e = em;}
				msg.html(e);
				w = \$(window);
				msg.css({top:(w.height()-msg.height())/2,left:(w.width()-msg.width())/2});
				msg.show();
				setTimeout(
					function(){
						msg.fadeOut(1000);
					},
					2000
				);
				if(em.substr(0,1)=='#'){
					setTimeout(
						function(){
							window.location.href='layout.php';
						},
						2500
					);
				}
			},
			error: function(er){
				var msg = \$('#msg');
				msg.html(er);
				w = \$(window);
				msg.css({top:(w.height()-msg.height())/2,left:(w.width()-msg.width())/2}).show;
				setTimeout(function(){msg.fadeOut();},5000);
			},
			complete: function(em){
				setTimeout(function(){\$('*').css({cursor:'default'});},2400);
			}
		});
	});
});
</script>
<div id='form'>
	<span id='msg'></span>
	<form id='logF' name='logF' onreset='garbitu()'>
		<fieldset style='text-align:center;'>
			<h3>LOG IN</h3>
<span><label>Mail*: <INPUT TYPE='mail' NAME='mail' id='mail' pattern='\w\w[a-z]*(\.)?\w\w[a-z]*(\d\d\d)?@(ikasle\.)?ehu\.eus$' value='' required></label></span><br/><br/>
			<span><label>Pasahitza*: <INPUT TYPE='password' NAME='pasahitza' id='pasahitza' size='20' minlength='8' value='' required></label></span><br/>
			<br/>
			<hr/>
			<p><button type='submit' value='bidali'>Bidali</button></p>
		</fieldset>
	</form>
</div>");
}
function amaitu($msg){
	echo($msg);
	exit();
}
