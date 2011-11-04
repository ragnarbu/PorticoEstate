<xsl:template match="data" name="view_check_list" xmlns:php="http://php.net/xsl">

<div id="main_content">
		
	  <!-- ===========================  SHOWS CONTROL ITEMS RECEIPT   =============================== -->
		
		<h1>Sjekkliste</h1>
		<fieldset class="control_details">
			<label>Tittel</label><xsl:value-of select="check_list/status"/><br/>
			<label>Startdato</label><xsl:value-of select="check_list/comment"/><br/>
			<label>Sluttdato</label><xsl:value-of select="check_list/deadline"/><br/>
		</fieldset>
				
		<h2>Sjekkpunkter</h2>
		<ul class="check_list">
			<li class="heading">
				<div class="status">Status</div>
				<div class="title">Tittel for kontrollpunkt</div>
				<div>Kommentar</div>
			</li>
			
			<xsl:choose>
				<xsl:when test="check_list/check_item_array/child::node()">
					<xsl:for-each select="check_list/check_item_array">
						<li>
					       <div class="order_nr"><xsl:number/>.</div>
					       <div class="status">
					       	 <xsl:variable name="status"><xsl:value-of select="status"/></xsl:variable>	
					         <xsl:choose>
								<xsl:when test="status = 1">
									<img height="15" src="controller/images/status_icon_light_green.png" />	
								</xsl:when>
							</xsl:choose>
					       </div>
					       <div class="title"><xsl:value-of select="control_item/title"/></div>
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