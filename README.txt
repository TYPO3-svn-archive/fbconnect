Allows Frontend user login using Facebook Connnect.

HOWTO:

Create a Facebook Application at http://www.facebook.com/developers/

Install this extension.
Include static template "Facebook Connect (fbconnect)" in page template.
Use Template => Constant Editor => FBCONNECT to enter user storage page, Facebook API key and Facebook secret.
Use the supplied template, or copy it to /fileadmin/, edit, change location in Constant Editor.
Insert plugin next to normal login.
Wrap normal logout-button in javascript - see top of template file.
