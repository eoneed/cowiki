<script language="JavaScript1.5" type="text/javascript">

  function getMarkerActions{%TPL_PLUGIN_ID%}() {
    var s;

    s =  '<a href="#" class="label" onfocus="if(this.blur)this.blur()"';
    s +=    ' onclick="markAll{%TPL_PLUGIN_ID%}(); return false;">';
    s +=    '{%I18N_SELECT_ALL%}';
    s += '</a>';
    s += '&nbsp;/&nbsp;';
    s += '<a href="#" class="label" onfocus="if(this.blur)this.blur()"';
    s +=    ' onclick="markRange{%TPL_PLUGIN_ID%}(); return false;">';
    s +=    '{%I18N_RANGE%}';
    s += '</a>';
    s += '&nbsp;/&nbsp;';
    s += '<a href="#" class="label" onfocus="if(this.blur)this.blur()"';
    s +=    ' onclick="markReverse{%TPL_PLUGIN_ID%}(); return false;">';
    s +=    '{%I18N_REVERSE%}';
    s += '</a>';
    s += '&nbsp;-';
    return s;
  }

  // ----------------------------------------------------------------------

  function markAll{%TPL_PLUGIN_ID%}() {
    var i;

    elm = document.getElementById('f{%TPL_PLUGIN_ID%}').elements;
    for (i=0, n=elm.length; i<n; i++) {
      if (elm[i].name.substr(0,7) == 'marker_') {
          elm[i].checked = true;
      }
    }
  }

  // ----------------------------------------------------------------------

  function markRange{%TPL_PLUGIN_ID%}() {
    var i, first = -1, last = -1;

    elm = document.getElementById('f{%TPL_PLUGIN_ID%}').elements;
    len = elm.length;

    for (i=0, n=len; i<n; i++) {
      if (elm[i].name.substr(0,7) == 'marker_') {
        if (elm[i].checked == true) {
          first = i;
          break;
        }
      }
    }
    for (i=len-1, n=-1; i>n; i--) {
      if (elm[i].name.substr(0,7) == 'marker_') {
        if (elm[i].checked == true) {
          last = i;
          break;
        }
      }
    }

    if (first < 0 || last < 0 || first >= last) {
        return;
    }

    for (i=first, n=last; i<n; i++) {
      if (elm[i].name.substr(0,7) == 'marker_') {
        elm[i].checked = true;
      }
    }
  }

  // ----------------------------------------------------------------------

  function markReverse{%TPL_PLUGIN_ID%}() {
    var i;

    elm = document.getElementById('f{%TPL_PLUGIN_ID%}').elements;
    for (i=0, n=elm.length; i<n; i++) {
      if (elm[i].name.substr(0,7) == 'marker_') {
          elm[i].checked = !elm[i].checked;
      }
    }
  }

</script>

<div style="display:inline;">
<form action="{%TPL_FORM_ACTION%}" id="f{%TPL_PLUGIN_ID%}" method="post"
  style="margin: 0px">

{%TPL_FORM_CONTROL_DATA%}

