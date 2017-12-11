<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\Http\Controllers;

use App\Config\SiteConstants;
use App\Services\CookieService;
use App\Services\EducationService;
use App\Services\MSGraphService;
use App\Services\TokenCacheService;
use App\Services\UserService;
use App\ViewModel\ArrayResult;
use App\ViewModel\SectionUser;
use App\ViewModel\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Microsoft\Graph\Connect\Constants;

class SchoolsController extends Controller
{
    private $educationService;
    private $userServices;

    public function __construct()
    {
        $this->userServices = new UserService();
    }

    /**
     * Show all schools.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->educationService = $this->getEduServices();

        $me = $this->educationService->getMe();
        $schools = $this->educationService->getSchools();
        foreach ($schools as $school) {
            $school->isMySchool = $school->schoolNumber === $me->schoolId;

        }

        // sort schools: firstly sort by whether it's my school, secondly sort by the display name
        usort($schools, function ($a, $b) {
            if ($a->isMySchool xor $b->isMySchool) {
                return $a->isMySchool ? -1 : 1;
            } else {
                return strcmp($a->displayName, $b->displayName);
            }
        });

        $cookieServices = new CookieService();
        $fullName = Auth::user()->firstName . ' '. Auth::user()->lastName ;
        $cookieServices->setCookies($fullName, $me->mail);

        $data = ["me" => $me, "schools" => $schools];
        return view('schools.schools', $data);
    }



    /**
     * Show details of the specified class.
     *
     * @param string $objectId The object id of the school
     * @param string $classId The object id of the class
     *
     * @return \Illuminate\Http\Response
     */
    public function classDetail($objectId, $classId)
    {
        $curUser = Auth::user();
        $this->educationService = $this->getEduServices();
        $me = $this->educationService->getMe();
        $school = $this->educationService->getSchool($objectId);
        $section = $this->educationService->getSectionWithMembers($classId);

        foreach ($section->getStudents() as $student) {
            $student->position = $this->userServices->getSeatPositionInClass($student->id, $classId);
            $student->favoriteColor = $this->userServices->getFavoriteColor($student->id);
        }

        $teachersInCurrentSchool = $this->educationService->getTeachers($school->schoolNumber,null,null);
        if(isset($teachersInCurrentSchool) && isset($teachersInCurrentSchool->value))
            $teachersInCurrentSchool = $teachersInCurrentSchool->value;
        $teachers = $section->getTeachers();
        $filteredTeachers=[];
        foreach ($teachersInCurrentSchool as $teacher){
            $filteredTeachers[$teacher->id] = $teacher;
        }
        foreach ($teachers as $item) {
              if(isset($filteredTeachers[$item->id])) {
                  unset($filteredTeachers[$item->id]);
              }
        }
        $assignments = $this->educationService->getAssignments($classId);
        $msGraph = new MSGraphService();
        $conversations = $msGraph->getGroupConversations($classId);
        $seeMoreConversationsUrl = sprintf(Constants::O365GroupConversationsUrlFormat, $section->email);
        $driveItems = $msGraph->getGroupDriveItems($classId);
        $seeMoreFilesUrl = $msGraph->getGroupDriveRoot($classId)->getWebUrl();

        $data =
            [
                "school" => $school,
                "section" => $section,
                "conversations" => $conversations,
                "seeMoreConversationsUrl" => $seeMoreConversationsUrl,
                "driveItems" => $driveItems,
                "seeMoreFilesUrl" => $seeMoreFilesUrl,
                "isStudent" => $me instanceof Student,
                "o365UserId" => $curUser->id,
                "myFavoriteColor" => $curUser->favorite_color,
                "filteredTeachers" => $filteredTeachers,
                "assignments" =>$assignments
            ];

        return view('schools.classdetail', $data);
    }

    public function getAssignmentResources($classId, $assignmentId)
    {
        $this->educationService = $this->getEduServices();
        $assignments = $this->educationService->getAssignmentResources($classId,$assignmentId);
        return response()->json($assignments);
    }

