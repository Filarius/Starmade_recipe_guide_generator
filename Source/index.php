<?php

//Starmade recipe guide generator.
//
//This is PHP script what takes XML Starmade server config file and generate 
//user-friendly guides for better understanding of recipes craft modification.
//Script generate 2 HTML files, one just redraw recipes, with repice prices 
//and currency and count of every item what participate in it (this must be best for mod balancing),
//second file show in what repices certain block used as both input and 
//output(this one must be best for building recipes chains).
//
//Script need not compressed server`s block config XML file.
//Config file must have information about all items as script use this 
//info to get block`s names from IDs in recipes.
//
//Bug: script can to not find names for blocks if there is more then 2 levels in blocks categories.
//     
//Code not best, and still can have unneeded and be not optimal.
//
//
//Script is free for use "AS IS", but please make credits to author, 
//and if you make profits on same project where you use this - it will be nice to make a tip.
//
// Code by Filarius  aka  Vladimir Shuvalov

header("Content-Type: text/html; charset=utf-8"); 

define("PATH_TO_CONFIG","blockconfig.xml");

function parser($ar,$text='')
{
    $data;
    
    foreach($ar as $key => $value)
    {
        if(is_array($value))
        {
            $data.=parser($value,$text."[$key]");
        }
        else {
           $data.= $text."[$key]=$value</br>\n\r";
        }
    }
    return $data;
}            


function xml2array($xml,$spacer='')
{                                       
    $i=-1;
    for ($xml->rewind(); $xml->valid(); $xml->next())
    {
        $i+=1;
                   
        foreach($xml->current()->attributes() as $key => $value)
        {
          $array[$i]['att'][strval($key)]=strval($value); 
        }                                     
        $array[$i]['tag']=strval( $xml->key()); 
        $array[$i]['text']=strval($xml->current());   
        
                      
        
        if($xml->hasChildren())
        {
          $array[$i]['child']= xml2array($xml->getChildren(),$spacer.'=')  ;
        }
           
       
    };
    return $array;
};        
       
$is_updated = false;
if(file_exists('recipes.html'))
{
    $is_updated = (filemtime('recipes.html') <= filemtime(PATH_TO_CONFIG));
    
};

