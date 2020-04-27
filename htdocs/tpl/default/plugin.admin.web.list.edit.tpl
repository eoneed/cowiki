<div style="display:inline;">
<form action="{%TPL_FORM_ACTION%}" method="post" style="margin: 0px">

{%TPL_FORM_CONTROL_DATA%}

<table width="100%" bgcolor="{%COLOR_TUBORG_CONTENT%}" class="window"
  cellpadding="0" cellspacing="0" border="0">

  {* Header *}
  <tr>
    <td colspan="10">
      <table width="100%" cellpadding="3" cellspacing="0" border="0">
        <tr valign="top">
          <td nowrap width="100%" class="wintitle">
            {%I18N_ADMIN_WEB_HEAD_MANAGE%}
          </td>
          <td align="right" class="wintitle">{%BUTTON_CLOSE%}</td>
        </tr>
      </table>
    </td>
  </tr>

  {* Separator *}
  {include inc.window.tr.separator.tuborg.tpl}

  <tr>
    <td>
      <table cellpadding="0" cellspacing="0" border="0">
        <tr>

          {foreach %TPL_ACTION%}

            <td nowrap onmouseover="mover(this)" onmouseout="mout(this)"><span
              class="label">&nbsp;</span><a onfocus="if(this.blur)this.blur()"
              target="{%TPL_ACTION['TARGET']%}" class="label"
              href="{%TPL_ACTION['HREF']%}">{%TPL_ACTION['NAME']%}</a><span
              class="label">&nbsp;</span></td>

            <td style="background-image:url({%PATH_IMAGES%}vert.gif)"><img
              src="{%PATH_IMAGES%}0.gif" width="2" height="1"
              alt="" border="0"></td>

          {/foreach}

          <td><img src="img/0.gif" width="1" height="22" alt="" border="0"></td>
        </tr>
      </table>
    </td>
  </tr>

  {* Separator *}
  {include inc.window.tr.separator.tuborg.tpl}

  {* Content area *}
  <tr>
    <td>
      <table width="100%" cellpadding="5" cellspacing="0" border="0">

        {ifdefined %TPL_ITEM_MESSAGE%}
          <tr>
            <td>{%TPL_ITEM_MESSAGE%}</td>
          </tr>
        {/ifdefined}

        <tr>
          <td>

            <div class="areaoverflow" style="height:{%EDIT_DIR_AREA_HEIGHT%}px;">
              <table width="100%" cellpadding="0" cellspacing="0" border="0">

                <tr>
                  <td colspan="2" align="center">{%I18N_SORT%}</td>
                  <td>&nbsp;</td>
                  <td align="center">{%I18N_ACCESS%}</td>
                  <td>{%I18N_USER%}</td>
                  <td>{%I18N_GROUP%}</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td width="100%">&nbsp;</td>
                  <td>{%I18N_DIR_IN_MENU%}</td>
                  <td>&nbsp;</td>
                  <td>{%I18N_DIR_IN_FOOTER%}</td>
                  <td>&nbsp;</td>
                </tr>

                <tr>
                  <td colspan="13"><img src="{%PATH_IMAGES%}0.gif"
                    width="1" height="5" alt="" border="0"></td>
                </tr>
                <tr bgcolor="{%COLOR_RAPPS_SHADOW%}">
                  <td colspan="13"><img src="{%PATH_IMAGES%}0.gif"
                    width="1" height="1" alt="" border="0"></td>
                </tr>
                <tr>
                  <td colspan="13"><img src="{%PATH_IMAGES%}0.gif"
                    width="1" height="5" alt="" border="0"></td>
                </tr>

                {foreach %TPL_ITEM%}
                  <tr bgcolor="{%COLOR_BGCOLOR%}" onmouseover="mover(this)"
                    onmouseout="mout(this, '{%COLOR_BGCOLOR%}')">
                    <td align="center">{%TPL_ITEM['BUTTON1']%}</td>
                    <td align="center">{%TPL_ITEM['BUTTON2']%}</td>
                    <td>&nbsp;</td>
                    <td nowrap class="monospace">{%TPL_ITEM['MODE']%}&nbsp;</td>
                    <td nowrap class="monospace">{%TPL_ITEM['USER']%}&nbsp;</td>
                    <td nowrap class="monospace">{%TPL_ITEM['GROUP']%}&nbsp;</td>
                    <td><a href="{%TPL_ITEM['HREF']%}">{%TPL_ITEM['ICON']%}</a></td>
                    <td></td>
                    <td nowrap><a href="{%TPL_ITEM['HREF']%}">{%TPL_ITEM['NAME']%}</a></td>
                    <td align="center">{%TPL_ITEM['IN_MENU']%}</td>
                    <td>&nbsp;</td>
                    <td align="center">{%TPL_ITEM['IN_FOOTER']%}</td>
                    <td>&nbsp;</td>
                  </tr>
                {/foreach}

                <tr>
                  <td colspan="13"><img src="{%PATH_IMAGES%}0.gif"
                    width="1" height="5" alt="" border="0"></td>
                </tr>

              </table>
            </div>

          </td>
        </tr>

      </table>
    </td>
  </tr>

  {* Separator *}
  {include inc.window.tr.separator.tuborg.tpl}

  {* Window form buttons *}
  {include inc.window.tr.threebutton.tpl}

</table>

</form>
</div>
