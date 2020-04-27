<table width="100%" cellpadding="5" cellspacing="0" border="0">
  <tr>
    <td bgcolor="{%COLOR_RAPPS_CONTENT%}" valign="top" class="rappsbox">
      {plugin PrivateFrontMenuDefaultDisplay
              img = "bullet.gif"
              imghspace = "5"
      }
    </td>
    <td></td>
    <td width="100%" valign="top">

      <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td>
            {plugin PrivateFrontBreadcrumbDisplay}
          </td>
        </tr>
        <tr>
          <td width="100%">
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td width="100%"><hr/></td>
                <td>&nbsp;</td>
                <td>
                  {plugin CustomOwnership}
                </td>
              </tr>
              <tr>
                <td colspan="2"><img src="img/0.gif"
                  width="1" height="8" alt="" border="0"></td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td>
            {%RUNTIME_END_NOINDEX%}

            {plugin PrivateFrontHistoryDisplay}

            {%RUNTIME_BEGIN_NOINDEX%}
          </td>
        </tr>
      </table>

    </td>
    <td>{plugin PrivateFrontMenuEmptyDisplay}</td>
  </tr>

</table>
