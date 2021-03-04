<?php
defined('MOODLE_INTERNAL') || die;
global $DB;

//----------------------------------------
// code for create combination of I+F or U
//----------------------------------------
$pinyinData = $DB->get_records_sql('SELECT c.id, LOWER(c.content) AS content FROM `{data_records}` as r LEFT JOIN `{data_content}` as c ON r.id = c.recordid WHERE `r`.`dataid` = 1 AND (c.fieldid = 1 OR c.fieldid = 3)');
$pinyinData = array_chunk($pinyinData, 2);

$initialArr = $finalArr = array();
foreach($pinyinData as $key => $value){
    if($value[1]->content == 'i'){
        $initialArr[] = $value[0]->content;
    }
    if($value[1]->content == 'f'){
        $finalArr[] = $value[0]->content;
    }
}
$initialArr[] = '';
$pinyinCharacterArr = [];

foreach($initialArr as $key => $value){
    foreach($finalArr as $k => $v){
        $pinyinchar = $value.$v;
        $pinyincharArr = array('ju'=>'jü','qu'=>'qü','xu'=>'xü','yu'=>'yü','jue'=>'jüe','que'=>'qüe','xue'=>'xüe','yue'=>'yüe','jun'=>'jün','qun'=>'qün','xun'=>'xün','yun'=>'yün');
        if(in_array($pinyinchar, $pinyincharArr)){
            $pinyinchar = array_search($pinyinchar, $pinyincharArr);
        }
        $pinyinCharacterArr[] = $pinyinchar;
    }
}
unset($pinyinData);
unset($initialArr);
unset($finalArr);
//$pinyinCharacterArr = array_unique($pinyinCharacterArr);
//----------------------------------------
// code for get chinese characters
//----------------------------------------

$sql = "SELECT c.id,c.fieldid,c.recordid, c.content,(SELECT c2.content FROM {data_content} AS c2 WHERE c2.fieldid = 14 AND c.recordid = c2.recordid) AS chinese_character FROM {data_content} AS c LEFT JOIN {data_records} AS r ON c.recordid = r.id WHERE r.dataid = 3 AND c.fieldid = 28";
$getData = $DB->get_records_sql($sql);
$tr = '';
$sesskey = sesskey();
foreach($getData as $value){
    $pinyin = $value->content;
    $multiplePinyin = explode(' ', $pinyin);
    $chineseChar = '';
    foreach($multiplePinyin as $pinyinVal){
        $number = preg_replace('/[^0-9]+/', '', $pinyinVal);
        if(!($number >= 1 && $number <= 5)){
            if($chineseChar == $value->chinese_character){
                $chineseChar == '';
                continue;
            }
            $tr .= '<tr>';
            $tr .= '<td>'.$value->chinese_character.'</td>';
            $tr .= '<td>'.$pinyin.'</td>';
            $tr .= '<td><a href="/mod/data/edit.php?d=3&rid='.$value->recordid.'&sesskey='.$sesskey.'" target="_blank" class="btn btn-primary">Update</a></td>';
            $tr .= '</tr>';
            $chineseChar = $value->chinese_character;
            continue;
        }
//        $pinyinVal = str_replace('jüe','jue',$pinyinVal);
        $pinyinVal = preg_replace('/[0-9]+/', '', $pinyinVal);
        if(!in_array($pinyinVal, $pinyinCharacterArr)){
            if($chineseChar == $value->chinese_character){
                $chineseChar == '';
                continue;
            }
            $tr .= '<tr>';
            $tr .= '<td>'.$value->chinese_character.'</td>';
            $tr .= '<td>'.$pinyin.'</td>';
            $tr .= '<td><a href="/mod/data/edit.php?d=3&rid='.$value->recordid.'&sesskey='.$sesskey.'" target="_blank" class="btn btn-primary">Update</a></td>';
            $tr .= '</tr>';
            $chineseChar = $value->chinese_character;
            continue;
        }
    }
}
if($tr!=""){
    echo '<table id="unmatchedpinyin" class="table table-bordered table-striped">';
    echo '<thead>';
    echo '<tr><th>Character</th><th>Pinyin</th><th>Action</th></tr>';
    echo '</thead>';
    echo '<tbody>';
    echo $tr;
    echo '</tbody>';
    echo '</table>';
}