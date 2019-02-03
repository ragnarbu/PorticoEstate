this.confirm_session = function (action)
{
	if (action == 'save' || action == 'apply')
	{
		var conf = {
			modules: 'location, date, security, file',
			validateOnBlur: false,
			scrollToTopOnError: true,
			errorMessagePosition: 'top',
			language: validateLanguage
		};
		var test = $('form').isValid(validateLanguage, conf);
		if (!test)
		{
			return;
		}
	}

	var oArgs = {menuaction: 'property.bocommon.confirm_session'};
	var strURL = phpGWLink('index.php', oArgs, true);

	$.ajax({
		type: 'POST',
		dataType: 'json',
		url: strURL,
		success: function (data)
		{
			if (data != null)
			{
				if (data['sessionExpired'] == true)
				{
					window.alert('sessionExpired - please log in');
					JqueryPortico.lightboxlogin();//defined in common.js
				}
				else
				{
					document.getElementById(action).value = 1;
//					var canvas = document.getElementById("my_canvas");
//					var image_data = canvas.toDataURL('image/png');
//					$('#pasted_image').val(image_data);

					try
					{
						validate_submit();
					}
					catch (e)
					{
						document.form.submit();
					}
				}
			}
		},
		failure: function (o)
		{
			window.alert('failure - try again - once');
		},
		timeout: 5000
	});
};
$(document).ready(function ()
{

	$('#group_id').attr("data-validation", "assigned").attr("data-validation-error-msg", lang['Please select a person or a group to handle the ticket !']);
	$('#user_id').attr("data-validation", "assigned").attr("data-validation-error-msg", lang['Please select a person or a group to handle the ticket !']);

	var conf_on_load = {
			modules: 'date, file',
			validateOnBlur: true,
			scrollToTopOnError: false,
			errorMessagePosition: 'inline'
		   };

	setTimeout(function ()
	{
		$('form').isValid(validateLanguage, conf_on_load, true);
	}, 500);


});

$.formUtils.addValidator({
	name: 'assigned',
	validatorFunction: function (value, $el, config, languaje, $form)
	{
		var v = false;
		var group_id = $('#group_id').val();
		var user_id = $('#user_id').val();
		if (group_id != "" || user_id != "")
		{
			v = true;
		}
		return v;
	},
	errorMessage: 'Assigned is required',
	errorMessageKey: ''
});

upload_canvas = function ()
{
	var canvas = document.getElementById("my_canvas");
	var image_data = canvas.toDataURL('image/png');
	$('#pasted_image').val(image_data);
	$('#pasted_image_is_blank').val(0);
//	confirm_session('apply');
}

$(document).ready(function ()
{

	var CLIPBOARD = new CLIPBOARD_CLASS("my_canvas", true);

	/**
	 * image pasting into canvas
	 *
	 * @param {string} canvas_id - canvas id
	 * @param {boolean} autoresize - if canvas will be resized
	 */
	function CLIPBOARD_CLASS(canvas_id, autoresize)
	{
		var _self = this;
		var canvas = document.getElementById(canvas_id);
		var ctx = document.getElementById(canvas_id).getContext("2d");
		var ctrl_pressed = false;
		var command_pressed = false;
		var paste_event_support;
		var pasteCatcher;

		//handlers
		document.addEventListener('keydown', function (e)
		{
			_self.on_keyboard_action(e);
		}, false); //firefox fix
		document.addEventListener('keyup', function (e)
		{
			_self.on_keyboardup_action(e);
		}, false); //firefox fix
		document.addEventListener('paste', function (e)
		{
			_self.paste_auto(e);
		}, false); //official paste handler

		//constructor - we ignore security checks here
		this.init = function ()
		{
			pasteCatcher = document.createElement("div");
			pasteCatcher.setAttribute("id", "paste_ff");
			pasteCatcher.setAttribute("contenteditable", "");
			pasteCatcher.style.cssText = 'opacity:0;position:fixed;top:0px;left:0px;width:10px;margin-left:-20px;';
			document.body.appendChild(pasteCatcher);

			// create an observer instance
			var observer = new MutationObserver(function (mutations)
			{
				mutations.forEach(function (mutation)
				{
					if (paste_event_support === true || ctrl_pressed == false || mutation.type != 'childList')
					{
						//we already got data in paste_auto()
						return true;
					}

					//if paste handle failed - capture pasted object manually
					if (mutation.addedNodes.length == 1)
					{
						if (mutation.addedNodes[0].src != undefined)
						{
							//image
							_self.paste_createImage(mutation.addedNodes[0].src);
						}
						//register cleanup after some time.
						setTimeout(function ()
						{
							pasteCatcher.innerHTML = '';
						}, 20);
					}
				});
			});
			var target = document.getElementById('paste_ff');
			var config = {attributes: true, childList: true, characterData: true};
			observer.observe(target, config);
		}();
		//default paste action
		this.paste_auto = function (e)
		{
			paste_event_support = false;
			if (pasteCatcher != undefined)
			{
				pasteCatcher.innerHTML = '';
			}
			if (e.clipboardData)
			{
				var items = e.clipboardData.items;
				if (items)
				{
					paste_event_support = true;
					//access data directly
					for (var i = 0; i < items.length; i++)
					{
						if (items[i].type.indexOf("image") !== -1)
						{
							//image
							var blob = items[i].getAsFile();
							var URLObj = window.URL || window.webkitURL;
							var source = URLObj.createObjectURL(blob);
							this.paste_createImage(source);
						}
					}
			//		e.preventDefault();
				}
				else
				{
					//wait for DOMSubtreeModified event
					//https://bugzilla.mozilla.org/show_bug.cgi?id=891247
				}
			}
		};
		//on keyboard press
		this.on_keyboard_action = function (event)
		{
			k = event.keyCode;
			//ctrl
			if (k == 17 || event.metaKey || event.ctrlKey)
			{
				if (ctrl_pressed == false)
					ctrl_pressed = true;
			}
			//v
			if (k == 86)
			{
				if (document.activeElement != undefined && document.activeElement.type == 'text')
				{
					//let user paste into some input
					return false;
				}

				if (ctrl_pressed == true && pasteCatcher != undefined)
				{
					pasteCatcher.focus();
				}
			}
		};
		//on kaybord release
		this.on_keyboardup_action = function (event)
		{
			//ctrl
			if (event.ctrlKey == false && ctrl_pressed == true)
			{
				ctrl_pressed = false;
			}
			//command
			else if (event.metaKey == false && command_pressed == true)
			{
				command_pressed = false;
				ctrl_pressed = false;
			}
		};
		//draw pasted image to canvas
		this.paste_createImage = function (source)
		{
			var pastedImage = new Image();
			pastedImage.onload = function ()
			{
				if (autoresize == true)
				{
					//resize
					canvas.width = pastedImage.width;
					canvas.height = pastedImage.height;
				}
				else
				{
					//clear canvas
					ctx.clearRect(0, 0, canvas.width, canvas.height);
				}
				ctx.drawImage(pastedImage, 0, 0);
			};
			pastedImage.src = source;
			setTimeout(function ()
			{
				upload_canvas();
			}, 500);
		};
	}

});

var oArgs = {menuaction: 'helpdesk.uitts.get_reverse_assignee'};
var strURL = phpGWLink('index.php', oArgs, true);
JqueryPortico.autocompleteHelper(strURL, 'set_user_name', 'set_user_id', 'set_user_container');