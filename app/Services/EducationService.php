<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\Services;

use \Exception;
use App\Config\Roles;
use App\Config\SiteConstants;
use App\ViewModel\ArrayResult;
use App\ViewModel\Assignment;
use App\ViewModel\EducationAssignmentResource;
use App\ViewModel\ResourcesFolder;
use App\ViewModel\School;
use App\ViewModel\Section;
use App\ViewModel\SectionUser;
use App\ViewModel\Student;
use App\ViewModel\Submission;
use App\ViewModel\Teacher;
use Illuminate\Support\Facades\Auth;
use Microsoft\Graph\Connect\Constants;
use Microsoft\Graph\Model;

class  EducationService
{

    private $o365UserId;
    private $aadGraphClient;
    private $token;

    /**
     * Create a new instance.
     *
     * @return void
     */
    public function __construct($token)
    {
        $this->aadGraphClient = new AADGraphService();
        $user = Auth::user();
        if ($user) {
            $this->o365UserId = $user->o365UserId;
        }
        $this->token = $token;
    }

    /**
     * Get the current logged in user
     * Reference URL: https://msdn.microsoft.com/office/office365/api/student-rest-operations#get-current-user
     *
     * @return Model\User The current logged in user
     */
    public function getMe()
    {
        $json = $this->getResponse( "me", null, null, null);
        $assignedLicenses = array_map(function ($license) {
            return new Model\AssignedLicense($license);
        }, $json["assignedLicenses"]);
        $role = (new UserRolesService)->getUserRole($json["id"]);
        if($role!="Admin")
        {
            $isStudent = $json["extension_fe2174665583431c953114ff7268b7b3_Education_ObjectType"]=="Student"?true:false; //$this->isUserStudent($assignedLicenses);
            $isTeacher =$json["extension_fe2174665583431c953114ff7268b7b3_Education_ObjectType"]=="Teacher"?true:false; //$this->isUserTeacher($assignedLicenses);

        }else{
            $isStudent=false;
            $isTeacher=false;
        }
        $user = new SectionUser();
        if ($isStudent) {
            $user = new Student();
            $user->userRole = Roles::Student;
        } else if ($isTeacher) {
            $user = new Teacher();
            $user->userRole = Roles::Faculty;
        } else {
            $user->userRole = Roles::Admin;
        }
        $user->parse($json);
        return $user;
    }

    /**
     * Get all schools that exist in the Azure Active Directory tenant
     * Reference URL: https://msdn.microsoft.com/office/office365/api/school-rest-operations#get-all-schools
     *
     * @return array all schools that exist in the Azure Active Directory tenant
     */
    public function getSchools()
    {
        try{
            return $this->getAllPages( "education/schools", School::class);
        }
        catch (Exception $e){
            return [];
        }

    }

    /**
     * Get a school by the id.
     * Reference URL: https://msdn.microsoft.com/office/office365/api/school-rest-operations#get-a-school
     *
     * @param string $objectId the object id of the school administrative unit in Azure Active Directory
     *
     * @return The school with the object id
     */
    public function getSchool($objectId)
    {
        return $this->getResponse( "education/schools/" . $objectId , School::class, null, null);
    }


    /**
     * Get all the sections the current logged in user belongs to.
     *
     * @param bool $loadMembers Whether get the members of the sections
     *
     * @return All the section the current logged in user belongs to
     */
    public function getMySections($loadMembers)
    {
        $memberOfs = $this->getAllPages( "education/me/classes", Section::class);

        if (empty($memberOfs) || !$loadMembers) {
            return $memberOfs;
        }


        $sectionsWithMembers = [];
        foreach ($memberOfs as $section) {
            $sectionWithMembers = $this->getSectionWithMembers($section->id);
            array_push($sectionsWithMembers, $sectionWithMembers);
        }
        return $sectionsWithMembers;
    }

    /**
     * Get a section with its members
     * Reference URL: https://msdn.microsoft.com/office/office365/api/section-rest-operations#get-a-section.
     * @param string $objectId The object id of the section
     *
     * @return The section with its members
     */
    public function getSectionWithMembers($objectId)
    {
        return $this->getResponse( 'education/classes/' . $objectId . '?$expand=members', Section::class, null, null);
    }

