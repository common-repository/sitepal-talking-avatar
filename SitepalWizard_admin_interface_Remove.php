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

<table align="center">
	<tr>
		<td>
		<div align="center" style="width:360px;height:270">
		<br>
		<br>
			<div class="divContainer" >
				<span class="divLabel">Current scene in the post:</span>
				<div  style="width::200px;height:150px;" name="PreviewExistedScene" id="PreviewExistedScene">No existed scene in the post </div>
				<br>
				<input type="button" name="btn_RemoveScene" id="btn_RemoveScene" value="Remove" onclick="DoRemove()"/>
				<div name="RemovePageStatus" id="RemovePageStatus"></div>
			</div>
		</div>
		</td>
	</tr>
</table>