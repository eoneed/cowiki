
<table cellpadding="0" cellspacing="0" border="0">

  {ifdefined %TPL_TITLE%}
    <tr>
      <td colspan="7" style="padding: 2px; border: solid 0px {%COLOR_RAPPS_SHADOW%}; border-bottom-width: 1px"><strong>{%TPL_TITLE%}</strong></td>
    </tr>
  {/ifdefined}

  {foreach %TPL_ITEM%}

    <tr onmouseover="mover(this)" onmouseout="mout(this, '{%COLOR_BGCOLOR%}')">
      <td nowrap class="monospace">{%TPL_ITEM['MODE']%}&nbsp;</td>
      <td nowrap class="monospace">{%TPL_ITEM['USER']%}&nbsp;</td>
      <td nowrap class="monospace">{%TPL_ITEM['GROUP']%}&nbsp;</td>
      <td><a href="{%TPL_ITEM['HREF']%}">{%TPL_ITEM['ICON']%}</a></td>
      <td>&nbsp;</td>
      <td nowrap ><a href="{%TPL_ITEM['HREF']%}">{%TPL_ITEM['NAME']%}</a></td>
      <td nowrap class="monospace" align="right">&nbsp;{%TPL_ITEM['MTIME']%}</td>
    </tr>

  {/foreach}

</table>
