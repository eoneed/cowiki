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
            {%I18N_ADMIN_USER_HEAD_EDIT%}
          </td>
          <td align="right" class="wintitle">{%BUTTON_CLOSE%}</td>
        </tr>
      </table>
    </td>
  </tr>

  {* Separator *}
  {include inc.window.tr.separator.tuborg.tpl}

  {* Form input fields *}
  <tr>
    <td>

      <table width="100%" cellpadding="5" cellspacing="0" border="0">

        {ifdefined %TPL_ITEM_MESSAGE%}
          <tr>
            <td colspan="2">{%TPL_ITEM_MESSAGE%}</td>
          </tr>
        {/ifdefined}

        <tr>
          <td>

            <table width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr valign="top">
                <td>
                  <fieldset class="tuborgbox">
                    <legend>{%I18N_RECORD_DATA%}</legend>

                    <table cellpadding="2" cellspacing="0" border="0">
                      <tr>
                        <td nowrap align="right">{%I18N_NAME%}:</td>
                        <td>{%TPL_ITEM_NAME%}</td>
                      </tr>
                      <tr>
                        <td align="right">{%I18N_EMAIL%}:</td>
                        <td>{%TPL_ITEM_EMAIL%}</td>
                      </tr>
                      <tr>
                        <td nowrap align="right">{%I18N_AUTH_LOGIN_NAME%}:</td>
                        <td>{%TPL_ITEM_LOGIN%}</td>
                      </tr>
                      <tr>
                        <td nowrap align="right">{%I18N_PASSWORD%}:</td>
                        <td>{%TPL_ITEM_PASSWORD%}</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>
                          <table cellpadding="0" cellspacing="0" border="0">
                            <tr>
                              <td>{%TPL_ITEM_PASSWORD_CRYPTED%}</td>
                              <td>&nbsp;</td>
                              <td nowrap>{%I18N_PASSWORD_IS_CRYPTED%}</td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                      <tr>
                        <td nowrap align="right">{%I18N_GROUP_DEFAULT%}:</td>
                        <td>{%TPL_ITEM_GROUP_DEFAULT%}</td>
                      </tr>
        <!--
                      <tr>
                        <td nowrap align="right">{%I18N_EXPIRES%}:</td>
                        <td>{%TPL_ITEM_EXPIRES%}</td>
                      </tr>
        -->
                      <tr>
                        <td nowrap align="right">{%I18N_ACTIVE%}:</td>
                        <td>{%TPL_ITEM_ACTIVE%}</td>
                      </tr>
                    </table>

                  </fieldset>
                </td>

                <td>&nbsp;</td>

                <td width="100%">
                  <fieldset class="tuborgbox">
                    <legend>{%I18N_GROUP_MEMBER%}</legend>

                    <table cellpadding="0" cellspacing="0" border="0">
                      {foreach %TPL_ITEM_MEMBER%}
                        <tr valign="top">
                          <td>{%TPL_ITEM_MEMBER['CHECKBOX']%}</td>
                          <td class="monospace">{%TPL_ITEM_MEMBER['NAME']%}</td>
                        </tr>
                      {/foreach}
                    </table>

                  </fieldset>
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
  {include inc.window.tr.threebutton.tpl}

</table>

</form>
</div>
