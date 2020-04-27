<table class="rappsboxsimple" style="{%TPL_TABLE_STYLE%}" cellpadding="0"
  cellspacing="0" border="0">

  {ifdefined %TPL_TITLE%}
    <tr>
      <td align="center" style="padding: 2px 5px 4px 5px; border: solid 0px {%COLOR_RAPPS_SHADOW%}; border-bottom-width: 1px">
        <a href="{%TPL_HREF%}" target="_blank">{%TPL_TITLE%}</a>
        {%TPL_DESCRIPTION%}
      </td>
    </tr>
  {/ifdefined}

  <tr>
    <td style="border: solid 0px {%COLOR_RAPPS_SHADOW%};">
      <dl style="padding: 5px">

        {foreach %TPL_ITEM%}

          {* show only name without date and teaser *}
          {ifdefined %TPL_SHOW_NAME_ONLY%}
            <dt>
              <a href="{%TPL_ITEM['HREF']%}" target="{%TPL_ITEM['TARGET']%}">{%TPL_ITEM['NAME']%}</a>
            </dt>
          {/ifdefined}

          {* show name with date but without teaser *}
          {ifdefined %TPL_SHOW_NAME_DATE%}
            <dt>
              <a href="{%TPL_ITEM['HREF']%}" target="_blank">{%TPL_ITEM['NAME']%}</a>&nbsp;<span class="monospace">({%TPL_ITEM['DATE']%})</span>
            </dt>
          {/ifdefined}

          {* show name with teaser but without date *}
          {ifdefined %TPL_SHOW_TEASER%}
            <dt>
              <a href="{%TPL_ITEM['HREF']%}" target="_blank">{%TPL_ITEM['NAME']%}</a>
              <dd>
                {%TPL_ITEM['TEASER']%}>&nbsp;</i><a href="{%TPL_ITEM['HREF']%}">{%I18N_MORE%}...</a></i>
                <br /><br />
              </dd>
            </dt>
          {/ifdefined}

          {* show name, date and teaser *}
          {ifdefined %TPL_SHOW_TEASER_DATE%}
            <dt>
              <a href="{%TPL_ITEM['HREF']%}" target="_blank">{%TPL_ITEM['NAME']%}</a>&nbsp;<span class="monospace">({%TPL_ITEM['DATE']%})</span>
              <dd>
                {%TPL_ITEM['TEASER']%}&nbsp;<i><a href="{%TPL_ITEM['HREF']%}" target="_blank">{%I18N_MORE%}...</a></i>
                <br /><br />
              </dd>
            </dt>
          {/ifdefined}

        {/foreach}

      </dl>
    </td>
  </tr>
</table>
