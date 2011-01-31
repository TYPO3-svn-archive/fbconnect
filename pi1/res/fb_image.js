/**
 * Routine replacing all images that are supposed to be replaced with the profile picture.
 *
 * Usage: <span class="fbReplaceImage" fbSize="large" fbWidth="20" fbHeight="20" fbUser="123456789"><img /></span>
 *			fbSize can be large/square/small
 * 			fbWidth/fbHeight will be applied to the img
 *			fbCreateImg -> overwrite, before, after, top, bottom -> insert the new img tag (if no img tag was found) at given position
 */
document.observe('dom:loaded',
	function () {
		$$('.fbReplaceImage').each(
			fbReplaceImageReplace
		);
	}
);

function fbReplaceImageReplace (n) {
	// read the information necessary
	if (!n.readAttribute('fbUser')) return;
	var user = n.readAttribute('fbUser');
	var size = n.readAttribute('fbSize');
	if (!size) size = 'square';
	var width = n.readAttribute('fbWidth');
	var height = n.readAttribute('fbHeight');
	
	var updateImg = function (img) {
		img.src = 'https://graph.facebook.com/' + user + '/picture?type=' + size;
		
		if (width) img.width = width;
		if (height) img.height = height;
		hasImg = true;
	};
	
	var hasImg = false;
	// replace all images
	n.select('img').each(updateImg);
	
	// no image found -> create one
	if (!hasImg && n.readAttribute('fbCreateImg')) {
		var mode = n.readAttribute('fbCreateImg');
		
		if (mode == 'overwrite') {
			n.innerHTML = '';
			mode = 'top';
		}
		
		var modes = {top: 1, bottom: 1, before: 1, after: 1};
		
		if (modes[mode]) {
			var insArr = {};
			insArr[mode] = '<img />';
			n.insert(insArr);
			updateImg(n.down('img'));
		}
	}
}
