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
            {%I18N_COM_HEAD_CREATE_EDIT%}
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
            <td colspan="5">{%TPL_ITEM_MESSAGE%}</td>
          </tr>
        {/ifdefined}

        <tr>
          <td align="right" nowrap>{%I18N_SUBJECT%}:</td>
          <td width="100%">{%TPL_ITEM_SUBJECT%}</td>
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
        <tr>
          <td>
            {%TPL_ITEM_CONTENT%}
          </td>
        </tr>
      </table>
    </td>
  </tr>
<!--
  {* Separator *}
  {include inc.window.tr.separator.tuborg.tpl}

  <tr>
    <td>
      <table cellpadding="2" cellspacing="0" border="0">
        <tr>
          <td>{%TPL_ITEM_NOTIFY%}</td>
          <td>{%I18N_COM_IF_ANSWER_NOTIFY%}</td>
        </tr>
      </table>
    </td>
  </tr>
-->
  {* Separator *}
  {include inc.window.tr.separator.tuborg.tpl}

  {* Window form buttons *}
  {include inc.window.tr.twobutton.tpl}

</table>

</form>
</div>
