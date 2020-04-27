<div style="display:inline;">
<form action="{%TPL_FORM_ACTION%}" method="post" style="margin: 0px">

{%TPL_FORM_CONTROL_DATA%}

<table width="100%" bgcolor="{%COLOR_TUBORG_CONTENT%}" class="window"
  cellpadding="0" cellspacing="0" border="0">

  {* Header *}
  <tr>
    <td>
      <table width="100%" cellpadding="3" cellspacing="0" border="0">
        <tr valign="top">
          <td nowrap width="100%" class="wintitle">
            {%I18N_COM_COMMENT%}
          </td>
          <td align="right" class="wintitle">{%BUTTON_CLOSE%}</td>
        </tr>
      </table>
    </td>
  </tr>

  {* Separator *}
  {include inc.window.tr.separator.tuborg.tpl}

  {include inc.plugin.comment.display.tr.nav.tpl}

  {* Separator *}
  {include inc.window.tr.separator.tuborg.tpl}

  <tr>
    <td>
      <table cellpadding="0" cellspacing="5" border="0">
        <tr valign="top">
          <td nowrap align="right" class="medium">{%I18N_FROM%}:</td>
          <td nowrap class="medium">{%TPL_ITEM_AUTHOR%} ({%TPL_ITEM_TIME%})</td>
          <td nowrap width="100%" class="medium" align="right">{%I18N_REPLIES%}: {%TPL_ITEM_REPLIES%}, {%I18N_VIEWS%}: {%TPL_ITEM_VIEWS%}</td>
        </tr>
        <tr valign="top">
          <td nowrap align="right" class="medium">{%I18N_SUBJECT%}:</td>
          <td colspan="2" class="medium"><strong>{%TPL_ITEM_SUBJECT%}</strong></td>
        </tr>
      </table>
    </td>
  </tr>

  {* Separator *}
  {include inc.window.tr.separator.tuborg.tpl}

  <tr>
    <td>

      <table width="100%" cellpadding="5" cellspacing="0" border="0">
        <tr valign="top">
          <td><pre class="areainset" style="margin:0px; background-color:{%COLOR_RAPPS_CONTENT%};">{%TPL_ITEM_CONTENT%}</pre></td>
        </tr>
      </table>

    </td>
  </tr>

  {* Separator *}
  {include inc.window.tr.separator.tuborg.tpl}

  {include inc.plugin.comment.display.tr.nav.tpl}
  
  {* Separator *}
  {include inc.window.tr.separator.tuborg.tpl}

  {* Window form buttons *}
  {include inc.window.tr.twobutton.tpl}

</table>

<br />

<table width="100%" cellpadding="0" cellspacing="0" border="0">

  {foreach %TPL_THREAD%}

    <tr onmouseover="mover(this)" onmouseout="mout(this, '{%COLOR_BGCOLOR%}')">
      <td width="100%" style="border-right: {%COLOR_RAPPS_SHADOW%} solid 1px;">
        <table cellpadding="0" cellspacing="0" border="0">
          <tr>
            <td>{%TPL_THREAD['CONNECTOR']%}</td>
            <td>{%TPL_THREAD['BRANCH']%}</td>
            <td nowrap class="medium">&nbsp;<a href="{%TPL_THREAD['HREF']%}">{%TPL_THREAD['SUBJECT']%}</a>&nbsp;</td>
          </tr>
        </table>
      </td>
      <td nowrap class="medium" style="border-right: {%COLOR_RAPPS_SHADOW%} solid 1px;">&nbsp;{%TPL_THREAD['AUTHOR']%}&nbsp;</td>
      <td nowrap class="medium">&nbsp;{%TPL_THREAD['TIME']%}&nbsp;</td>
    </tr>

  {/foreach}

</table>

</form>
</div>
