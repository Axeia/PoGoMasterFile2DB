<?php

/**
 * Downloads SVN file name list
 */
class FileDownloaderSvnFileNames extends FileDownloader
{
    /**
     * Downloads the file if there isn't one yet or if isOutdated() returns true.
     * 
     * @param string $from svn URL to a directory
     * @param string $fileName name to save the file to
     */
    public function __construct($from, $fileName)
    {
        $this->downloadUrl = $from;
        $this->fileName = $fileName;
        $this->downloadedFileLocation = self::$downloadToDirectory.$fileName;

        $this->downloadIfNeeded();
    }

    /**
     * Checks if the file is considered outdated.
     *
     * @return boolean
     */
    protected function isOutdated()
    {
        /** Check if the file is older than 7 days */
        $dtNow = new DateTime();
        $dtFileLastModified = new DateTime();
        $fileLastModifiedTimeStamp = filemtime($this->downloadedFileLocation);
        $dtFileLastModified->setTimestamp($fileLastModifiedTimeStamp);
        $dateDifference = date_diff($dtFileLastModified, $dtNow);

        return intval($dateDifference->format('%d'))>= 7;
    }

    /**
     * Downloads the file
     * 
     * May write a message to $messages if lacking permissions
     * or if SVN is not installed.
     *
     * @return void
     */
    protected function downloadFile()
    {        
        //Check if we have permissions to create files.
        if($this->hasWritePermission())
        {
            $svnOutput = shell_exec('svn ls https://github.com/ZeChrales/PogoAssets/trunk/pokemon_icons/');
            //Changes line endings so that windows (notepad) actually puts each name on a new line.
            $svnOutput = preg_replace('~\R~u', "\r\n", $svnOutput);
            file_put_contents($this->downloadedFileLocation, $svnOutput);
        }
    }

    public function getImageFilesArray()
    {
        return explode("\n", file_get_contents($this->downloadedFileLocation));
    }
}

?>