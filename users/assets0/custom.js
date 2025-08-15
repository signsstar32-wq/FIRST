

$(document).on('click','.nnn-btn',function(){
	console.log("as");
	let user_id=$(this).attr('data-uid');
let obj={has_seen_it:"yes",crud:"toggle_n",user_id};
	 

	let u=`${window.location}/api/users.php`;
	//console.log(u);
	$.post(window.API+'?crud=toggle_n',obj,function(n,m,j){
$('#notification-3').addClass('nnn-gone');
	});


});