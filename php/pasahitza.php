<?php session_start();

if(isset($_POST['pasahitza0'])){$pasahitza0 = $_POST['pasahitza0'];}
if(isset($_POST['pasahitza1'])){$pasahitza1 = $_POST['pasahitza1'];}
if(isset($_POST['pasahitza2'])){$pasahitza2 = $_POST['pasahitza2'];}
if(isset($_POST['mail1'])){$mail = $_POST['mail1'];}
if(isset($_POST['mail2'])){$mail2 = $_POST['mail2'];}
if(isset($_POST['mailId'])){$userId = $_POST['mailId'];}

if(isset($_POST['matrikula'])){
	//ikaslea matrikulatuta dagoen aztertu
	amaitu(matrikulatua($_POST['mail1']));
}
if(isset($pasahitza0)){
	//pasahitz bat aldatu nahi da
	$msg = datoak_aztertu($mail,$pasahitza0,$pasahitza1,$pasahitza2);
	if(strlen($msg)>0){
		amaitu($msg);
	}
	//hemen datoak ongi daude
	include('dbconfig.php');
	$konexioa = new mysqli($zerbitzaria,$erabiltzaile,$gakoa,$db);
	if($konexioa -> connect_error){
		amaitu('errorea datu-basearekin konektatzean');
	}
	$sql = 'select pasahitza from users where eposta = "' . $mail . '"';
	$pswrd = $konexioa -> query($sql);
	if($konexioa -> error){
		amaitu('errorea datu-basea atzitzean');
	}
	$pasahitza = $pswrd -> fetch_array(MYSQLI_ASSOC);
	if(!password_verify($pasahitza0,$pasahitza['pasahitza'])){
		amaitu('pasahitza zaharra ez da zuzena');
	}
	$p3 = password_hash($pasahitza1,PASSWORD_DEFAULT);

	$sql = 'update users set pasahitza = "' . $p3 . '" where eposta = "' . $mail .'"';
	$konexioa -> query($sql);
	if($konexioa -> error){
		amaitu('errorea pasahitza berria gordetzean');
	}
	//honara iritsi bada, pasahitz berria gorde du
	amaitu('# pasahitz berria gorde da #');
}
if(isset($mail2)){
	//mail berreskuratzeko eskaera bat jaso da
	$mtrkl = matrikulatua($mail2);
	if(strcmp($mtrkl,'BAI') != 0){
		amaitu('mail hori ez dago matrikulatuta edo ez dago sisteman gordeta');
	}
	include('dbconfig.php');
	$konexioa = new mysqli($zerbitzaria,$erabiltzaile,$gakoa,$db);
	if($konexioa -> connect_error){
		amaitu('ezin izan da zure eskaera gauzatu');
	}
	$mailId = eman_id($konexioa,$mail2);
	if(eskaera_aktibo_du($konexioa,$mailId)){
		amaitu('dirudi mail horrek pasahitza aldaketa bat aktibatuta duela. begiratu mail hontzia.<br/>azkeneko 24 orduetan ez baduzu honelako <br/>eskaerarik egin, saiatu berriz');
	}
	$kode = strina_sortu(40);
	$data = time() + 86400;
	
	$str = sortu_hash($konexioa,$mailId,$kode);
	if(strlen($str)==0){
		amaitu('errore bat gertatu da zure eskaera gauzatzean<br/>saiatu berriro beranduago');
	}
	echo($str);
	$konexioa -> query("insert into pasahitz(mailId,kode,muga,aktibo) values('" . $mailId . "','" . $kode . "','" . $data . "'," . true .")");
	if($konexioa -> error){
		amaitu('eskaera gauzatzean erroreren bat gertatu da<br/>saiatu berriro beranduago');
	}
	$ok = mail($mail2,"Pasahitza Berrabiarazi","Pasahitza berrabiarazteko ondorengo esteka aukeratu:\n\nhttps://zmutu.000webhostapp.com/Web_Sistemak/AzkLan/php/pasahitza.php?jatorri=" . $str . "\n\n24 orduko epea du esteka honek baliagarria izateko\n\nBaliteke pasahitza berabiarazteko zuk eskatua ez izatea.. BLA BLA BLA...\n\nmezu hau inprimatu aurretik BLA BLA BLA...");

	if(!$ok){
		amaitu('errore bat gertatu da zuere eskaera gauzatzean<br/>saiatu berriro beranduago');
	}
	amaitu('laister mezu bat jasoko duzu<br/>jarraitu mezuaren esanak');
	
}
if(isset($_GET['jatorri'])){
	//pasahitza berrabiarazteko eskaera bat gauzatu behar da
	$str = $_GET['jatorri'];
	include('dbconfig.php');
	$konexioa = new mysqli($zerbitzaria,$erabiltzaile,$gakoa,$db);
	if($konexioa -> connect_error){
		amaitu('ezin da pasahitza berrabiarazi<br/>erabil ezazu berriz zure mailean duzu esteka');
	}
	$id = emanId($konexioa,$str);

	
	$iraungitu = eskaera_aktibo_du($konexioa,$id);
	if($iraungitu){
		berrabiarazi($id);
		exit();
	}
	else{
		amaitu('aktibazio saio hau iraungituta dago.<br/>behar baduzu sortu beste eskaera bat<br/><br/><a href="https://zmutu.000webhostapp.com/Web_Sistemak/AzkLan/php/layout.php"> hemen </a>');
	}
	amaitu('une honetan ezin da pasahitza berrabiarazi<br/>barkatu eragozpenak<br/>saiatu aurrerago');
}
if(isset($userId)){
	//pasahitza berreskuratu behar da
	$p1 = $_POST['pasahitza3'];
	$p2 = $_POST['pasahitza4'];
	if(strcmp($p1,$p2) != 0){
		amaitu('bi pasahitzak ez dira berdinak');
	}
	if(strlen($p1)<8){
		amaitu('pasahitzaren luzera ez da egokia');
	}
	include('dbconfig.php');
	$konexioa = new mysqli($zerbitzaria,$erabiltzaile,$gakoa,$db);
	if($konexioa -> connect_error){
		amaitu('ezin da pasahitza berria gorde<br/>erabil ezazu berriz zure mailean duzu esteka');
	}
	$p0 = password_hash($p1,PASSWORD_DEFAULT);
	$konexioa -> query("update users set pasahitza = '" . $p0 . "' where id = '" . $userId . "'");
	if($konexioa -> error){
		amaitu('ezin da pasahitza berria gorde<br/>erabil ezazu berriz zure mailean duzu esteka');
	}
	//aktibo dagoen eskaera aldatu
	$konexioa -> query("update pasahitz set aktibo = false where mailId = " . $userId);
	$konexioa -> close();
	amaitu('# pasahitz berria gorde da #');
}
?>
<style>
form{border:1px solid #338;background-color:#dde;padding:15px;}
input{margin:2px;}
button{padding:5px;}
</style>
<?php if(isset($_GET['berreskuratu'])){?>
<script>
var hasi = {
	onReady: function(e){
		$('#berriaEskatu').on(
			'submit',
			function(e){
				$('*').css({cursor:'wait'});
				e.preventDefault();
				$.ajax({
					type:'post',
					url:'pasahitza.php',
					data:{mail2:$('#mail2').val()},
					dataType:'text',
					success:function(em){
						var e = em.substr(0,1)=='#'?'#':'';
						m = $('<span>'+em+'</span>');
						$('body').append(m);
						$W = $(window);
						W = $W.width();
						H = $W.height();
						w = m.width();
						h = m.height()
						m.css({position:'fixed','background-color':'#aac','font-size':'1.7em',padding:'25px',border:'6px outset #aaf',top:((H-h)/2),left:((W-w)/2),'text-align':'center'});
						if(e == '#'){
							setTimeout(
								function(){
									$('#pswrd').parent().html('');
								},
								8000
							);
						}
						setTimeout(
							function(){
								$('*').css({cursor:'default'});
								m.fadeOut(500);
							},
							8000
						);
					}
				});
			}
		);
		$('#aldatu').on(
			'click',
			function(e){
				$.ajax({
					type:'get',
					url:'pasahitza.php',
					dataType:'text',
					success:function(em){
						$('#pswrd').parent().html(em);
					}
				});
			}
		);
	}
};
$(document).ready(hasi.onReady);
</script>
<span style='float:right;background-color:#dde;border-color:#338;border-top:1px solid #338;border-left:1px solid #338;border-right:1px solid #338'>Berreskuratu</span>
<span style='float:right' id='aldatu'>Aldatu</span>
<div id='pswrd'>
	<h3 style='margin-bottom:2px'>Pashitza berreskuratu</h3>
	<form id='berriaEskatu' name='berriaEskatu' enctype='multipart/form-data' method='post'>
		<label>Mail*: <INPUT TYPE='mail' NAME='mail2' id='mail2' pattern='\w\w[a-z]*\d\d\d@(ikasle\.)?ehu\.eus' value='' required></label>
		<button type='submit' id='bidali' value='bidali'>Bidali</button>
	</form>
</div>
<?php
}
else{?>
<script>
var hasi = {
	onReady: function(e){
		$('#mail1').focusout(
			function(){
				var expReg = new RegExp(/\w\w[a-z]*\d\d\d@(ikasle\.)?ehu\.eus$/);
				if(expReg.test(this.value)){
					$.ajax({
						type:'post',
						url:'pasahitza.php',
						data:{mail1:this.value,matrikula:1},
						dataType:'text',
						success:function(em){
							if(em == 'BAI'){
								$('#bidali').prop('disabled',false);
							}
							else{
								m = $('<span>mail hori ez dago matrikulatuta<br/> edo ez dago erregistratua</span>');
								$('body').append(m);
								$W = $(window);
								W = $W.width();
								H = $W.height();
								w = m.width();
								h = m.height();
								m.css({position:'fixed','background-color':'#aac','font-size':'1.7em',padding:'25px',border:'6px outset #aaf',top:((H-h)/2),left:((W-w)/2)});
								setTimeout(
									function(){
										m.remove();
									},
									3000
								);
							}
						}
					});
				}
			}
		);
		$('#aldatu').on(
			'submit',
			function(e){
				$('*').css({cursor:'wait'});
				e.preventDefault();
				$.ajax({
					type:'post',
					url:'pasahitza.php',
					data:{mail1:$('#mail1').val(),pasahitza0:$('#pasahitza0').val(),pasahitza1:$('#pasahitza1').val(),pasahitza2:$('#pasahitza2').val()},
					dataType:'text',
					success:function(em){
						var e = em.substr(0,1)=='#'?'#':'';
						m = $('<span>'+em+'</span>');
						$('body').append(m);
						$W = $(window);
						W = $W.width();
						H = $W.height();
						w = m.width();
						h = m.height();
						m.css({position:'fixed','background-color':'#aac','font-size':'1.7em',padding:'25px',border:'6px outset #aaf',top:((H-h)/2),left:((W-w)/2)});
						if(e == '#'){
							setTimeout(
								function(){
									$('#pswrd').parent().html('');
								},
								3000
							);
						}
						setTimeout(
							function(){
								$('*').css({cursor:'default'});
								m.fadeOut(500);
							},
							3000
						);
					}
				});
			}
		);
		$('#berreskuratu').on(
			'click',
			function(e){
				$.ajax({
					type:'get',
					url:'pasahitza.php',
					data:{berreskuratu:1},
					dataType:'text',
					success:function(em){
						$('#pswrd').parent().html(em);
					}
				});
			}
		);
	}
};
$(document).ready(hasi.onReady);
</script>
<div id='pswrd'>
<span style='float:right' id='berreskuratu'>Berreskuratu</span>
<span style='float:right;background-color:#dde;border-color:#338;border-top:1px solid #338;border-left:1px solid #338;border-right:1px solid #338'>Aldatu</span>
<h3 style='margin-bottom:2px'>Pasahitza aldatu</h3>
<form id='aldatu' name='aldatu' enctype='multipart/form-data' method='post'>
	<frameset>
		<label>Mail*: <INPUT TYPE='mail' NAME='mail1' id='mail1' pattern='\w\w[a-z]*\d\d\d@(ikasle\.)?ehu\.eus' value='' required /></label><br/>
		<label>Pasahitza Zaharra*: <INPUT TYPE='password' NAME='pasahitza0' id='pasahitza0' minlength='8' size='20' value='' required/></label><br/>
		<label>Pasahitza Berria*: <INPUT TYPE='password' NAME='pasahitza1' id='pasahitza1' minlength='8' size='20' value='' required/></label><br/>
		<label>Pasahitza Berria*: <INPUT TYPE='password' NAME='pasahitza2' id='pasahitza2' minlength='8' size='20' value='' required/></label><br/>
	</frameset>
	<button type='reset' value='garbitu'>Garbitu</button>
	<button type='submit' id='bidali' value='bidali' disabled>Bidali</button>
</form>
</div>
<?php
}
function datoak_aztertu($m,$p0,$p1,$p2){
	//mail, pasahitz zaharra, pasahitz berriak ongi eraikituta dauden aztertzen du
	//mail zaharra, bi mail berriekiko desberina den
	//bi mail berriak berdinak diren

	//mail aztertu
	$exp_reg = '/\w\w[a-z]*\d\d\d@(ikasle\.)?ehu\.eus$/';
	if(!preg_match ($exp_reg, $m)){return 'mail ez da zuzena';}
	
	//pasahitzak desberdinak dira
	if($p1 != $p2){return 'pasahitz berriak desberdinak dira';}
	
	//pasahitz luzera
	if(strlen($p1) < 8){return 'pasahitzaren luzera motzegia da';}

	//pasahitza zaharra eta berriak desberdinak
	if(strcmp($p0,$p1) == 0){return 'pasahitza zaharra eta berriak berdinak dira';}

	return '';
}
function matrikulatua($ml){
	//mail matrikulatuta dagoen aztertzen du
	//mail datu-basean gordeta dagoen aztertzen du.
	//bi baldintzak ez baditu betetzen false itzulko 'EZ' itzuliko du
	$i = 0;
	require_once('../nuSOAP/nusoap.php');	
	$param = array('x' => $ml);
	$mailMatrikulatutaDago = new nusoap_client('http://ehusw.es/rosa/webZerbitzuak/egiaztatuMatrikula.php?wsdl', true);
	$matrikulatutaDago = $mailMatrikulatutaDago -> call('egiaztatuE',$param);
	//ikaslea matrikulatuta badago, datu-basean dagoen aztertu

	if(strcmp($matrikulatutaDago,'BAI')==0){
		include('dbconfig.php');
		$k = new mysqli($zerbitzaria,$erabiltzaile,$gakoa,$db);
		$id = $k -> query("select count(id) as zk from users where eposta = '" . $ml . "'");
		$i = $id -> fetch_array(MYSQLI_NUM);
		//echo($i[0]);
		if($i[0] == 0){$matrikulatutaDago = 'EZ';}
	}
	return $matrikulatutaDago;
}
function amaitu($msg){
	//mezua bidali eta skripta amaitzen du
	echo($msg);
	exit();
}
function sortu_eskaera($k,$i){
	//mail batek pasahitza berreskuratzeko eskaera bat aktibo duen aztertzen du
	$em = $k -> query("select muga from pasahitz where mailId = '" . $i . "' and aktibo = " . true);
	$data = time() + 86400;
	$e = $em -> fetch_array(MYSQLI_ASSOC);
	if($data < $e['muga']){
		return $e['muga'];
	}
	else{
		return '';
	}
}
function eman_id($k,$m){
	//mail baten users taulako esleitua duen id itzultzen du
	$em = $k -> query("select id from users where eposta = '" . $m . "'");
	$e = $em -> fetch_array(MYSQLI_ASSOC);
	return $e['id'];
}
function strina_sortu($luz){
	//ausaz $luz luzerako string bat sortzen du, $auk bektoreko elementuekin
	$a = '';
	$auk = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','1','2','3','4','5','6','7','8','9','0','$','%','&','?','#'];

	for($i=0;$i<$luz;$i++){
		$z = rand(0,(count($auk)-1));
		$a .= $auk[$z];
	}
	return $a;
}
function sortu_hash($k,$m,$g){
	//erabiltzailearen id,izena eta eposta eta $g (ausaz sortutako strina) datoekin hash bat sortzen du
	$erabiltzaile = $k -> query("select concat(id,eposta,izena) from users where id = '".$m."'");
	$em = '';
	if($erabiltzaile){
		$erabiltz = $erabiltzaile -> fetch_array(MYSQLI_NUM);
		$str = $erabiltz[0].$g;
		$em = password_hash($str,PASSWORD_DEFAULT);
	}
	$erabiltzaile -> close();
	return $em;
}
function emanId($k,$s){
	//$s = (password_hash(id+izena+eposta+kode)) duen erabiltzailearen id itzultzen du
	$id = '';
	$zerrenda = $k -> query("select id,eposta,izena from users");
	if($k -> error){
		return 'ezin izan da pasahitza berreskuratu<br/>erabil ezazu berriz korreoan duzun esteka';
	}
	while($z = $zerrenda -> fetch_array(MYSQLI_ASSOC)){
		$param = $k -> query("select kode from pasahitz where mailId = '".$z['id']."' and aktibo = 1");
		$prm = $param -> fetch_array(MYSQLI_ASSOC);
		$strina = $z['id'].$z['eposta'].$z['izena'].$prm['kode'];
		if(password_verify($strina,$s)){
			$id = $z['id'];
			break;
		}
	}
	$zerrenda -> close();
	return $id;
}
function eskaera_aktibo_du($k,$i){
	//mail batek berrabiarazte eskaera bat aktibo badu true itzuliko du, false bestela
	$egoera = $k -> query("select muga from pasahitz where mailId = '" . $i . "' and aktibo = 1");
	$iraungituta = 0;
	if($egoera){
		$data = $egoera -> fetch_array(MYSQLI_ASSOC);
		if(strlen($data['muga'])>0){
			$iraungituta = $data['muga']<time()?0:1;
		}
		$egoera -> close();
	}
	if(!$iraungituta){//erabiltzaileak eskaera iraungituta du baina oraindik aktibo dago
		$k -> query("update pashitz set aktibo = 0 where mailId = " . $i);
	}
	return $iraungituta;
}
function berrabiarazi($m){
?>
<style>
form{border:1px solid #338;width:300px;background-color:#dde;padding:15px;}
input{margin:2px;}
button{padding:5px;}
</style>
<script src='../js/jquery.js' type='text/javascript'></script>
<script>
var hasi = {
	onReady: function(e){
		$('#berrabiarazi').on(
			'submit',
			function(e){
				$('*').css({cursor:'wait'});
				e.preventDefault();
				$.ajax({
					type:'post',
					url:'pasahitza.php',
					data:{mailId:$('#mailId').val(),pasahitza3:$('#pasahitza3').val(),pasahitza4:$('#pasahitza4').val()},
					dataType:'text',
					success:function(em){
						var e = em.substr(0,1)=='#'?'#':'';
						m = $('<span>'+em+'</span>');
						$('body').append(m);
						$W = $(window);
						W = $W.width();
						H = $W.height();
						w = m.width();
						h = m.height();
						m.css({position:'fixed','background-color':'#aac','font-size':'1.7em',padding:'25px',border:'6px outset #aaf',top:((H-h)/2),left:((W-w)/2)});
						if(e == '#'){
							setTimeout(
								function(){
									document.location='layout.php';
								},
								3000
							);
						}
						setTimeout(
							function(){
								$('*').css({cursor:'default'});
								m.fadeOut(3000);
							},
							3000
						);
					}
				});
			}
		);
	}
}
$(document).ready(hasi.onReady);
</script>
<span style='margin:0px auto'>
<h3>Idatzi pasahitza berria</h3>
<form name='berrabiarazi' id='berrabiarazi' enctype='multipart/form-data' method='post'>
	<frameset>
		<INPUT TYPE='hidden' NAME='mailId' id='mailId' value='<?php echo($m);?>'/>
		<label>Pasahitza Berria*: <INPUT TYPE='password' NAME='pasahitza3' id='pasahitza3' minlength='8' size='20' value='' required/></label><br/>
		<label>Pasahitza Berria*: <INPUT TYPE='password' NAME='pasahitza4' id='pasahitza4' minlength='8' size='20' value='' required/></label><br/>
	</frameset>
	<button type='reset' value='garbitu'>Garbitu</button>
	<button type='submit' id='bidali' value='bidali'>Bidali</button>
</form>
</span>
<?php
}
?>