<?php session_start();

if(!isset($_SESSION['rola'])){
	header('location:layout.php');
	exit();
}
if(strcmp($_SESSION['rola'],'ikasle') != 0 && strcmp($_SESSION['rola'],'kudeatzaile') != 0){
	header('location:layout.php');
	exit();
}

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
	
	if($msg != ''){mezua($msg);}
	else{
		//datoak jaso dira eta egokiak dire
		include('dbConfig.php');

		$max_kb = 1048576; //1Mb-eko argazkia gehienez
		$imgs = array("image/jpg", "image/jpeg", "image/gif", "image/png", "image/bmp");

		$konexioa = new mysqli($zerbitzaria,$erabiltzaile,$gakoa,$db);
		if($konexioa -> connect_error){
			mezua("errorea datu-basearekin konektatzean");
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
		$sql = "insert into questions(mail,galdera,erantzun_zuzena,erantzun_okerra_1,erantzun_okerra_2,erantzun_okerra_3,zailtasuna,gaia,marrazkia,mota) values('".$mail."','".$galdera."','".$zuzena."','".$erantzunokerra1."','".$erantzunokerra2."','".$erantzunokerra3."','".$zailtasuna."','".$gaia."','".$img."','".$img_tp."')";
		$konexioa -> query($sql);
		if($konexioa -> error){
			mezua('datu-basea atzitzean erroreren bat sortu da');
		}
		else{
			$konexioa -> close();
			sleep(2);
			mezua('galdera gorde da');
		}
	}
}
function datoak_aztertu($m,$gl,$ez,$e1,$e2,$e3,$z,$ga){
	if($ez == '' || $e1 == '' || $e2 == '' || $e3 == '' || $ga == ''){return 'daturen bat falta da';}

	//mail aztertu
	$exp_reg = '/\w\w[a-z]*\d\d\d@(ikasle\.)?ehu\.eus$/';
	if(!preg_match ($exp_reg, $m)){return 'eposta ez dago ongi eraikita';}

	//galdera aztertu
	$gl = preg_replace('/\s\s+/', ' ', $gl);

	if(strlen($gl) < 10){return 'galdera motzegia da';}

	//zailtasuna aztertu
	$zk = filter_var ($z, FILTER_VALIDATE_INT);
	if($zk > 5 || $zk < 1){return 'zailtasuna ez da mugen artean';}

	return '';
}
function mezua($msg){
	echo($msg);
	exit();
}
?>