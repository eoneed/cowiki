<div style="display:inline;">
<form action="{%TPL_FORM_ACTION%}" method="post" style="margin: 0px">

{%TPL_FORM_CONTROL_DATA%}

<table cellpadding="0" bgcolor="{%COLOR_TUBORG_CONTENT%}"
  class="window" cellspacing="0" border="0">

  {* Header *}
  <tr>
    <td colspan="10">
      <table width="100%" cellpadding="3" cellspacing="0" border="0">
        <tr valign="top">
          <td nowrap width="100%" class="wintitle">
            {%I18N_ADMINISTRATION%}
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

      <table cellpadding="0" cellspacing="5" border="0">

        {ifdefined %TPL_ITEM_MESSAGE%}
          <tr>
            <td>{%TPL_ITEM_MESSAGE%}</td>
          </tr>
        {/ifdefined}

        <tr>
          <td>
            <table cellpadding="10" cellspacing="0" border="0">

              {foreach %TPL_ITEM%}

                <tr>
                  <td nowrap><a class="menubottom"
                    href="{%TPL_ITEM['LINK1']%}">{%TPL_ITEM['LABEL1']%}</a></td>
                  <td>&nbsp;</td>
                  <td nowrap><a class="menubottom"
                    href="{%TPL_ITEM['LINK2']%}">{%TPL_ITEM['LABEL2']%}</a></td>
                </tr>

              {/foreach}

            </table>
          </td>
        </tr>

      </table>

    </td>
  </tr>

</table>

</form>
</div>