<?php
/**
 * Verifies that all namespace has a dock block and @namespace token
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Alec Erasmus <alec.erasmus@a24group.com>
 */

/**
 * Verifies that all namespace has a dock block and @namespace token
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Alec Erasmus <alec.erasmus@a24group.com>
 * @since     28 January 2013
 */
class A24StudioCS_Sniffs_Commenting_NamespaceCommentSniff implements PHP_CodeSniffer_Sniff
{


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_NAMESPACE, T_CLASS);

    }//end register()


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @author    Alec Erasmus <alec.erasmus@a24group.com>
     * @since     28 January 2013
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if ($tokens[$stackPtr]['type'] == 'T_CLASS') {
            for ($x = $stackPtr; $x > 0; $x--) {
                if ($tokens[$x]['type'] == 'T_NAMESPACE') {
                    return;
                }
            }
            $error = 'Missing name space in class';
            $phpcsFile->addError($error, 0, 'MissingNamespace');
            return;
        } else if ($tokens[$stackPtr]['type'] == 'T_NAMESPACE') {
            if (
                $tokens[$stackPtr - 2]['type'] === 'T_WHITESPACE' &&
                $tokens[$stackPtr - 3]['type'] === 'T_DOC_COMMENT'
            ) {
                $error ='Found space between namespace and doc block';
                $phpcsFile->addError($error, $stackPtr - 1, 'SpaceBetweenDocAndNamespace');
                return;
            } elseif (
                $tokens[$stackPtr - 2]['type'] !== 'T_DOC_COMMENT'
            ) {
                $error ='Missing namespace doc block';
                $phpcsFile->addError($error, $stackPtr, 'MissingDocBlock');
                return;
            }

	        if (
	            $tokens[$stackPtr - 4]['line'] !== $tokens[$stackPtr]['line'] - 3 ||
	            $tokens[$stackPtr - 4]['type'] !== 'T_DOC_COMMENT' ||
	            trim($tokens[$stackPtr - 4]['content']) !== '/**'
	        ) {
	            $error ='Namespace doc block is in the incorrect format. Need to use "/**" formate or missing @namespace';
                $phpcsFile->addError($error, $stackPtr, 'IncorrectDocBlock');
	            return;
	        }

	        if (
	            $tokens[$stackPtr - 3]['line'] != $tokens[$stackPtr]['line'] - 2 ||
	            $tokens[$stackPtr - 3]['type'] != 'T_DOC_COMMENT' ||
	            trim($tokens[$stackPtr - 3]['content']) != '* @namespace'
	        ) {
	            $error ='Missing @namespace token';
                $phpcsFile->addError($error, $stackPtr, 'IncorrectDocBlock');
                return;
	        }
        }
    }//end process()

}//end class
?>