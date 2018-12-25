<?php session_start();
if(!isset($_SESSION['rola'])){header('location:layout.php');}

if(strcmp($_SESSION['rola'],'ikasle') != 0 && strcmp($_SESSION['rola'],'kudeatzaile') != 0){header('location:layout.php');}
?>
<style>
	input:invalid{border:1px solid red;margin:1px;}
	form{width:350px;margin:0px auto;text-align:center;}
</style>
<!--
<script src='https://code.jquery.com/jquery-3.3.1.min.js' type='text/javascript'></script>
-->
<script>
function garbitu(){
	$('#marrazkia').attr('src','');
	$('#neurri').text('');
}
function fitxategi(){
	var $img = $('#img')[0].files[0];
	$('#neurri').text('fitxategiaren neurria: '+$img.size+' byte').css({display:'inline'});
	if($img){
		var FR = new FileReader();/*fitxategi bat irakurtzeko objetua  sortu*/
		FR.readAsDataURL($img);/*fitxategiaren edukia irakurri*/
		FR.onload=function(e){/*fitxategi guztia irakurtu ondoren...*/
			$('#marrazkia').attr({'src':e.target.result,'width':100}).css({display:'inline'});/*'img' objetuaren 'src' propietateari esleitu*/
		}
	}
}
var hasi = {
	onReady: function(e){
		$('#galderenF').on('submit',function(e){
			$('*').css({cursor:'wait'});
			e.preventDefault();
			$.ajax({
				type: 'POST',
				url: 'addQuestionwithImage.php',
				data: new FormData(this),
				contentType: false,
				cache: false,
				processData: false,
				dataType: 'text',
				success: function(em){
					m = $('#msg');
					m.html(em);
					w = $(window);
					m.css({'background-color':'#aaf','font-size':'1.3em',padding:'25px',border:'5px outset #778',position:'absolute',top:(w.height()-m.height())/2,lef:(w.width()-m.width())/2}).show();
					setTimeout(
						function(){
							m.fadeOut();
							garbitu();
                            $('#galderenF').trigger("reset");
						},
						5000
					);
				},
				error: function(er){
					m = $('#msg');
					m.html(er);
					w = $(window);
					m.css({top:(w.height()-m.height())/2,lef:(w.width()-m.width())/2}).show();
					setTimeout(
						function(){
							m.fadeOut();
							garbitu();
						},
						5000
					);
				},
				complete: function(em){
					$('*').css({cursor:'default'});
				}
			});
		});
	}
};
$(document).ready(hasi.onReady);
</script>
<form id="galderenF" name="galderenF" enctype="multipart/form-data" onreset="garbitu()" method="post" action="addQuestionwithImage.php">
	<fieldset>
		<p style='display:none'><label>Mail(*): <INPUT TYPE='mail' NAME='mail' id='mail' pattern='\w\w[a-z]*\d\d\d@(ikasle\.)?ehu\.eus$' size='25' value='<?php echo($_SESSION['eposta']);?>'></label></p>
		<p>
			<label>Galdera (*): <INPUT TYPE='text' NAME='galdera' id='galdera' minlength='10' required></label><br/>
			<label>Erantzun zuzena (*): <INPUT TYPE='text' NAME='zuzena' id='eZuzen' size='20' required></label><br/>
			<label>Erantzun okerra 1 (*): <INPUT TYPE='text' NAME='erantzunokerra1' id='erantzunokerra1' required></label><br/>
			<label>Erantzun okerra 2 (*): <INPUT TYPE='text' NAME='erantzunokerra2' id='erantzunokerra2' required></label><br/>
			<label>Erantzun okerra 3 (*): <INPUT TYPE='text' NAME='erantzunokerra3' id='erantzunokerra3' required></label><br/>
		</P>
		<p>
			<label>Zailtasuna(*): <INPUT TYPE='number' NAME='zailtasuna' id='zailtasuna' min='0' max='5' required></label><br/>
			<label>Gaia(*): <INPUT TYPE='text' NAME='gaia' id='gaia' required></label><br/>
			<label>Argazkia: <INPUT TYPE='file' NAME='html_file' ACCEPT='text/html' id='img' onchange='fitxategi()'></label><br/>
			<span id='neurri' style='color:darkblue;font-weight:bold;display:none'></span><br/>
			<img src='' style='display:none' id='marrazkia'/>
		</p>
		<p>
			<button type='reset' value='garbitu'>Garbitu</button>
			<button type='submit' value='bidali'>Bidali</button>
		</p>
	</fieldset>
</form>
<span id='msg'></span>
