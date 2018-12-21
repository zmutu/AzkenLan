<?php session_start();

if(isset($_GET['irten'])){
	session_destroy();
	echo('ondo izan');
	exit();
}
?>
<div id='edukia'>
<style>
#logout{width:150px;border:5px outset #05a;margin:0px auto;text-align:center;padding:15px;background-color:#5af;border-radius:10px;-moz-border-radius:10px;-webkit-border-radius:10px;}
p:hover{background-color:#556;cursor:pointer;cursor:hand;color:#ff0;}
</style>
<script>

function itxi(){
	$('*').css({cursor:'wait'});
	$.ajax({
		url:'logout.php',
		data:{irten:'itxi'},
		dataType: 'text',
		success: function(em){
			m = $('<span>'+em+'</span>');
			$('body').append(m);
			$W = $(window);
			W = $W.width();
			w = m.width();
			H = $W.height();
			h = m.height();
			m.css({position:'fixed','background-color':'#5af','font-size':'1.7em',padding:'25px',border:'6px outset #05a',top:((H-h)/2),left:((W-w)/2),'border-radius':'10px','-moz-border-radiu':'10px','-webkit-border-radius':'10px'});
			setTimeout(
				function(){
					$('*').css({cursor:'default'});
					document.location = 'layout.php';
				},
				2000
			);
		}
	});
}
function irten(){
	var m = $('#edukia');
	m.fadeOut(1000,function(){this.remove();});
}
</script>
<div id='logout'>
<p onclick='irten()' style='float:right;pading:0px;border:1px solid #05a'>&nbsp;&nbsp;x&nbsp;&nbsp;</p><br/>
<p onclick='itxi()'>Log Out</p>
</div>
</div>