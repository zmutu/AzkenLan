<?php session_start();

$f = fopen('fitx.txt','a+');
fwrite($f,"\nHasi behar du...\t");
fwrite($f,$_POST['galdera']);

if(isset($_POST['mail'])){fwrite($f,"\nMail: ".$_POST['mail']);}

if(!isset($_SESSION['rola'])){header('location:../layout.htm');}
if(strcmp($_SESSION['rola'],'ikasle') != 0){header('location:../layout.htm');}

fwrite($f,"\nSaioa: ".$_SESSION['rola']." -> ".$_SESSION['user']);

$img_nm = '';	//marrazki izena gordetzeko
$img_tp = '';	//marrazki mota gordetzeko

//datu guztiak jaso (marrazkia ezik)
if(isset($_POST['mail'])){$mail = $_POST['mail'];}
if(isset($_POST['galdera'])){$galdera = $_POST['galdera'];}
if(isset($_POST['zuzena'])){$zuzena = $_POST['zuzena'];}
if(isset($_POST['erantzunokerra1'])){$erantzunokerra1 = $_POST['erantzunokerra1'];}
if(isset($_POST['erantzunokerra2'])){$erantzunokerra2 = $_POST['erantzunokerra2'];}
if(isset($_POST['erantzunokerra3'])){$erantzunokerra3 = $_POST['erantzunokerra3'];}
if(isset($_POST['zailtasuna'])){$zailtasuna = $_POST['zailtasuna'];}
if(isset($_POST['gaia'])){$gaia = $_POST['gaia'];}

if(isset($mail)){
	//mail jaso bada, datuak aztertu
	$msg = datoak_aztertu($mail,$galdera,$zuzena,$erantzunokerra1,$erantzunokerra2,$erantzunokerra3,$zailtasuna,$gaia);
	fwrite($f,"\naddQuestionwithImage (30): ->".$msg."<-");
	
	if($msg != ''){echo($msg);}
	else{
		//datoak jaso da eta egokiak dire
		include('dbConfig.php');

		$max_kb = 1048576; //1Mb-eko argazkia gehienez
		$imgs = array("image/jpg", "image/jpeg", "image/gif", "image/png", "image/bmp");

		$konexioa = new mysqli($zerbitzaria,$erabiltzaile,$gakoa,$db);
		if($konexioa -> connect_error){
			fwrite($f,"\naddQuestionwithImage (45): ->".$konexioa -> connect_error."<-");
			echo("errorea datu-basearekin konektatzean");
			exit();
		}
		if (isset($_FILES["html_file"]) || $_FILES["html_file"]["error"] = 0){
			$img = $_FILES["html_file"]["tmp_name"];
			$img_tp = $_FILES["html_file"]["type"];
			$neurri = $_FILES["html_file"]["size"];
			
			if(is_uploaded_file($img)){
				if(in_array($img_tp,$imgs) && $neurri < $max_kb){
					$img = $konexioa -> real_escape_string(file_get_contents($img));
				}
			}
		}
		else{
			$img = '';
			$img_tp = '-';
		}
		$sql = "insert into questions(mail,galdera,zuzena,erantzun_okerra1,erantzun_okerra2,erantzun_okerra3,zailtasuna,gaia,argazkia,mota) values('".$mail."','".$galdera."','".$zuzena."','".$erantzunokerra1."','".$erantzunokerra2."','".$erantzunokerra3."','".$zailtasuna."','".$gaia."','".$img."','".$img_tp."')";

		if(!$konexioa -> query($sql)){
			fwrite($f,"\naddQuestionwithImage (67): ->".$konexioa -> error."<-");
			echo("Errorea datu-basean datuak sartzean<br/>\ninsert into questions(mail,galdera,zuzena,erantzun_okerra1,erantzun_okerra2,erantzun_okerra3,zailtasuna,gaia,argazkia,mota) values('".$mail."','".$galdera."','".$zuzena."','".$erantzunokerra1."','".$erantzunokerra2."','".$erantzunokerra3."','".$zailtasuna."','".$gaia."','','".$img_tp."')");
		}
		else{
			fwrite($f,"insert into questions(mail,galdera,zuzena,erantzun_okerra1,erantzun_okerra2,erantzun_okerra3,zailtasuna,gaia,argazkia,mota) values('".$mail."','".$galdera."','".$zuzena."','".$erantzunokerra1."','".$erantzunokerra2."','".$erantzunokerra3."','".$zailtasuna."','".$gaia."','','".$img_tp."')");
			$msg2 = gorde_XML($mail,$galdera,$zuzena,$erantzunokerra1,$erantzunokerra2,$erantzunokerra3,$zailtasuna,$gaia);
			sleep(5);
			echo('Galdera sortu da<br/>'.$msg2);
		}
		$konexioa -> close();
	}
}
function gorde_XML($m,$g,$ez,$e1,$e2,$e3,$z,$ga){
	if (file_exists('../xml/questions.xml')) {
		$xml = simplexml_load_file('../xml/questions.xml');
	}
	else{return 'fitxategia ez da aurkitzen';}
	$nodoa = $xml -> addChild('assessmentItem');
	$nodoa -> addAttribute('author',$m);
	$nodoa -> addAttribute('subject',$ga);

	$galdera = $nodoa -> addChild('itemBody');
	$galdera -> addChild('p',$g);

	$zuzena = $nodoa -> addChild('correctResponse');
	$zuzena -> addChild('value',$ez);
	
	$okerrak = $nodoa -> addChild('incorrectResponses');
	$okerrak -> addChild('value',$e1);
	$okerrak -> addChild('value',$e2);
	$okerrak -> addChild('value',$e3);
	
	$xml -> asXML('../xml/questions.xml');
	
	return 'xml itxategian gorde da';
}
function datoak_aztertu($m,$gl,$ez,$e1,$e2,$e3,$z,$ga){
	if($ez == '' || $e1 == '' || $e2 == '' || $e3 == '' || $ga == ''){return 'daturen bat falta da';}

	//mail aztertu
	$exp_reg = '/\w\w[a-z]*\d\d\d@ikasle\.ehu\.eus$/';
	if(!preg_match ($exp_reg, $m)){return 'eposta ez dago ongi eraikita';}

	//galdera aztertu
	$gl = preg_replace('/\s\s+/', ' ', $gl);

	if(strlen($gl) < 10){return 'galdera motzegia da';}

	//zailtasuna aztertu
	$zk = filter_var ($z, FILTER_VALIDATE_INT);
	if($zk > 5 || $zk < 1){return 'zailtasuna ez da mugen artean';}

	return '';
}
?>