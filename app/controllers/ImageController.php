<?php
/**
 * Petdiscount Magento Importer
 *
 * @author Ruthger Idema <ruthger.idema@gmail.com>
 * @license http://creativecommons.org/licenses/MIT/ MIT
 */


class ImageController {

    /**
     * @return string
     */
    public function DownloadImages() {

        unlink('temp/images.zip');
        $url = "https://api.petdiscount.nl/500.zip";
        $zipFile = "temp/images.zip"; // Local Zip File Path
        $zipResource = fopen($zipFile, "w");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER,true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FILE, $zipResource);
        $page = curl_exec($ch);
        if(!$page) {
            return "Error :- ".curl_error($ch);
        }
        curl_close($ch);

        system('unzip -o -q temp/images.zip -d temp/images');
        unlink('temp/images.zip');

        return "images downloaded";
    }

}