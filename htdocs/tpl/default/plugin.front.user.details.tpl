<div style="display:inline;">

<form action="{%TPL_FORM_ACTION%}" method="post" style="margin: 0px">

{%TPL_FORM_CONTROL_DATA%}


<table width="100%" cellpadding="0" bgcolor="{%COLOR_TUBORG_CONTENT%}"
  class="window" cellspacing="0" border="0">

  {* Header *}
  <tr>
    <td colspan="10">
      <table width="100%" cellpadding="3" cellspacing="0" border="0">
        <tr valign="top">
          <td nowrap width="100%" class="wintitle">
            {%I18N_FRONT_USER_HEAD_DETAILS%}
          </td>
          <td align="right" class="wintitle">{%BUTTON_CLOSE%}</td>
        </tr>
      </table>
    </td>
  </tr>

  <tr>
    <td>

      <table cellpadding="5" cellspacing="0" border="0">

        {ifdefined %TPL_ITEM_MESSAGE%}
          <tr>
            <td colspan="4">{%TPL_ITEM_MESSAGE%}</td>
          </tr>
        {/ifdefined}

        <tr>
          <td align="right" nowrap>{%I18N_PASSWORD%}:</td>
          <td>
            <table cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td>{%TPL_ITEM_PASSWORD%}</td>
                <td>&nbsp;</td>
                <td nowrap>({%I18N_LEAVE_BLANK_UNLESS_CHANGING%})</td>
              </tr>
            </table>
          </td>
        </tr>

        <tr>
          <td align="right" nowrap>{%I18N_EMAIL%}:</td>
          <td>
            <table cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td>{%TPL_ITEM_EMAIL%}</td>
                <td>&nbsp;</td>
                <td nowrap>&nbsp;</td>
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
