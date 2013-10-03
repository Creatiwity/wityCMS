
$(document).ready(function() {
	$('.button-sign-in').click(function() {
		$('.login').addClass('closed');
		$('.sign-in').toggleClass('closed');
		return false;
	});
	
	$('.button-login').click(function() {
		$('.sign-in').addClass('closed');
		$('.login').toggleClass('closed');
		return false;
	});
});
