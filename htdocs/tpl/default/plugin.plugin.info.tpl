<table width="100%" bgcolor="{%COLOR_BGCOLOR%}" class="rappsboxsimple" cellpadding="2" cellspacing="1" border="0">

  {foreach %TPL_ITEM%}
    <tr valign="top" bgcolor="{%COLOR_RAPPS_CONTENT%}">
      <td rowspan="2" nowrap class="small"><tt>&lt;plugin {%TPL_ITEM['NAME']%}&gt;</tt></td>
      <td width="100%" class="small">{%TPL_ITEM['PURPOSE']%}</td>
    </tr>
    <tr valign="top">
      <td class="small">{%TPL_ITEM['PARAM']%}</td>
    </tr>
  {/foreach}

</table>
