<style type="text/css">
				DIV.divContent {
					padding:			7px;
				}
				DIV.divContainer {
					border:				1px solid #A7A6AA;
					padding:			10px;
					padding-top:		0px;
					margin-top:			15px;
				}
				DIV.divContainer SPAN.divLabel {
					position:			relative;
					top:				-7px;
					left:				-5px;
					background:			WHITE;
					padding-left:		10px;
					padding-right:		10px;
					width: 				300px;
				}
				DIV.divContainer P {
					margin-top:			0px;
					margin-bottom:		12px;
				}
			</style>
			
			<table border="0" align="center" cellspacing="8" >
			<tr>
				<td valign="top">
					<div class="divContainer" style="height:58px;">
						<span class="divLabel">1. Select A Scene From The List Below:</span>
						<div align="center"><select id="demosceneSelect" name="demosceneSelect" onChange="PreviewDemoScene(this)"><option value='0'>Scene 1: Dana</option><option value='1'>Scene 2: Kyle</option><option value='2'>Scene 3: Rachel</option></select></div>
					</div>				</td>
				<td valign="top">
					<div class="divContainer" style="height:58px;">
						<span class="divLabel">2. Additional Options:</span>
						<div align="center">
						  <p align="left"><input type="checkbox" class="" value="1" name="SecureProtocol" id="SecureProtocol" disabled="disabled" >&nbsp;Secure Protocol (HTTPS)</p>
						  <p align="left"><input type="checkbox" class="" value="1" name="PlayOnLoad" id="PlayOnLoad" disabled="disabled" >&nbsp;Play on Load </p>
						</div>
					</div>				</td>
			</tr>
			<tr>
<!--		<td>				
				</td>-->			
			<td valign="top">
					<div style="height:220px;" class="divContainer">
						<span class="divLabel">3. Dimensions & Background Color:</span>
						<table>
							<tr>
								<td>
									Width:<input type="text" value="200" name="sceneWidth" id="sceneWidth" size="8" onBlur="onWidthChanged()" disabled="disabled">Height:<input type="text" value="150" name="sceneHeight" id="sceneHeight" size="8" onBlur="onHeightChanged()" disabled="disabled">								</td>
							</tr>
							<tr>
								<td>
									<input type="checkbox" class="" value="1" name="sceneMaintainAspectRatio" id="sceneMaintainAspectRatio" checked="checked" disabled="disabled" >Keep Scene Dimensions Proportional								</td>
							</tr>
							<tr>
								<td>
									<div align="center"><object id="bgcolorPalette" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" height="140" width="200"> 
							<param name="movie" value=<?php echo get_option('siteurl').$pluginPath.'bgcolor.swf'; ?> >
							<embed id="bgcolorPalette" src=<?php echo get_option('siteurl').$pluginPath.'bgcolor.swf'; ?> swliveconnect="true" width="200" height="140" name="bgcolorPalette"></embed></object></div>								</td>
							</tr>
						</table>
					</div>			</td>		
				<td>
					<div style="height:220px;" class="divContainer">
					<span class="divLabel">4. Scene Preview: </span>
					<div align="center" name="PreviewDemoEmbedScene" id="PreviewDemoEmbedScene" style="width:200px;" ></div>
					<br>
					<div align="center" style="width:200px;" ><input type="button" id="btn_AddRepDemoScene" value="Add To Post" onclick="AddDemoCodeToPost()" /></div>
				</div>	</td>
				<td valign="bottom"><a href="<?php echo get_option('siteurl').$pluginPath.'SitepalWizard_admin_interface_Login.php' ?>" style="color:#000000"  onclick="LoadTabPage('LoginPage'); return false;">Back to Intro</a></td>
			</tr>
			<tr align="center">
				<td colspan="2" align="center">
		<div style="font-size:18px;"><a style="text-decoration:none;border:0px" href="Javascript:PopUp" onClick="window.open('<?php echo get_option('siteurl').$pluginPath.'help/add_help.htm';?>', 'Help', 'height=700, width=660, scrollbars=1'); return false;"><img width="16" heigh="16" src="<?php echo get_option('siteurl').$pluginPath.'img/helpicon.jpg';?>"><span style="color:#ff9900;">Click For Help?</span></a></div>				</td>
			</tr>
		</table>

	
	