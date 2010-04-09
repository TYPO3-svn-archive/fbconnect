function reload_page() {
	// Reload the page, so the plugin can perform auto-login of a frontend user
	window.location.reload();
} // End reload_page()

function submit_form(id) {
	var form = document.getElementById(id);
	form.submit();
	return false;
}