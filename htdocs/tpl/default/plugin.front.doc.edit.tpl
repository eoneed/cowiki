<div style="display:inline;">

{ifdefined %TPL_ENABLE_UPLOAD%}
<script type="text/javascript">
<!--

function attachmentChanged()
{
    var usedFields = 0;
    var fields = new Array();
    for (var i = 0; i < document.editdoc.elements.length; i++) {
        if (document.editdoc.elements[i].type == 'file' &&
            document.editdoc.elements[i].name.substr(0, 7) == 'upload_') {
            fields[fields.length] = document.editdoc.elements[i];
        }
    }

    for (var i = 0; i < fields.length; i++) {
        if (fields[i].value.length > 0) {
            usedFields++;
        }
    }

    if (usedFields == fields.length) {
        var lastRow = document.getElementById('attachment_row_' + usedFields);
        if (lastRow) {

            var newRow = document.createElement('TR');
            newRow.id = 'attachment_row_' + (usedFields + 1);

            var td = document.createElement('TD');
            newRow.appendChild(td);
            td.align = 'left';
            td.appendChild(document.createTextNode('{%TPL_UPLOAD_FILE_NAME%} ' + (usedFields + 1) + ':'));
            td.appendChild(document.createTextNode(' '));

            var file = document.createElement('INPUT');
            file.type = 'file';
            td.appendChild(file);
            file.name = 'upload_' + (usedFields + 1);
            file.onchange = function() { attachmentChanged(); };
            file.size = 25;
            file.className = 'fixed';
            td = document.createElement('TD');
            newRow.appendChild(td);
            td.align = 'left';

            var select = document.createElement('SELECT');
            td.appendChild(select);
            select.name = 'upload_disposition_' + (usedFields + 1);
            select.options[0] = new Option('{%TPL_UPLOAD_DISPOSTION_ATTACH%}', 'attachment', true);
            select.options[1] = new Option('{%TPL_UPLOAD_DISPOSTION_INLINE%}', 'inline');
            lastRow.parentNode.insertBefore(newRow, lastRow.nextSibling);
        }
    }
}

-->
</script>
{/ifdefined}

<form action="{%TPL_FORM_ACTION%}" method="post" enctype="multipart/form-data" name="editdoc" style="margin: 0px">

{%TPL_FORM_CONTROL_DATA%}

<table width="100%" bgcolor="{%COLOR_TUBORG_CONTENT%}" class="window"
  cellpadding="0" cellspacing="0" border="0">

  {* Header *}
  <tr>
    <td colspan="10">
      <table width="100%" cellpadding="3" cellspacing="0" border="0">
        <tr valign="top">
          <td nowrap width="100%" class="wintitle">
            {%I18N_DOC_HEAD_CREATE_EDIT%}
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
            <td colspan="5">{%TPL_ITEM_MESSAGE%}</td>
          </tr>
        {/ifdefined}

        <tr>
          <td align="right" nowrap>{%I18N_NAME%}:</td>
          <td>{%TPL_ITEM_NAME%}</td>
          <td>&nbsp;</td>
          <td nowrap>{%I18N_DOC_KEYWORDS%}:</td>
          <td width="100%">{%TPL_ITEM_KEYWORDS%}</td>
        </tr>
        <tr>
          <td align="right" nowrap>{%I18N_USER%}:</td>
          <td colspan="4">

            <table cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td>{%TPL_ITEM_USER_OPTIONS%}</td>
                <td>&nbsp;</td>
                <td><tt>rw</tt></td>
                <td>{%TPL_ITEM_ACCESS_USER_READ%}</td>
                <td>{%TPL_ITEM_ACCESS_USER_WRITE%}</td>

                <td>&nbsp;&nbsp;&nbsp;</td>

                <td nowrap>{%I18N_GROUP%}:</td>
                <td>&nbsp;</td>
                <td>{%TPL_ITEM_GROUP_OPTIONS%}</td>
                <td>&nbsp;</td>
                <td><tt>rw</tt></td>
                <td>{%TPL_ITEM_ACCESS_GROUP_READ%}</td>
                <td>{%TPL_ITEM_ACCESS_GROUP_WRITE%}</td>

                <td>&nbsp;&nbsp;&nbsp;</td>

                <td nowrap>{%I18N_WORLD%}:</td>
                <td>&nbsp;</td>
                <td><tt>rw</tt></td>
                <td>{%TPL_ITEM_ACCESS_WORLD_READ%}</td>
                <td>{%TPL_ITEM_ACCESS_WORLD_WRITE%}</td>
              </tr>
            </table>

          </td>
        </tr>
      </table>

    </td>
  </tr>

  {* Separator *}
  {include inc.window.tr.separator.tuborg.tpl}

  {* Content area *}
  <tr>
    <td>
      <table width="100%" cellpadding="5" cellspacing="0" border="0">
        <tr>
          <td>
            {%TPL_ITEM_CONTENT%}
          </td>
        </tr>
      </table>
    </td>
  </tr>

  {* Separator *}
  {include inc.window.tr.separator.tuborg.tpl}

