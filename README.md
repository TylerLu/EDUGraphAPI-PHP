# EDUGraphAPI - Office 365 Education Code Sample

In this sample, we show you how to integrate with school roles/roster data as well as O365 services available via the Graph API. 

School data is kept in sync in O365 Education tenants by [Microsoft School Data Sync](http://sds.microsoft.com).  

**Table of contents**

- [Sample Goals](#sample-goals)
- [Prerequisites](#prerequisites)
- [Register the application in Azure Active Directory](#register-the-application-in-azure-active-directory)
- [Debug locally](#debug-locally)
- [Deploy the sample to Azure](#deploy-the-sample-to-azure)
- [Understand the code](#understand-the-code)
- [Questions and comments](#questions-and-comments)
- [Contributing](#contributing)

## Sample Goals

The sample demonstrates:

- Calling Graph APIs, including:

  - [Microsoft Azure Active Directory Graph API](https://www.nuget.org/packages/Microsoft.Azure.ActiveDirectory.GraphClient/)
  - [Microsoft Graph API](https://www.nuget.org/packages/Microsoft.Graph/)

- Linking locally-managed user accounts and Office 365 (Azure Active Directory) user accounts. 

  After linking accounts, users can use either local or Office 365 accounts to log into the sample website and use it.

- Getting schools, sections, teachers, and students from Office 365 Education:

  - [Office 365 Schools REST API reference](https://msdn.microsoft.com/office/office365/api/school-rest-operations)

The sample is implemented with the PHP language and the [Laravel](https://laravel.com/) framework.

> [Laravel](https://laravel.com/) is a PHP web application framework with expressive, elegant syntax.  It attempts to take the pain out of development by easing common tasks used in the majority of web projects.
>
> Laravel is accessible, yet powerful, providing tools needed for large, robust applications. A superb combination of simplicity, elegance, and innovation give you tools you need to build any application with which you are tasked.

## Prerequisites

**Deploying and running this sample requires**:

- An Azure subscription with permissions to register a new application, and deploy the web app.

- An O365 Education tenant with Microsoft School Data Sync enabled

  - One of the following browsers: Edge, Internet Explorer 9, Safari 5.0.6, Firefox 5, Chrome 13, or a later version of one of these browsers.

  Additionally: Developing/running this sample locally requires the following:  

  - [PHP 7.0](http://php.net/downloads.php)
  - [Composer](https://getcomposer.org/download/)
  - [Git](https://git-scm.com/download/win)
  - PHP IDE like [PhpStorm](https://www.jetbrains.com/phpstorm/specials/phpstorm/phpstorm.html)
  - Familiarity with PHP and [Laravel](https://laravel.com/).

**Optional configuration**:

A feature in this sample demonstrates calling the Bing Maps API which requires a key to enable the Bing Maps feature. 

Create a key to enable Bing Maps API features in the app:

1. Open [https://www.bingmapsportal.com/](https://www.bingmapsportal.com/) in your web browser and sign in.

2. Click  **My account** -> **My keys**.

3. Create a **Basic** key, select **Public website** as the application type.

4. Copy the **Key** and save it. 

   ![](Images/bing-maps-key.png)

   > **Note:** The key is used in the app configuration steps for debug and deploy.


## Register the application in Azure Active Directory

1. Sign into the new Azure portal: [https://portal.azure.com/](https://portal.azure.com/).

2. Choose your Azure AD tenant by selecting your account in the top right corner of the page:

   ![](Images/aad-select-directory.png)

3. Click **Azure Active Directory** -> **App registrations** -> **+Add**.

   ![](Images/aad-create-app-01.png)

4. Input a **Name**, and select **Web app / API** as **Application Type**.

   Input **Sign-on URL**: http://localhost. 

   ![](Images/aad-create-app-02.png)

   Click **Create**.

5. Once completed, the app will show in the list.

   ![](/Images/aad-create-app-03.png)

6. Click it to view its details. 

   ![](/Images/aad-create-app-04.png)

7. Click **All settings**, if the setting window did not show.

   - Click **Properties**, then set **Multi-tenanted** to **Yes**.

     ![](/Images/aad-create-app-05.png)

     Copy aside **Application ID**, then Click **Save**.

   - Click **Required permissions**. Add the following permissions:

     | API                            | Application Permissions | Delegated Permissions                    |
     | ------------------------------ | ----------------------- | ---------------------------------------- |
     | Microsoft Graph                |                         | Read all users' full profiles<br>Read all groups<br>Read directory data<br>Access directory as the signed in user<br>Sign users in |
     | Windows Azure Active Directory |                         | Sign in and read user profile<br>Read and write directory data |

     ![](/Images/aad-create-app-06.png)

   - Click **Keys**, then add a new key:

     ![](Images/aad-create-app-07.png)

     Click **Save**, then copy aside the **VALUE** of the key. 

   Close the Settings window.

## Debug locally

The following software and components are required:

- PHP >= 7.0.0
- OpenSSL PHP Extension
- PDO PHP Extension
- Mbstring PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension
- [Git](https://git-scm.com/download/win)

[PhpStorm](https://www.jetbrains.com/phpstorm/specials/phpstorm/phpstorm.html) is recommended to debug the sample locally.

1. Download the zip or clone source code from GitHub to local machine. 

2. Open directory of source code with PhpStorm.

3. Run "Composer update" command with terminal. 


Debug the **EDUGraphAPI**:

1. Configure **environment variables**. Create a local .env file and input like below:

   ![env](/Images/env.jpg)

   - **clientId**: use the Client Id of the app registration you created earlier.
   - **clientSecret**: use the Key value of the app registration you created earlier.
   - **BingMapKey**: use the key of Bing Map you got earlier. This setting is optional.
   - **SourceCodeRepositoryURL**: use the URL of this repository.

2. Run the site.

## Deploy the sample to Azure

**GitHub Authorization**

1. Generate Token

   - Open https://github.com/settings/tokens in your web browser.
   - Sign into your GitHub account where you forked this repository.
   - Click **Generate New Token**
   - Enter a value in the **Token description** text box
   - Select the followings (your selections should match the screenshot below):
     - repo (all) -> repo:status, repo_deployment, public_repo
     - admin:repo_hook -> read:repo_hook

   ![](Images/github-new-personal-access-token.png)

   - Click **Generate token**
   - Copy the token

2. Add the GitHub Token to Azure in the Azure Resource Explorer

   - Open https://resources.azure.com/providers/Microsoft.Web/sourcecontrols/GitHub in your web browser.
   - Log in with your Azure account.
   - Selected the correct Azure subscription.
   - Select **Read/Write** mode.
   - Click **Edit**.
   - Paste the token into the **token parameter**.

   ![](Images/update-github-token-in-azure-resource-explorer.png)

   - Click **PUT**

**Deploy the Azure Components from GitHub**

1. Check to ensure that the build can run in local environment.

2. Fork this repository to your GitHub account.

3. Click the Deploy to Azure Button:

   [![Deploy to Azure](http://azuredeploy.net/deploybutton.png)](https://portal.azure.com/#create/Microsoft.Template/uri/https%3A%2F%2Fraw.githubusercontent.com%2FOfficeDev%2FO365-EDU-PHP-Samples%2Fmaster%2Fazuredeploy.json)

4. Fill in the values in the deployment page and select the **I agree to the terms and conditions stated above** checkbox.

   ![](Images/azure-auto-deploy.png)

   - **Resource group**: we suggest you create a new group.

   - **Site Name**: please input a name. Like EDUGraphAPICanviz or EDUGraphAPI993.

     > Note: If the name you input is taken, you will get some validation errors:
     >
     > ![](Images/azure-auto-deploy-validation-errors-01.png)
     >
     > Click it you will get more details like storage account is already in other resource group/subscription.
     >
     > In this case, please use another name.

   - **Source Code Repository URL**: replace <YOUR REPOSITORY> with the repository name of your fork.

   - **Source Code Manual Integration**: choose **false**, since you are deploying from your own fork.

   - **Client Id**: use the Client Id of the app registration you created earlier.

   - **Client Secret**: use the Key value of the app registration you created earlier.

   - **Bing Map Key**: use the key of Bing Map you got earlier. This setting is optional. It will hide Bing map icon on schools page if this field is empty.

   - Check **I agree to the terms and conditions stated above**.

5. Click **Purchase**.

**Add REPLY URL to the app registration**

1. After the deployment, open the resource group:

   ![](Images/azure-resource-group.png)

2. Click the web app.

   ![](Images/azure-web-app.png)

   Copy the URL aside and change the schema to **https**. This is the replay URL and will be used in next step.

3. Navigate to the app registration in the new Azure portal, then open the setting windows.

   Add the reply URL:

   ![](Images/aad-add-reply-url.png)

   > Note: to debug the sample locally, make sure that http://localhost is in the reply URLs.

4. Click **SAVE**.

## Understand the code

### Introduction

**Solution Component Diagram**

![solution](/Images/solution.jpg)

**Authentication Mechanisms**

We utilized the built-in [authentication](https://laravel.com/docs/5.4/authentication) of the Laravel framework to enable user login.

- **Local users authentication**: implemented by the default Eloquent authentication driver. The `App\User` [Eloquent model](https://laravel.com/docs/5.4/eloquent) is included to access users stored in the database.

- **O365 users authentication**: implemented by [Laravel Socialite](https://github.com/laravel/socialite) and `App\Providers\O365Provider` which is based on the [Microsoft Azure](http://socialiteproviders.github.io/providers/microsoft-azure/) service provider.

  > [Laravel Socialite](https://github.com/laravel/socialite) provides an expressive, fluent interface to OAuth authentication with Facebook, Twitter, Google, LinkedIn, GitHub and Bitbucket. It handles almost all of the boilerplate social authentication code you are dreading writing.
  >
  > Adapters for other platforms like [Microsoft Azure](http://socialiteproviders.github.io/providers/microsoft-azure/) are listed at the community driven [Socialite Providers](https://socialiteproviders.github.io/) website.

[thephpleague/oauth2-client](https://github.com/thephpleague/oauth2-client) is used to handle tokens.

**Data Access**

[Eloquent](https://laravel.com/docs/5.4/eloquent) is used to access data stored in the SQLite database.

The tables used in this demo:

| Table                        | Description                              |
| ---------------------------- | ---------------------------------------- |
| Users                        | Contains the user's information: name, email, password...<br>*o365UserId* and *o365Email* are used to connect the local user with an O365 user. |
| UserRoles                    | Contains users' role. Three roles are used in this sample: admin, teacher, and student. |
| Organizations                | A row in this table represents a tenant in AAD.<br>*isAdminConsented* column records than if the tenant consented by an administrator. |
| TokenCache                   | Contains the users' access/refresh tokens. |
| ClassroomSeatingArrangements | Contains the classroom seating arrangements. |

**Controllers**

Below are the main controllers used by the sample.

| Controller         | Description                              |
| ------------------ | ---------------------------------------- |
| LoginController    | contains actions for local users to log in. |
| O365AuthController | contains actions for O365 users to log in |
| LinkController     | implements the **Local/O365 Login Authentication Flow**. Please check [Authentication Flows](https://github.com/TylerLu/EDUGraphAPI#authentication-flows) section for more details. |
| AdminController    | contains the admin actions               |
| SchoolsController  | contains actions to show schools and classes. `SchoolsService` class is mainly used by this controller. Please check [Office 365 Education API](https://github.com/TylerLu/EDUGraphAPI#office-365-education-api) section for more details. |

All the controllers are under the **app/Http/Controller** folder.

**Middlewares**

We create several middlewares for authentication and authorization.

| Middleware              | Description                              |
| ----------------------- | ---------------------------------------- |
| AdminOnlyMiddleware     | Only allows admin to access the protected routes. It is mainly used for AdminController. |
| LinkRequiredMiddleware  | Redirects unlinked users to /link. It is mainly used for the SchoolsController. |
| SocializeAuthMiddleware | Integrate O365 user with PHP authentication framework. The current O365 user could be got through ```Auth:user()```. |

All the middleware are in the **app/Http/Middleware**.

**Services**

Below are the main services used by the sample:

| Service           | Description                              |
| ----------------- | ---------------------------------------- |
| AADGraphService   | Contains methods used to access AAD Graph REST APIs. |
| MSGraphService    | Contains methods used to access MS Graph REST APIs. |
| EducationService  | Contains methods like get user information,  get schools/classes/users, get/update seating arrangements. |
| CookieService     | Contains methods that used to manage cookies. |
| TokenCacheService | Contains methods used to get and update token cache from the database. |
| UserService       | Contains methods used to manipulate users in the database. |
| AdminService      | Contains administrative methods like consent tenant, manage linked accounts. |

All the services are in the **app/Services** folder.

**Multi-tenant app**

This web application is a **multi-tenant app**. In the AAD, we enabled the option:

![](Images/app-is-multi-tenant.png)

Users from any Azure Active Directory tenant can access this app. Some permissions used by this app require an administrator of the tenant to consent before users can use the app. Otherwise, users will see this error:

![](Images/app-requires-admin-to-consent.png)

For more information, see [Build a multi-tenant SaaS web application using Azure AD & OpenID Connect](https://azure.microsoft.com/en-us/resources/samples/active-directory-dotnet-webapp-multitenant-openidconnect/).

### Office 365 Education API

The [Office 365 Education APIs](https://msdn.microsoft.com/office/office365/api/school-rest-operations) return data from any Office 365 tenant which has been synced to the cloud by Microsoft School Data Sync. The APIs provide information about schools, sections, teachers, students, and rosters. The Schools REST API provides access to school entities in Office 365 for Education tenants.

In this sample, the `App\Services\EducationService` class encapsulates the Office 365 Education API. 

**Get schools**

~~~typescript
public function getSchools()
{
    return $this->getAllPages("get", "/administrativeUnits?api-version=beta", School::class);
}
~~~

~~~typescript
public function getSchool($objectId)
{
    return $this->getResponse("get", "/administrativeUnits/" . $objectId . "?api-version=beta", School::class, null, null);
}
~~~

**Get classes**

~~~typescript
public function getSections($schoolId, $top, $skipToken)
{
    return $this->getResponse("get", '/groups?api-version=beta&$filter=extension_fe2174665583431c953114ff7268b7b3_Education_ObjectType%20eq%20\'Section\'%20and%20extension_fe2174665583431c953114ff7268b7b3_Education_SyncSource_SchoolId%20eq%20\'' . $schoolId . '\'', Section::class, $top, $skipToken);
}
~~~

```typescript
public function getSectionWithMembers($objectId)
{
    return $this->getResponse("get", '/groups/' . $objectId . '?api-version=beta&$expand=members', Section::class, null, null);
}
```
**Get users**

```typescript
public function getMembers($objectId, $top, $skipToken)
{
    return $this->getResponse("get", "/administrativeUnits/" . $objectId . "/members?api-version=beta", SectionUser::class, $top, $skipToken);
}
```
Below are some screenshots of the sample app that show the education data.

![](Images/edu-schools.png)

![](Images/edu-users.png)

![](Images/edu-classes.png)

![](Images/edu-class.png)

### Authentication Flows

There are 4 authentication flows in this project.

The first 2 flows (Local Login/O365 Login) enable users to login in with either a local account or an Office 365 account, then link to the other type account. This procedure is implemented in the LinkController.

**Local Login Authentication Flow**

![](Images/auth-flow-local-login.png)

**O365 Login Authentication Flow**

![](Images/auth-flow-o365-login.png)

**Admin Login Authentication Flow**

This flow shows how an administrator logs into the system and performs administrative operations.

After logging into the app with an Office 365 account, the administrator will be asked to link to a local account. This step is not required and can be skipped. 

As mentioned earlier, the web app is a multi-tenant app which uses some application permissions, so tenant administrator must consent the app first.  

This flow is implemented in the AdminController. 

![](Images/auth-flow-admin-login.png)

### Two Kinds of Graph APIs

There are two distinct Graph APIs used in this sample:

|              | [Azure AD Graph API](https://docs.microsoft.com/en-us/azure/active-directory/develop/active-directory-graph-api) | [Microsoft Graph API](https://graph.microsoft.io/) |
| ------------ | ---------------------------------------- | ---------------------------------------- |
| Description  | The Azure Active Directory Graph API provides programmatic access to Azure Active Directory through REST API endpoints. Apps can use the Azure AD Graph API to perform create, read, update, and delete (CRUD) operations on directory data and directory objects, such as users, groups, and organizational contacts | A unified API that also includes APIs from other Microsoft services like Outlook, OneDrive, OneNote, Planner, and Office Graph, all accessed through a single endpoint with a single access token. |
| Client       | Install-Package [Microsoft.Azure.ActiveDirectory.GraphClient](https://www.nuget.org/packages/Microsoft.Azure.ActiveDirectory.GraphClient/) | Install-Package [Microsoft.Graph](https://www.nuget.org/packages/Microsoft.Graph/) |
| End Point    | https://graph.windows.net                | https://graph.microsoft.com              |
| API Explorer | https://graphexplorer.cloudapp.net/      | https://graph.microsoft.io/graph-explorer |

> **IMPORTANT NOTE:** Microsoft is investing heavily in the new Microsoft Graph API, and they are not investing in the Azure AD Graph API anymore (except fixing security issues).

> Therefore, please use the new Microsoft Graph API as much as possible and minimize how much you use the Azure AD Graph API.

Below is a piece of code shows how to get "me" from the Microsoft Graph API.

```typescript
public function getMe()
{
    $json = $this->getResponse("get", "/me?api-version=1.5", null, null, null);
    $assignedLicenses = array_map(function ($license) {
        return new Model\AssignedLicense($license);
    }, $json["assignedLicenses"]);
    ...
}
```

Note that in the AAD Application settings, permissions for each Graph API are configured separately:

![](Images/aad-create-app-06.png) 

## Questions and comments

- If you have any trouble running this sample, please [log an issue](https://github.com/OfficeDev/O365-EDU-AspNetMVC-Samples/issues).
- Questions about GraphAPI development in general should be posted to [Stack Overflow](http://stackoverflow.com/questions/tagged/office-addins). Make sure that your questions or comments are tagged with [ms-graph-api]. 

## Contributing

We encourage you to contribute to our samples. For guidelines on how to proceed, see [our contribution guide](/Contributing.md).

This project has adopted the [Microsoft Open Source Code of Conduct](https://opensource.microsoft.com/codeofconduct/). For more information see the [Code of Conduct FAQ](https://opensource.microsoft.com/codeofconduct/faq/) or contact [opencode@microsoft.com](mailto:opencode@microsoft.com) with any additional questions or comments.



**Copyright (c) 2017 Microsoft. All rights reserved.**