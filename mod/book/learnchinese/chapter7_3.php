<?php
defined('MOODLE_INTERNAL') || die;
global $DB;
$sesskey = sesskey();

$sql = "SELECT c.recordid, c.content AS pinyin, (SELECT c1.content FROM {data_content} AS c1 WHERE c1.recordid = c.recordid AND c1.fieldid = 67) AS chchar FROM {data_content} AS c WHERE c.fieldid = 68 AND c.content LIKE '%5%' AND c.content NOT LIKE '%5'";
$sentences = $DB->get_records_sql($sql);

$sql = "SELECT recordid, content FROM {data_content} AS c WHERE fieldid = 14";
$chineseChars = $DB->get_records_sql($sql);

$chineseCharacters = [];

foreach($chineseChars as $key => $chineseChar){
    $chineseCharacters[] = $chineseChars[$key]->content;
}

$sql = "SELECT recordid, content FROM {data_content} AS c WHERE fieldid = 28";
$pinyintones = $DB->get_records_sql($sql);

$pinyintonesdata = [];
foreach($pinyintones as $key => $pinyintone){
    $pinyintonesdata[] = $pinyintones[$key]->content;
}
?>

<table id="datatable" class="table table-bordered table-striped">    
    <thead>
        <tr><th>Character</th><th>Pinyin</th><th>Action</th></tr>
    </thead>
    <tbody>
<?php
foreach($sentences as $skey => $svalue){
    $chchar =  preg_replace("/[^\x{4e00}-\x{9fa5}]+/u", '', $sentences[$skey]->chchar);
    $explodechchar = mb_str_split($chchar);
    $pinyin = $sentences[$skey]->pinyin;
    $sentenceAddPipe = str_replace(array('1','2','3','4','5'), array('1|','2|','3|','4|','5|'), $pinyin);
    $sentenceAddPipe = rtrim($sentenceAddPipe,'|');
    $sentenceArr = explode('|', $sentenceAddPipe);
    $showrecord = true;
    foreach($sentenceArr as $key=>$value){
        $value = trim($value);
        if(strpos($value, '5') !== false){
            $characterkeynum = array_search($explodechchar[$key],$chineseCharacters);
            $charpinyin = $pinyintonesdata[$characterkeynum];
            $checkpos = strpos($charpinyin,$value);
            $prevpos = $checkpos - 1;
            if(strpos($value,'5') !== false && strpos($charpinyin,$value) === false && substr($charpinyin,$prevpos,1) != ' '){
                $showrecord = false;
            }
        }
    }
    if($showrecord == false){
        echo '<tr><td>'.$sentences[$skey]->chchar.'</td><td>'.$sentences[$skey]->pinyin.'</td><td><a target="_blank" class="btn btn-primary" href="../data/edit.php?d=9&rid='.$skey.'&sesskey='.$sesskey.'">Edit</a></td></tr>';
    }
}
?>
    </tbody>
</table>
<?php
function mb_str_split( $string ) {

    return preg_split('/(?<!^)(?!$)/u', $string );
}