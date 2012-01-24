<!-- $Id$ -->
<xsl:template name="tab_view_check_lists" xmlns:php="http://php.net/xsl">
<xsl:variable name="date_format">d/m-Y</xsl:variable>

<div class="yui-content tab_content">
		
	  <!-- ===========================  SHOWS CONTROL ITEMS RECEIPT   =============================== -->
		<xsl:variable name="control_id"><xsl:value-of select="control_id"/></xsl:variable>	
		<input type="hidden" id="control_id" name="control_id" value="{control_id}" />
		
		<fieldset class="tab_check_list_details">
			<label>Startdato</label>
			<xsl:if test="control_as_array/start_date != ''">
				<xsl:value-of select="php:function('date', $date_format, number(control_as_array/start_date))"/><br/>
			</xsl:if>
			<label>Sluttdato</label>
			<xsl:if test="control_as_array/end_date != ''">
				<xsl:value-of select="php:function('date', $date_format, number(control_as_array/end_date))"/><br/>
			</xsl:if>
			<label>Syklustype</label><xsl:value-of select="control_as_array/repeat_type"/><br/>
			<label>Syklusfrekvens</label><xsl:value-of select="control_as_array/repeat_interval"/><br/>
		</fieldset>
		
		<ul class="check_list">
			<li class="heading">
				<div class="status">Status</div>
				<div>Skal utføres innen dato</div>
				<div>Planlagt utført dato</div>
				<div>Ble utført dato</div>
				<div>Kommentar</div>
			</li>
			<xsl:choose>
				<xsl:when test="check_list_array/child::node()">
					<xsl:for-each select="check_list_array">
						<li>
						   <div class="order_nr"><xsl:number/>.</div>
						   <div class="status">
						   	 <xsl:variable name="status"><xsl:value-of select="status"/></xsl:variable>	
							 <xsl:choose>
								<xsl:when test="status = 1">
									<img height="15" src="controller/images/status_icon_light_green.png" />	
								</xsl:when>
								<xsl:otherwise>
									<img height="15" src="controller/images/status_icon_red.png" />
								</xsl:otherwise>
							</xsl:choose>
						   </div>
						   <div>
							   <a>
									<xsl:attribute name="href">
										<xsl:text>index.php?menuaction=controller.uicheck_list_for_location.view_check_list</xsl:text>
										<xsl:text>&amp;check_list_id=</xsl:text>
											<xsl:value-of select="id"/>
									</xsl:attribute>
									<xsl:if test="deadline != ''">
						  				<xsl:value-of select="php:function('date', $date_format, number(deadline))"/>
						  			</xsl:if>
								</a>	
							</div>
						   <div>
						  		<xsl:if test="planned_date != ''">
						  			<xsl:value-of select="php:function('date', $date_format, number(planned_date))"/>
						  		</xsl:if>  		
						   </div>
						   <div>
						   		<xsl:if test="completed_date != ''">
						  			<xsl:value-of select="php:function('date', $date_format, number(completed_date))"/>
						  		</xsl:if>
						   </div>
						   <div><xsl:value-of select="comment"/></div>
						</li>
					</xsl:for-each>
				</xsl:when>
				<xsl:otherwise>
					Ingen sjekklister for denne kontrollen
				</xsl:otherwise>
			</xsl:choose>
		</ul>
</div>
</xsl:template>
