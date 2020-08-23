<?php

// $Id$
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Unit tests for Services_JSONFIX.
 * @see JSON.php
 *
 * @category
 * @package     Services_JSON
 * @author      Olivier Lutzwiller
 * @copyright   2020 sos-productions.com
 * @version     CVS: $Id$
 * @license     http://www.opensource.org/licenses/bsd-license.php
 * @link        https://stackoverflow.com/questions/13236819/how-to-fix-badly-formatted-json-in-php/63492087#63492087
 */
 
include("JSON.php"); //Patched version 
	

$arn_ja = "{sos:presents,james:'bond',agent:[0,0,7],secret:\"{mission:'impossible',permit:\"tokill\"}\",go:true, regex: /https:\/\/player.blubrry.com\/id\/([^\/]+)/}";
$arn_ja_valid ="{\"sos\":\"presents\",\"james\":\"bond\",\"agent\":[0,0,7],\"secret\":\"{mission:'impossible',permit:\\\"tokill\\\"}\",\"go\":true,\"regex\": \"/https:\/\/player.blubrry.com\/id\/([^\/]+)/\"}";


$arn_d = 'Json Fix test case with undoublequoted keys and values, challenging decoding specially for classes and json values';


//back to Class, PHP compatible (loose mode for JS) else JS compatible (broken for PHP)
function backToClass($json, $class="PHP") {
    //unprotect regexp delimiters values
    $repl=($class =="PHP") ? '"/$1/"' : '/$1/';
    $tjson=preg_replace('/"\${#(.*)#}"/ismU', $repl, $json);
    //unprotext undoublequoted values
    $repl=($class =="PHP") ? '"$1"' : '$1';
    return preg_replace('/"\${(.*)}"/ismU', $repl, $tjson);
}

function json_fix($json,$to="PHP",$skipBackToClass=false) {
    $sjson = new Services_JSON(SERVICES_JSON_IN_ARR|SERVICES_JSON_USE_TO_JSON| SERVICES_JSON_LOOSE_TYPE);
    
     echo(htmlentities(var_export($sjson->decode($json),true)));
    
    $json=json_encode($sjson->decode($json),true);
    if(!$skipBackToClass) $json=backToClass($json,$to);
    return $json;
}


echo "<h1>json_fix PHPUnit test check by sos-productions.com using patched Services_JSON</h1><br><em>Note this replaces PHPUnit Gas factory I did not manage to install with php7 from pear</em><hr><ol><li>Let's take for the example json source (arn_ja) coming from Javascript world:
<pre>";
    
    echo htmlentities($arn_ja);
    
    echo "</pre></li><li>In PHP world, json_decode(arn_ja) gives:<div><pre>";
            
    $arn_fail=json_decode($arn_ja,true);
    
    if(json_last_error() != JSON_ERROR_NONE) {
    
        echo(json_last_error_msg());
    }
    
    echo "</li>";

echo "<li>fixed json arn_ja for php's world with json_fix(arn_ja) gives arn_ja_fixed:";


$arn_ja_fixed=json_fix($arn_ja);


$arn_fixed=json_decode($arn_ja_fixed,true);
 if(json_last_error() == JSON_ERROR_NONE) {

    //$json=json_encode($json_array);//,JSON_PRETTY_PRINT);
    
    echo "<pre>";
    
    echo "$arn_ja_fixed";
    
    echo "</pre></li><li>json_decode(arn_ja_fixed) gives arn_fixed:<div><pre>";
            
    print_r($arn_fixed);
    
    echo "</pre></div></li>";
    
    
  
    $arn_valid=json_decode($arn_ja_valid,true);
    
    if(json_last_error() == JSON_ERROR_NONE) {
    
        echo "<li><p>We expected equivalent with dump(arn_ja_valid): <div><pre>";
                
        var_dump($arn_valid);
        
        echo "</pre></div></li>";
    
    
        
    }else {
        echo('json_decode($arn_ja_valid,true) with $arn_ja_valid='.$arn_ja_valid.' retruns'.json_last_error_msg()); 
    }
    
     echo "<li>So result is: ";
     
    if($arn_fixed == $arn_valid) {
        echo "Success, arn_ja has been fixed for PHP world with json_fix(arn_ja). This has been made in two steps.<br>a)decode without back to class (arn_ja_partial_fix) with json_fix(arn_ja,'PHP',true)";
        $arn_ja_partial_fix=json_fix($arn_ja,"PHP",true);
        echo "<pre>";
        echo htmlentities($arn_ja_partial_fix);
        echo "</pre>";
        echo "<br>b) and then back to class to PHP with backToClass(arn_ja_partial_fix,'PHP') ";
         echo "<pre>";
        echo backToClass($arn_ja_partial_fix,"PHP");
        echo "</pre>";
        echo "<br><u>Note</u> We could have done a processing on a) and then return back to class in JS world with backToClass(arn_ja_partial_fix,'JS') giving:";
         echo "<pre>";
        echo backToClass($arn_ja_partial_fix,"JS");
         echo "</pre> which is quite equivalent to arn_ja in JS world with the keys were doublequoted in the process.<br> However json_decode(backToClass(arn_ja_partial_fix,'JS') produces ";
         $try=json_decode(backToClass($arn_ja_partial_fix,"JS"),true);
          if(json_last_error() != JSON_ERROR_NONE) {
            echo(json_last_error_msg());
          }
          echo " because in PHP world values exept array and object, numerical or boolean need to be doublequoted, define('presents',1) may have make json_decode happy..but not we still have ";
         define('presents',1);
           $retry=json_decode(backToClass($arn_ja_partial_fix,"JS"),true);
          if(json_last_error() != JSON_ERROR_NONE) {
            echo(json_last_error_msg());
          }
          
        }else {
        echo "FAILURE";
    }
    
    
} else {
   echo(json_last_error_msg());
}

echo "</li></ol>";


?>
