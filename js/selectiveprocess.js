$(document).ready(function(){

	$("#phase_2").ready(function(){

		getPhasesToSort();

		$(this).change(function(){
			getPhasesToSort();
		});
	});

	$("#phase_3").ready(function(){

		getPhasesToSort();
		
		$(this).change(function(){
			getPhasesToSort();
		});
	});
	
	$("#phase_4").ready(function(){

		getPhasesToSort();
		
		$(this).change(function(){
			getPhasesToSort();
		});
	});

	$("#open_selective_process_btn").click(function(){
		saveSelectiveProcess("newSelectionProcess");
	});

	$("#edit_selective_process_btn").click(function(){
		saveSelectiveProcess("updateSelectionProcess");
	});

	$("#selective_process_start_date").datepicker($.datepicker.regional["pt-BR"], {
		dateFormat: "dd-mm-yy"
	});

	$("#selective_process_end_date").datepicker($.datepicker.regional["pt-BR"], {
		dateFormat: "dd-mm-yy"
	});

	$('#edit_notice_path_form').submit(function(e) {
    	e.preventDefault();
		editNoticePath($(this)[0]);
	});

});

function makeSortable(){
	$("#sortable").sortable();
	$("#sortable").sortable("option", "axis", "y");
	$("#sortable").sortable("option", "cursor", "move");
	$("#sortable").sortable("option", "containment", "parent");
}

function saveSelectiveProcess(saveMethod){

	var course = $("#course").val();
	var studentType = $("#student_type").val();
	var noticeName = $("#selective_process_name").val();
	var startDate = $("#selective_process_start_date").val();
	var endDate = $("#selective_process_end_date").val();


	var preProject = $("#phase_2").val();
	var preProjectWeight = $("#phase_weight_2").val();
	
	var writtenTest = $("#phase_3").val();
	var writtenTestWeight = $("#phase_weight_2").val();
	
	var oralTest = $("#phase_4").val();
	var oralTestWeight = $("#phase_weight_4").val();
	
	var phasesOrder = $("#sortable").sortable("toArray");
	
	var siteUrl = $("#site_url").val();

	var urlToPost = siteUrl + "/program/ajax/selectiveprocessajax/"+ saveMethod;

	data = {
		course: course,
	    student_type: studentType,
	    selective_process_name: noticeName,
	    selective_process_start_date: startDate,
	    selective_process_end_date: endDate,
		phase_2: preProject,
		phase_weight_2: preProjectWeight,
	    phase_3: writtenTest,
	    phase_weight_3: writtenTestWeight,
	    phase_4: oralTest,
	    phase_weight_4: oralTestWeight,
	    phases_order: phasesOrder
	}
	if(document.getElementById("processId")){
		var processId = $("#processId").val();
	    data['processId'] = processId;
	}
	$.post(
		urlToPost,
		data,
		function(data){
			$("#selection_process_saving_status").html(data);
			if(saveMethod == "updateSelectionProcess"){
				window.setTimeout(function () {
			        location.href = siteUrl + "/program/selectiveprocess/courseSelectiveProcesses/" + course;
			    }, 1000);
			}
		}
	);
}

function getPhasesToSort(){

	var preProject;
	var writtenTest;
	var oralTest;
	
	preProject = $("#phase_2").val();
	writtenTest = $("#phase_3").val();
	oralTest = $("#phase_4").val();
	var siteUrl = $("#site_url").val();
	
	data = {
		preProject: preProject,
		writtenTest: writtenTest,
		oralTest: oralTest	
	}

	if(!document.getElementById("processId")){
		var urlToPost = siteUrl + "/program/ajax/selectiveprocessajax/getPhasesToSort";

		$.post(
			urlToPost,
			data,
			function(data){
				$("#phases_list_to_order").html(data);
				makeSortable();
			}
		);
	}
	else{
		var processId = $("#processId").val();
	    var urlToPost = siteUrl + "/program/ajax/selectiveprocessajax/showPhasesInOrder";
	    data['processId'] = processId;
		$.post(
			urlToPost,
			data,
			function(data){
				$("#phases_list_to_order_in_edition").html(data);
				makeSortable();
			}
		);
	}

}

function editNoticePath(formData){
	var siteUrl = $("#site_url").val();
	var urlToPost = siteUrl + "/program/ajax/selectiveprocessajax/editNoticeFile";

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
			$("#status_notice_file").html(data);
		}
	});
}
