$(document).ready(function(){
	$(".classify_form").validate({
		debug: true,
		submitHandler: function(form) {
			$.post('process.php', $(".classify_form").serialize(), function(data) {
				$('#results').html(data);
			});
		}
	});
});