     /**
     * Get all the sections the current logged in user belongs to in a school
     *
     * @param string $schoolId The object id of the school
     *
     * @return array All the sections the current logged in user belongs to in a school
     */
    public function getMySectionsOfSchool($schoolId)
    {
        $sections = $this->getMySections(true);
        usort($sections, function ($a, $b) {
            return strcmp($a->combinedCourseNumber, $b->combinedCourseNumber);
        });
        return $sections;
    }

    /**
     * Get sections in a school
     * Reference URL: https://msdn.microsoft.com/office/office365/api/section-rest-operations#get-a-section.
     * @param string $schoolId The object id of the school
     * @param string $top The number of items to return in a result set
     * @param string $skipToken The token used to retrieve the next subset of the requested collection
     *
     * @return array A subset of the sections in the school
     */
    public function getSections($schoolId, $top=SiteConstants::DefaultPageSize, $skipToken=null)
    {
        return $this->getResponse( 'education/schools/' . $schoolId . '/classes', Section::class, $top, $skipToken);
    }


    /**
     * Get students within a school
     * Reference URL: https://msdn.microsoft.com/en-us/office/office365/api/school-rest-operations#get-school-members
     *
     * @param string $schoolId the id of the school administrative unit in Azure Active Directory
     * @param int $top The number of items to return in a result set.
     * @param string $skipToken The token used to retrieve the next subset of the requested collection
     *
     * @return array A subset of the students within the school
     */
    public function getStudents($schoolId, $top, $skipToken)
    {
        return $this->getResponse( "users?\$filter=extension_fe2174665583431c953114ff7268b7b3_Education_SyncSource_SchoolId eq '$schoolId' and extension_fe2174665583431c953114ff7268b7b3_Education_ObjectType eq 'Student'", SectionUser::class, $top, $skipToken);
    }

    /**
     * Get teachers within a school
     * Reference URL: https://msdn.microsoft.com/en-us/office/office365/api/school-rest-operations#get-school-members
     *
     * @param string $schoolId the id of the school administrative unit in Azure Active Directory
     * @param int $top The number of items to return in a result set.
     * @param string $skipToken The token used to retrieve the next subset of the requested collection
     *
     * @return array A subset of the teachers within the school
     */
    public function getTeachers($schoolId, $top, $skipToken)
    {
        return $this->getResponse( "users?\$filter=extension_fe2174665583431c953114ff7268b7b3_Education_SyncSource_SchoolId eq '$schoolId' and extension_fe2174665583431c953114ff7268b7b3_Education_ObjectType eq 'Teacher'", SectionUser::class, $top, $skipToken);
    }

    public function getAssignments($classId)
    {
        return $this->getAllPages( 'education/classes/' . $classId . '/assignments', Assignment::class);
    }

    public function getAssignmentResources($classId,$assignmentId)
    {
        return $this->getAllPages( 'education/classes/' . $classId . '/assignments/'.$assignmentId .'/resources', EducationAssignmentResource::class);

    }

    public function getAssignmentSubmissions($classId,$assignmentId)
    {
        return $this->getAllPages( 'education/classes/' . $classId . '/assignments/'.$assignmentId .'/submissions',
            Submission::class);
    }

    public function getAssignmentSubmissionsByUser($classId, $assignmentId, $userId)
    {
        return $this->getAllPages( 'education/classes/' . $classId . '/assignments/'.$assignmentId .'/submissions?$filter=submittedBy/user/id eq \''.$userId.'\'',
            EducationAssignmentResource::class);

    }
    public function getSubmissionResources($classId,$assignmentId,$sectionId)
    {
        return $this->getAllPages( 'education/classes/' . $classId . '/assignments/'.$assignmentId .'/submissions/'.$sectionId.'/resources', EducationAssignmentResource::class);

    }

    public function addSubmissionResource($classId,$assignmentId,$submissionId,$fileName,$resourceURL)
    {
        $url = "education/classes/".$classId."/assignments/".$assignmentId."/submissions/".$submissionId."/resources";
        $fileType = $this->getFileType($fileName);
        $json=array(
            "resource"=>array(
                "displayName"=>$fileName,
                "@odata.type"=>$fileType,
                "file"=>array("odataid"=>$resourceURL)
            )
        );
        $data = json_encode($json);
        $this->postJSON($url,$data);
    }