{ifdefined %TPL_ENABLE_UPLOAD%}
  {* Upload area *}
  <!-- <ack> -->
  <tr>
    <td>
      <table cellpadding="5" cellspacing="0" border="0">
        <tr>
          <td>Attach file: <input type="file" name="attachment" /></td>
        </tr>
      </table>
    </td>
  </tr>
  <!-- </ack> -->

  <!-- <ack>
  <tr>
    <td>
      <table width="100%" cellpadding="5" cellspacing="0" border="0">
        <tr>
          <td>
            <table>
              <tr id="attachment_row_1">
                <td>{%TPL_UPLOAD_FILE_NAME%} 1:&nbsp;
                  <input name="upload_1" type="file"
                    onchange="attachmentChanged()" size="25"
                  />
                </td>
                <td>
                  <select name="upload_disposition_1">
                    <option value="inline" selected="selected"
                      >{%TPL_UPLOAD_DISPOSTION_INLINE%}</option>
                    <option value="attachment"
                      >{%TPL_UPLOAD_DISPOSTION_ATTACH%}</option>
                  </select>
                </td>
              </tr>
              <tr>
                <td colspan="2">
                  ({%TPL_UPLOAD_MAX_MSG%}: {%TPL_UPLOAD_MAX_SIZE%})
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  </ack> -->
{/ifdefined}

{ifdefined %TPL_UPLOAD_WARNING%}
  <tr>
    <td>
      <table width="100%" cellpadding="5" cellspacing="0" border="0">
        <tr>
          <td>{%TPL_UPLOAD_WARNING%}</td>
        </tr>
      </table>
    </td>
  </tr>
{/ifdefined}

  {* Separator *}
  {include inc.window.tr.separator.tuborg.tpl}

  <tr>
    <td>
      <table cellpadding="2" cellspacing="0" border="0">
        <tr>
          <td>{%TPL_ITEM_MINOR_CHANGE%}</td>
          <td>{%I18N_DOC_MINOR_CHANGE%}</td>
          <td>&nbsp;</td>
          <td>{%TPL_ITEM_ALLOW_COMMENTS%}</td>
          <td>{%I18N_DOC_ALLOW_COMMENTS%}</td>

          <td>&nbsp;</td>
          <td>{%TPL_ITEM_NOTIFICATION_USER%}</td>
          <td>{%I18N_DOC_NOTIFICATION_USER%}</td>
          <td>&nbsp;</td>
          <td>{%TPL_ITEM_NOTIFICATION_GROUP%}</td>
          <td>{%I18N_DOC_NOTIFICATION_GROUP%}</td>

        </tr>
      </table>
    </td>
  </tr>

  {* Separator *}
  {include inc.window.tr.separator.tuborg.tpl}

  {* Window form buttons *}
  {include inc.window.tr.threebutton.tpl}

</table>

</form>
</div>

{ifnotdefined %TPL_MESSAGE%}

  <br /><hr /><br />
  <strong>{%I18N_AVAIL_PLUGIN%}:</strong>
  <br /><br />
  {plugin CustomPluginInfo}

{/ifnotdefined}