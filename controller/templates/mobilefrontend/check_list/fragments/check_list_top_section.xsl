<xsl:template name="check_list_top_section" xmlns:php="http://php.net/xsl">
	<xsl:param name="active_tab" />
	<xsl:variable name="session_url">
		<xsl:text>&amp;</xsl:text>
		<xsl:value-of select="php:function('get_phpgw_session_url')" />
	</xsl:variable>

	<!-- ==================  CHECKLIST DETAILS INFO  ========================= -->
	<div id="check-list-heading">
		<div class="box-1">
			<h1>Kontroll: <xsl:value-of select="control/title"/></h1>

			<xsl:if test="last_completed_checklist_date != ''">
				<xsl:value-of select="php:function('lang', 'last inspection')" />
				<xsl:text>: </xsl:text>
				<xsl:value-of select="last_completed_checklist_date"/>
			</xsl:if>

			<xsl:choose>
				<xsl:when test="type = 'component'">
					<h2>
						<xsl:value-of select="component_array/xml_short_desc"/>
					</h2>
					<button id = "submit_parent_component" type="button" class="mb-3 btn btn-info btn-block" onclick="show_parent_component_information({component_array/location_id}, {component_array/id});">
						<xsl:value-of select="php:function('lang', 'details')" />
					</button>
					<div id = "form_parent_component_2"></div>
				</xsl:when>
				<xsl:otherwise>
					<xsl:choose>
						<xsl:when test="location_level = 1">
							<h2>Eiendom: <xsl:value-of select="location_array/loc1_name"/></h2>
						</xsl:when>
						<xsl:otherwise>
							<h2>Bygg: <xsl:value-of select="location_array/loc2_name"/></h2>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:otherwise>
			</xsl:choose>
		</div>
		<!-- ==================  CHECKLIST TAB MENU  ===================== -->
		<nav class="navbar bg-light navbar-light">

			<!-- LOGO -->
			<a class="navbar-brand" href="#" data-toggle="collapse" data-target="#collapsibleNavbar">
				<xsl:value-of select="php:function('lang', $active_tab)"/>
			</a>
			<button class="navbar-toggler collapsed" type="button" data-toggle="collapse" data-target="#collapsibleNavbar" aria-expanded="false">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="navbar-collapse collapse" id="collapsibleNavbar" style="">

				<ul class="navbar-nav">
					<xsl:choose>
						<xsl:when test="count(check_list_type) = 0 or check_list_type != 'add_check_list'">
							<xsl:call-template name="check_list_menu">
								<xsl:with-param name="active_tab">
									<xsl:value-of select="$active_tab" />
								</xsl:with-param>
							</xsl:call-template>
							<xsl:call-template name="nav_control_plan" />
						</xsl:when>
						<xsl:otherwise>
							<xsl:call-template name="add_check_list_menu" />
							<xsl:call-template name="nav_control_plan" />
						</xsl:otherwise>
					</xsl:choose>
				</ul>
			</div>
		</nav>


		<!-- ==================  CHANGE STATUS FOR CHECKLIST  ===================== -->
		<xsl:choose>
			<xsl:when test="check_list/id != 0 and $active_tab != 'view_details'">
				<div class="box-2 select-box">
					<xsl:variable name="action_url">
						<xsl:value-of select="php:function('get_phpgw_link', '/index.php', 'menuaction:controller.uicheck_list.update_status,phpgw_return_as:json')" />
					</xsl:variable>
					<form id="update-check-list-status" class="done" action="{$action_url}" method="post">
						<input type="hidden" name="check_list_id" value="{check_list/id}" />
						<xsl:choose>
							<xsl:when test="check_list/status = 0">
								<input id='update-check-list-status-value' type="hidden" name="status" value="1" />
								<input id="status_submit" type="submit" class="mt-3 btn btn-success btn-block">
									<xsl:attribute name="value">
										<xsl:value-of select="php:function('lang', 'set status: done')" />
									</xsl:attribute>
								</input>
							</xsl:when>
							<xsl:otherwise>
								<input id='update-check-list-status-value' type="hidden" name="status" value="0" />
								<input type="submit" class="mt-3 btn btn-success btn-block">
									<xsl:attribute name="value">
										<xsl:value-of select="php:function('lang', 'is_executed')" />
									</xsl:attribute>
								</input>
							</xsl:otherwise>
						</xsl:choose>
					</form>
				</div>
			</xsl:when>
		</xsl:choose>
	</div>

</xsl:template>
