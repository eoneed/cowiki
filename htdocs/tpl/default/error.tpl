<table bgcolor="{%COLOR_MSG_BORDER%}" cellpadding="1" cellspacing="0" border="0">
  <tr>
    <td>

      <table bgcolor="{%COLOR_MSG_BACKGROUND%}" cellpadding="3"
        cellspacing="0" border="0">

        {foreach %TPL_ITEM%}

          <tr valign="top">
            <td><img src="{%PATH_IMAGES%}{%TPL_ITEM['IMAGE']%}" width="16"
                height="16" alt="" border="0"></td>
            <td nowrap>{%TPL_ITEM['TEXT']%}</td>
          </tr>

        {/foreach}

      </table>

    </td>
  </tr>
</table>