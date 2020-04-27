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
            {%I18N_PREFERENCES%} ({%I18N_COOKIES_REQUIRED%})
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

      <table cellpadding="5" cellspacing="0" border="0">

        {ifdefined %TPL_ITEM_MESSAGE%}
          <tr>
            <td colspan="4">{%TPL_ITEM_MESSAGE%}</td>
          </tr>
        {/ifdefined}

        <tr>
          <td align="right" nowrap>{%I18N_TEMPLATE%}:</td>
          <td>
            <select name="template">
              {foreach %TPL_TEMPLATE%}
                <option value="{%TPL_TEMPLATE['VALUE']%}" {%TPL_TEMPLATE['SELECTED']%}>{%TPL_TEMPLATE['OPTION']%}{%TPL_TEMPLATE['DESC']%}</option>
              {/foreach}
            </select>
          </td>
        </tr>

        <tr>
          <td align="right" nowrap>{%I18N_LANGUAGE%}:</td>
          <td>
            <select name="catalog">
              {foreach %TPL_CATALOG%}
                <option value="{%TPL_CATALOG['VALUE']%}" {%TPL_CATALOG['SELECTED']%}>{%TPL_CATALOG['OPTION']%}</option>
              {/foreach}
            </select>
          </td>
        </tr>

        <tr>
          <td align="right" nowrap>{%I18N_FONT_FAMILY%}:</td>
          <td>
            <table cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td>{%TPL_ITEM_FONT_FAMILY%}</td>
                <td>&nbsp;</td>
                <td nowrap>(Arial, Helvetica, Verdana etc.)</td>
              </tr>
            </table>
          </td>
        </tr>

        <tr>
          <td align="right" nowrap>{%I18N_FONT_ALIGN%}:</td>
          <td>
            <select name="font_align">
              {foreach %TPL_FONT_ALIGN%}
                <option value="{%TPL_FONT_ALIGN['VALUE']%}" {%TPL_FONT_ALIGN['SELECTED']%}>{%TPL_FONT_ALIGN['OPTION']%}</option>
              {/foreach}
            </select>
          </td>
        </tr>

        <tr>
          <td align="right" nowrap>{%I18N_FONT_SIZE%}:</td>
          <td>
            <table cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td>{%TPL_ITEM_FONT_SIZE%}</td>
                <td>&nbsp;</td>
                <td>px</td>
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
