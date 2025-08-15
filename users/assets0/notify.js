
document.getElementById('close_n');

document.getElementById('mark_red');

$(document).on('click','#close_n',function(){
	console.log("close_n");
	$('#a-notification').addClass("gone");
});

$(document).on('click','#mark_red',function(){
	console.log("mark_red");
	var id=$(this).attr('data-id');
	$.post( "notifyapi.php",{id},function(x){
		console.log(x);
	});
	$('#a-notification').addClass("gone");
});