    public function updateAssignment(request $request)
    {
        //$ids= $this->getIdsFromResourceFolder("https://graph.microsoft.com/v1.0/drives/b!SL9Uk3LQjEuffPg9XD6ipm0C2Yly5gFNnYUl0wD2wXRFlWlrWvV6SJ_IGS25d5Cu/items/01PSIOWBW4UYWIBFS235DKQ6M7SUWVI5NK");
       $formDate= Input::all();
        $input = $request->all();
        $files = $request->newResource;
        $this->educationService = $this->getEduServices();
        $assignment =  $this->educationService->getAssignment($formDate['classId'],$formDate['assignmentId']);
        if($assignment->status==='draft' && $formDate['assignmentStatus']==='assigned'){
            $assignment = $this->educationService->publishAssignmentAsync($formDate['classId'], $formDate['assignmentId']);
        }
        $resourceFolder =  $this->educationService->getAssignmentResourceFolderURL($formDate['classId'], $formDate['assignmentId']);
        $msGraph = new MSGraphService();
        foreach ($files as $file)
        {
            if($file!=null)
            {

            }
        }
       $b=0;
    }

    public function addCoTeacher($classId,$teacherId)
    {
        $this->educationService = $this->getEduServices();
        $this->educationService->addGroupMember($classId,$teacherId);
        $a = $this->educationService->addGroupOwner($classId,$teacherId);
        $url  = $_SERVER['HTTP_REFERER'];
        header('Location: '.$url, true,302);
        exit();
    }

    /**
     * Show classes of the specified school.
     * @param string $objectId The object id of the school
     * @return \Illuminate\Http\Response
     */
    public function classes($objectId)
    {
        $this->educationService = $this->getEduServices();
        $me = $this->educationService->getMe();
        $school = $this->educationService->getSchool($objectId);

        $myClasses = $this->educationService->getMySectionsOfSchool($school->schoolNumber);
        $allClasses = $this->educationService->getSections($school->id);
        $this->markMyClasses($allClasses, $myClasses);

        $data = ["myClasses" => $myClasses, "allClasses" => $allClasses, "school" => $school, "me" => $me];
        return view('schools.classes', $data);
    }

    /**
     * Get classes for the specified school.
     * @param string $schoolId The id of the school
     * @param string $skipToken The token used to retrieve the next subset of the requested collection
     * @return \Illuminate\Http\JsonResponse
     */
    public function classesNext($schoolId, $skipToken)
    {
        $this->educationService = $this->getEduServices();
        $myClasses = $this->educationService->getMySectionsOfSchool($schoolId);
        $allClasses = $this->educationService->getSections($schoolId, SiteConstants::DefaultPageSize, $skipToken);
        $this->markMyClasses($allClasses, $myClasses);
        return response()->json($allClasses);
    }

    /**
     * Save the seating arrangements.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveSeatingArrangements()
    {
        $succeeded = $this->userServices->saveSeatingArrangements(Input::all());
        return response()->json([], $succeeded ? 200 : 500);
    }

    private function uploadFileToOneDrive()
    {
        
    }

    private function getIdsFromResourceFolder($resourceFolder)
    {
        //https://graph.microsoft.com/v1.0/drives/b!SL9Uk3LQjEuffPg9XD6ipm0C2Yly5gFNnYUl0wD2wXRFlWlrWvV6SJ_IGS25d5Cu/items/01PSIOWBW4UYWIBFS235DKQ6M7SUWVI5NK
        $array = explode('/',$resourceFolder);
        $arrayLength = count($array);
        return array($array[$arrayLength-3],$array[$arrayLength-1]);
    }

    private function markMyClasses($allClasses, $myClasses)
    {
        foreach ($allClasses->value as $class1) {
            $class1->isMySection = false;
            foreach ($myClasses as $class2) {
                if ($class1->id === $class2->id) {
                    $class1->isMySection = true;
                    $class1->members = $class2->members;
                    break;
                }
            }
        }
    }

    private function getEduServices()
    {
        $user=Auth::user();
        $token = (new TokenCacheService())->getMSGraphToken($user->o365UserId);
        return new EducationService($token);
    }


}
