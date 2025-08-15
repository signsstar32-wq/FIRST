/*$('div.myTable').children("div.dtype").sort(function(a, b) {
    var A = $(a).text().toUpperCase();
    var B = $(b).text().toUpperCase();
    return (A < B) ? -1 : (A > B) ? 1 : 0;
}).appendTo('div.myTable');*/

$(document).on('keyup','#myInput',function(){

  //$("#myInput").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    //console.log({value});

    $(".myTable div.dtype").filter(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });

});

$(document).on('change','#packagename',function(){

  //$("#myInput").on("keyup", function() {
    var value = $(this).val();
    //console.log({value});
 	window.packagename2=value;
});

$(document).on('change','#ChooseFile',function(){
	$('.chum').text("file selected");
});

//

$('#exampleModal').on('show.bs.modal', function (event) {
	/*if (typeof window.packagename2=="undefined"  || window.packagename2=="undefined") {
		setTimeout(()=>{
			$('#exampleModal').modal('hide');
			noticePop("Select Package","error");
		},1000);
	}*/
  var button = $(event.relatedTarget); // Button that triggered the modal
  var recipient = button.data('whatever');//ng library or other methods instead.
  var modal = $(this);
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
  //$('.amount').val($('#amount').val()); 
  modal.find('#paymentmethod').val(recipient);
  modal.find('#packagename').val(window.packagename2);
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

	//console.log("copied");
	$('#addr-notification span#msg').text(`   Address Copied   `);
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

//

$(document).on('click','#close-btn',function(){
	$('#exampleModal').modal('hide');
});
