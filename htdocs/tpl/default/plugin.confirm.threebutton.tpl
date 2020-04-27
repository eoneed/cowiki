<div style="display:inline;">
<form action="{%TPL_FORM_ACTION%}" method="post" style="margin: 0px">

{%TPL_FORM_CONTROL_DATA%}

<table width="100%" cellpadding="0" cellspacing="0" border="0">

  <tr><td align="center">
  <br /><br /><br />

  <table width="450" cellpadding="0" bgcolor="{%COLOR_TUBORG_CONTENT%}"
    class="window" cellspacing="0" border="0">

    {* Header *}
    <tr>
      <td colspan="10">
        <table width="100%" cellpadding="3" cellspacing="0" border="0">
          <tr valign="top">
            <td nowrap width="100%" class="wintitle">
              {%TPL_ITEM_CONFIRM_HEADER%}
            </td>
            <td align="right" class="wintitle">{%BUTTON_CLOSE%}</td>
          </tr>
        </table>
      </td>
    </tr>

    {* Separator *}
    {include inc.window.tr.separator.tuborg.tpl}

    <tr>
      <td align="center" style="padding:20px">
        {%TPL_ITEM_CONFIRM_TEXT%}
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
