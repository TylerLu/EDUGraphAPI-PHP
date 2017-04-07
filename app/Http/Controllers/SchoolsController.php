<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\Http\Controllers;

use App\Services\CookieService;
use App\Services\EducationService;
use App\Services\MapService;
use App\Services\MSGraphService;
use App\Services\UserService;
use App\ViewModel\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Microsoft\Graph\Connect\Constants;

class SchoolsController extends Controller
{
    /**
     * Show all the schools.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $educationService = new EducationService();
        $me = $educationService->getMe();
        $schools = $educationService->getSchools();
        foreach ($schools as $school) {
            $school->isMySchool = $school->schoolId === $me->schoolId;
            $ll = MapService::getLatitudeAndLongitude($school->state, $school->city, $school->address);
            if ($ll) {
                $school->latitude = $ll[0];
                $school->longitude = $ll[1];
            }
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
        $cookieServices->SetCookies($fullName, $me->mail);

        $data = ["me" => $me, "schools" => $schools, "bingMapKey" => env(Constants::BINGMAPKEY)];
        return view('schools.schools', $data);
    }

    /**
     * Show teachers and students of a school
     *
     * @param string $objectId The object id of the school
     *
     * @return \Illuminate\Http\Response
     */
    public function users($objectId)
    {
        $educationService = new EducationService();
        $school = $educationService->getSchool($objectId);
        $users = $educationService->getMembers($objectId, 12, null);
        $students = $educationService->getStudents($school->schoolId, 12, null);
        $teachers = $educationService->getTeachers($school->schoolId, 12, null);
        $data = ["school" => $school, "users" => $users, "students" => $students, "teachers" => $teachers];

        return view('schools.users', $data);
    }

    /**
     * Get users of a school
     *
     * @param string $objectId The object id of the school
     * @param string $skipToken The token used to retrieve the next subset of the requested collection
     *
     * @return \Illuminate\Http\JsonResponse The next page of users
     */
    public function usersNext($objectId, $skipToken)
    {
        $educationService = new EducationService();
        $users = $educationService->getMembers($objectId, 12, $skipToken);
        return response()->json($users);
    }

    /**
     * Get students of a school.
     *
     * @param string $objectId The object id of the school
     * @param string $skipToken The token used to retrieve the next subset of the requested collection
     *
     * @return \Illuminate\Http\JsonResponse The next page of students
     */
    public function studentsNext($objectId, $skipToken)
    {
        $educationService = new EducationService();
        $school = $educationService->getSchool($objectId);
        $students = $educationService->getStudents($school->schoolId, 12, $skipToken);
        return response()->json($students);
    }

    /**
     * Get teachers of a school.
     *
     * @param string $objectId The object id of the school
     * @param string $skipToken The token used to retrieve the next subset of the requested collection
     *
     * @return \Illuminate\Http\JsonResponse The next page of teachers
     */
    public function teachersNext($objectId, $skipToken)
    {
        $educationService = new EducationService();
        $school = $educationService->getSchool($objectId);
        $teachers = $educationService->getTeachers($school->schoolId, 12, $skipToken);
        return response()->json($teachers);
    }

    /**
     * Show details of a class
     *
     * @param string $objectId The object id of the school
     * @param string $classId The object id of the class
     *
     * @return \Illuminate\Http\Response
     */
    public function classDetail($objectId, $classId)
    {
        $curUser = Auth::user();
        $educationService = new EducationService();
        $me = $educationService->getMe();
        $school = $educationService->getSchool($objectId);
        $section = $educationService->getSectionWithMembers($classId);
        foreach ($section->getStudents() as $student) {
            $student->position = UserService::getSeatPositionInClass($student->o365UserId, $classId);
            $student->favoriteColor = UserService::getFavoriteColor($student->o365UserId);
        }

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
                "o365UserId" => $curUser->o365UserId,
                "myFavoriteColor" => $curUser->favorite_color
            ];

        return view('schools.classdetail', $data);
    }

    /**
     * Show all classes of a school.
     * @param string $objectId The object id of the school
     * @return \Illuminate\Http\Response
     */
    public function classes($objectId)
    {
        $educationService = new EducationService();
        $me = $educationService->getMe();
        $school = $educationService->getSchool($objectId);
        $schoolId = $school->schoolId;
        $myClasses = $educationService->getMySectionsOfSchool($schoolId);
        $allClasses = $educationService->getSections($schoolId, 12, null);
        $this->checkClasses($allClasses, $myClasses);

        $data = ["myClasses" => $myClasses, "allClasses" => $allClasses, "school" => $school, "me" => $me];
        return view('schools.classes', $data);
    }

    /**
     * Show next 12 schools for classes page.
     * @param string $schoolId The id of the school
     * @param string $skipToken The token used to retrieve the next subset of the requested collection
     * @return \Illuminate\Http\JsonResponse
     */
    public function classesNext($schoolId, $skipToken)
    {
        $educationService = new EducationService();
        $myClasses = $educationService->getMySectionsOfSchool($schoolId);
        $allClasses = $educationService->getSections($schoolId, 12, $skipToken);
        $this->checkClasses($allClasses, $myClasses);
        return response()->json($allClasses);
    }

    /**
     * Get photo of a user
     *
     * @param string $o365UserId The Office 365 user id of the user
     *
     * @return \Illuminate\Http\Response
     */
    public function userPhoto($o365UserId)
    {
        $msGraph = new MSGraphService();
        $stream = $msGraph->getUserPhoto($o365UserId);
        if ($stream) {
            $contents = $stream->getContents();
            $headers = [
                "Content-type" => "image/jpeg",
                "Accept-Ranges" => "bytes",
                "Content-Length" => strlen($contents)
            ];
            return response()->stream(function () use ($stream, $contents) {
                $out = fopen('php://output', 'wb');
                fwrite($out, $contents);
                fclose($out);
            }, 200, $headers);
        }
        if ($stream === null) {
            return response()->file(realpath("./public/images/header-default.jpg"));
        }
        return response('', 403);
    }

    /**
     * Save the seat arrangements
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveSeatingArrangements()
    {
        $succeeded = UserService::saveSeatingArrangements(Input::all());
        return response()->json([], $succeeded ? 200 : 500);
    }

    /**
     * Check every class if it's my class by comparing with known my classes, if true, set its members from the known my class
     *
     * @param array $allClasses The classes to check
     * @param array $myClasses The known my classes
     *
     * @return void
     */
    private function checkClasses($allClasses, $myClasses)
    {
        foreach ($allClasses->value as $class1) {
            $class1->isMySection = false;
            foreach ($myClasses as $class2) {
                if ($class1->email === $class2->email) {
                    $class1->isMySection = true;
                    $class1->members = $class2->members;
                    break;
                }
            }
        }
    }
}
