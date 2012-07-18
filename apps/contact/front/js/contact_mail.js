$(document).ready(function() {

var name = $( "#name" ),
organisme = $( "#organisme" ),
email = $( "#email" ),
objet = $( "#objet" ),
message = $( "#message" ),
allFields = $( [] ).add(name).add(organisme).add(email).add( objet ).add(message);

function validateFirm () {
	reg = new RegExp("^[a-zA-Z0-9&-':.()ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ ]+$","gi");
	organisme.removeClass("form-error-inputs");
	if(organisme.val()=="") {
		$("#error-organisme").text("");
		return true;
	} else if(!( reg.test( organisme.val() ) ) ) {
		$("#error-organisme").text("Seuls les caractères alphanumériques et '.-&: sont autorisés.");
		organisme.addClass("form-error-inputs");
        return false;
	} else if(organisme.val().length > 200) {
		$("#error-organisme").text("200 caractères au maximum et 1 au minimum.");
		organisme.addClass("form-error-inputs");
        return false;
	} else {
		$("#error-organisme").text("");
        return true;
	}
}

function validateName () {
    reg = new RegExp("^[a-zA-Z-.()ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ ]+$","gi");
	name.removeClass("form-error-inputs");
	if(name.val()=="") {
		$("#error-name").text("");
		return false;
	} else if(!( reg.test( name.val() ) ) ) {
		$("#error-name").text("Seuls les caractères alphanumériques et .- sont autorisés.");
		name.addClass("form-error-inputs");
        return false;
	} else if(name.val().length > 200) {
		$("#error-name").text("200 caractères au maximum et 1 au minimum.");
		name.addClass("form-error-inputs");
        return false;
	} else {
		$("#error-name").text("");
        return true;
	}
}

function validateObjet () {
    reg = new RegExp("^[a-zA-Z0-9-.':()ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ ]+$","gi");
	objet.removeClass("form-error-inputs");
	if(objet.val()=="") {
		$("#error-objet").text("");
		return false;
	} else if(!( reg.test( objet.val() ) ) ) {
		$("#error-objet").text("Seuls les caractères alphanumériques et ':.- sont autorisés.");
		objet.addClass("form-error-inputs");
        return false;
	} else if(objet.val().length > 250) {
		$("#error-objet").text("250 caractères au maximum et 1 au minimum.");
		objet.addClass("form-error-inputs");
        return false;
	} else {
		$("#error-objet").text("");
        return true;
	}
}

function validateEmail () {
	reg = new RegExp("^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$","gi");
    email.removeClass("form-error-inputs");
	if(email.val()=="") {
		$("#error-email").text("");
		return false;
	} else if(!( reg.test( email.val() ) ) ) {
		$("#error-email").text("Seul un email valide est autorisé");
		email.addClass("form-error-inputs");
        return false;
	} else if(email.val().length > 100) {
		$("#error-email").text("100 caractères au maximum et 1 au minimum.");
		email.addClass("form-error-inputs");
        return false;
	} else {
		$("#error-email").text("");
        return true;
	}
}

function validateMessage () {
	message.removeClass("form-error-inputs");
	if(message.val()=="") {
		$("#error-message").text("");
		return false;
	} else if(message.val().length > 10000) {
		$("#error-message").text("10000 caractères au maximum et 1 au minimum.");
		message.addClass("form-error-inputs");
        return false;
	} else {
		$("#error-message").text("");
        return true;
	}
}

function validateForm() {
	if(validateName()&&validateFirm()&&validateEmail()&&validateObjet()&&validateMessage()) {
		$("#email-button").removeAttr("disabled");
	} else {
		$("#email-button").attr("disabled","disabled");
	}
}

$(name).keyup(function () {validateForm();});
$(organisme).keyup(function () {validateForm();});
$(email).blur(function () {validateForm();});
$(objet).keyup(function () {validateForm();});
$(message).keyup(function () {validateForm();});
});
