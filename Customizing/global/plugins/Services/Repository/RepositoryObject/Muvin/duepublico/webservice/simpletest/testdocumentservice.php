<?php
/**
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
 * @abstract This file contains a test client for the Miless/MyCoRe 
 *           DocumentService web service client implementation.
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

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

@set_time_limit(0);     

require_once ($CFG->dirroot.'/lib/duepublico/webservice/duep_DocumentService.php');

class documentservice_test extends UnitTestCase {
    
    private $documentService;
    
    function setUp() {
        $this->documentService = new DocumentService ();
    }//setUp
    
    function tearDown () {
        $this->documentService = null;
    }//tearDown
    
    function testGetLanguages () {
        $languages = $this->documentService->GetLanguages('German');
        $this->assertEqual ('Deutsch', $languages['ger']);
        
        $languages = $this->documentService->GetLanguages('English');
        $this->assertEqual ('German', $languages['ger']);
    }//testGetLanguages
    
    function testCreateDeleteExists () {
        $paramsDocCreation['language']        = 'de';
        $paramsDocCreation['title']           = 'Moodle course document 2';
        $paramsDocCreation['title2']          = 'Moodel test environment';
        $paramsDocCreation['authorId']        = '12231';
        $paramsDocCreation['role']            = 'author';
        $paramsDocCreation['description']     = 'This is a moodle test environment for testing the DuEPublico interface';
        $paramsDocCreation['keyword']         = 'Moodle, Test';
        $paramsDocCreation['erstelltDatum']   = date ('d.m.Y');
        $paramsDocCreation['geaendertDatum']  = 'tt.mm.jjjj';
        $paramsDocCreation['gueltigVonDatum'] = 'tt.mm.jjjj'; 
        $paramsDocCreation['gueltigBisDatum'] = 'tt.mm.jjjj';
        $paramsDocCreation['typeid']          = 'a.10';
        $paramsDocCreation['formatid']        = '5';
        $paramsDocCreation['originid']        = 'LV';
        
        $first = $this->documentService->CreateDocument($paramsDocCreation);
        $second = $this->documentService->CreateDocument($paramsDocCreation);
        
        $this->assertEqual($first + 1 , $second);
        
        $this->assertEqual (true, $this->documentService->ExistDocument($first));
        $this->assertEqual (true, $this->documentService->ExistDocument($second));

        $this->assertEqual (false, $this->documentService->ExistDocument(999999));
        
        $this->assertEqual (true, $this->documentService->DeleteDocument($first));
        $this->assertEqual (true, $this->documentService->DeleteDocument($second));
        
        $this->assertEqual (false, $this->documentService->ExistDocument($first));
        $this->assertEqual (false, $this->documentService->ExistDocument($second));
    }//testCreateDeleteExists
    
    function testSmallestDerivate () {
        global $CFG;
        $paramsDocCreation['language']        = 'de';
        $paramsDocCreation['title']           = 'Moodle course document 2';
        $paramsDocCreation['title2']          = 'Moodel test environment';
        $paramsDocCreation['authorId']        = '12231';
        $paramsDocCreation['role']            = 'author';
        $paramsDocCreation['description']     = 'This is a moodle test environment for testing the DuEPublico interface';
        $paramsDocCreation['keyword']         = 'Moodle, Test';
        $paramsDocCreation['erstelltDatum']   = date ('d.m.y');
        $paramsDocCreation['geaendertDatum']  = 'tt.mm.jjjj';
        $paramsDocCreation['gueltigVonDatum'] = 'tt.mm.jjjj'; 
        $paramsDocCreation['gueltigBisDatum'] = 'tt.mm.jjjj';
        $paramsDocCreation['typeid']          = 'a.10';
        $paramsDocCreation['formatid']        = '5';
        $paramsDocCreation['originid']        = 'LV';
        
        $documentId = $this->documentService->CreateDocument($paramsDocCreation);
        
        require_once ($CFG->dirroot.'/lib/duepublico/webservice/duep_FileService.php');
        $fileService = new FileService ();
        
        $derivateId1 = $fileService->CreateFileDerivate($documentId);
        $derivateId2 = $fileService->CreateFileDerivate($documentId);
        
        $this->assertNotNull ($derivateId1);
        $this->assertNotNull ($derivateId2);
        
        $smallestDerivateId = $this->documentService->SmallestDerivateId($documentId);
        
        $this->assertEqual($derivateId1, $smallestDerivateId);
        
        $this->assertTrue($fileService->DeleteFileDerivate($derivateId1));
        $this->assertTrue($fileService->DeleteFileDerivate($derivateId2));
        $this->assertTrue($this->documentService->DeleteDocument($documentId));
        
        $this->assertFalse($fileService->ExistsFileDerivate($derivateId1));
        $this->assertFalse($fileService->ExistsFileDerivate($derivateId2));
        $this->assertFalse($this->documentService->ExistDocument($documentId));
        
        $fileService = null;
    }//testSmallestDerivate
    
    
     function testGetDocumentData () {
        $title1 = 'Moodle course document 2';
        $title2 = 'Moodel test environment';
        
        $paramsDocCreation['language']        = 'de';
        $paramsDocCreation['title']           = $title1;
        $paramsDocCreation['title2']          = $title2;
        $paramsDocCreation['authorId']        = '12231';
        $paramsDocCreation['role']            = 'author';
        $paramsDocCreation['description']     = 'This is a moodle test environment for testing the DuEPublico interface';
        $paramsDocCreation['keyword']         = 'Moodle, Test';
        $paramsDocCreation['erstelltDatum']   = date ('d.m.y');
        $paramsDocCreation['geaendertDatum']  = 'tt.mm.jjjj';
        $paramsDocCreation['gueltigVonDatum'] = 'tt.mm.jjjj'; 
        $paramsDocCreation['gueltigBisDatum'] = 'tt.mm.jjjj';
        $paramsDocCreation['typeid']          = 'a.10';
        $paramsDocCreation['formatid']        = '5';
        $paramsDocCreation['originid']        = 'LV';
        
        $documentId = $this->documentService->CreateDocument($paramsDocCreation);
        
        $docData = $this->documentService->GetDocumentData($documentId);
        
        $docElem = $docData->GetChildXpath('/document');
        
        $this->assertTrue ($docElem instanceof XMLElement);
        $this->assertEqual ($documentId, $docElem->GetAttributeValue('ID'));
        $this->assertEqual ('LuL', $docElem->GetAttributeValue('collection'));
        $this->assertEqual ('published', $docElem->GetAttributeValue('status'));
        
        $titles = $docData->GetChildXpath('/document/titles/title');
        
        $this->assertTrue (is_array($titles));
        $this->assertEqual (2, count($titles));
        $first = $titles[0];
        $second = $titles[1];
        $this->assertTrue ($first instanceof XMLElement);
        $this->assertTrue ($second instanceof XMLElement);
        $this->assertEqual ($title2, $first->GetElementText());
        $this->assertEqual ($title1, $second->GetElementText());
        
     }//testGetDocumentData
    
}//documentservice_test
?>
