<table width="100%" cellpadding="5" cellspacing="0" border="0">
  <tr>
    <td bgcolor="{%COLOR_RAPPS_CONTENT%}" valign="top" align="center"
      class="rappsbox">

      {plugin PrivateFrontMenuDefaultDisplay
              img = "bullet.gif"
              imghspace = "5"
      }

      <br />

      {plugin CustomReferrer}

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
            </table>
          </td>
        </tr>
        <tr>
          <td>
            {%RUNTIME_END_NOINDEX%}

            {plugin PrivateFrontDocumentDisplay}

            {%RUNTIME_BEGIN_NOINDEX%}
          </td>
        </tr>
        <tr>
          <td>
            <br />
            <hr />
            {plugin CustomWikiWordReference}
            <br />
          </td>
        </tr>
        <tr>
          <td>
            {plugin PrivateFrontCommentTeaserDisplay}
          </td>
        </tr>
        <tr>
          <td>
            <br />
            {plugin CustomPager}
          </td>
        </tr>
      </table>

    </td>
    <td>{plugin PrivateFrontMenuEmptyDisplay}</td>
  </tr>

</table>
