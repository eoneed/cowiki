<h1>{%TPL_ITEM_TITLE%}</h1>

<dl style="padding-left: 15px">

  {foreach %TPL_ITEM%}
    <dt><a href="{%TPL_ITEM['HREF']%}">{%TPL_ITEM['NAME']%}</a>&nbsp;{%TPL_ITEM['TYPE']%}
      <dd>{%TPL_ITEM['TEASER']%}<br /><br /></dd>
    </dt>
  {/foreach}

</dl>
