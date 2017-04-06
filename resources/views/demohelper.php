<?php
/**
 *  Copyright (c) Microsoft. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

use Illuminate\Support\Facades\Route;
use Microsoft\Graph\Connect\Constants;

$links = getCurrentPageLinks();

function getCurrentPageLinks()
{
    $router = getActionAndController();
    $controller = $router['controller'];
    $action=$router['action'];

    $links=null;
    if($controller && $action){
        $array = getJsonArray();
        foreach ($array as $item){
            if(strtolower($item->controller)===$controller && strtolower($item->action)===$action){
               return $item->links;
            }
        }
    }
    return [];
}
function getActionAndController()
{
    $result=["controller" => '', "action"=>''];
    $route = Route::current();
    $actionName = $route->getActionName();
    if(strpos($actionName,'@')<0)
        return $result;
    $var = explode('@',$actionName);
    $controller=strtolower($var[0]);
    $action = strtolower($var[1]);
    if(strpos($controller,'\\')>0){
        $var = explode('\\',$controller);
        $controller = $var[count($var)-1];
    }
    return ["controller" => $controller, "action"=>$action];
}
function clearBom($contents){
    $BOM = chr(239).chr(187).chr(191);
    return str_replace($BOM,'',$contents);
}
function getJsonArray()
{
    $json_string = file_get_contents('public/demo-pages.json');
    $json_string =trim( preg_replace("#(/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/)|([\s\t]//.*)|(^//.*)#", '', $json_string));
    $json_string = clearBom($json_string);
// 把JSON字符串转成PHP数组
    return json_decode($json_string,false);
}
?>

<div class="demo-helper-control collapsed">
    <div class="header">DEMO HELPER</div>
    <div class="header-right-shadow-mask"></div>
    <div class="body">
        <p class="desc">Code sample links for this page:</p>

        <ul>
    <?php
    foreach ($links as $link)
    {
    ?>
            <li>
                <p class="title"><?php echo $link->title; ?></p>
                <p><a href="<?php echo env(Constants::SOURCECODERESPOSITORYURL) . $link->url; ?>" target="_blank"><?php echo env(Constants::SOURCECODERESPOSITORYURL).$link->url; ?></a></p>
            </li>
<?php
    }
?>
        </ul>
<?php
if(count($links)==0){
?>
        <p class="empty-result">Links not available.</p>
<?php }?>
    </div>
</div>