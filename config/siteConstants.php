<?php
namespace App\Config;
class SiteConstants
{
    const Session_State                 ='SessionState';
    const Session_RedirectURL           ='SessionRedirectURL';
    const Session_EnabledUserCount      ='SessionEnabledUserCount';
    const AADCompanyAdminRoleName       = "Company Administrator";
    const Consent                       = "consent";
    const Login                         = "login";
    const AdminConsent                  = "admin_consent";
    const AdminConsentSucceedMessage    ='Admin consented successfully!';
    const AdminUnconsentMessage         ='Admin unconsented successfully!';
    const NoPrincipalError              ='Could not found the service principal. Please provdie the admin consent.';
    const EnableUserAccessFailed        ='Enable user access failed.';
    const UsernameCookie                ='O365CookieUsername';
    const EmailCookie                   ='O365CookieEmail';
    const SocializeUser                 ='SocializeUser';

}
 class Roles
{
     const Admin   = "Admin";
     const Faculty = "Faculty";
     const Student = "Student";
}
class UserType
{
    const Local = 'local';
    const O365 = 'O365';
}
class O365ProductLicenses
{
    /**
     * Microsoft Classroom Preview
     */
    const  Classroom   = '80f12768-d8d9-4e93-99a8-fa2464374d34';
    /**
     * Office 365 Education for faculty
     */
    const  Faculty     = '94763226-9b3c-4e75-a931-5c89701abe66';
    /**
     * Office 365 Education for students
     */
    const  Student     = '314c4481-f395-4525-be8b-2ec4bb1e9d91';
    /**
     * Office 365 Education for faculty
     */
    const  FacultyPro  = '78e66a63-337a-4a9a-8959-41c6654dfb56';
    /**
     * Office 365 Education for students
     */
    const StudentPro   = 'e82ae690-a2d5-4d76-8d30-7c6e01e6022e';
}

class EduConstants
{
    /**
     * Office 365 Education for faculty
     */
    const  TeacherObjectType  = 'Teacher';
    /**
     * Office 365 Education for students
     */
    const StudentObjectType   = 'Student';
}