function yesOrNoDialog(title, content, callback, params){
	var divD = $('<div></div>');
	divD.attr('id','dialogDiv');
	divD.attr('title',title);
	var childP = $('<p></p>');
	childP.attr('class', 'txtcenter-line');
	childP.html(content);
	childP.appendTo(divD);
	divD.appendTo('body');  
	$("#dialogDiv").dialog({
		autoOpen: false,
		modal : true,
		resizable:false,
		width: 400,
		buttons: [
			{
				text: "确认",
				click: function() {
					callback(params);
					$(this).dialog( "close" );
					$("#dialogDiv").remove();
				}
			},
			{
				text: "取消",
				click: function() {
					$(this).dialog( "close" );
					$("#dialogDiv").remove();
				}
			}
		]
	});
	$("#dialogDiv").dialog( "open" );
}

function alert(content){
	var alertDiv = $('<div></div>');
	alertDiv.attr('id','alertDiv');
	alertDiv.attr('title','提示信息');
	var childP = $('<p></p>');
	childP.attr('class', 'txtcenter-line');
	childP.html(content);
	childP.appendTo(alertDiv);
	alertDiv.appendTo('body'); 
	$( "#alertDiv" ).dialog({
		autoOpen: false,
		modal : true,
		resizable:false,
		width: 500,
	});
	$( "#alertDiv" ).dialog( "open" );
	$( "#alertDiv" ).dialog( {
		close:function(e, ui){
			$("#alertDiv").dialog('close');
			$("#alertDiv").remove();
		}
	} );
}
