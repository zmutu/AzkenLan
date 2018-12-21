<?php session_start();

if(!isset($_SESSION['id'])){exit();}

include('dbConfig.php');
//kode hau 20 segunduoro exekutatzen da alako
//batean konexioa edo kontsulta egitean errorerik 
//gertatzen bada, auntzaren gauerdiko eztula

$konexioa = new mysqli($zerbitzaria,$erabiltzaile,$gakoa,$db);
if($konexioa->connect_error){echo($konexioa->connect_error);}

//erabiltzailearen erregistroak
$sql = 'Select count(id) as id from questions where mail = "'. $_SESSION['eposta'] . '"';

$nereak = $konexioa -> query($sql);

if($nereak){
	$n = $nereak -> fetch_array(MYSQLI_ASSOC);
	$erabiltzailearenak = $n['id'];
	$nereak -> free_result();
}
else{
	$erabiltzailearenak=0;
}

//erregistro guztiak
$sql = 'Select count(id) as id from questions';
$guztiak = $konexioa -> query($sql);
if($guztiak){
	$g = $guztiak -> fetch_array(MYSQLI_ASSOC);
	$denak = $g['id'];
	$guztiak -> free_result();
}
else{
	$denak = 0;
}

$konexioa -> close();

echo($erabiltzailearenak.'/'.$denak);
?>