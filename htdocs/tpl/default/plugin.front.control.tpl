{*
    This continues the table data from 'front.page.header.tpl'

    SPAN-, A- and TD-tags are intentionally kept in one line,
    otherwise Mozilla will render the buttons ugly and not valigned ...
*}

  <td width="100%">&nbsp;</td>
  
{foreach %TPL_ITEM%}

  <td style="background-image:url({%PATH_IMAGES%}vert.gif)"></td>
  <td nowrap style="padding-top: 2px; padding-bottom: 2px;"
    onmouseover="mover(this)" onmouseout="mout(this)"><span
    class="label">&nbsp;</span><a onfocus="if (this.blur) this.blur()"
    target="{%TPL_ITEM['TARGET']%}" class="label"
    href="{%TPL_ITEM['HREF']%}">{%TPL_ITEM['NAME']%}</a><span
    class="label">&nbsp;</span></td>

{/foreach}
