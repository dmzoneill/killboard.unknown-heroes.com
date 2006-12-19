<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1">
<title>{$kb_title}</title>
<link rel="stylesheet" type="text/css" href="{$style_url}/common.css">
<link rel="stylesheet" type="text/css" href="{$style_url}/{$style}/style.css">
<script language=javascript src="{$common_url}/generic.js"></script>

<style type="text/css">
</style>
</head>
<body bgcolor="#222222" {$on_load} style="height: 100%">
<div align="center" id="popup" style="display:none;
	position:absolute;
    top:217px; width:99%;
	z-index:3;
    padding: 5px;"></div>
	<table class="main-table" height="100%" align="center" bgcolor="#111111" border="0" cellspacing="1" style="height: 100%">
<tr style="height: 100%">
<td valign="top" height="100%" style="height: 100%">
<div id="header">
{if $banner_link}
<a href="{$banner_link}">
<img src="{$img_url}/logo/{$banner}.jpg" border="0">
</a>
{else}
<img src="{$img_url}/logo/{$banner}.jpg" border="0">
{/if}
</div>
{include file="menu.tpl"}
<div id="page-title">{$page_title}</div>
<table cellpadding="0" cellspacing="0" width="100%" border="0">
<tr><td valign="top"><div id="content">
{$content_html}
</div></td>
{if $context_html}
<td valign="top" align="right">
<div id="context">{$context_html}</div>
</td>
{/if}
</tr></table>
{if $profile}
<table class="kb-subtable" width="99%" border="0">
<tr><td height="100%" align="right" valign="bottom">{$profile_time}s</td></tr>
</table>
{else}
<!-- {$profile_time}s -->
{/if}
<div class="counter"></div>
</td></tr></table>
</body>
</html>