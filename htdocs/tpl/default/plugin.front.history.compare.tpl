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
            {%I18N_COMPARISON%}:&nbsp;{%TPL_ITEM_ORIG_NAME%}
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
            <table width="100%" cellpadding="0" cellspacing="0" border="0">

              <tr valign="top">
                <td width="100%" bgcolor="{%COLOR_BGCOLOR%}" class="area">

                  <table width="100%" cellpadding="0" cellspacing="1" border="0">
                    <tr valign="top">
                      <td width="50%">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                          <tr>
                            <td nowrap class="monospace" align="right">&nbsp;{%I18N_OWNERSHIP%}:&nbsp;</td>
                            <td class="monospace" width="100%">{%TPL_ITEM_HIST_MODE%}&nbsp;{%TPL_ITEM_HIST_USER%}&nbsp;{%TPL_ITEM_HIST_GROUP%}</td>
                          </tr>
                          <tr>
                            <td nowrap class="monospace" align="right">&nbsp;{%I18N_MODIFIED%}:&nbsp;</td>
                            <td class="monospace">{%TPL_ITEM_HIST_MOD_DATE%}</td>
                          </tr>
                          <tr>
                            <td nowrap class="monospace" align="right">&nbsp;{%I18N_MODIFIED_BY%}:&nbsp;</td>
                            <td class="monospace">{%TPL_ITEM_HIST_MOD_NAME%} ({%TPL_ITEM_HIST_MOD_LOGIN%})</td>
                          </tr>
                          <tr>
                            <td nowrap class="monospace" align="right">{%I18N_REVISION%}:&nbsp;</td>
                            <td class="monospace">{%TPL_ITEM_HIST_REVISION%}</td>
                          </tr>
                        </table>
                      </td>

                      <td rowspan="99999" class="tuborgbox" style="border-left-width: 0px" bgcolor="{%COLOR_TUBORG_CONTENT%}">&nbsp;</td>

                      <td width="50%">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                          <tr>
                            <td nowrap class="monospace" align="right">&nbsp;{%I18N_OWNERSHIP%}:&nbsp;</td>
                            <td class="monospace" width="100%">{%TPL_ITEM_ORIG_MODE%}&nbsp;{%TPL_ITEM_ORIG_USER%}&nbsp;{%TPL_ITEM_ORIG_GROUP%}</td>
                          </tr>
                          <tr>
                            <td nowrap class="monospace" align="right">&nbsp;{%I18N_MODIFIED%}:&nbsp;</td>
                            <td class="monospace">{%TPL_ITEM_ORIG_MOD_DATE%}</td>
                          </tr>
                          <tr>
                            <td nowrap class="monospace" align="right">&nbsp;{%I18N_MODIFIED_BY%}:&nbsp;</td>
                            <td class="monospace">{%TPL_ITEM_ORIG_MOD_NAME%} ({%TPL_ITEM_ORIG_MOD_LOGIN%})</td>
                          </tr>
                          <tr>
                            <td nowrap class="monospace" align="right">{%I18N_REVISION%}:&nbsp;</td>
                            <td class="monospace">{%TPL_ITEM_ORIG_REVISION%}</td>
                          </tr>
                        </table>
                      </td>
                    </tr>

                    <tr>
                      <td colspan="3">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                          <tr>
                            <td><img src="{%PATH_IMAGES%}0.gif"
                              width="1" height="3" alt="" border="0"></td>
                          </tr>
                          <tr bgcolor="{%COLOR_RAPPS_SHADOW%}">
                            <td><img src="{%PATH_IMAGES%}0.gif"
                              width="1" height="1" alt="" border="0"></td>
                          </tr>
                        </table>
                      </td>
                    </tr>

                    <tr valign="top">
                      <td width="50%" style="padding: 0px 5px 0px 5px">
                        <br />
                        {%TPL_ITEM_HIST_CONTENT%}
                        <br />
                      </td>
                      <td width="50%" style="padding: 0px 5px 0px 5px">
                        <br />
                        {%TPL_ITEM_ORIG_CONTENT%}
                        <br />
                      </td>
                    </tr>

                  </table>

                </td>
              </tr>

            </table>
          </td>
        </tr>

      </table>
    </td>
  </tr>

  {* Separator *}
  {include inc.window.tr.separator.tuborg.tpl}



  {* Window form buttons *}
  {include inc.window.tr.twobutton.tpl}

</table>

</form>
</div>
