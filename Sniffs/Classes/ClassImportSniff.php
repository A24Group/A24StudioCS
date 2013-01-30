<?php
/**
 * A24StudioCS_Sniffs_Classes_ClassImportSniff.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Alec Erasmus <alec.erasmus@a24group.com>
 * @since     28 January 2013
 */

/**
 * A24StudioCS_Sniffs_Classes_ClassImportSniff.
 *
 * Checks the separation between methods in a class or interface.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Alec Erasmus <alec.erasmus@a24group.com>
 * @since     28 January 2013
 */
class A24StudioCS_Sniffs_Classes_ClassImportSniff implements PHP_CodeSniffer_Sniff
{

    /**
     * The line number of the last occurens of the token.
     *
     * @var int
     */
    static $iLastOccurrence;

    /**
     * Array of the imported classes as strings
     *
     * @var array
     */
    static $arrImports = array();

    /**
     * The number of use statements already loop through
     *
     * @var int
     */
    static $iNumberOfStatements;

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_USE);

    }//end register()


    /**
     * Processes this sniff, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @author Alec Erasmus <alec.erasmus@a24group.com>
     * @since  28 January 2013
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $arrLineTokens = array();
        $iCount = 0;
        $arrWhiteSpaces = array();
        $sContent = '';
        $arrClass = array();

        /*
            Check the blank lines
            between use statements.
        */

        self::$iNumberOfStatements++;
        // Check for use statements
        if (count($tokens[$stackPtr]) === 0) {
            $this->clearStaticVariables($iCount);
            return;
        }
        // Loop through the file tokens
        foreach ($tokens as $sKey => $sValue) {
            // Count the number of use statements in the file
            if ($sValue['type'] === 'T_USE') {
                $iCount++;
            }
            // Get all tokens on the current current use statement tokens line
            if ($sValue['line'] == $tokens[$stackPtr]['line']) {
                $arrLineTokens[] = $sValue;
            }
            if ($sValue['type'] == 'T_CLASS') {
                $arrClass = $sValue;
            }
        }

        // Loop through the tokens on a single line
        foreach ($arrLineTokens as $arrLineToken) {
            // If a white space token is found
            if ($arrLineToken['type'] == 'T_WHITESPACE') {
                // If there is more than one white space next to each other
                if (strlen($arrLineToken['content']) != 1) {
                    if ($arrLineToken['line'] > $arrClass['line']) {
                        return;
                    }
                    $error = 'Found %s white spaces in "use" statement';
                    $data  = array(strlen($arrLineToken['content']));
                    $phpcsFile->addError($error, $stackPtr, 'WhiteSpace', $data);
                }
            }
            // Get the class that in imported in the use statements
            // Removes all white space and ';' and the use key word
            if (
                $arrLineToken['type'] == 'T_USE' ||
                $arrLineToken['type'] == 'T_WHITESPACE' ||
                $arrLineToken['type'] == 'T_SEMICOLON'
            )
            {
            } else {
                $sContent .= $arrLineToken['content'];
            }
        }
        // Check for duplicate use statements
        if (!in_array($sContent, self::$arrImports)) {
            // Adds to an array if not found for the next use token
            self::$arrImports[] = $sContent;
        } else {
            $error = 'Multiple "use" statements of %s was found';
            $data  = array($sContent);
            $phpcsFile->addError($error, $stackPtr, 'DuplicateUseStatement', $data);
        }

        // Set the last occurrence of the use statement
        $arrUserStatement = $tokens[$stackPtr];
        if (!self::$iLastOccurrence) {
            self::$iLastOccurrence = $arrUserStatement['line'];
            $this->clearStaticVariables($iCount);
            return;
        }

        if (($arrUserStatement['line'] - self::$iLastOccurrence) != 1) {
            $error = 'Excessive white spaces found';
            $phpcsFile->addError($error, $stackPtr, 'WhiteSpace');
            self::$iLastOccurrence = $arrUserStatement['line'];
        } else {
            self::$iLastOccurrence = $arrUserStatement['line'];
        }

        $this->clearStaticVariables($iCount);
        return;

    }//end process()

    /**
     * Destroys the static variables once a file is
     * completed so that the next file have empty
     * variables.
     *
     * @param int $iCount - The number of times the class is going to be used for a file
     *
     * @author Alec Erasmus <alec.erasmus@a24group.com>
     * @since  29 January 2013
     *
     * @return void
     */
    public function clearStaticVariables($iCount) {
        if ($iCount == self::$iNumberOfStatements) {
            self::$iNumberOfStatements = null;
            self::$arrImports = array();
            self::$iLastOccurrence = null;
        }
    }

}//end class

?>
