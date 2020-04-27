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
            {%I18N_ADMIN_WEB_HEAD_EDIT%}
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
          <td align="right" nowrap>{%I18N_NAME%}:</td>
          <td width="100%">{%TPL_ITEM_NAME%}</td>
        </tr>

        <tr>
          <td align="right" nowrap>{%I18N_USER%}:</td>
          <td colspan="4">

            <table cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td>{%TPL_ITEM_USER_OPTIONS%}</td>
                <td>&nbsp;</td>
                <td><tt>rwx</tt></td>
                <td>{%TPL_ITEM_ACCESS_USER_READ%}</td>
                <td>{%TPL_ITEM_ACCESS_USER_WRITE%}</td>
                <td>{%TPL_ITEM_ACCESS_USER_EXEC%}</td>

                <td>&nbsp;&nbsp;&nbsp;</td>

                <td nowrap>{%I18N_GROUP%}:</td>
                <td>&nbsp;</td>
                <td>{%TPL_ITEM_GROUP_OPTIONS%}</td>
                <td>&nbsp;</td>
                <td><tt>rwx</tt></td>
                <td>{%TPL_ITEM_ACCESS_GROUP_READ%}</td>
                <td>{%TPL_ITEM_ACCESS_GROUP_WRITE%}</td>
                <td>{%TPL_ITEM_ACCESS_GROUP_EXEC%}</td>

                <td>&nbsp;&nbsp;&nbsp;</td>

                <td nowrap>{%I18N_WORLD%}:</td>
                <td>&nbsp;</td>
                <td><tt>rwx</tt></td>
                <td>{%TPL_ITEM_ACCESS_WORLD_READ%}</td>
                <td>{%TPL_ITEM_ACCESS_WORLD_WRITE%}</td>
                <td>{%TPL_ITEM_ACCESS_WORLD_EXEC%}</td>
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