    public function getAssignment($sectionId, $assignmentId)
    {
        return $this->getResponse('education/classes/'.$sectionId.'/assignments/'.$assignmentId,Assignment::class,null,null);
    }

    public function publishAssignmentAsync($sectionId, $assignmentId)
    {
        $url = "education/classes/".$sectionId."/assignments/".$assignmentId."/publish";
        return $this->getPostResponseWithReturnObject($url,null,Assignment::class);
    }

    public function getAssignmentResourceFolderURL($sectionId, $assignmentId)
    {
        $url = "education/classes/".$sectionId."/assignments/".$assignmentId."/GetResourcesFolderUrl";
        return $this->getResponse($url,ResourcesFolder::class,null,null);
    }

    public function addAssignmentResources($sectionId,$assignmentId,$fileName,$resourceURL)
    {
       $url = "education/classes/".$sectionId."/assignments/".$assignmentId."/resources";
        $fileType = $this->getFileType($fileName);
       $json=array(
           "resource"=>array(
               "displayName"=>$fileName,
               "@odata.type"=>$fileType,
               "file"=>array("odataid"=>$resourceURL)
           )
       );
       $data = json_encode($json);
       $this->postJSON($url,$data);
    }

    public function createAssignment($formDate)
    {
        $url = "education/classes/".$formDate['classId']."/assignments";
        $json = array(
            "displayName"=>$formDate['name'],
            "status"=>"draft",
            "dueDateTime"=>date('Y-m-d\TH:i:s\Z',strtotime($formDate['duedate']." " . $formDate['duetime'])),
            "allowStudentsToAddResourcesToSubmission"=>true,
            "assignTo"=>array(
                "@odata.type"=>"#microsoft.graph.educationAssignmentClassRecipient"
            )
        );
        $data = json_encode($json);
        $result = $this->postJSON($url,$data);
        $json  = json_decode($result->getBody(), true);
        return $json;

    }

    /**
     * Add a member to a class.
     * Reference URL: https://developer.microsoft.com/en-us/graph/docs/api-reference/v1.0/api/group_post_members
     * @param $classId
     * @param $teacherId
     */
    public function addGroupMember($classId,$teacherId)
    {
        $data['@odata.id'] =Constants::MSGraph. '/v1.0/directoryObjects/'.$teacherId ;
        return $this->getPostResponse( "groups/".$classId."/members/\$ref", $data);

    }

    /**
     * Add a member to the owner a class.
     * Reference URL: https://developer.microsoft.com/en-us/graph/docs/api-reference/v1.0/api/group_post_owners
     * @param $classId
     * @param $teacherId
     */
    public function addGroupOwner($classId,$teacherId)
    {
        $data['@odata.id'] =Constants::MSGraph . '/v1.0/users/'.$teacherId ;
        return $this->getPostResponse( "groups/".$classId."/owners/\$ref", $data);
    }

    private function getFileType($fileName)
    {
        $defaultFileType = "#microsoft.graph.educationFileResource";
        $ext ="";
        if(strpos($fileName,".")>0)
        {
            $array = explode(".",$fileName);
            $ext = $array[1];
        }
        switch ($ext)
        {
            case "docx":
                $defaultFileType = "#microsoft.graph.educationWordResource";
                break;
            case "xlsx":
                $defaultFileType = "#microsoft.graph.educationExcelResource";
                break;
            default:
                $defaultFileType = "#microsoft.graph.educationFileResource"; //"#microsoft.graph.educationFileResource";
                break;
        }

        return $defaultFileType;

    }
    private function isUserStudent($licenses)
    {
        return AADGraphService::isUserStudent($licenses);
    }

    private function isUserTeacher($licenses)
    {
        return AADGraphService::isUserTeacher($licenses);
    }

    private function isUserAdmin($licenses)
    {
        return AADGraphService::ad($licenses);
    }

