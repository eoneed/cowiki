{%RUNTIME_BEGIN_NOINDEX%}

<table width="1" class="rappsboxsimple" style="{%TPL_TABLE_STYLE%}" cellpadding="0" cellspacing="0" border="0">

  {ifdefined %TPL_ITEM_TITLE%}
    <tr>
      <th style="padding: 2px; border: solid 0px {%COLOR_RAPPS_SHADOW%}; border-bottom-width: 1px">{%TPL_ITEM_TITLE%}</th>
    </tr>
  {/ifdefined}

  <tr>
    <td align="center" style="padding: 2px;"><a target="_blank" href="{%TPL_ITEM_HREF%}">{%TPL_ITEM%}</a></td>
  </tr>

  {ifdefined %TPL_ITEM_DESC%}
    <tr>
      <td align="center" class="tiny" style="padding: 2px; border: solid 0px {%COLOR_RAPPS_SHADOW%}; border-top-width: 1px">{%TPL_ITEM_DESC%}</td>
    </tr>
  {/ifdefined}

</table>

{%RUNTIME_END_NOINDEX%}
