<?php

/**
 *
 * $Id: class.ImageManipulator.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * @package     lib
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @copyright   (C) Daniel T. Gorski, {@link http://www.develnet.org}
 * @license     http://www.gnu.org/licenses/gpl.html
 * @version     $Revision: 19 $
 *
 */

/**
 * coWiki - Image manipulator class
 *
 * @package     lib
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 *
 * @todo        [D11N]  Complete documentation
 */
class ImageManipulator extends Object {

    /**
     * Class constructor
     *
     * @access  public
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function __construct() {
        // For sure, check if GD functions are compiled in
        if (!function_exists('imagecreatetruecolor')) {
            throw new Exception();
        }
    }

    /**
     * Resize image from string
     *
     * @access  public
     * @param   string
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function resizeImageFromString(&$sStr) {

        $sFileName = tempnam('/tmp', 'coWiki_img_');

        try {
            $Out = new FileOutputStream($sFileName);
            $Out->write($sStr);
            $Out->close();

        } catch (Exception $e) {
            throw $e; // rethrow
        }
        
        try {
            $sImg = $this->resizeImageFile($sFileName);
        } catch (Exception $e) {
            @unlink($sFileName);
            throw $e;
        }

        // Remove temp file
        @unlink($sFileName);

        return $sImg;
    }

    /**
     * &resize image file
     *
     * @access  public
     * @param   string
     * @param   integer
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nWidth"
     */
    public function &resizeImageFile($sFileName, $nWidth = 120) {
        $aImgInfo = @getimagesize($sFileName);

        try {
            switch ($aImgInfo[2]) {

                case 2:
                    $sImg = $this->resizeJpegFile($sFileName, $nWidth);
                    break;

                default:
                    throw new Exception();
                    break;
            }

        } catch (Exception $e) {
            throw $e;
        }

        return $sImg;
    }

    /**
     * &resize jpeg file
     *
     * @access  protected
     * @param   string
     * @param   integer
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nWidth"
     */
    protected function &resizeJpegFile($sFileName, $nWidth) {

        // JPEG support might be disabled
        if (!function_exists('imagecreatefromjpeg')) {
            throw new Exception();
        }

        // ---

        $aImgInfo = @getimagesize($sFileName);

        // Avoid division by zero
        $nWidth = (int)abs($nWidth) < 1 ? 1 : (int)abs($nWidth);

        // Scale: src_height / (src_width / width)
        $nHeight = floor($aImgInfo[1] / ($aImgInfo[0]/$nWidth));

        // ---

        $rImg = imagecreatefromjpeg($sFileName);

        $rImgOut = imagecreatetruecolor($nWidth, $nHeight);

        @imagecopyresampled(
            $rImgOut,
            $rImg,
            0, 0, 0, 0,
            $nWidth, $nHeight, $aImgInfo[0], $aImgInfo[1]);

        ob_start();
        @imagejpeg($rImgOut);
        $sImg = ob_get_contents(); ob_end_clean();

        return $sImg;
    }

} // of class

?>