    private function getPostResponse($endpoint,$data)
    {
        $token = $this->getToken();
        if ($token) {
            $url = Constants::MSGraph . '/' . Constants::MSGraph_VERSION . '/' . $endpoint;
            $result = HttpUtils::postHttpResponseWithData( $token, $url,$data);
            return $result;
        }
        return null;
    }

    private function postJSON($endpoint,$data)
    {
        $token = $this->getToken();
        if ($token) {
            $url = Constants::MSGraph . '/' . Constants::MSGraph_VERSION . '/' . $endpoint;
            $result = HttpUtils::postJSON( $token, $url,$data);
            return $result;
        }
        return null;
    }


    private function getPostResponseWithReturnObject($endpoint,$data,$returnType)
    {
        $result = $this->getPostResponse($endpoint,$data);
        $json = json_decode($result->getBody(), true);
        if ($returnType) {
            $isArray = (array_key_exists('value', $json) && is_array($json['value']));
            $retObj = $isArray ? new ArrayResult($returnType) : new $returnType();
            $retObj->parse($json);
            return $retObj;
        }
        return $json;
    }

    /**
     * Get response of AAD Graph API
     *
     * @param string $requestType The HTTP method to use, e.g. "GET" or "POST"
     * @param string $endpoint The Graph endpoint to call
     * @param string $returnType The type of the return object or object of an array
     * @param int $top The number of items to return in a result set.
     * @param string $skipToken The token used to retrieve the next subset of the requested collection
     *
     * @return mixed Response of AAD Graph API
     */
    private function getResponse( $endpoint, $returnType, $top, $skipToken,$requestType='get')
    {
        $token = $this->getToken();
        if ($token) {
            $url = Constants::MSGraph . '/' . Constants::MSGraph_VERSION . '/' . $endpoint;
            if ($top) {
                $url = $this->appendParamToUrl($url, "\$top", $top);
            }
            if ($skipToken) {
                $url = $this->appendParamToUrl($url, "\$skiptoken", $skipToken);
            }
            if($requestType==='get'){
                $result = HttpUtils::getHttpResponse( $token, $url);
            }else if($requestType==='post'){
                $result = HttpUtils::postHttpResponse( $token, $url);
            }

            $json = json_decode($result->getBody(), true);
            if ($returnType) {
                $isArray = (array_key_exists('value', $json) && is_array($json['value']));
                $retObj = $isArray ? new ArrayResult($returnType) : new $returnType();
                $retObj->parse($json);
                return $retObj;
            }
            return $json;
        }
        return null;
    }

    /**
     * Get all pages of data of AAD Graph API
     *
     * @param string $requestType The HTTP method to use, e.g. "GET" or "POST"
     * @param string $endpoint The Graph endpoint to call
     * @param string $returnType The type of the return object or object of an array
     *
     * @return mixed All pages of data of AAD Graph API
     */
    private function getAllPages( $endpoint, $returnType)
    {
        $data = $nextPage = $this->getResponse( $endpoint, $returnType, 100, null);
        while ($nextPage->skipToken) {
            $nextPage = $this->getResponse($endpoint, $returnType, 100, $data->skipToken);
            $data->value = array_merge($data->value, $nextPage->value);
        }
        return $data->value;
    }


    /**
     * Get access token
     *
     * @return string The access token
     */
    private function getToken()
    {
        if (!isset($this->o365UserId) || strlen($this->o365UserId) == 0) {
            return null;
        }
        return $this->token;
    }

    /**
     * Get tenant id
     *
     * @return string The tenant id
     */
    private function getTenantId()
    {
        if (isset(Auth::user()->tenantId)) {
            return Auth::user()->tenantId;
        }
        $token = (new TokenCacheService)->getMSGraphToken($this->o365UserId);
        return $this->aadGraphClient->getTenantIdByUserId($this->o365UserId,$token);
    }

    /**
     * Append a parameter to a url
     *
     * @param string $url The url
     * @param string $name The name of the parameter
     * @param string $value The value of the parameter
     *
     * @return string The url with the appended parameter
     */
    private function appendParamToUrl($url, $name, $value)
    {
        $str = strrchr($url, '?') === false ? "?" : "&";
        $url .= $str . $name . "=" . $value;
        return $url;
    }
}