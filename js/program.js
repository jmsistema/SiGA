
$(document).ready(function(){

	$(document).on('submit', '#add_field_file_form', function(e){
    	e.preventDefault();
		addFieldFile($(this)[0]);
	});

	$('#add_info_btn').click(function(e) {
    	e.preventDefault();
		addInfo();
	});

	$('#edit_info_btn').click(function(e) {
    	e.preventDefault();
		editInfo();
	});

	(function($) {

	  hide_show = function(data) {
	  	changeExtraInfoStatus(data);
	  };
	})(jQuery);
});


function addFieldFile(formData){
	var siteUrl = $("#site_url").val();
	var urlToPost = siteUrl + "/program/ajax/programajax/addFieldFile";

	var data = new FormData(formData);

	$.ajax({
		url: urlToPost,
		type: 'post',
		data: data,
		async: false,
		cache: false,
		contentType: false,
		processData: false,
		success: function (data) {
			$("#file_result").html(data);
			window.setTimeout(function () {
				location.reload();
		    }, 1000);
		}
	});
}


function addInfo(){
	var siteUrl = $("#site_url").val();
	var title = $("#title").val();
	var details = $("#details").val();
	var urlToPost = siteUrl + "/program/ajax/programajax/addInformationOnPortal";
	var program_id = $("#program_id").val();

	var data = {
		details: details,
		title: title,
		program_id: program_id
	}
	$.post(
		urlToPost,
		data,
		function(data){
			$('#add_field_form').collapse('hide');
			$("#add_result").html(data);
		}
	);
}

function changeExtraInfoStatus(infoId){
	var siteUrl = $("#site_url").val();
	var urlToPost = siteUrl + "/program/ajax/programajax/changeExtraInfoStatus";

	var data = {
		infoId: infoId
	}
	$.post(
		urlToPost,
		data,
		function(data){
			var values = JSON.parse(data);
			$("#label_" + infoId).html(values.label);
			var actions = values.button + "<a href='" + siteUrl + "/" + values.link_to_edit + "' class='btn btn-primary'><i class='fa fa-edit'></i></a>";
			$("#button_" + infoId).html(actions);
		}
	);
}

function editInfo(){
	var siteUrl = $("#site_url").val();
	var title = $("#title").val();
	var details = $("#details").val();
	var urlToPost = siteUrl + "/program/ajax/programajax/editInformationOnPortal";
	var info_id = $("#info_id").val();
	var program_id = $("#program_id").val();

	var data = {
		details: details,
		title: title,
		info_id: info_id,
		program_id:program_id
	}
	$.post(
		urlToPost,
		data,
		function(data){
			$("#add_result").html(data);
		}
	);	
}