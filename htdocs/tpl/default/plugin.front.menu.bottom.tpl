{foreach %TPL_ITEM%}

  <span>
    {%TPL_ITEM['SEPARATOR']%}
    <a href="{%TPL_ITEM['HREF']%}" class="menubottom">{%TPL_ITEM['NAME']%}</a>
  </span>

{/foreach}
