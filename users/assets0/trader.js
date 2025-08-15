

$(document).on('keyup','#myInput',function(){

  //$("#myInput").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    //console.log({value});

    $(".myTable2 div.trade-con").filter(function() {
      $(this).toggle($(this).find('.t-name').text().toLowerCase().indexOf(value) > -1)
    });

});

$(document).on('change','#ChooseFile',function(){
	$('.chum').text("file selected");
});

$('#exampleModal').on('show.bs.modal', function (event) {

	/*if ($('#amount').val().length === 0 ) {
		setTimeout(()=>{
			$('#exampleModal').modal('hide');
			noticePop("enter amount","error");
		},1000);
	}*/
  var button = $(event.relatedTarget); // Button that triggered the modal
  var recipient = button.data('whatever');//ng library or other methods instead.
  var modal = $(this)
  //console.log({recipient,modal});
  try{
	  if (window.ze2[recipient]) {
	  	modal.find('#method-name').text(recipient);//window.d2[recipient]
	  	Object.values(window.ze2[recipient]).forEach((h)=>{
	  		if(h.includes("address")){
				modal.find('#address-7').text(window.w2[h]);
	  		}else if(h.includes("file")){
	  			console.log({b:`../wp-admin/uploads/${window.w2[h]}`,h});
				modal.find('.method-img img').attr("src",`../wp-admin/uploads/${window.w2[h]}`);
	  		}
	  	});
	  	
	  }else{
	  	modal.find('#method-name').text("");
	  }
	}catch(e){
		//console.log(e);
	}
  modal.find('.amount').val($('#amount').val()); 
  modal.find('#depositmethod').val(recipient);
  //modal.find('#method-name').val(recipient)
});


//copy
$(document).on('click','.copy',function(){

	var id=$(this).find('.instr').attr('data-id');


	// Get the text field
  var copyText = document.getElementById(id);// $(`#${id}`).text();
  //console.log({copyText});

  try{
  // Select the text field
  copyText.select();}catch(e){}

  try{
  copyText.setSelectionRange(0, 99999); // For mobile devices
  }catch(e){}

   // Copy the text inside the text field
  navigator.clipboard.writeText(copyText.textContent);

	$('#addr-notification span#msg').text(`   Address Copied   `);
	//console.log("copied");
	$('#addr-notification').removeClass("gone");
	setTimeout(function(){
		$('#addr-notification').addClass("gone");
	},2000);

});

const noticePop=(m,t)=>{
	if (t=="error") {
		$('#tryagain-notification span#msg').text(`   ${m}   `);

		$('#tryagain-notification').removeClass("gone");
		setTimeout(function(){
			$('#tryagain-notification').addClass("gone");
		},4000);
	}else{
		$('#addr-notification span#msg').text(`   ${m}   `);
		$('#addr-notification').removeClass("gone");
		setTimeout(function(){
			$('#addr-notification').addClass("gone");
		},2000);
	}
}
window.noticePop=noticePop;


$(document).on('click','.btn-green',function(){
	var t=$(this).closest('.trade-con');
	var v=t.attr('data-i');
	var i=t.find('.idlemode');
	var intercept=t.find('.intercept');
	//intercept
	var c=t.find('.cmode');
	var d=t.find('.dmode');
	i.hide();
	intercept.show();
	$.post(window.location.href,{trader_id:v,addtrader:1},function(e,i,o){
		setTimeout(()=>{
			intercept.hide();
			c.show();

		},2000);
	});
	
});
$(document).on('click','.btn-red',function(){
	var t=$(this).closest('.trade-con');
	var v=t.attr('data-i');
	var i=t.find('.idlemode');
	var intercept=t.find('.intercept');
	var c=t.find('.cmode');
	var d=t.find('.dmode');


	intercept.show();
	c.hide();

	$.post(window.location.href,{trader_id:v,removetrader:1},function(e,ix,o){
		setTimeout(()=>{
			intercept.hide();
					
			i.show();
			c.hide();
			d.hide();
		},2000);
	});
	
});