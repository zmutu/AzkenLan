<?php session_start();
if(isset($_SESSION['rola'])){
	echo('larehundalau errorea');
	exit();
}
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
if(isset($_POST['segurtasuna'])){
	//pasahtiza segurua den aztertu (ez du funtzionatzen)
	$pswrd = $_POST['segurtasuna'];
	require_once("../nuSOAP/nusoap.php");
	$param = array('pasahitza' => $pasahitza1,'ticket' => '1010');
	$pswdSeguruaDa = new soapclient('https://zmutu.000webhostapp.com/Web_Sistemak/AzkLan/php/egiaztatuPasahitza.php',true);
	$seguruaDa = $pswdSeguruaDa -> call('egokiaDa', $param);
	amaitu($seguruaDa);
}
if(isset($mail)){
	if(isset($_POST['matrikula'])){
		//matrikulatua dagoen aztertu
		require_once('../nuSOAP/nusoap.php');
		$param = array('x' => $mail);
		$mailMatrikulatutaDago = new nusoap_client('http://ehusw.es/rosa/webZerbitzuak/egiaztatuMatrikula.php?wsdl', true);
		$matrikulatutaDago = $mailMatrikulatutaDago -> call('egiaztatuE',$param);
		amaitu($matrikulatutaDago);


	}
	//mail jaso bada, datuak aztertu
	$msg = datoak_aztertu($mail,$izena,$pasahitza1,$pasahitza2);
	//$pasahitz = password_hash($pasahitza1, PASSWORD_DEFAULT);
	if($msg != ''){
		amaitu($msg);
	}
	else{
		//datoak jaso da eta egokiak dire
		include('dbConfig.php');

		$max_kb = 1048576; //1Mb-eko argazkia gehienez
		$imgs = array("image/jpg", "image/jpeg", "image/gif", "image/png", "image/bmp");

		$konexioa = new mysqli($zerbitzaria,$erabiltzaile,$gakoa,$db);
		if($konexioa -> connect_error){
			amaitu('Datu-basearekin konexioa ez da lortu<br/>'.$konexioa -> connect_error);
		}
		$erabiltzaile = $konexioa -> query("Select id from users where eposta = '" . $mail . "'");
		$id = $erabiltzaile -> fetch_array(MYSQLI_ASSOC);
		if($id['id']){
			amaitu('mail hori erregistratuta dago');
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
		$pasahitza = password_hash($pasahitza1,PASSWORD_DEFAULT);
		$sql = "Insert into users(aktibo,rola,eposta,izena,pasahitza,argazkia,mota) values(1,'ikasle','".$mail."','".$izena."','".$pasahitza."','".$img."','".$img_tp."')";
		$konexioa -> query($sql);
		if($konexioa -> error){
			amaitu('Errorea datu-basean datuak sartzean<br>'.$konexioa -> error);
		}
		else{
			amaitu('Erabiltzailea sortu da');
		}
	}
}
else{
	//ez da datorik jaso
?>
<div id='edukia'>
<style>
input{margin:2px;}
#galderenF input:invalid{border:1px solid red;}
#msg{border:6px outset #0055AA;background-color:#0055FF;color:#000;font-size:2em;display:none;padding:10px;position:absolute;}
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
function mezua(msg){
	m = $('#msg');
	$('body').append(m);
	m.html(msg);
	w = $(window);
	m.css({top:(w.height()-m.height())/2,left:(w.width()-m.width())/2,align:'center'}).show();
	setTimeout(function(){m.fadeOut();},2000);
}
var hasi = {
	onReady: function(e){
		$('#galderenF').on('submit',function(e){
			$('*').css({cursor:'wait'});
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
					m = $('#msg');
					m.html(em);
					w = $(window);
					m.css({top:(w.height()-m.height())/2,left:(w.width()-m.width())/2}).show();
					setTimeout(function(){m.fadeOut();},2000);
					if(em == 'Erabiltzailea sortu da'){setTimeout(function(){$('#edukia').parent().html('');},2000);}
				},
				error: function(er){
					$('*').css({cursor:'default'});
					m = $('#msg');
					m.html(er);
					w = $(window);
					m.css({top:(w.height()-m.height())/2,left:(w.width()-m.width())/2}).show();
					setTimeout(function(){m.fadeOut();},2000);
				},
				complete: function(){setTimeout(function(){$('*').css({cursor:'default'});},2000)}
			});
			
		});
		$('#mail').focusout(function(){
			var expReg = new RegExp(/\w\w[a-z]*(\.)?\w\w[a-z]*(\d\d\d)?@(ikasle\.)?ehu\.eus$/);
			if(expReg.test(this.value)){
				$.ajax({
					url: 'signup.php',
					type: 'post',
					data: {'mail':$(this).val(),'matrikula':'1'},
					dataType: 'text',
					success: function(em){
						if(em == 'BAI'){
							$('#bidali').prop('disabled',false);
						}
						else{
							mezua('mail hori ez dago matrikulatuta');
							$('#bidali').prop('disabled',true); 
						}
					},
					error: function(er){
						mezua('mail hori matrikulatuta dagoen aztertzean<br/>errore bat gertatu da<br/>ezingo duzu alta eman');
					}
				});
			}
		});
		$('#pasahitza1').focusout(function(){
			$.ajax({
				url:'signup.php',
				type:'post',
				data:{pasahitza:$(this).val(),segurtasuna:1},
				dataType:'text',
				success:function(em){console.log('dasen uork');}
			});
		});
		$('#mail2').focusout(function(){
			$.post('signup.php',{'mail':$(this).val(),'matrikula':1},function(em){if(em=='BAI'){$('#bidali').prop("disabled",false);}});
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
			<p><label>Mail*: <INPUT TYPE='mail' NAME='mail' id='mail' pattern='\w\w[a-z]*(\.)?\w\w[a-z]*(\d\d\d)?@(ikasle\.)?ehu\.eus$' value='' required></label></p>
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
				<button type='submit' id='bidali' value='bidali' disabled>Bidali</button>
			</span><br/>
		</fieldset>
	</form>
</div>
</div>
<?php
}
function datoak_aztertu($m,$i,$p1,$p2){
	//mail aztertu
	$exp_reg = '/\w\w[a-z]*(\.)?\w\w[a-z]*(\d\d\d)?@(ikasle\.)?ehu\.eus$/';
	if(!preg_match ($exp_reg, $m)){return 'mail ez da zuzena';}
	
	//izena (bi hitz eta hizki larriz hasten direnak)
	$exp_reg = '/[A-Z]\w+\s[A-Z]\w+/';
	if(!preg_match ($exp_reg, $i)){return 'izena ez dago ongi eraikia';}
	
	//pasahitza gutxienez 8ko luzera
	if($p1 != $p2 || strlen($p1) < 8){return 'pasahitzak desberdinak dira';}

	return '';
}
function amaitu($msg){
	echo($msg);
	exit();
}
?>
