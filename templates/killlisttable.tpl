{section name=day loop=$killlist}
    {if $daybreak}
<div class="kb-date-header">{"l, F jS"|date:$killlist[day].date}</div><br/>
    {/if}
<table class="kb-table" width="99%" align="center" cellspacing="1">
    <tr class="kb-table-header">
        <td class="kb-table-header" colspan="2" align="center">Ship type</td>
        <td class="kb-table-header">Victim</td>
        <td class="kb-table-header">Final blow</td>
        <td class="kb-table-header" align="center">System</td>
        <td class="kb-table-header" align="center">Time</td>
    {if $comments_count}
        <td class="kb-table-header" align="center"><img src="{$img_url}/comment{$comment_white}.gif"></td>
    {/if}
    </tr>
    {cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
    {section name=kill loop=$killlist[day].kills}
        {assign var="k" value=$killlist[day].kills[kill]}
    <tr class="{cycle advance=false name=ccl}" onmouseout="this.className='{cycle name=ccl}';" style="height: 34px; cursor: pointer;"
            onmouseover="this.className='kb-table-row-hover';" onClick="window.location.href='?a=kill_detail&kll_id={$k.id}';">
        <td width="32" align="center"><img src="{$k.victimshipimage}" border="0" width="32" heigth="32"></td>
        <td height="34" width=150 valign="middle"><div class="kb-shiptype"><b>{$k.victimshipname}</b><br>{$k.victimshipclass}</div><div class="kb-shipicon"><img src="{$k.victimshipindicator}" border="0"></div></td>
        <td width="200" class="kb-table-cell"><b>{$k.victim}</b><br/>{$k.victimcorp|truncate:30}</td>
        <td width="200" class="kb-table-cell"><b>{$k.fb}</b><br>{$k.fbcorp|truncate:30}</td>
        <td width="110" class="kb-table-cell" align="center"><b>{$k.system|truncate:10}</b><br/>({$k.systemsecurity|max:0|string_format:"%01.1f"})</td>
        {if $daybreak}
        <td class="kb-table-cell" align="center"><b>{$k.timestamp|date_format:"%H:%M"}</b></td>
        {else}
        <td class="kb-table-cell" align="center" width=80><b>{$k.timestamp|date_format:"%Y-%m-%d"}<br>{$k.timestamp|date_format:"%H:%M"}</b></td>
        {/if}
        {if $comments_count}
        <td width="10" class="kb-table-cell" align="center"><b>{$k.commentcount}</b></td>
        {/if}
    </tr>
    {/section}
</table>
{sectionelse}
<p>No data.
{/section}