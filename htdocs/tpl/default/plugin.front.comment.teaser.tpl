<table bgcolor="{%COLOR_RAPPS_CONTENT%}" class="rappsbox" width="100%"
  cellpadding="2" cellspacing="0" border="0">
  <tr>
    <td nowrap><a
      href="{%TPL_ITEM_COMMENTS%}">{%I18N_COM_COMMENTS%}</a>:&nbsp;{%TPL_ITEM_COUNT%}</td>
    <td nowrap align="right" width="100%"><a
      href="{%TPL_ITEM_WRITE_HREF%}">{%I18N_COM_COMMENT_WRITE%}</a></td>
  </tr>
</table>

{ifdefined %TPL_ITEM%}
<table cellpadding="2" cellspacing="0" border="0" style="border-bottom: {%COLOR_RAPPS_SHADOW%} solid 1px;">

  {foreach %TPL_ITEM%}
    <tr>
      <td class="medium" width="100%" nowrap
        onmouseover="moverOnParentName(this, 'TR')"
        onmouseout="moverOnParentName(this, 'TR', '{%COLOR_BGCOLOR%}')"
        style="border-right: {%COLOR_RAPPS_SHADOW%} solid 1px"><a
        href="{%TPL_ITEM['HREF']%}">{%TPL_ITEM['SUBJECT']%}</a>&nbsp;</td>
      <td class="medium" nowrap style="border-right: {%COLOR_RAPPS_SHADOW%} solid 1px;">&nbsp;{%TPL_ITEM['NAME']%}&nbsp;</td>
      <td class="medium" nowrap>&nbsp;{%TPL_ITEM['TIME']%}</td>
    </tr>
  {/foreach}

</table>
{/ifdefined}