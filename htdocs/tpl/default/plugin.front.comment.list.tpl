<table bgcolor="{%COLOR_RAPPS_CONTENT%}" class="rappsbox" width="100%"
  cellpadding="0" cellspacing="0" border="0">
    {include inc.plugin.comment.list.tr.nav.tpl}
</table>

<table width="100%" cellpadding="2" cellspacing="0" border="0">

  {foreach %TPL_ITEM%}

    <tr>
      <td width="100%" class="medium" nowrap
        onmouseover="moverOnParentName(this, 'TR')"
        onmouseout="moverOnParentName(this, 'TR', '{%COLOR_BGCOLOR%}')"
        style="border-right: {%COLOR_RAPPS_SHADOW%} solid 1px"><a
          href="{%TPL_ITEM['HREF']%}">{%TPL_ITEM['SUBJECT']%}</a>&nbsp;({%TPL_ITEM['REPLIES']%})&nbsp;</td>
      <td class="medium" nowrap style="border-right: {%COLOR_RAPPS_SHADOW%} solid 1px;">&nbsp;{%TPL_ITEM['NAME']%}&nbsp;</td>
      <td class="medium" nowrap>&nbsp;{%TPL_ITEM['CREATED']%}</td>
    </tr>

  {/foreach}

</table>

{ifdefined %TPL_NAV_BOTTOM%}
  <table bgcolor="{%COLOR_RAPPS_CONTENT%}" class="rappsbox" width="100%"
    cellpadding="0" cellspacing="0" border="0">
      {include inc.plugin.comment.list.tr.nav.tpl}
  </table>
{/ifdefined}