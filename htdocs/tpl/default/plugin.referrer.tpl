{%RUNTIME_BEGIN_NOINDEX%}

<table width="1" class="rappsboxsimple" style="{%TPL_TABLE_STYLE%}" cellpadding="0" cellspacing="0" border="0">

  {ifdefined %TPL_TITLE%}
    <tr>
      <th class="small" style="padding: 2px; border: solid 0px {%COLOR_RAPPS_SHADOW%}; border-bottom-width: 1px">{%TPL_TITLE%}</th>
    </tr>
  {/ifdefined}

  {foreach %TPL_ITEM%}
    <tr valign="top">
      <td nowrap class="small" style="padding: 0px 3px 0px 3px;"><a rel="nofollow" target="referrer" title="{%TPL_ITEM['HREF']%}" href="{%TPL_ITEM['HREF']%}">{%TPL_ITEM['NAME']%}</a></td>
    </tr>
  {/foreach}

</table>

{%RUNTIME_END_NOINDEX%}
