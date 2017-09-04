<?php
/**
 *  Copyright (c) Microsoft. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

use Illuminate\Support\Facades\Route;
use Microsoft\Graph\Connect\Constants;

$functions = getCurrentPageLinks();

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
                if(isset($item->functions))
                    return $item->functions;
                else
                    return [];
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
    return json_decode($json_string,false);
}
?>

<div class="demo-helper-control collapsed">
    <div class="header">DEMO HELPER</div>
    <div class="header-right-shadow-mask"></div>
    <div class="body">
        <p class="desc">Code sample links for this page:</p>
<?php
if($functions) {
    ?>
    <ul class="functions">
        <?php
        foreach ($functions as $function) {
            ?>
            <li>
                <p class="title"><?php echo $function->title; ?></p>
                <p class="tab"><?php echo $function->tab; ?></p>
                <ul class="files">
                    <?php
                    $files = $function->files;
                    $GitHubURL = env(Constants::SOURCECODERESPOSITORYURL);
                    $GitHubURL = rtrim($GitHubURL, '/');
                    foreach ($files as $file) {
                        $url = $GitHubURL . $file->url;
                        ?>
                        <li>
                            <p class="title"><a href="<?php echo $url ?>" target="_blank"><?php echo $url ?></a></p>
                            <ul class="methods">
                                <?php
                                $methods = $file->methods;
                                foreach ($methods as $method) {
                                    ?>
                                    <li>
                                        <p class="title"><?php echo $method->title; ?></p>
                                        <p class="desc"><?php echo $method->description; ?></p>
                                    </li>
                                    <?php
                                }
                                ?>
                            </ul>
                        </li>
                        <?php
                    }
                    ?>
                </ul>

            </li>
            <?php
        }
        ?>
    </ul>
    <?php
}
if(count($functions)==0){
?>
        <p class="empty-result">Links not available.</p>
<?php }?>
    </div>
</div>