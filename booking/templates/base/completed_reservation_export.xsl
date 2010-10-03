<xsl:template match="data" xmlns:php="http://php.net/xsl">
	<div id="content">
		<ul class="pathway">
			<li><a href="{export/index_link}"><xsl:value-of select="php:function('lang', 'Invoice Data Exports')" /></a></li>
			<li><xsl:value-of select="export/id"/></li>
		</ul>
		
		<xsl:call-template name="msgbox"/>
		<xsl:call-template name="yui_booking_i18n"/>
		
		<dl class="proplist-col">
			<dt><xsl:value-of select="php:function('lang', 'Building')" /></dt>
			<dd><xsl:copy-of select="phpgw:booking_link(export/building_id)"/></dd>
			
			<dt><xsl:value-of select="php:function('lang', 'Season')" /></dt>
			<dd><xsl:copy-of select="phpgw:booking_link(export/season_id)"/></dd>
			
			<dt><xsl:value-of select="php:function('lang', 'Total Items')" /></dt>
			<dd><xsl:value-of select="export/total_items"/></dd>
			
			<dt><xsl:value-of select="php:function('lang', 'Total Cost')" /></dt>
			<dd><xsl:value-of select="export/total_cost"/></dd>
			
			<dt><xsl:value-of select="php:function('lang', 'Created')" /></dt>
			<dd><xsl:value-of select="export/created_on"/></dd>
			
			<dt><xsl:value-of select="php:function('lang', 'Created by')" /></dt>
			<dd><xsl:value-of select="export/created_by_name"/></dd>
		</dl>
		
		<!-- <div class="form-buttons">
			<button>
				<xsl:attribute name="onclick">window.location.href="<xsl:value-of select="export/download_link"/>"</xsl:attribute>
				<xsl:value-of select="php:function('lang', 'Download')" />
			</button>
		</div> -->
		
		<!--h4><xsl:value-of select="php:function('lang', 'Invoice Data Exports')" /></h4>
		<div id="completed_reservation_exports_container"/-->
	</div>
		
	<script type="text/javascript">
		var lang = <xsl:value-of select="php:function('js_lang', 'ID', 'Building', 'Season', 'From', 'To')"/>;
	</script>
</xsl:template>