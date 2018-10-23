<xsl:template match="data" xmlns:php="http://php.net/xsl">

<div id="event-edit-page-content" class="margin-top-content">
        	<div class="container wrapper">
				<div class="location">
					<span>
						<a><xsl:attribute name="href">
								<xsl:value-of select="php:function('get_phpgw_link', '/bookingfrontend/index.php', 'menuaction:bookingfrontend.uisearch.index')"/>
							</xsl:attribute>
							<xsl:value-of select="php:function('lang', 'Home')" />
						</a>
					</span>
					<span><xsl:value-of select="php:function('lang', 'Edit Events')" /></span>
					<span>#<xsl:value-of select="event/id"/></span>
															
				</div>

            	<div class="row">					

					<form action="" method="POST" id="event_form" name="form" class="col-md-8">

						<div class="col mb-4">
							<xsl:call-template name="msgbox"/>
						</div>

						<dt class="heading mt-4 mb-4">
							<xsl:value-of select="php:function('lang', 'Why')" />
						</dt>

						<div class="col-12">
							<div class="form-group">
								<label for="field_activity">
									<xsl:value-of select="php:function('lang', 'Activity')" />
								</label>
								<select name="activity_id" class="form-control" id="field_activity">
										<xsl:attribute name="data-validation">
											<xsl:text>required</xsl:text>
										</xsl:attribute>
										<xsl:attribute name="data-validation-error-msg">
											<xsl:value-of select="php:function('lang', 'Please select an activity')" />
										</xsl:attribute>
										<option value="">
											<xsl:value-of select="php:function('lang', '-- select an activity --')" />
										</option>
										<xsl:for-each select="activities">
											<option>
												<xsl:if test="../event/activity_id = id">
													<xsl:attribute name="selected">selected</xsl:attribute>
												</xsl:if>
												<xsl:attribute name="value">
													<xsl:value-of select="id"/>
												</xsl:attribute>
												<xsl:value-of select="name"/>
											</option>
										</xsl:for-each>
								</select>
							</div>
						</div>

						<div class="col-12">
							<div class="form-group">
								<label class="text-uppercase"><xsl:value-of select="php:function('lang', 'Event type')"/></label>
								<select id="field_public" class="form-control" name="is_public">
									<option value="1">
										<xsl:if test="event/is_public=1">
											<xsl:attribute name="selected">checked</xsl:attribute>
										</xsl:if>
										<xsl:value-of select="php:function('lang', 'Public event')"/>
									</option>
									<option value="0">
										<xsl:if test="event/is_public=0">
											<xsl:attribute name="selected">checked</xsl:attribute>
										</xsl:if>
										<xsl:value-of select="php:function('lang', 'Private event')"/>
									</option>
								</select>
							</div>
						</div>

						<div class="col-12">
							<div class="form-group">
								<label class="text-uppercase"><xsl:value-of select="php:function('lang', 'Description')" /></label>
								<textarea id="field_description" class="form-control" name="description">
									<xsl:attribute name="data-validation">
										<xsl:text>required</xsl:text>
									</xsl:attribute>
									<xsl:attribute name="data-validation-error-msg">
										<xsl:value-of select="php:function('lang', 'Please enter a description')" />
									</xsl:attribute>
									<xsl:value-of select="event/description"/>
								</textarea>
							</div>
						</div>

						<dt class="heading mt-4 mb-4">
							<xsl:value-of select="php:function('lang', 'Where')" />
						</dt>

						<div class="col-12">
							<div class="form-group">
								<label class="text-uppercase"><xsl:value-of select="php:function('lang', 'Building')" /></label>
								<div class="autocomplete">
									<input id="field_building_id" class="form-control" name="building_id" type="hidden">
										<xsl:attribute name="data-validation">
											<xsl:text>required</xsl:text>
										</xsl:attribute>
										<xsl:attribute name="data-validation-error-msg">
											<xsl:value-of select="php:function('lang', 'Please enter a building')" />
										</xsl:attribute>
										<xsl:attribute name="value">
											<xsl:value-of select="event/building_id"/>
										</xsl:attribute>
									</input>
									<input id="field_building_name" class="form-control" name="building_name" type="text">
										<xsl:attribute name="data-validation">
											<xsl:text>required</xsl:text>
										</xsl:attribute>
										<xsl:attribute name="data-validation-error-msg">
											<xsl:value-of select="php:function('lang', 'Please enter a building')" />
										</xsl:attribute>
										<xsl:attribute name="value">
											<xsl:value-of select="event/building_name"/>
										</xsl:attribute>
									</input>
									<div id="building_container"/>
								</div>
							</div>
						</div>

						<div class="col-12">
							<div class="form-group">
								<label class="text-uppercase"><xsl:value-of select="php:function('lang', 'Resources')" /></label>
								<input type="hidden" class="form-control" data-validation="application_resources">
									<xsl:attribute name="data-validation-error-msg">
										<xsl:value-of select="php:function('lang', 'Please choose at least 1 resource')" />
									</xsl:attribute>
								</input>
								<div id="resources_container">
									<span class="select_first_text">
										<xsl:value-of select="php:function('lang', 'Select a building first')" />
									</span>
								</div>
							</div>
						</div>

						<dt class="heading mt-4 mb-4">
							<xsl:value-of select="php:function('lang', 'When')" />
						</dt>

						<div class="col-12">
							<div class="form-group">
								<label class="text-uppercase"><xsl:value-of select="php:function('lang', 'From')" /></label>
								<xsl:value-of select="event/from_"/>
								<br />
								<input name="org_from" class="form-control" type="hidden">
									<xsl:attribute name="value">
										<xsl:value-of select="event/from_"/>
									</xsl:attribute>
								</input>
								<!--div class="time-picker">
									<input id="field_from" name="from_" type="text">
										<xsl:attribute name="value"><xsl:value-of select="event/from_"/></xsl:attribute>
									</input>
								</div-->
								<input class="form-control from_" name="from_" type="text">
									<xsl:attribute name="data-validation">
										<xsl:text>required</xsl:text>
									</xsl:attribute>
									<xsl:attribute name="data-validation-error-msg">
										<xsl:value-of select="php:function('lang', 'Please enter a from date')" />
									</xsl:attribute>
									<xsl:if test="event/from_ != ''">
										<xsl:attribute name="value">
											<xsl:value-of select="event/from_2" />
										</xsl:attribute>
									</xsl:if>
								</input>
							</div>
						</div>


						<div class="col-12">
							<div class="form-group">
								<label class="text-uppercase"><xsl:value-of select="php:function('lang', 'To')" /></label>
								<xsl:value-of select="event/to_"/>
								<br />
								<input name="org_to" class="form-control" type="hidden">
									<xsl:attribute name="value">
										<xsl:value-of select="event/to_"/>
									</xsl:attribute>
								</input>
								<!--div class="time-picker">
									<input id="field_to" name="to_" type="text">
										<xsl:attribute name="value"><xsl:value-of select="event/to_"/></xsl:attribute>
									</input>
								</div-->
								<input class="form-control to_" name="to_" type="text">
									<xsl:attribute name="data-validation">
										<xsl:text>required</xsl:text>
									</xsl:attribute>
									<xsl:attribute name="data-validation-error-msg">
										<xsl:value-of select="php:function('lang', 'Please enter an end date')" />
									</xsl:attribute>
									<xsl:if test="event/to_ != ''">
										<xsl:attribute name="value">
											<xsl:value-of select="event/to_2" />
										</xsl:attribute>
									</xsl:if>
								</input>
							</div>
						</div>

						<dt class="heading mt-4 mb-4">
							<xsl:value-of select="php:function('lang', 'Who')" />
						</dt>

						<div class="col-12">
							<div class="form-group">
								<label class="text-uppercase"><xsl:value-of select="php:function('lang', 'Target audience')" /></label>
								<input type="hidden" class="form-control" data-validation="target_audience">
									<xsl:attribute name="data-validation-error-msg">
										<xsl:value-of select="php:function('lang', 'Please choose at least 1 target audience')" />
									</xsl:attribute>
								</input>
								<ul id="audience">
									<xsl:for-each select="audience">
										<li>
											<input type="radio" class="form-control" name="audience[]">
												<xsl:attribute name="value">
													<xsl:value-of select="id"/>
												</xsl:attribute>
												<xsl:if test="../event/audience=id">
													<xsl:attribute name="checked">checked</xsl:attribute>
												</xsl:if>
											</input>
											<label>
												<xsl:value-of select="name"/>
											</label>
										</li>
									</xsl:for-each>
								</ul>
							</div>
						</div>

						<div class="col-12">
							<div class="form-group">
								<label class="text-uppercase"><xsl:value-of select="php:function('lang', 'Number of participants')" /></label>
								<input type="hidden" class="form-control" data-validation="number_participants">
									<xsl:attribute name="data-validation-error-msg">
										<xsl:value-of select="php:function('lang', 'Number of participants is required')" />
									</xsl:attribute>
								</input>
								<table id="agegroup" class="pure-table pure-table-bordered">
									<thead>
										<tr>
											<th/>
											<th>
												<xsl:value-of select="php:function('lang', 'Male')" />
											</th>
											<th>
												<xsl:value-of select="php:function('lang', 'Female')" />
											</th>
										</tr>
									</thead>
									<tbody id="agegroup_tbody">
										<xsl:for-each select="agegroups">
											<xsl:variable name="id">
												<xsl:value-of select="id"/>
											</xsl:variable>
											<tr>
												<th>
													<xsl:value-of select="name"/>
												</th>
												<td>
													<input type="text" class="form-control" size="4">
														<xsl:attribute name="name">male[<xsl:value-of select="id"/>]</xsl:attribute>
														<xsl:attribute name="value">
															<xsl:value-of select="../event/agegroups/male[../agegroup_id = $id]"/>
														</xsl:attribute>
													</input>
												</td>
												<td>
													<input type="text" class="form-control" size="4">
														<xsl:attribute name="name">female[<xsl:value-of select="id"/>]</xsl:attribute>
														<xsl:attribute name="value">
															<xsl:value-of select="../event/agegroups/female[../agegroup_id = $id]"/>
														</xsl:attribute>
													</input>
												</td>
											</tr>
										</xsl:for-each>
									</tbody>
								</table>
							</div>
						</div>

						<dt class="heading mt-4 mb-4">
							<xsl:value-of select="php:function('lang', 'Contact information')" />
						</dt>

						<div class="col-12">
							<div class="form-group">
								<label class="text-uppercase"><xsl:value-of select="php:function('lang', 'Name')" /></label>
								<input id="field_contact_name" class="form-control" name="contact_name" type="text">
									<xsl:attribute name="data-validation">
										<xsl:text>required</xsl:text>
									</xsl:attribute>
									<xsl:attribute name="data-validation-error-msg">
										<xsl:value-of select="php:function('lang', 'Please enter a contact name')" />
									</xsl:attribute>
									<xsl:attribute name="value">
										<xsl:value-of select="event/contact_name"/>
									</xsl:attribute>
								</input>
							</div>
						</div>

						<div class="col-12">
							<div class="form-group">
								<label class="text-uppercase"><xsl:value-of select="php:function('lang', 'Email')" /></label>
								<input id="field_contact_mail" class="form-control" name="contact_email" type="text">
									<xsl:attribute name="value">
										<xsl:value-of select="event/contact_email"/>
									</xsl:attribute>
								</input>
							</div>
						</div>

						<div class="col-12">
							<div class="form-group">
								<label class="text-uppercase"><xsl:value-of select="php:function('lang', 'Phone')" /></label>
								<input id="field_contact_phone" class="form-control" name="contact_phone" type="text">
									<xsl:attribute name="value">
										<xsl:value-of select="event/contact_phone"/>
									</xsl:attribute>
								</input>
							</div>
						</div>

						<div class="col-12">
							<div class="form-group">
								<label class="text-uppercase"><xsl:value-of select="php:function('lang', 'Cost')" /></label>
								<input id="field_cost" class="form-control" name="cost" type="text" readonly="readonly">
									<xsl:attribute name="data-validation">
										<xsl:text>required</xsl:text>
									</xsl:attribute>
									<xsl:attribute name="data-validation-error-msg">
										<xsl:value-of select="php:function('lang', 'Please enter a cost')" />
									</xsl:attribute>
									<xsl:attribute name="value">
										<xsl:value-of select="event/cost"/>
									</xsl:attribute>
								</input>
							</div>
						</div>

						<dt class="heading mt-4 mb-4">
							<xsl:value-of select="php:function('lang', 'Invoice information')" />
						</dt>

						<div class="col-12">
							<div class="form-group">
								<!--<label class="text-uppercase"><xsl:value-of select="php:function('lang', 'Phone')" /></label>-->
								<xsl:copy-of select="phpgw:booking_customer_identifier(event, '')"/>
							</div>
						</div>


						<div class="col mt-5">
							<input type="submit" class="btn btn-light mr-4">
								<xsl:attribute name="value">
									<xsl:value-of select="php:function('lang', 'Save')"/>
								</xsl:attribute>
							</input>
							<a class="cancel">
								<xsl:attribute name="href">
									<xsl:value-of select="event/cancel_link"/>
								</xsl:attribute>
								<xsl:value-of select="php:function('lang', 'Cancel')" />
							</a>
						</div>
					</form>
            	</div>         
            
        	</div>
    	
	</div>
    <div class="push"></div>

	<script type="text/javascript">
		var initialSelection = <xsl:value-of select="event/resources_json" />;
		var lang = <xsl:value-of select="php:function('js_lang', 'Name', 'Resources Type')" />;
		YUI({ lang: 'nb-no' }).use(
			'aui-timepicker',
			function(Y) {
			new Y.TimePicker(
				{
				trigger: '.to_, .from_',
				popover: {
					zIndex: 99999
				},
				mask: '%H:%M',
				on: {
					selectionChange: function(event) { 
						new Date(event.newSelection);
						$(this).val(event.newSelection);
					}
				}
				}
			);
			}
		);
	</script>
</xsl:template>
