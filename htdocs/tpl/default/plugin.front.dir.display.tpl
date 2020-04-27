<br />

<table cellpadding="0" cellspacing="0" border="0">

  {foreach %TPL_ITEM%}

    <tr onmouseover="mover(this)" onmouseout="mout(this, '{%COLOR_BGCOLOR%}')">
      <td nowrap class="monospace">{%TPL_ITEM['MODE']%}&nbsp;</td>
      <td nowrap class="monospace">{%TPL_ITEM['USER']%}&nbsp;</td>
      <td nowrap class="monospace">{%TPL_ITEM['GROUP']%}&nbsp;</td>
      <td><a href="{%TPL_ITEM['HREF']%}">{%TPL_ITEM['ICON']%}</a></td>
      <td>&nbsp;</td>
      <td nowrap width="100%"><a href="{%TPL_ITEM['HREF']%}">{%TPL_ITEM['NAME']%}</a></td>
      <td nowrap class="monospace" align="right">&nbsp;{%TPL_ITEM['MTIME']%}</td>
    </tr>

  {/foreach}

</table>
