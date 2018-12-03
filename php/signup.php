<?php session_start();
$izena = '';
$pasahitza1 = '';
$pasahitza2 = '';
$img = '';
$img_tp = '-';

if(isset($_POST['mail'])){$mail = $_POST['mail'];}
if(isset($_POST['izena'])){$izena = $_POST['izena'];}

if(isset($_POST['pasahitza1'])){$pasahitza1 = $_POST['pasahitza1'];}
if(isset($_POST['pasahitza2'])){$pasahitza2 = $_POST['pasahitza2'];}
if(isset($_FILES['html_file']) && $_FILES['html_file']['error'] == 0){
	$img = $_FILES['html_file']['tmp_name'];	//fitxategiaren edukia
	$img_nm = $_FILES['html_file']['name'];		//fitxategiaren izena
	$img_tp = $_FILES['html_file']['type'];		//fitxategi mota
	$neurri = $_FILES['html_file']['size'];		//fitxategiaren neurriak
}

if(isset($mail)){
	$f = fopen('fitx.txt','a+');
	$m="\nlanean...";
	fwrite($f,$m);
	fwrite($f,"\r\nbigarren lerroa\r\n".$mail."\r\n".$izena);
	fclose($f);

	//mail jaso bada, datuak aztertu
	$msg = datoak_aztertu($mail,$izena,$pasahitza1,$pasahitza2);
	$pasahitz = password_hash($pasahitza1, PASSWORD_DEFAULT);
	
	if($msg != ''){echo($msg);}
	else{
		//datoak jaso da eta egokiak dire
		include('dbConfig.php');

		$max_kb = 1048576; //1Mb-eko argazkia gehienez
		$imgs = array("image/jpg", "image/jpeg", "image/gif", "image/png", "image/bmp");

		if(!$konexioa = new mysqli($zerbitzaria,$erabiltzaile,$gakoa,$db)){
			echo('Datu-basearekin konexioa ez da lortu');
			exit();
		}
		if(is_uploaded_file($img)){
			if(in_array($img_tp,$imgs) && $neurri < $max_kb){
				$img = $konexioa -> real_escape_string(file_get_contents($img));
			}
			else{
				$img = '';
				$img_tp = '-';
			}
		}
		$sql = "Insert into users(aktibo,rola,eposta,izena,pasahitza,argazkia,mota) values(1,'ikasle','".$mail."','".$izena."','".$pasahitz."','".$img."','".$img_tp."')";
		//$sql = "Insert into users(aktibo,rola,eposta,izena,pasahitza,argazkia,mota) values(1,'ikasle','".$mail."','".$izena."','".password_hash($pasahitza1, PASSWORD_DEFAULT)."','".$img."','".$img_tp."')";

	$f = fopen('fitx.txt','a+');
	$m="\nlanean...";
	fwrite($f,$m);
	fwrite($f,"\n-> Bigarren lerroa\r\n".$sql);
	fclose($f);

		if(!$konexioa -> query($sql)){
			echo('Errorea datu-basean datuak sartzean');
		}
		else{
			sleep(2);
			echo('Erabiltzailea sortu da');
		}
	}
}
else{
	//ez da datorik jaso
?>
<style>
input{margin:2px;}
#galderenF input:invalid{border:1px solid red;}
#msg{border:solid 2px #0055AA;background-color:#0055FF;font-size:2em;display:none;padding:5px;position:absolute;}
#signup{width:350px;margin:5px auto;border:5px outset #555555;}
</style>
<script>
function fitxategi(){
	var img = $('#img')[0].files[0];
	$('#neurri').text('fitxategiaren neurria: '+img.size+' byte');
	if(img){
		var FR = new FileReader();
		FR.readAsDataURL(img);
		FR.onload = function(e){
			m = $('#marrazkia');
			m.attr('src',e.target.result);
			m.css('width',75);
		}
	}
}
function garbitu(){
	$('#marrazkia').attr('src','');
	$('#neurri').html('');
}
var hasi = {
	onReady: function(e){
		$('body').css({cursor:'progress'});
		$('#galderenF').on('submit',function(e){
			e.preventDefault();
			$.ajax({
				type: 'POST',
				url: 'signup.php',
				data: new FormData(this),
				contentType: false,
				cache: false,
				processData: false,
				dataType: 'text',
				success: function(em){
					$('body').css({cursor:'default'});
					m = $('#msg');
					m.html(em);
					w = $(window);
					m.css({top:(w.height()-m.height())/2,lef:(w.width()-m.width())/2}).show();
					setTimeout(function(){m.fadeOut();},5000);
				},
				error: function(er){
					$('body').css({cursor:'default'});
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
<div id='signup'>
	<span id='msg'></span><br/>
	<form id='galderenF' name='galderenF' enctype='multipart/form-data' onreset='garbitu()' method='post'>
		<fieldset style='text-align:center;'>
			<h3 style='text-align:center;'>SIGN UP</h3>
			<p><label>Mail*: <INPUT TYPE='mail' NAME='mail' id='mail' pattern='\w\w[a-z]*\d\d\d@ikasle\.ehu\.eus' value='' required></label></p>
			<p>
				<label>Izena*: <INPUT TYPE='text' NAME='izena' id='izena' value='' required></label><br/>
				<label>Pasahitza*: <INPUT TYPE='password' NAME='pasahitza1' id='pasahitza1' minlength='8' size='20' value='' required></label><br/>
				<label>Pasahitza*: <INPUT TYPE='password' NAME='pasahitza2' id='pasahitza2' minlength='8' size='20' value='' required></label><br/>
			</p>
			<p>
				<label>Argazkia: <INPUT TYPE='file' NAME='html_file' ACCEPT='text/html' id='img' onchange='fitxategi()'></label><br/>
				<img src='' id='marrazkia'/>
			</p>
			<span>
				<button type='reset' value='garbitu'>Garbitu</button>
				<button type='submit' value='bidali'>Bidali</button>
			</span><br/>
		</fieldset>
	</form>
</div>

<?php
}
function datoak_aztertu($m,$i,$p1,$p2){
	//mail aztertu
	$exp_reg = '/\w\w[a-z]*\d\d\d@ikasle\.ehu\.eus$/';
	if(!preg_match ($exp_reg, $m)){return 'mail ez da zuzena';}
	
	//izena (bi hitz eta hizki larriz hasten direnak)
	$exp_reg = '/[A-Z]\w+\s[A-Z]\w+/';
	if(!preg_match ($exp_reg, $i)){return 'izena ez dago ongi eraikia';}
	
	//pasahitza gutxienez 8ko luzera
	if($p1 != $p2 || strlen($p1) < 8){return 'pasahitzak desberdinak dira';}

	return '';
}
?>