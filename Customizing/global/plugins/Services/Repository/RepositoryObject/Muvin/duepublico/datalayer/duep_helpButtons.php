<?php
/**
 * @copyright Copyright &copy; 2009, University of Duisburg-Essen, Campus Essen
 * @copyright Copyright &copy; 2009, Marcel Heusinger
 * @version 1.0 
 * 
 * Created on 26.05.2009 by Marcel Heusinger (marcel.heusinger [at] uni-essen.de)       
 *                                                                               
 * This file is part of the DuEPublico to Moodle interface that was developed        
 * during the project 'Systemkonvergenz' (see project's website 
 * http://systemkonvergenz.de/) sponsored by the German Research Association (DFG). 
 * If you have any suggestions, comments, or questions do not hesitate to contact
 * the author at the below-mentioned email address.
 * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
 * 
 * @abstract This class
 * 
 * 
 * 
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License
 * <para>
 *  This program is free software; you can use it, redistribute it
 *  and / or modify it under the terms of the GNU General Public License
 *  (GPL) as published by the Free Software Foundation; either version 2
 *  of the License or (at your option) any later version.
 * </para>
 * <para>
 *  This program is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 * </para> 
 * <para>
 *  You should have received a copy of the GNU General Public License
 *  along with this program, see root folder.
 *  If not, write to the Free Software Foundation Inc.,
 *  59 Temple Place - Suite 330, Boston, MA  02111-1307 USA
 * </para>
 */

require_once ($CFG->dirroot.'/lib/weblib.php'); 

class HelpButtons {
    
    /**
     * Default constructor of this class.
     */
    public function __construct () {
    }//Constructor
    
