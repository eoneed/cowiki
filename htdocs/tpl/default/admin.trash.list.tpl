<div style="display:inline;">
<form action="{%TPL_FORM_ACTION%}" method="post" style="margin: 0px">

{%TPL_FORM_CONTROL_DATA%}

<table width="100%" bgcolor="{%COLOR_TUBORG_CONTENT%}" class="window"
  cellpadding="0" cellspacing="0" border="0">

  {* Header *}
  <tr>
    <td colspan="4">
      <table width="100%" cellpadding="3" cellspacing="0" border="0">
        <tr valign="top">
          <td nowrap width="100%" class="wintitle">
            {%I18N_ADMIN_TRASH_HEAD_MANAGE%}
          </td>
          <td align="right" class="wintitle">{%BUTTON_CLOSE%}</td>
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
          <td width="100%">

            <div class="areaoverflow" style="height:{%EDIT_HIST_AREA_HEIGHT%}px;">
              <table width="100%" cellpadding="0" cellspacing="0" border="0">

                <tr>
                  <td align="center" style="border-right: {%COLOR_RAPPS_SHADOW%} solid 1px">&nbsp;{%I18N_REVISION%}&nbsp;</td>
                  <td width="100%" style="border-right: {%COLOR_RAPPS_SHADOW%} solid 1px">&nbsp;{%I18N_NAME%}&nbsp;</td>
                  <td align="center" style="border-right: {%COLOR_RAPPS_SHADOW%} solid 1px">&nbsp;{%I18N_DATE%}&nbsp;/&nbsp;{%I18N_TIME%}&nbsp;</td>
                  <td align="center" style="border-right: {%COLOR_RAPPS_SHADOW%} solid 1px">&nbsp;{%I18N_USER%}&nbsp;</td>
                </tr>

                <tr>
                  <td colspan="4"><img src="{%PATH_IMAGES%}0.gif"
                    width="1" height="1" alt="" border="0"></td>
                </tr>
                <tr bgcolor="{%COLOR_RAPPS_SHADOW%}">
                  <td colspan="4"><img src="{%PATH_IMAGES%}0.gif"
                    width="1" height="1" alt="" border="0"></td>
                </tr>
                <tr>
                  <td colspan="4"><img src="{%PATH_IMAGES%}0.gif"
                    width="1" height="1" alt="" border="0"></td>
                </tr>

                {foreach %TPL_ITEM%}
                  <tr bgcolor="{%COLOR_BGCOLOR%}" onmouseover="mover(this)"
                    onmouseout="mout(this, '{%COLOR_BGCOLOR%}')" valign="top">
                    <td align="center" style="border-right: {%COLOR_RAPPS_SHADOW%} solid 1px">&nbsp;{%TPL_ITEM['REVISION']%}&nbsp;</td>
                    <td align="left" style="border-right: {%COLOR_RAPPS_SHADOW%} solid 1px"><nobr>&nbsp;<a href="{%TPL_ITEM['HREF_RESTORE']%}">{%TPL_ITEM['NAME']%}</a>&nbsp;</nobr></td>
                    <td align="right" nowrap style="border-right: {%COLOR_RAPPS_SHADOW%} solid 1px;font-size: 80%">&nbsp;{%TPL_ITEM['DATE']%}&nbsp;</td>
                    <td class="monospace" nowrap style="border-right: {%COLOR_RAPPS_SHADOW%} solid 1px">&nbsp;{%TPL_ITEM['USER']%}&nbsp;</td>
                  </tr>
                {/foreach}

                <tr>
                  <td colspan="4"><img src="{%PATH_IMAGES%}0.gif"
                    width="1" height="1" alt="" border="0"></td>
                </tr>
                <tr bgcolor="{%COLOR_RAPPS_SHADOW%}">
                  <td colspan="4"><img src="{%PATH_IMAGES%}0.gif"
                    width="1" height="1" alt="" border="0"></td>
                </tr>
                <tr>
                  <td colspan="4"><img src="{%PATH_IMAGES%}0.gif"
                    width="1" height="1" alt="" border="0"></td>
                </tr>

                <tr>
                  <td colspan="4"><img src="{%PATH_IMAGES%}0.gif"
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
  {include inc.window.tr.onebutton.tpl}

</table>

</form>
</div>
