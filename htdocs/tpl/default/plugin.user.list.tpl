{%RUNTIME_BEGIN_NOINDEX%}

<table class="rappsboxsimple" style="{%TPL_TABLE_STYLE%}" cellpadding="0"
  cellspacing="0" border="0">
  
  {ifdefined %TPL_TITLE%}
    <tr>
      <td align="center" colspan="5" style="padding: 2px; border: solid 0px {%COLOR_RAPPS_SHADOW%}; border-bottom-width: 1px">{%TPL_TITLE%}</td>
    </tr>
  {/ifdefined}
  
  {foreach %TPL_ITEM%}
    <tr style="white-space: nowrap" valign="top" onmouseover="mover(this)" onmouseout="mout(this, '{%COLOR_BGCOLOR%}')">
      <td class="monospace" style="padding: 0px 5px 0px 5px;">{%TPL_ITEM['LOGIN']%}</td>
      <td class="monospace" style="padding: 0px 5px 0px 5px;">{%TPL_ITEM['GROUP']%}</td>
      <td style="padding: 0px 5px 0px 5px;"><a href="mailto:{%TPL_ITEM['EMAIL']%}">{%TPL_ITEM['NAME']%}</a></td>
      <td class="monospace" style="padding: 0px 5px 0px 5px;">{%TPL_ITEM['MEMBER']%}</td>
    </tr>
  {/foreach}
  
</table>

{%RUNTIME_END_NOINDEX%}