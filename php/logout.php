<?php session_start();

if(isset($_GET['irten'])){
	session_destroy();
	$egitura = new DOMDocument();
	$egitura->load('../xml/counter.xml');
	$erabiltzaile_kopurua = (int)$egitura -> getElementsByTagName('kopuru')[0] -> nodeValue;
	$erabiltzaile_kopurua--;
	$egitura -> getElementsByTagName('kopuru')[0] -> nodeValue = $erabiltzaile_kopurua;
	$users = $egitura -> getElementsByTagName('erabiltzaile');
	foreach($users as $u){
		if($u -> nodeValue == $_SESSION['eposta']){
			$ezabatzeko = $u;
			$egitura -> getElementsByTagName('erabiltzaileak')[0] -> removeChild($u);
		}
	}
	$egitura -> save('../xml/counter.xml');
	echo('saioa itxi da');
	exit();
}
?>
<style>
#msg{border:solid 2px #0055AA;background-color:#EEF;display:none;padding:5px;position:absolute;}
#logout{width:300px;border:5px outset #555555;margin:0px auto;text-align:center;}
p:hover{background-color:#AAB;}
</style>
<script>
function itxi(){
	$.ajax({
		url:'logout.php',
		data:{irten:'itxi'},
		dataType: 'text',
		success: function(e){
			if(e == 'saioa itxi da'){
				var m = $('#msg');
				m.fadeOut(1000);
				setTimeout(function(){document.location = '../layout.htm';},1300);
			}
		}
	});
}
function irten(){
	var m = $('#msg');
	m.fadeOut(1000);
	setTimeout(function(){m.html('')},1500);
}
</script>
<div id='logout'>
<p onclick='itxi()'>Log Out</p>
<p onclick='irten()'>Leihoa ezkutu<p>
</div>