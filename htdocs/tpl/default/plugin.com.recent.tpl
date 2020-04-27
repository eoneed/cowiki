{%RUNTIME_BEGIN_NOINDEX%}

<table class="rappsboxsimple" style="{%TPL_TABLE_STYLE%}" cellpadding="0" cellspacing="0" border="0">

  {ifdefined %TPL_TITLE%}
    <tr>
      <th colspan="3" style="padding: 2px; border: solid 0px {%COLOR_RAPPS_SHADOW%}; border-bottom-width: 1px">{%TPL_TITLE%}</th>
    </tr>
  {/ifdefined}

  {foreach %TPL_ITEM%}
    <tr style="white-space: nowrap" valign="top" onmouseover="mover(this)" onmouseout="mout(this, '{%COLOR_BGCOLOR%}')">
      <td class="monospace" style="padding: 0px 5px 0px 5px;" nowrap align="right"><tt>{%TPL_ITEM['TIME']%}</tt></td>
      <td style="font-size: 81%; padding: 0px 5px 0px 5px;"><a href="{%TPL_ITEM['HREF']%}">{%TPL_ITEM['NAME']%}</a></td>
    </tr>
  {/foreach}

</table>

{%RUNTIME_END_NOINDEX%}
