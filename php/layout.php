<?php session_start();
if(isset($_SESSION['rola'])){
	if(strcmp($_SESSION['rola'],'kudeatzaile') == 0){
		if(isset($_GET['ikasle'])){
			$rol = 2; //administrari kautotuta eta ikaslearen rolarekin
		}
		else{
			$rol = 3; //administraria
		}
	}
	else{
		$rol = 1; //ikaslea
	}
	
}
else{
	$rol = 0; //anonimoa
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta name='tipo_contenido' content='text/html;' http-equiv='content-type' charset='utf-8'>
	<title>Quizzes</title>
        <link rel="shortcout icon" href="../images/fabikon.png">
        <link rel='stylesheet' type='text/css' href='../styles/style.css' />
	<link rel='stylesheet' 
		   type='text/css' 
		   media='only screen and (min-width: 530px) and (min-device-width: 481px)'
		   href='../styles/wide.css' />
	<link rel='stylesheet' 
		   type='text/css' 
		   media='only screen and (max-width: 480px)'
		   href='../styles/smartphone.css' />
	<title>Azken Lana</title>
	<style>
		span:hover{background-color:#dde;border-radius:5px;cursor:pointer;cursor:hand;}
		span{color:blue;padding:2px 5px}
		.eskuin{float:right;margin:5px;padding:3px;}
	</style>
	<!--
	<script src='https://code.jquery.com/jquery-3.3.1.min.js' type='text/javascript'></script>
	-->
	<style>nav span{color:blue;cursor:hand;cursor:pointer;}</style>
	<script src='../js/jquery.js' type='text/javascript'></script>
	<script>
		function info(){
			$.get(
				'credits.php',
				function(em){
					$('#gorputza').html(em);
				}
			);
		}
		function erabiltzaileBerria(){
			$('*').css({cursor:'wait'});
			$.get(
				'signup.php',
				function(em){
					$('#gorputza').html(em);
					$('*').css({cursor:'default'});
				}
			)
		}
		function galderakKudeatu(){
			$.get(
				'handlingQuizes.php',
				function(em){
					$('#gorputza').html(em);
				}
			);
		}
		<?php
		if($rol == 0){
			//erabiltzaile anonimoak  bakarrik erabiliko dituen funtzioak
			echo('
				function login(){
					$.get(
						"login.php",
						function(em){
							$("#gorputza").html(em);
						}
					);
				}
				function pasahitzAldatu(){
					$.get(
						"pasahitza.php",
						function(em){
							$("#gorputza").html(em);
						}
					);
				}
			');
		}
		if($rol > 0){//erabiltzailea (ikaslea edo kudeatzailea) kautotuta dago
			echo('
				function saioaItxi(){
					$.get(
						"logout.php",
						function(em){
							b = $(em);
							$("body").append(b);
							W = $(window).width();
							b.css({position:"fixed",top:"25px",left:(W-200),"background-color":"#aac"});
						}
					);
				}
			');
		}
		if($rol == 3){
			echo('
		function erabiltzaileKudeaketa(){
			$.get(
				"handlingAccounts.php",
				function(em){
					$("#gorputza").html(em);
				}
			);
		}
			');
		}
		if($rol == 2 || $rol == 1){
			echo('
			function galderakKudeatu(){
				$.get(
					"handlingQuizes.php",
					function(em){
						$("#gorputza").html(em);
					}
				);
			}
			function galderaBerria(){
				$.ajax({
					type:"GET",
					url:"addQuestion.php",
					dataType:"html",
					success:function(em){
						$("#gorputza").html(em);
					},
					error:function(er){
						var msg = $("#msg");
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
			function galderaZerrenda(){
				$.ajax({
					url:"handlingQuizes.php",
					data:{zerrenda:1},
					type:"GET",
					dataType:"html",
					success:function(em){
						if(em.indexOf("<table")>0){$("#gorputza").html(em);}
						else{
							var msg = $("<span>"+em+"</span>");
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
			');
		}
		?>
	</script>
  </head>
  <body>
  <div id='page-wrap'>
	<header class='main' id='h1'>
		<?php if($rol==0){?>
		
			<span>anonimous </span> || <span id='berria' onclick='pasahitzAldatu()'>Pasahitza Aldatu</span> || <span onclick='login()'>Log In</span> || <span onclick='erabiltzaileBerria()'> Sign Up </span>
		<?php }
			else{
				$img = emanArgazkia();
				$a = getimagesize($img);
				if($a[0] > $a[1]){$b='width=\'50\'';}
				else{$b='height=\'50\'';}
				echo("<img src='".$img."' ".$b." style='float:left'/><span style='float:left'>".$_SESSION['user']."</span>");
		?>
			<span onclick='saioaItxi()' class='eskuin'>LogOut</span>
		<?php }?>
		<?php if($rol==1 || $rol==2){?>
			<span id='itxi' onclick='galderaZerrenda()' class='eskuin'>Galdera Zerrenda</span>
			<span id='berria' onclick='galderaBerria()' class='eskuin'>Galdera Berria</span>
		<?php }?>
		<?php if($rol==2){?>
			<span onclick='document.location="layout.php"' class='eskuin'>Kudeatzaile</span>
		<?php }?>
		<?php if($rol==3){?>
			<span onclick='document.location="layout.php?ikasle=1"' class='eskuin'>Ikasle</span>
		<?php }?>
		<h2>Quiz: crazy questions</h2>
	</header>
	<nav class='main' id='n1' role='navigation'>
		<span><a href='layout.php<?php if($rol==2){echo('?ikasle=1');}?>'>Home</a></span>
		<?php if($rol==1 || $rol==2){?>
			<span onclick='galderakKudeatu()'>Galderak</span>
		<?php }?>
		<?php if($rol==3){?>
			<span onclick='erabiltzaileKudeaketa()'>Kontuak</span>
		<?php }?>
		
		<span onclick='galderaGuztiak()'>Quizz</span>
		<span onclick='info()'>Credits</span><br/>
		<hr/>
		<script>var maiz1</script>
		<?php if($rol > 0){?>
		<script>
			maiz1 = setInterval(
				function(){
					$.get(
						'emanErregistroakOraintxeBertan.php',
						function(em){
							var e = em.split('/');
							$('#erregistroak').html('[nereak: '+e[0]+']<br/>[guztiak: '+e[1]+']');
						}
					);
				},
				20000
			);
		</script>
		<p style='padding:5px'>galderak</p>
		<p id='erregistroak'></p>
		<?php }?>
	</nav>
    <section class='main' id='s1'>
	<div id='gorputza'>
		Quizzes and credits will be displayed in this spot in future laboratories ...
	</div>
    </section>
	<footer class='main' id='f1'>
		 <a href='https://github.com'>Link GITHUB</a>
	</footer>
</div>
</body>
</html>
<?php
function emanArgazkia(){
	include('dbConfig.php');
	$sql = "Select argazkia, mota from users where id = '".$_SESSION['id']."'";
	$konexioa = new mysqli($zerbitzaria,$erabiltzaile,$gakoa,$db);
	$em = $konexioa -> query($sql);
	$argazkia = $em -> fetch_array(MYSQLI_ASSOC);
	if($argazkia["mota"]!='-'){
		$argazki = "data:".$argazkia["mota"].";base64,".base64_encode($argazkia["argazkia"]);
	}
	else{$argazki = '';}
	return $argazki;
}
?>
