<table width="100%" bgcolor="{%COLOR_BGCOLOR%}" class="rappsboxsimple" cellpadding="2" cellspacing="1" border="0">
  {foreach %TPL_ITEM%}
    <tr valign="top" bgcolor="{%COLOR_RAPPS_CONTENT%}">
      <td>&nbsp;</td>
      <td nowrap><tt><b>{%TPL_ITEM['NAME']%}</b></tt></td>
    </tr>
    <tr>
      <td valign="top" align="right" bgcolor="{%COLOR_RAPPS_CONTENT%}"><tt>Purpose:</tt></td>
      <td width="100%" class="small"><tt>{%TPL_ITEM['PURPOSE']%}</tt></td>
    </tr>
    <tr>
      <td valign="top" align="right" bgcolor="{%COLOR_RAPPS_CONTENT%}"><tt>Parameter:</tt></td>
      <td valign="top">
        <table>
          <tr>
            <td colspan="2" nowrap class="small"><tt>&lt;plugin {%TPL_ITEM['NAME']%}</tt></td>
          </tr>
          <tr>
            <td><tt>&nbsp;&nbsp;&nbsp;&nbsp;</tt></td>
            <td nowrap class="small"><tt>{%TPL_ITEM['PARAM']%}</tt></td>
          </tr>
          <tr><td colspan="2" nowrap class="small"><tt>&gt;</tt></td></tr>
        </table>
      </td>
    </tr>
    <tr>
      <td valign="top" align="right" bgcolor="{%COLOR_RAPPS_CONTENT%}"><tt>Caching:</tt></td>
      <td width="100%" class="small"><tt>{%TPL_ITEM['CACHING']%}</tt></td>
    </tr>
    <tr>
      <td valign="top" align="right" bgcolor="{%COLOR_RAPPS_CONTENT%}"><tt>Comment:</tt></td>
      <td width="100%" class="small"><tt>{%TPL_ITEM['COMMENT']%}</tt></td>
    </tr>
    <tr>
      <td valign="top" align="right" bgcolor="{%COLOR_RAPPS_CONTENT%}"><tt>Version:</tt></td>
      <td width="100%" class="small"><tt>{%TPL_ITEM['VERSION']%}</tt></td>
    </tr>
    <tr>
      <td valign="top" align="right" bgcolor="{%COLOR_RAPPS_CONTENT%}"><tt>Date:</tt></td>
      <td width="100%" class="small"><tt>{%TPL_ITEM['DATE']%}</tt></td>
    </tr>
    <tr>
      <td valign="top" align="right" bgcolor="{%COLOR_RAPPS_CONTENT%}"><tt>Author:</tt></td>
      <td width="100%" class="small"><tt>{%TPL_ITEM['AUTHOR']%}</tt></td>
    </tr>
  {/foreach}
</table>


<!--
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
-->