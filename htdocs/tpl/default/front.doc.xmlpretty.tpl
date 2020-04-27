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
          <td width="100%"><hr/></td>
        </tr>
        <tr>
          <td>
            {plugin PrivateXmlPrettyPrinter}
          </td>
        </tr>
      </table>

    </td>
    <td>{plugin PrivateFrontMenuEmptyDisplay}</td>
  </tr>

</table>