    /**
     * Returns the help buttons for a specific part of the modules' workflow
     * @param string $type There are the following possible values:
     * <ul>
     *  <li>query: Help buttons for the query page</li>
     *  <li>results: Help buttons for the result page</li>
     *  <li>upload: Helpbuttons for the upload page</li>
     *  <li>account: Help button for the login page</li>
     *  <li>newAcc: Help buttons for creating a new account</li>
     *  <li>backup: Help buttons for the backup module</li>
     * </ul>
     * @return array An <c>array</c> that contains the requested help buttons
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function GetButtons ($type) {
        //TODO: Add titles and all the other properties
        if ($type == 'query') {
            return $this->Query();
        } else if ($type == 'result') {
            return $this->Result();
        } else if ($type == 'upload') {
            return $this->Upload();
        } else if ($type == 'account') {
            return $this->AskAccount();
        } else if ($type == 'newAcc') {
            return $this->Account();
        } else if ($type == 'backup') {
            return $this->Backup();
        } else if ($type == 'import') {
            return $this->Import();
        }
    }//GetButtons
    
    /**
     * Returns the help buttons that are required for the import module.
     * @return array An <code>array</code> that contains the help buttons for the import module
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    private function Import () {
        $helpBts = array ();
        $helpBts['import'] = helpbutton ('duepublico/import', get_string ('importbutton','duepublicolang'), '', true , '', '', true, '');
        return $helpBts;
    }//Import
    
    /**
     * Creates all helpbuttons that are used in the backup module
     * @return array An <c>array</c> that contains the helpbuttons for the backup module
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    private function Backup () {
        $helpBts = array ();
        $helpBts['backup'] = helpbutton ('duepublico/backups', '', '', true, false, '', true, '');
        $helpBts['md5']    = helpbutton ('duepublico/md5',     '', '', true, false, '', true, '');
        return $helpBts;
    }//Backup
    
    /**
     * Creates all helpbuttons that are used within the login screee
     * @return array An <c>array</c> that contains the helpbuttons 
     *               used within the login screen
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    private function Account () {
        $helpBts = array ();
        $helpBts['uname']         = helpbutton ('duepublico/duepAccount/username',      '', '', true, false, '', true, false);
        $helpBts['pword']         = helpbutton ('duepublico/duepAccount/password',      '', '', true, false, '', true, false);
        $helpBts['academictitle'] = helpbutton ('duepublico/duepAccount/academictitle', '', '', true, false, '', true, false);
        $helpBts['readaccount']   = helpbutton ('duepublico/duepAccount/readaccount',   '', '', true, false, '', true, false);
        return $helpBts;
    }//Account
    
    /**
     * Creates helpbuttons for the screen where the user is informed about
     * the DuEPublico account.
     * @return array An <c>array</c> that contains the helpbuttons
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    private function AskAccount () {
        $helpBts = array ();    
        $helpBts['account'] = helpbutton ('duepublico/duepAccount/duepublicoaccount', '', '', true, false, '', true, false);
        return $helpBts;
    }//AskAccount
    
    /**
     * Creates all the buttons used on the show result screen in the download module
     * @return array An <c>array</c> that contains the helpbuttons used within 
     *               the result page
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    private function Result () {
        $helpBts = array ();
        $helpBts['duepid']     = helpbutton ('duepublico/resultsTable/duepid',     '', '', true , '', '', true, '');
        $helpBts['addlink']    = helpbutton ('duepublico/resultsTable/addlink',    '', '', true , '', '', true, '');
        $helpBts['detailview'] = helpbutton ('duepublico/resultsTable/detailview', '', '', true , '', '', true, '');
        $helpBts['filetype']   = helpbutton ('duepublico/resultsTable/filetype',   '', '', true , '', '', true, '');
        /*
        $helpButtons['duepid']     = helpbutton ('duepublico/resultsTable/duepid',     'DuEP.-ID',                                       '', true , '', '', true, '');
        $helpButtons['addlink']    = helpbutton ('duepublico/resultsTable/addlink',    get_string ('addlinkbutton','duepublicolang'),    '', true , '', '', true, '');
        $helpButtons['detailview'] = helpbutton ('duepublico/resultsTable/detailview', get_string ('detailviewbutton','duepublicolang'), '', true , '', '', true, '');
        $helpButtons['filetype']   = helpbutton ('duepublico/resultsTable/filetype',   get_string ('filetype','duepublicolang') ,        '', true , '', '', true, '');
        */
        return $helpBts;
    }//Result
    
    /**
     * Returns an <c>array</c> with the buttons used in the upload script.
     * @return array The helpbuttons used in the upload script
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    private function Upload () {
        global $DUEP, $CFG;
        $pixPath = '';
        if (isset($CFG->pixpath)) {
            $pixPath = $CFG->pixpath.'/'.$DUEP->dueppics;
        } else {
            $pixPath = $CFG->dirrot.'/pix/'.$DUEP->dueppics;
        }//else
        $helpBts = array ();
        /*
        $helpBts['failed'] = helpbutton ('duepublico/resultsTable/duepid',     '', '', true , '', '', true, '');
        $helpBts['ok']     = helpbutton ('duepublico/resultsTable/addlink',    '', '', true , '', '', true, '');
        $helpBts['locked'] = helpbutton ('duepublico/resultsTable/detailview', '', '', true , '', '', true, '');
        */
        $helpBts['failed']  = '<img alt="Failed" src="'.$pixPath.'/fail.png"/>';
        $helpBts['ok']      = '<img alt="Ok"     src="'.$pixPath.'/ok.png"/>';    
        $helpBts['locked']  = '<img alt="Locked" src="'.$pixPath.'/locked.png"/>';
        $helpBts['workdir'] = helpbutton ('duepublico/workdirbutton', get_string ('viewworkspace','duepublicolang'), '', true , '', '', true, '');
        
        return $helpBts;
    }//Upload
    
    /**
     * Returns an <c>array</c> that contains a few help buttons that
     * explain some of the search field table's input fields.
     * The key of the returned <c>array</c> is the name of the search field
     * that will be explained by the button whereas the value is the help button itself.
     * @return array An <c>array</c> that contains helputtons
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    private function Query () {
        $helpBts = array ();
        $helpBts['operator'] = helpbutton ('duepublico/queryTable/operatorInfo', '', '', true , '', '', true, '');
        $helpBts['title']    = helpbutton ('duepublico/queryTable/titleInfo',    '', '', true , '', '', true, '');
        $helpBts['name']     = helpbutton ('duepublico/queryTable/personInfo',   '', '', true , '', '', true, '');
        $helpBts['keywords'] = helpbutton ('duepublico/queryTable/keywordInfo',  '', '', true , '', '', true, '');
        $helpBts['date']     = helpbutton ('duepublico/queryTable/dateInfo',     '', '', true , '', '', true, '');
        return $helpBts;
    }//Query
    
    /**
     * Magic method used for debugging purposes. It makes debugging
     * more convienient because its called if another class tries to 
     * call a method on an instance of this class but that method is 
     * not defined within this class.
     * @param string $method The name of the method that was called.
     * @param mixed $parameters The parameters that should be passed to the called method.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __call($method, $parameters) {
        throw new Exception ("Method $method is not implemented within ".__CLASS__.".");
    }//__call
        
}//Class: HelpButtons
?>
