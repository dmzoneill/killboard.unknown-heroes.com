{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
<table cellpadding=0 cellspacing=1 border=0>
    <tr>
        <td width=360 align=left valign=top><table class=kb-table width=360 cellpadding=0 cellspacing=1 border=0>
                <tr class= {cycle name=ccl}>
                    <td rowspan=3 width="64"><img src="{$VictimPortrait}" border="0" width="64" heigth="64"></td>
                    <td class=kb-table-cell width=64><b>Victim:</b></td>
                    <td class=kb-table-cell><b><a href="{$VictimURL}">{$VictimName}</a></b></td>
                </tr>
                <tr class={cycle name=ccl}>
                    <td class=kb-table-cell width=64><b>Corp:</b></td>
                    <td class=kb-table-cell><b><a href="{$VictimCorpURL}">{$VictimCorpName}</a></b></td>
                </tr>
                <tr class={cycle name=ccl}>
                    <td class=kb-table-cell width=64><b>Alliance:</b></td>
                    <td class=kb-table-cell><b><a href="{$VictimAllianceURL}">{$VictimAllianceName}</a></b></td>
                </tr>
            </table>
            <div class=block-header>Involved parties</div>
            <table class=kb-table width=360 border=0 cellspacing="1">
{foreach from=$involved key=key item=i}
                <tr class={cycle name=ccl}>
                    <td rowspan=5 width="64"><img {if $i.FB == "true"}class=finalblow{/if} src="{$i.portrait}" border="0"></td>
                    <td rowspan=5 width="64"><img  {if $i.FB == "true"}class=finalblow{/if} src="{$i.shipImage}" border="0"></td>
                    <td class=kb-table-cell style="padding-top: 1px; padding-bottom: 1px;"><a href="{$i.PilotURL}">{$i.PilotName}</a></td>
                </tr>
                <tr class={cycle name=ccl}>
                    <td class=kb-table-cell style="padding-top: 1px; padding-bottom: 1px;"><a href="{$i.CorpURL}">{$i.CorpName}</a></td>
                </tr>
                <tr class={cycle name=ccl}>
                    <td class=kb-table-cell style="padding-top: 1px; padding-bottom: 1px;"><a href="{$i.AlliURL}">{$i.AlliName}</a></td>
                </tr>
                <tr class={cycle name=ccl}>
                    <td class=kb-table-cell style="padding-top: 1px; padding-bottom: 1px;"><b>{$i.ShipName}</b></td>
                </tr>
                <tr class={cycle name=ccl}>
                    <td class=kb-table-cell style="padding-top: 1px; padding-bottom: 1px;">{$i.weaponName}</td>
                </tr>
{/foreach}
            </table>
{if $config->getConfig('comments')}{$comments}{/if}
        </td>
        <td width=50>&nbsp;</td>
        <td align=left valign=top width=360><table class=kb-table width=360 cellspacing="1">
                <tr class={cycle name=ccl}>
                    <td width="64" heigth="64" rowspan=3><img src="{$VictimShipImg}" width="64" heigth="64"></td>
                    <td class=kb-table-cell><b>Ship:</b></td>
                    <td class=kb-table-cell><b>{$ShipName}</b> ({$ClassName})</td>
                </tr>
                <tr class={cycle name=ccl}>
                    <td class=kb-table-cell><b>Location:</b></td>
                    <td class=kb-table-cell><b><a href="{$SystemURL}">{$System}</a></b> ({$SystemSecurity})</td>
                </tr>
                <tr class={cycle name=ccl}>
                    <td class=kb-table-cell><b>Date:</b></td>
                    <td class=kb-table-cell>{$TimeStamp}</td>
                </tr>
            </table>

            <div class="block-header">Ship details</div>
            <table class="kb-table" width="360" border="0" cellspacing="1">
{foreach from=$destroyed item=slot}
{* set to true to show empty slots *}
{if $slot.items}
                <tr class="kb-table-row-even">
                    <td class="item-icon" width="32"><img src="img/{$slot.img}" alt="{$slot.text}" border="0"></td>
                    <td colspan="2" class="kb-table-cell"><b>{$slot.text}</b> </td>
    {if $config->getConfig('item_values')}
                    <td align="center" class="kb-table-cell"><b>Value</b></td>
    {/if}
                </tr>
    {foreach from=$slot.items item=i}
                <tr class="kb-table-row-odd">
                    <td class="item-icon" width="32" height="34" valign="top">{$i.Icon}</td>
                    <td class="kb-table-cell">{$i.Name}</td>
                    <td width="30" align="center">{$i.Quantity}</td>
        {if $config->getConfig('item_values')}
                    <td align="center">{$i.Value}</td>
        {/if}
                </tr>
        {if $admin and $config->getConfig('item_values')}
                    <tr class="kb-table-row-even">
                      <form method="post" action="">
                        <td height="34" colspan="3" valign="top">
                            <div align="right">
                                Current single Item Value:
                                <input name="IID" value="{$i.itemID}" type="hidden">
                                <input name="{$i.itemID}" type="text" class="comment-button" value="{$i.single_unit}" size="6">
                            </div>
                        <td height="34" valign="top"><input type="submit" name="submit" value="Update" class="comment-button"></td>
                      </form>
                    </tr>
        {/if}
    {foreachelse}
                <tr class="kb-table-row-odd">
                    <td colspan="4" valign="top">No Items lost</td>
                </tr>
    {/foreach}
{/if}
{/foreach}
{if $item_values}
                <tr class={cycle name=ccl}>
                    <td colspan="3"><div align="right"><strong>Total Module Loss:</strong></div></td>
                    <td align="right">{$ItemValue}</td>
                </tr>
                <tr class={cycle name=ccl}>
                    <td colspan="3"><div align="right"><strong>Ship Loss:</strong></div></td>
                    <td align="right">{$ShipValue}</td>
                </tr>
                <tr class={cycle name=ccl}>
                    <td colspan="3"><div align="right"><strong>Total Loss:</strong></div></td>
                    <td align="right">{$TotalLoss}</td>
                </tr>
{/if}
            </table>
        </td>
    </tr>
</table>