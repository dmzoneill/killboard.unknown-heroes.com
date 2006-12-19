<br/>
<div class="kb-kills-header">Battle Summary for {$system}, {$firstts|date_format:"%H:%M"} - {$lastts|date_format:"%H:%M"}</div>
<table border="0" width="100%" cellspacing="0" cellpadding="0">
<tr><td width="49%" valign="top">
<div class="kb-date-header">Friendly ({$friendlycnt})</div>
<br/>
<table class="kb-table" width="95%" align="center">
    <tr class="kb-table-header">
      <td class="kb-table-header" colspan="2" align="center">Ship/Pilot</td>
      <td class="kb-table-header" align="center" style="min-width: 45%; width: 45%; max-width: 45%;">Corp/Alliance</td>
    </tr>
{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
{foreach from=$pilots_a item=i key=pilot}
    <tr class="{cycle name=ccl}"{if is_destroyed($pilot)} style="background-color: #EE4444;"{/if}>
      <td width="32" height="32" style="max-width: 32px;">
{if is_destroyed($pilot)}
        <a href="?a=kill_detail&amp;kll_id={$kll_id}"><img src="{$i.spic}" width="32" height="32" border="0"></a>
{else}
        <img src="{$i.spic}" width="32" height="32" border="0">
{/if}
      </td>
{if podded($pilot) and $i.ship != 'Capsule'}
{if $config->getConfig('bs_podlink')}
      <td class="kb-table-cell">
        <b>{$i.name}&nbsp;<a href="?a=kill_detail&amp;kll_id={$pod_kll_id}">[Pod]</a></b><br/>{$i.ship}
      </td>
{else}
      <td class="kb-table-cell" style="background-image: url({$podpic}); background-repeat: no-repeat; background-position: right;">
        <b>{$i.name}</b><br/>{$i.ship}
      </td>
{/if}
{else}
      <td class="kb-table-cell"><b><a href="?a=pilot_detail&amp;plt_id={$pilot}">{$i.name}</a></b><br/>{$i.ship}</td>
{/if}
      <td class="kb-table-cell"><b><a href="?a=corp_detail&amp;crp_id={$i.cid}">{$i.corp}</a></b><br/><a href="?a=alliance_detail&amp;kll_id={$i.aid}" style="font-weight: normal;">{$i.alliance}</a></td>
    </tr>
{/foreach}
</table>
</td><td width="55%" valign="top">
<div class="kb-date-header">Hostile ({$hostilecnt})</div>
<br/>
<table class="kb-table" width="95%" align="center">
    <tr class="kb-table-header">
      <td class="kb-table-header" colspan="2" align="center">Ship/Pilot</td>
      <td class="kb-table-header" align="center" style="min-width: 45%; width: 45%; max-width: 45%;">Corp/Alliance</td>
    </tr>
{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
{foreach from=$pilots_e item=i key=pilot}
    <tr class="{cycle name=ccl}"{if is_destroyed($pilot)} style="background-color: #EE4444;"{/if}>
      <td width="32" height="32" style="max-width: 32px;">
{if is_destroyed($pilot)}
        <a href="?a=kill_detail&amp;kll_id={$kll_id}"><img src="{$i.spic}" width="32" height="32" border="0"></a>
{else}
        <img src="{$i.spic}" width="32" height="32" border="0">
{/if}
      </td>
{if podded($pilot) and $i.ship != 'Capsule'}
{if $config->getConfig('bs_podlink')}
      <td class="kb-table-cell">
        <b>{$i.name}</b>&nbsp;<a href="?a=kill_detail&amp;kll_id={$pod_kll_id}">[Pod]</a><br/>{$i.ship}
      </td>
{else}
      <td class="kb-table-cell" style="background-image: url({$podpic}); background-repeat: no-repeat; background-position: right;">
        <b>{$i.name}</b><br/>{$i.ship}
      </td>
{/if}
{else}
      <td class="kb-table-cell"><b><a href="?a=pilot_detail&amp;plt_id={$pilot}">{$i.name}</a></b><br/>{$i.ship}</td>
{/if}
      <td class="kb-table-cell"><b><a href="?a=corp_detail&amp;crp_id={$i.cid}">{$i.corp}</a></b><br/><a href="?a=alliance_detail&amp;kll_id={$i.aid}" style="font-weight: normal;">{$i.alliance}</a></td>
    </tr>
{/foreach}
</table>
</td>
</tr>
</table>
<br/>