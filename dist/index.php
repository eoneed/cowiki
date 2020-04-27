<?php

/**
 *
 * $Id: index.php 27 2011-01-09 12:37:59Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * @package     dist
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @copyright   (C) Daniel T. Gorski, {@link http://www.develnet.org}
 * @license     http://www.gnu.org/licenses/gpl.html
 * @version     $Revision: 27 $
 *
 */

/**
 * coWiki - snapshots and nighly builds
 *
 * @package     dist
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 */

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
    <title>coWiki - snapshots and nighly builds</title>
  <meta name="robots" content="noindex, nofollow">
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
</head>

<style media="screen" type="text/css">

  body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 15px;
    margin:3% 5% 3% 5%;
  }

  td, h1 {
    font-size: 16px;
  }

  a {
    color: #0000DD;
  }

  a:visited {
    color: #999999;
  }

  hr {
    width: 100%;
    height: 1px;
    background-color: #CCCCCC;
    border-width: 0px;
    margin: 15px 0px 15px 0px;
  }

  .mono {
    font-family: Courier New, Courier, monospace;
  }

</style>

<body>

<table cellpadding="0" cellspacing="0" border="0">

  <tr>
    <td style="border-width:1px; border-style:solid; border-color:#BBBBBB;
      background-color:#EEEEEE"><a target="_blank"
      href="http://www.cowiki.org"><img src="http://www.cowiki.org/img/cowiki.gif"
      width="140" height="40" alt="coWiki" border="0"></a></td>
    <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
    <td style="font-size:25px" width="100%">
      <h1>Snapshots and nightly builds repository</h1>
    </td>
  </tr>

  <tr>
    <td colspan="3">&nbsp;</td>
  </tr>

  <tr>
    <td colspan="3" style="font-size:16px">
      For more information about the coWiki web collaboration software
      project - or the latest stable version
      - please visit its home at <a
      target="_blank" href="http://www.cowiki.org/">www.cowiki.org</a>.
      <i>Warning:</i> The listed files are (maybe unstable) CVS snapshots.
      Do not whine if they do not work properly for you or if they do
      not match your expectation. The current
      <a target="_blank" href="http://cowiki.tigris.org/source/browse/cowiki/ChangeLog?rev=HEAD&content-type=text/vnd.viewcvs-markup">ChangeLog</a>
      may differ from the latest snapshot due to a time shift.
    </td>
  </tr>

</table>

<hr />

<blockquote>
  <table cellpadding="0" cellspacing="0" border="0">

<?php

    // --------------------------------------------------------------------

    $sDir = dirname(realpath(__FILE__)) . '/';
    $rDir = @opendir($sDir);

    $aData = array();
    while ($sFileName = @readdir($rDir)) {

        if ($sFileName == '.' || $sFileName == '..') {
            continue;
        }

        if (substr($sFileName, 0, 6) != 'cowiki') {
            continue;
        }

        if (substr($sFileName, -2) != 'gz') {
            continue;
        }

        $sName      = $sFileName;
        $sFullPath  = $sDir . $sName;
        $nCtime     = filemtime($sFullPath);
        $sDate      = strftime('%d. %b %y, %H:%M', $nCtime);
        $sSize      = number_format(filesize($sFullPath), 0, '.', '.');
        $sKsize     = number_format(filesize($sFullPath) / 1024 , 0, '.', '.');

        $aData[] = array(
                      'name'  => $sName,
                      'full'  => $FullsName,
                      'mtime' => $nCtime,
                      'date'  => $sDate,
                      'size'  => $sSize,
                      'ksize' => $sKsize
                   );
    }

    // --------------------------------------------------------------------

    // Sort entries by mtime
    $aSortBy = array();
    foreach($aData as $v) {
        $aSortBy[] = $v['mtime'];
    }
    array_multisort($aSortBy, SORT_DESC, $aData);

    // --------------------------------------------------------------------

    renderRow(
        $aData,
        0,
        1,
        '<b>Freshest</b> &nbsp; (latest snapshot)'
    );

    renderRow(
        $aData,
        1,
        sizeof($aData),
        '<br /><b>Withered</b> &nbsp; (older)'
    );

    // --------------------------------------------------------------------

?>

  </table>
</blockquote>

<hr />

<table cellpadding="0" cellspacing="0" border="0">
    <td style="font-size:16px">

      If you encounter problems with the latest snapshot, please try an
      older one (withered), and report your problem on the

      <a target="_blank" href="http://cowiki.tigris.org/servlets/ProjectMailingListList">
      developer mailing list</a> (dev [at] cowiki [dot] tigris [dot] org).

      If you have found a real bug, feed it to our
      <a target="_blank" href="http://cowiki.tigris.org/servlets/ProjectIssues">bug eating machine</a>.

      Please read the <a target="_blank" href="http://www.cowiki.org/16.html">
      bug reporting policy</a> first.

      <br /><br />

      Thank you for your interest and your efforts ...
      your coWiki development team.

    </td>
  </tr>
</table>

</body>
</html>

<?php

    // --------------------------------------------------------------------

    /**
     * Render a single output table row
     *
     * @param array
     * @param integer
     * @param integer
     * @param string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    function renderRow($aData, $nBegin, $nEnd, $sStr) {

        echo '<tr>';
        echo    '<td colspan="6" nowrap>';
        echo       $sStr;
        echo    '</td>';
        echo '</tr>';

        for ($i=$nBegin; $i<$nEnd; $i++) {

            echo '<tr>';
            echo    '<td>&nbsp;&nbsp;&nbsp;</td>';
            echo    '<td class="mono" nowrap>';
            echo      '<a href="'.$aData[$i]['name'].'">';
            echo        $aData[$i]['name'];
            echo      '</a';
            echo    '</td>';

            echo    '<td>&nbsp;&nbsp;</td>';

            echo    '<td class="mono" nowrap>';
            echo      '('.$aData[$i]['ksize'] . ' KB)';
            echo    '</td>';

            echo    '<td>&nbsp;&nbsp;&nbsp;</td>';

            echo    '<td nowrap>';
            echo      $aData[$i]['date'];
            echo    '</td>';

            echo '</tr>';
        }
    }

/*

    I wanna be a creature
    Before humanity is on earth
    No hate no wars, no ignorance, no force
    Only one with mother earth
    No sorrow escorts us from our birth
    Be a bridge over the river of love
    From justice to happiness
    Set up by the hands of freedom

    Yet wishes could be true
    Don't turn away it could be you
    The answer will roar at us
    If we listen into ourselves
    We twaddle since centuries
    About justice and peace
    We're living in a material world
    Supported through avarice
    Mendacity is our life
    We conceal love and light

    Today we're abusing
    Animals and nature
    To enrich ourselves

    We're sleeping so deep
    While the bomb is ticking
    We pity ourselves
    While the fear is kicking

    Now it's time for the world
    To see a man with open eyes
    Now it's time to realize
    We tell ourselves the best of lies
    Now it's time to see the fact
    We all are the unity
    Now it's time for us
    To deliberate our aim

    No hate no wars no ignorance
    No politics no pain no force
    No weapons no oppression no sorrow
    No chemicals no dust no leaders
    No violence no dust no leaders

    Now it's time for the world
    To see a man with open eyes
    Now it's time to realize
    We tell ourselves the best of lies
    Now it's time to see the fact
    We all are the unity
    Now it's time for us
    To deliberate our aim

    Now it's time
    Now !
    See that aim

    Now it's time for the world
    To see a man with open eyes

*/

?>