if(!($is_updated)){
 touch(PATH_TO_CONFIG)  ;
 echo "<br>Lucky one ! You make guides are rebuilded on new config<br>"    ;



// START OF MANY CODE FOR PARSE DATA ON NEW CONFIG


$file = file_get_contents(PATH_TO_CONFIG);
$file = str_replace(array("\n", "\r", "\t"), '', $file);
$file = trim(str_replace('"', "'", $file));

$xml = simplexml_load_string($file);

$iter = new SimpleXMLIterator($file);
$array = xml2array($iter->Recipes[0]);
                       
foreach($array as $key=>$value)
{
                           
    $arr['Recipe'][$key]['cost']=$value['att']['costAmount'];
    $arr['Recipe'][$key]['costtype']=$value['att']['costType'];
    $arr['Recipe'][$key]['name']=$value['att']['name'];   
    foreach($value['child'] as $key2=>$value2)
    {  
                       

     foreach($value2['child'] as $key3=>$value3)
     {
     //inputs  and outputs
       foreach($value3['child'] as $key4=>$value4)
        {  
                              
           $arr['Recipe'][$key][$value2['tag']][$key2][$value3['tag']][$key4]['count']=$value4['att']['count'];
           $arr['Recipe'][$key][$value2['tag']][$key2][$value3['tag']][$key4]['name']=$value4['text'];
                                       
        } 
     }  
    }
                         
}

//normaizing array
$array=array();

for($i1=0;$i1<count($arr['Recipe']);$i1+=1)
{
    for($i2=0;$i2<count($arr['Recipe'][$i1]['Product']);$i2+=1)
    {
        for($i3=0;$i3<(count($arr['Recipe'][$i1]['Product'][$i2]['Input']));$i3+=1)
        {
          if($arr['Recipe'][$i1]['Product'][$i2]['Input'][$i3]['count']==0)
          {
             $arr['Recipe'][$i1]['Product'][$i2]['Input'][$i3]=array();    
          }    
          for($i4=($i3+1);$i4<count($arr['Recipe'][$i1]['Product'][$i2]['Input']);$i4+=1)
          {
          
            if
             (
              $arr['Recipe'][$i1]['Product'][$i2]['Input'][$i3]['name']
               ==
              $arr['Recipe'][$i1]['Product'][$i2]['Input'][$i4]['name'] 
             )
             {
                 $arr['Recipe'][$i1]['Product'][$i2]['Input'][$i3]['count'] +=                                   
                   $arr['Recipe'][$i1]['Product'][$i2]['Input'][$i4]['count'];
                 $arr['Recipe'][$i1]['Product'][$i2]['Input'][$i4]['count']=0;  
             }
          }  
        }  
         for($i3=0;$i3<(count($arr['Recipe'][$i1]['Product'][$i2]['Output']));$i3+=1)
        {       
          if($arr['Recipe'][$i1]['Product'][$i2]['Output'][$i3]['count']==0)
          {
              $arr['Recipe'][$i1]['Product'][$i2]['Output'][$i3]=array(); 
             
              continue;   
          }    
          for($i4=($i3+1);$i4<count($arr['Recipe'][$i1]['Product'][$i2]['Output']);$i4+=1)
          {
            if
             (
              $arr['Recipe'][$i1]['Product'][$i2]['Output'][$i3]['name']
               ==
              $arr['Recipe'][$i1]['Product'][$i2]['Output'][$i4]['name'] 
             )
             {
                 $arr['Recipe'][$i1]['Product'][$i2]['Output'][$i3]['count'] +=                                   
                   $arr['Recipe'][$i1]['Product'][$i2]['Output'][$i4]['count'];  
                 $arr['Recipe'][$i1]['Product'][$i2]['Output'][$i4]['count']=0;
             }
          }           
        }
        //Fixing indexes  
        foreach($arr['Recipe'][$i1]['Product'][$i2]['Input'] as $key3 => $value3)
        {   
            if(count($arr['Recipe'][$i1]['Product'][$i2]['Input'][$key3])==0)
            {
              unset($arr['Recipe'][$i1]['Product'][$i2]['Input'][$key3])  ;
            }   
        }
        
        foreach($arr['Recipe'][$i1]['Product'][$i2]['Input'] as $key3 => $value3)
        {
            $arr['Recipe'][$i1]['Product'][$i2]['Input']=
              array_values($arr['Recipe'][$i1]['Product'][$i2]['Input']);
        }
        
         foreach($arr['Recipe'][$i1]['Product'][$i2]['Output'] as $key3 => $value3)
        {   
            if(count($arr['Recipe'][$i1]['Product'][$i2]['Output'][$key3])==0)
            {
              unset($arr['Recipe'][$i1]['Product'][$i2]['Output'][$key3])  ;
            }   
        }
        
        foreach($arr['Recipe'][$i1]['Product'][$i2]['Output'] as $key3 => $value3)
        {
            $arr['Recipe'][$i1]['Product'][$i2]['Output']=
              array_values($arr['Recipe'][$i1]['Product'][$i2]['Output']);
        }      
    }
}


               
$element = xml2array($iter->Element[0]);
                   

for($i1=0;$i1<count($element);$i1+=1)
{
    
    for($i2=0;$i2<count($element[$i1]['child']);$i2+=1)
    {
        if(isset($element[$i1]['child'][$i2]['att']['type']))
        {
            $blocks[$element[$i1]['child'][$i2]['att']['type']]=$element[$i1]['child'][$i2]['att']['name'];            
        }
        
        for($i3=0;$i3<count($element[$i1]['child'][$i2]['child']);$i3+=1)        
        {
          if(isset($element[$i1]['child'][$i2]['child'][$i3]['att']['type']))
        {
            $blocks[$element[$i1]['child'][$i2]['child'][$i3]['att']['type']]
             =
              $element[$i1]['child'][$i2]['child'][$i3]['att']['name'];            
        }
            
        }
    }    
}                       

//STARTING MAKING RECIPES
$html='';
$html.='<html xmlns="http://www.w3.org/1999/xhtml">
<head>    
  <meta charset="utf-8"
  </head>
  <body>';
 
$productid=0;
$changecolor=false;
for($i1=0;$i1<count($arr['Recipe']);$i1+=1)
{
    $html.= '<table border="1" bgcolor=#E0E0E0>
       <tr><td colspan="2" style="background-color:#B0B0B0">
       '; 
       $html.=$arr['Recipe'][$i1]['name'].'  '.
              $arr['Recipe'][$i1]['cost'].'x'.
              $arr['Recipe'][$i1]['costtype'];
       $html.='</td></tr> ';
    
    for($i2=0;$i2<count($arr['Recipe'][$i1]['Product']);$i2+=1)
    {
        $productid+=1;
        $blockindex=0;
        $html.='<tr ';
         if($changecolor)
            {
                $html.= 'style="background-color:#A0A0A0"';
            }
            else 
            {
              $html.= 'style="background-color:#CCCCCC"';
            }
            $html.='><td>';
            $changecolor=!($changecolor);
        
        
        for($i3=0;$i3<(count($arr['Recipe'][$i1]['Product'][$i2]['Input']));$i3+=1)
        {
            $links[$productid][$arr['Recipe'][$i1]['name']][$blockindex]['block']=$blocks[$arr['Recipe'][$i1]['Product'][$i2]['Input'][$i3]['name']];
            $links[$productid][$arr['Recipe'][$i1]['name']][$blockindex]['type']='input';
            $blockindex+=1;
            
            $html.= $arr['Recipe'][$i1]['Product'][$i2]['Input'][$i3]['count'].
                 'x '.
                 $blocks[$arr['Recipe'][$i1]['Product'][$i2]['Input'][$i3]['name']].
                 '<br>';
                 if(isset($_GET['dev']))
                 {
                     $html.= $arr['Recipe'][$i1]['Product'][$i2]['Input'][$i3]['name'];
                     $html.= "<br>";
                 }
                 
         
        } 
        $html.='</td><td>';
        
         for($i3=0;$i3<(count($arr['Recipe'][$i1]['Product'][$i2]['Output']));$i3+=1)
        {   
            $links[$productid][$arr['Recipe'][$i1]['name']][$blockindex]['block']=$blocks[$arr['Recipe'][$i1]['Product'][$i2]['Output'][$i3]['name']];
            $links[$productid][$arr['Recipe'][$i1]['name']][$blockindex]['type']='output';
            $blockindex+=1;
            
            $html.= $arr['Recipe'][$i1]['Product'][$i2]['Output'][$i3]['count'].
                 'x '.
                 $blocks[$arr['Recipe'][$i1]['Product'][$i2]['Output'][$i3]['name']].
                 '<br>';    
                 if(isset($_GET['dev']))
                 {
                     $html.= $arr['Recipe'][$i1]['Product'][$i2]['Output'][$i3]['name'];
                     $html.= "<br>";
                 }
          
        }
        $html.='</td></tr>';
        
    }
    $html.= '</table><br>';
       
    
}
                                      

$i=0;
$j=0;
foreach($links as $key1 => $value1)
{
    
    
    foreach($value1 as $key2 => $value2)
    {
        $nodes[$key2.$key1]=$key2;
   
        $j+=1;
         
    
        foreach($value2 as $key3 => $value3)
        { 
            $nodes[$value3['block']]=$value3['block'];
            $blocklist[$value3['block']]=$j;
            $j+=1;
             
            if($value3['type']=='input')
            {
                $nodeslinks[$i]['from']=$value3['block'];
                $nodeslinks[$i]['to']=$key2.$key1;
            }
            else
            {
                $nodeslinks[$i]['from']=$key2.$key1;
                $nodeslinks[$i]['to']=$value3['block'];
            }
            $i+=1;
        }
        
    }
}   

$html.='<br><br><br><div align=center>Code by Filarius</div></body></html>';
file_put_contents('recipes.html',$html);

///////////////////////////////////

           

$html='';

$html.='<html xmlns="http://www.w3.org/1999/xhtml">
<head>

  <meta charset="utf-8"
  </head>
  <body>';
;       


$html.='


<script>
<!--

function land(ref, target)
{
lowtarget=target.toLowerCase();
if (lowtarget=="_self") {window.location=loc;}
else {if (lowtarget=="_top") {top.location=loc;}
 else {if (lowtarget=="_blank") {window.open(loc);}
  else {if (lowtarget=="_parent") {parent.location=loc;}
   else {parent.frames[target].location=loc;};
     }}}
}

function jump(menu)
{
ref=menu.choice.options[menu.choice.selectedIndex].value;
splitc=ref.lastIndexOf("*");
target="";
if (splitc!=-1)
{loc=ref.substring(0,splitc);
target=ref.substring(splitc+1,1000);}
else {loc=ref; target="_self";};
if (ref != "") {land(loc,target);}
}
//-->
</script>



 <form action="">
 <select size="1" name="choice">
    <option value="">CHOOSE BLOCK</option>';


 ksort($blocklist,SORT_STRING);

foreach($blocklist as $key1 => $value1)
{
$html.='<option value="#'.$value1.'">'.$key1.'</option>';
}    

$html.='
 </select>
 <input type="button" onclick="jump(this.form)" value="GO!">
 </form>';

$changecolor = true;

foreach($blocklist as $key1 => $value1)
{
    $html.= '<br>';
    $html.='<table border=1 bgcolor=#E0E0E0 id="'.$value1.'">'; 


    $html.= '<tr><td colspan="2" style="background-color:#B0B0B0">';
    $html.= $key1;
    $html.='</td></tr>';
     
    $html.='<tr>';
    $html.='<td>==>></td>';
    $html.= '<td>>>==</td>';
    $html.='</tr>';
    
    $html.='<tr>';
    // START OF LEFT SIDE
    $html.='<td valign="top">';
    
    
   
    
    $html.= '<table border=0>';
    foreach($nodeslinks as $value2)
    {
        if($value2['to']==$key1)
        {
            
            $html.= '<tr ';
             if($changecolor)
            {
                $html.= 'style="background-color:#A0A0A0"';
            }
            else 
            {
              $html.='style="background-color:#CCCCCC"';
            } 
            $changecolor=!($changecolor);               
             
            $html.= '><td>';
            foreach($nodeslinks as $value3)
            {
                if($value3['to']==$value2['from'])
                {
                    $html.= '<a href="#'.$blocklist[$value3['from']].'">';
                    $html.= $value3['from'];
                    $html.= '</a>';
                    $html.= '<br>';
                }
                
            }
            $html.= '</td>';
            $html.= '<td>';
            $html.= $nodes[$value2['from']];
            $html.= '</td>';
            $html.= '</tr>';
            
        }
    }
    $html.= '</table>';
  
    
    $html.= '</td>';
    // END OF LEFT SIDE
    // START OF RIGHT SIDE 
    
    
    $html.= '<td  valign="top">';
    $html.= '<table border=0>';
    foreach($nodeslinks as $value2)
    {
        if($value2['from']==$key1)
        {
            
             $html.= '<tr ';
            if($changecolor)
            {
                $html.= 'style="background-color:#A0A0A0"';
            }
            else 
            {
              $html.= 'style="background-color:#CCCCCC"';
            }
            $changecolor=!($changecolor);               
             
            $html.= '><td>';
            $html.= $nodes[$value2['to']];
            $html.= '</td>';
            $html.= '<td>';
            foreach($nodeslinks as $value3)
            {
                if($value3['from']==$value2['to'])
                {
                    $html.= '<a href="#'.$blocklist[$value3['to']].'">';
                    $html.= $value3['to'];
                    $html.= '</a>';
                    $html.= '<br>';
                }
                
            }
            $html.= '</td>';
            $html.= '</tr>';
            
        }
    }
    $html.= '</table>';
 
    
    $html.= '</td>';
    // END OF RIGHT SIDE
    $html.= '</tr>';
    $html.= '</table><br>';
    
}
$html.='<br><br><div align=center>Code by Filarius</div></body></html>';
file_put_contents('blockbuild.html',$html);

}
 echo '<br><a href="recipes.html">Recipe`s list</a>';
 echo '<br><a href="blockbuild.html">Crafting info</a>';
   
// END OF MANY CODE FOR PARSE DATA ON NEW CONFIG

                         

?>
