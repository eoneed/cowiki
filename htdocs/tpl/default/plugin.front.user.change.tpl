<div style="display:inline;">
<form action="{%TPL_FORM_ACTION%}" method="post" style="margin: 0px">

{%TPL_FORM_CONTROL_DATA%}

<table width="100%" cellpadding="0" cellspacing="0" border="0">

  <tr><td align="center">
  <br /><br /><br />

  <table width="400" cellpadding="0" bgcolor="{%COLOR_TUBORG_CONTENT%}"
    class="window" cellspacing="0" border="0">

    {* Header *}
    <tr>
      <td colspan="10">
        <table width="100%" cellpadding="3" cellspacing="0" border="0">
          <tr valign="top">
            <td nowrap width="100%" class="wintitle">
              {%I18N_AUTH_HEAD_CHANGE%}
            </td>
          </tr>
        </table>
      </td>
    </tr>

    {* Separator *}
    {include inc.window.tr.separator.tuborg.tpl}

    <tr>
      <td align="center">

        <table cellpadding="5" cellspacing="0" border="0">

          {ifdefined %TPL_ITEM_MESSAGE%}
            <tr>
              <td colspan="4">{%TPL_ITEM_MESSAGE%}</td>
            </tr>
          {/ifdefined}

          <tr>
            <td align="right" nowrap>
              {%I18N_AUTH_LOGIN_NAME%}:
            </td>
            <td>
              {%TPL_ITEM_LOGIN%}
            </td>
            <td align="right" nowrap>
              {%I18N_PASSWORD%}:
            </td>
            <td>
              {%TPL_ITEM_PASSWORD%}
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

<br /><br /><br />
</td></tr></table>

</form>
</div>
