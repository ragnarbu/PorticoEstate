$(document).ready(function ()
{

	// EDIT COMPONENT
	show_parent_component_information = function (location_id,  component_id)
	{
		var x = document.getElementById("form_parent_component_2");

		var y = document.getElementById("new_picture_parent");
		if (x.style.display === "block")
		{
			x.style.display = "none";
			y.style.display = "none";
		}
		else
		{
			x.style.display = "block";
			y.style.display = "block";
		}

		var oArgs = {
			menuaction: 'controller.uicase.edit_parent_component',
			location_id: location_id,
			component_id: component_id,
			get_info: 1
		};
		var requestUrl = phpGWLink('index.php', oArgs, true);

		$.ajax({
			type: 'POST',
			url: requestUrl,
			success: function (data)
			{
				if (data)
				{
					$("#form_parent_component_2").html(data.html);
					var script = document.createElement("script");
					script.textContent = data.lookup_functions;
					document.head.appendChild(script);
					show_component_parent_picture(location_id + '_' + component_id);
				}
			}
		});
	};

	get_parent_component_edit_form = function ()
	{
		var location_id = $('input[name=location_id]')[0];
		var component_id = $('input[name=component_id]')[0];

		var oArgs = {
			menuaction: 'controller.uicase.edit_parent_component',
			location_id: $(location_id).val(),
			component_id: $(component_id).val(),
			get_edit_form: 1
		};
		var requestUrl = phpGWLink('index.php', oArgs, true);

		$.ajax({
			type: 'POST',
			url: requestUrl,
			success: function (data)
			{
				if (data)
				{
					$("#form_parent_component_2").html(data.html);
					var script = document.createElement("script");
					script.textContent = data.lookup_functions;
					document.head.appendChild(script);
				}
			}
		});
	};

	remove_component_form = function (form)
	{
		var edit_parent = $(form).find("input[name=edit_parent]").val();

		if(edit_parent == 1)
		{
			$("#form_parent_component_2").html('');
			$("#form_parent_component_2").hide();
		}
		else
		{
			$("#form_new_component_2").html('');
		}
	};

	submitComponentForm = function (e, form)
	{
		var edit_parent = $(form).find("input[name=edit_parent]").val();

		e.preventDefault();
		var requestUrl = $(form).attr("action");

		var inputs = form.getElementsByTagName("input"), input = null, flag = true;
		for (var i = 0, len = inputs.length; i < len; i++)
		{
			input = inputs[i];

			if ($(input).attr("data-validation") == "required")
			{
				if (!input.value)
				{
					$(input).addClass('error');
					$(input).attr("style", 'border-color: rgb(185, 74, 72);');
					$(input).focus();
					flag = false;
				}
				else
				{
					$(input).removeClass('error');
					$(input).removeAttr("style");
					$(input).addClass('valid');
				}
			}
		}

		if (!flag)
		{
			return false;
		}

		$.ajax({
			type: 'POST',
			url: requestUrl,
			data: $(form).serialize(),
			success: function (data)
			{
				if (data.status == "saved")
				{
					$("#choose-child-on-component").empty();

					var component_children = data.component_children;

					$.each(component_children, function (i, val)
					{
						$('#choose-child-on-component').append($('<option>', {
							value: val.location_id + '_' + val.id,
							text: val.short_description
						}));
					});
				}
				if(edit_parent == 1)
				{
					$("#form_parent_component_2").html(data.message);
					$("#form_parent_component_2").hide(2000);
				}
				else
				{
					$("#form_new_component_2").html(data.message);
					$('#equipment_picture_container').html('');
					$("#new_picture").hide();
					$("#view_cases").hide();
				}
			}
		});

		return false;
	};

});