<table width="100%" bgcolor="{%COLOR_TUBORG_CONTENT%}" class="window"
  cellpadding="0" cellspacing="0" border="0">

  {* Header *}
  <tr>
    <td colspan="10">
      <table width="100%" cellpadding="3" cellspacing="0" border="0">
        <tr valign="top">
          <td nowrap width="100%" class="wintitle">
            {%I18N_DIR_HEAD_CREATE_EDIT%}
          </td>
          <td align="right" class="wintitle">{%BUTTON_CLOSE%}</td>
        </tr>
      </table>
    </td>
  </tr>

  {* Separator *}
  {include inc.window.tr.separator.tuborg.tpl}

  {* Form input fields *}
  <tr>
    <td>

      <table width="100%" cellpadding="5" cellspacing="0" border="0">

        {ifdefined %TPL_ITEM_MESSAGE%}
          <tr>
            <td colspan="2">{%TPL_ITEM_MESSAGE%}</td>
          </tr>
        {/ifdefined}

        <tr>
          <td align="right" nowrap>{%I18N_NAME%}:</td>
          <td width="100%">{%TPL_ITEM_NAME%}</td>
        </tr>
        <tr>
          <td align="right" nowrap>{%I18N_USER%}:</td>
          <td colspan="4">

            <table cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td>{%TPL_ITEM_USER_OPTIONS%}</td>
                <td>&nbsp;</td>
                <td><tt>rwx</tt></td>
                <td>{%TPL_ITEM_ACCESS_USER_READ%}</td>
                <td>{%TPL_ITEM_ACCESS_USER_WRITE%}</td>
                <td>{%TPL_ITEM_ACCESS_USER_EXEC%}</td>

                <td>&nbsp;&nbsp;&nbsp;</td>

                <td nowrap>{%I18N_GROUP%}:</td>
                <td>&nbsp;</td>
                <td>{%TPL_ITEM_GROUP_OPTIONS%}</td>
                <td>&nbsp;</td>
                <td><tt>rwx</tt></td>
                <td>{%TPL_ITEM_ACCESS_GROUP_READ%}</td>
                <td>{%TPL_ITEM_ACCESS_GROUP_WRITE%}</td>
                <td>{%TPL_ITEM_ACCESS_GROUP_EXEC%}</td>

                <td>&nbsp;&nbsp;&nbsp;</td>

                <td nowrap>{%I18N_WORLD%}:</td>
                <td>&nbsp;</td>
                <td><tt>rwx</tt></td>
                <td>{%TPL_ITEM_ACCESS_WORLD_READ%}</td>
                <td>{%TPL_ITEM_ACCESS_WORLD_WRITE%}</td>
                <td>{%TPL_ITEM_ACCESS_WORLD_EXEC%}</td>
              </tr>
            </table>

          </td>
        </tr>
      </table>

    </td>
  </tr>

  {ifdefined %TPL_ITEM%}

    {* Separator *}
    {include inc.window.tr.separator.tuborg.tpl}

    {* Content area *}
    <tr>
      <td>
        <table width="100%" cellpadding="5" cellspacing="0" border="0">
          <tr>
            <td>

              <div class="areaoverflow" style="height:{%EDIT_DIR_AREA_HEIGHT%}px;">
                <table width="100%" cellpadding="0" cellspacing="0" border="0">

                  <tr>
                    <!-- <td style="border-right: {%COLOR_RAPPS_SHADOW%} solid 1px">&nbsp;</td> -->
                    <td style="border-right: {%COLOR_RAPPS_SHADOW%} solid 1px" colspan="2" align="center">&nbsp;{%I18N_SORT%}&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td width="100%" style="border-right: {%COLOR_RAPPS_SHADOW%} solid 1px">&nbsp;</td>
                    <td>&nbsp;{%I18N_DIR_IN_MENU%}</td>
                    <td style="border-right: {%COLOR_RAPPS_SHADOW%} solid 1px">&nbsp;</td>
                    <td>&nbsp;{%I18N_DIR_IS_INDEX%}</td>
                    <td>&nbsp;</td>
                  </tr>

                  <tr>
                    <td colspan="11"><img src="{%PATH_IMAGES%}0.gif"
                      width="1" height="1" alt="" border="0"></td>
                  </tr>
                  <tr bgcolor="{%COLOR_RAPPS_SHADOW%}">
                    <td colspan="11"><img src="{%PATH_IMAGES%}0.gif"
                      width="1" height="1" alt="" border="0"></td>
                  </tr>
                  <tr>
                    <td colspan="11"><img src="{%PATH_IMAGES%}0.gif"
                      width="1" height="1" alt="" border="0"></td>
                  </tr>

                  {foreach %TPL_ITEM%}
                    <tr bgcolor="{%COLOR_BGCOLOR%}" onmouseover="mover(this)"';
                      onmouseout="mout(this, '{%COLOR_BGCOLOR%}')">
                      <!-- <td align="center" style="border-right: {%COLOR_RAPPS_SHADOW%} solid 1px">{%TPL_ITEM['MARKER']%}</td> -->
                      <td align="center">{%TPL_ITEM['BUTTON1']%}</td>
                      <td align="center" style="border-right: {%COLOR_RAPPS_SHADOW%} solid 1px">{%TPL_ITEM['BUTTON2']%}</td>
                      <td></td>
                      <td><img src="{%PATH_IMAGES%}{%TPL_ITEM['IMAGE']%}"
                        width="18" height="20" alt="" border="0"></td>
                      <td></td>
                      <td nowrap style="border-right: {%COLOR_RAPPS_SHADOW%} solid 1px">{%TPL_ITEM['NAME']%}</td>
                      <td align="center">{%TPL_ITEM['IN_MENU']%}</td>
                      <td style="border-right: {%COLOR_RAPPS_SHADOW%} solid 1px">&nbsp;</td>
                      <td align="center">{%TPL_ITEM['IS_INDEX']%}</td>
                      <td>&nbsp;</td>
                    </tr>
                  {/foreach}

                  <tr>
                    <td colspan="11"><img src="{%PATH_IMAGES%}0.gif"
                      width="1" height="1" alt="" border="0"></td>
                  </tr>
                  <tr bgcolor="{%COLOR_RAPPS_SHADOW%}">
                    <td colspan="11"><img src="{%PATH_IMAGES%}0.gif"
                      width="1" height="1" alt="" border="0"></td>
                  </tr>
                  <tr>
                    <td colspan="11"><img src="{%PATH_IMAGES%}0.gif"
                      width="1" height="5" alt="" border="0"></td>
                  </tr>

                  <tr>
                    <td colspan="8" align="right" nowrap>{%I18N_DIR_NO_INDEX%}</td>
                    <td align="center">{%TPL_ITEM_NO_INDEX%}</td>
                    <td></td>
                  </tr>

                  <tr>
                    <td colspan="11"><img src="{%PATH_IMAGES%}0.gif"
                      width="1" height="5" alt="" border="0"></td>
                  </tr>

                </table>
              </div>

            </td>
          </tr>

        </table>
      </td>
    </tr>
<!--    
    <tr>
      <td>

        <table cellpadding="0" cellspacing="0" border="0">
          <tr>
            <td><img hspace="10" vspace="2"
              src="{%PATH_IMAGES%}with.gif" width="18" height="20"
              alt="0" border="0"></td>
            <td>
                <script language="JavaScript" type="text/javascript">
                  document.write(getMarkerActions{%TPL_PLUGIN_ID%}());
                </script>
                {%I18N_DIR_WITH_SELECTED%}
            </td>
            <td>&nbsp;</td>
            <td>{%TPL_ITEM_ACTION1%}</td>
          </tr>
        </table>

      </td>
    </tr>
-->
  {/ifdefined}

  {* Separator *}
  {include inc.window.tr.separator.tuborg.tpl}

  {* Window form buttons *}
  {include inc.window.tr.threebutton.tpl}

</table>

</form>
</div>
