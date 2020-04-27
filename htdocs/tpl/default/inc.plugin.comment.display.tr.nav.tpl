<tr>
  <td>
    <table cellpadding="0" cellspacing="0" border="0">
      <tr>

        {ifdefined %TPL_PREV_THREAD_HREF%}
          <td onmouseover="mover(this)" onmouseout="mout(this)">
            <table cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td><a onfocus="if(this.blur)this.blur()" href="{%TPL_PREV_THREAD_HREF%}"><img src="{%PATH_IMAGES%}left.gif" hspace="1"
                  width="18" height="20" alt="" border="0" /></a></td>
                <td nowrap><a onfocus="if(this.blur)this.blur()" class="label" onfocus="if(this.blur)this.blur()" href="{%TPL_PREV_THREAD_HREF%}">{%I18N_COM_PREV_THREAD%}</a>&nbsp;</td>
              </tr>
            </table>
          </td>
        {/ifdefined}

        {ifnotdefined %TPL_PREV_THREAD_HREF%}
          <td>
            <table cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td><img src="{%PATH_IMAGES%}left_disabled.gif" hspace="1"
                  width="18" height="20" alt="" border="0" /></td>
                <td nowrap class="labeldisabled">{%I18N_COM_PREV_THREAD%}&nbsp;</td>
              </tr>
            </table>
          </td>
        {/ifnotdefined}

        <td style="background-image:url({%PATH_IMAGES%}vert.gif)"><img
            src="{%PATH_IMAGES%}0.gif" width="2" height="22"
            alt="" border="0"></td>

        {ifdefined %TPL_NEXT_THREAD_HREF%}
          <td onmouseover="mover(this)" onmouseout="mout(this)">
            <table cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td nowrap>&nbsp;<a onfocus="if(this.blur)this.blur()" class="label" href="{%TPL_NEXT_THREAD_HREF%}">{%I18N_COM_NEXT_THREAD%}</a></td>
                <td><a onfocus="if(this.blur)this.blur()" href="{%TPL_NEXT_THREAD_HREF%}"><img src="{%PATH_IMAGES%}right.gif" hspace="1"
                  width="18" height="20" alt="" border="0" /></a></td>
              </tr>
            </table>
          </td>
        {/ifdefined}

        {ifnotdefined %TPL_NEXT_THREAD_HREF%}
          <td>
            <table cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td nowrap class="labeldisabled">&nbsp;{%I18N_COM_NEXT_THREAD%}</td>
                <td><img src="{%PATH_IMAGES%}right_disabled.gif" hspace="1"
                  width="18" height="20" alt="" border="0" /></td>
              </tr>
            </table>
          </td>
        {/ifnotdefined}

        <td style="background-image:url({%PATH_IMAGES%}vert.gif)"><img
            src="{%PATH_IMAGES%}0.gif" width="2" height="1"
            alt="" border="0"></td>

        <td width="100%">&nbsp;</td>

        <td style="background-image:url({%PATH_IMAGES%}vert.gif)"><img
            src="{%PATH_IMAGES%}0.gif" width="2" height="1"
            alt="" border="0"></td>

        {ifdefined %TPL_PREV_POSTING_HREF%}
          <td onmouseover="mover(this)" onmouseout="mout(this)">
            <table cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td><a onfocus="if(this.blur)this.blur()" href="{%TPL_PREV_POSTING_HREF%}"><img src="{%PATH_IMAGES%}up.gif" hspace="1"
                  width="18" height="20" alt="" border="0" /></a></td>
                <td nowrap><a onfocus="if(this.blur)this.blur()" class="label" href="{%TPL_PREV_POSTING_HREF%}">{%I18N_COM_PREV_POSTING%}</a>&nbsp;</td>
              </tr>
            </table>
          </td>
        {/ifdefined}

        {ifnotdefined %TPL_PREV_POSTING_HREF%}
          <td>
            <table cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td><img src="{%PATH_IMAGES%}up_disabled.gif" hspace="1"
                  width="18" height="20" alt="" border="0" /></td>
                <td nowrap class="labeldisabled">{%I18N_COM_PREV_POSTING%}&nbsp;</td>
              </tr>
            </table>
          </td>
        {/ifnotdefined}

        <td style="background-image:url({%PATH_IMAGES%}vert.gif)"><img
            src="{%PATH_IMAGES%}0.gif" width="2" height="1"
            alt="" border="0"></td>

        {ifdefined %TPL_NEXT_POSTING_HREF%}
          <td onmouseover="mover(this)" onmouseout="mout(this)">
            <table cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td nowrap>&nbsp;<a onfocus="if(this.blur)this.blur()" class="label" href="{%TPL_NEXT_POSTING_HREF%}">{%I18N_COM_NEXT_POSTING%}</a></td>
                <td><a onfocus="if(this.blur)this.blur()" href="{%TPL_NEXT_POSTING_HREF%}"><img src="{%PATH_IMAGES%}down.gif" hspace="1"
                  width="18" height="20" alt="" border="0" /></a></td>
              </tr>
            </table>
          </td>
        {/ifdefined}

        {ifnotdefined %TPL_NEXT_POSTING_HREF%}
          <td>
            <table cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td nowrap class="labeldisabled">&nbsp;{%I18N_COM_NEXT_POSTING%}</td>
                <td><img src="{%PATH_IMAGES%}down_disabled.gif" hspace="1"
                  width="18" height="20" alt="" border="0" /></td>
              </tr>
            </table>
          </td>
        {/ifnotdefined}

      </tr>
    </table>
  </td>
</tr>
