<table class="rappsboxsimple" style="{%TPL_TABLE_STYLE%}" cellpadding="0"
  cellspacing="0" border="0">

  {ifdefined %TPL_TITLE%}
    <tr>
      <td colspan="5" style="padding: 2px; border: solid 0px {%COLOR_RAPPS_SHADOW%}; border-bottom-width: 1px">{%TPL_TITLE%}</td>
    </tr>
  {/ifdefined}

  {ifdefined %TPL_SHOW_HEAD%}
    <tr>
      <td colspan="3" align="center"><a href="{%TPL_HREF_NAME%}">{%I18N_FILENAME%}</a></td>
      <td align="center"><a href="{%TPL_HREF_TIME%}">{%I18N_LAST_MODIFIED%}</a></td>
      <td align="center"><a href="{%TPL_HREF_SIZE%}">{%I18N_FILESIZE%}</a></td>
    </tr>
  {/ifdefined}

  {foreach %TPL_ITEM%}
    <tr style="white-space: nowrap" valign="top" onmouseover="mover(this)" onmouseout="mout(this, '{%COLOR_BGCOLOR%}')">
      <td><a href="{%TPL_ITEM['HREF']%}">{%TPL_ITEM['ICON']%}</a></td>
      <td>&nbsp;</td>
      <td style="font-size: 81%; padding: 0px 5px 0px 5px;"><a href="{%TPL_ITEM['HREF']%}" title="{%TPL_ITEM['TITLE']%}">{%TPL_ITEM['NAME']%}</a></td>
      <td class="monospace" style="padding: 0px 5px 0px 5px;" nowrap align="right"><tt>{%TPL_ITEM['LAST_MODIFIED']%}</tt></td>
      <td class="monospace" style="padding: 0px 5px 0px 5px;" nowrap align="right"><tt>{%TPL_ITEM['SIZE']%}</tt></td>
    </tr>
  {/foreach}

</table>