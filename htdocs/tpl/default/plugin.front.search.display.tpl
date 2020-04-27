<h1>{%I18N_SEARCH_RESULT_FOR%}: '{%TPL_SEARCH_QUERY%}'</h1>

<p>{%TPL_SEARCH_MATCHES%}</p>

{ifdefined %TPL_ITEM%}
<dl style="padding-left: 15px">

  {foreach %TPL_ITEM%}
    <dt>{%TPL_ITEM['COUNT']%}.&nbsp;<a href="{%TPL_ITEM['HREF']%}">{%TPL_ITEM['NAME']%}</a>
      <dd>
          {%TPL_ITEM['TEASER']%}
          <i>{%TPL_ITEM['VIEWS']%}&nbsp;{%I18N_VIEWS%}...&nbsp;<a href="{%TPL_ITEM['HREF']%}">{%I18N_MORE%}</a>...</i>
          <br /><br />
      </dd>
    </dt>
  {/foreach}

</dl>
{/ifdefined}
