{var TPL_SEPARATOR}
  <table width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
      <td bgcolor="{%COLOR_TUBORG_SHADOW%}"><img src="{%PATH_IMAGES%}0.gif"
        width="1" height="1" alt="" border="0"></td>
    </tr>
    <tr>
      <td bgcolor="{%COLOR_TUBORG_HIGHLIGHT%}"><img src="{%PATH_IMAGES%}0.gif"
        width="1" height="1" alt="" border="0"></td>
    </tr>
  </table>
{/var}

<table width="{%MENU_MIN_WIDTH%}" bgcolor="{%COLOR_TUBORG_CONTENT%}"
  class="tuborgbox" cellpadding="1" cellspacing="0" border="0">

  <tr>
    <td>

    {foreach %TPL_ITEM%}

      {%TPL_ITEM['SEPARATOR']%}
      <table cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td>{%TPL_ITEM['INDENT']%}</td>
          <td>{%TPL_ITEM['IMAGE']%}</td>
          <td onmouseover="moverOnParentName(this, 'TR')"
            onmouseout="moutOnParentName(this, 'TR')"
            nowrap width="100%"><a onfocus="if (this.blur) this.blur()"
            href="{%TPL_ITEM['HREF']%}"
            class="label">{%TPL_ITEM['NAME']%}</a><span
            class="label">&nbsp;</span></td>
        </tr>
      </table>

    {/foreach}

    </td>
  </tr>
</table>
