$(document).ready(function(){

	//消息提示
	//$("#message").animate({opacity:'hide'},5000);
	$("#message").show();

	$('.config tbody tr:even').addClass('odd');
	$('.config tbody tr').hover(
		function() {$(this).addClass('highlight');},
		function() {$(this).removeClass('highlight');}
	);
	$('.config tbody tr').click(
		function() {
			if ($(this).hasClass('selected')) {
				$(this).removeClass('selected');
			} else {
				$(this).addClass('selected');
			}
		}
	);


	///////////

	$('.list_td tbody tr:even').addClass('odd');
	$('.list_td tbody tr').hover(
		function() {$(this).addClass('highlight');},
		function() {$(this).removeClass('highlight');}
	);

	$('.list_td input[type="checkbox"]:checked').parents('tr').addClass('selected');

	$('.list_td input[type="checkbox"]').click(
		function() {
			if ($(this).parents('tr').hasClass('selected')) {
				$(this).parents('tr').removeClass('selected');
				$(this).attr('checked',false);
				//$(this).find('input[type="checkbox"]').attr('checked','checked');
			} else {
				$(this).parents('tr').addClass('selected');
				$(this).attr('checked',true);
				//$(this).find('input[type="checkbox"]').removeAttr('checked');
			}
		}
	);


	$(".chkall").click(
		function() {
			$("input[name='selectall[]']").each(
				function() {
					$(this).attr("checked", true);
					$(this).parents('tr').addClass('selected');
				}
			);
		}
	);
	$(".dechkall").click(
		function() {
			$("input[name='selectall[]']").each(
				function() {
					$(this).attr("checked", false);
					$(this).parents('tr').removeClass('selected');
				}
			);
		}
	);

});