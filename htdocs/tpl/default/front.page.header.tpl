{*
  Exclude anything from the (internal) search engine. This tag has to
  be closed after the last output (footer). Between these tags
  you may allow any template to be indexed by closing and reopening
  these tags again.
*}

{%RUNTIME_BEGIN_NOINDEX%}

<body dir="{%I18N_DIRECTION%}" text="{%COLOR_TEXT%}" link="{%COLOR_LINK%}"
  alink="{%COLOR_ALINK%}" vlink="{%COLOR_VLINK%}" bgcolor="{%COLOR_BGCOLOR%}"
  style="margin:2px;"
  onload="
    obj = document.getElementById('logininput'); if (obj) {obj.focus()};
  "
>

<table width="100%" cellpadding="2" cellspacing="0" border="0" class="rappsbox">
  <tr bgcolor="{%COLOR_RAPPS_CONTENT%}">
    <td><a href="/"><img hspace="5"
        src="{%PATH_IMAGES%}cowiki.gif" width="140" height="40"
        alt="{%COWIKI_FULL_NAME%}" border="0" class="tuborgbox"></a></td>
    <td align="right">
      {plugin PrivateFrontUserControlDisplay}
    </td>
  </tr>
</table>

<table width="100%" bgcolor="{%COLOR_TUBORG_CONTENT%}" cellspacing="0"
  cellpadding="1" border="0" class="tuborgbox">
  <tr>
    <td><img src="{%PATH_IMAGES%}0.gif" width="4"
        height="21" alt="" border="0"></td>

    <td width="100%">{plugin PrivateFrontSearchQuery}</td>

    {* TDs will be continued in the output of this plugin: *}
    {plugin PrivateFrontControlDisplay}

  </tr>
</table>

