<table cellpadding="0" cellspacing="0" border="0">
  <tr>
    <td>
      {foreach %TPL_ITEM%}

        {* Keep this in one line *}
        <a href="{%TPL_ITEM['HREF']%}" class="breadcrumb">{%TPL_ITEM['NAME']%}</a>{%TPL_ITEM['DELI']%}

      {/foreach}

      <span class="breadcrumb">{%TPL_CURRENT_ITEM%}</span>
    </td>
  </tr>
</table>
