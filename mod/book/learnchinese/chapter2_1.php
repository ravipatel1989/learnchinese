<?php
defined('MOODLE_INTERNAL') || die;
global $DB;

$totalradicals = $DB->get_record_sql('SELECT count(r.id) AS id FROM `{data_records}` as r LEFT JOIN `{data_content}` as c ON r.id = c.recordid WHERE `r`.`dataid` = 4');
$totalradicals = $totalradicals->id / 12;
$lastPage = ceil($totalradicals / 10);
$pageNum = intval(filter_input(INPUT_GET, 'page'));
$pagenumber = '';
if($pageNum == 0 || $pageNum == 1){
    $previous = "javascript:void(0)";
    $limitStart = 0;
}else{
    $pagenumber = $pageNum - 1;
    $previous = new moodle_url('/mod/book/view.php', ['id'=>15, 'chapterid'=>6, 'page'=> $pagenumber]);
    $limitStart = ($pageNum - 1) * 120;
}
if($pageNum == $lastPage){
    $next = "javascript:void(0)";
}else{
    if($pageNum==0 || $pageNum==1){
        $pagenumber = 2;
    }else{
        $pagenumber = $pageNum + 1;
    }
    $next = new moodle_url('/mod/book/view.php', ['id'=>15, 'chapterid'=>6, 'page'=> $pagenumber]);
}
if($pageNum==0 || $pageNum==1){
    $start = 0;
}else{
    $start = intval($pageNum - 1) * 10;
}

$desccontent = $DB->get_records_sql("SELECT c.recordid FROM {data_content} AS c LEFT JOIN {data_records} AS r ON c.recordid = r.id WHERE c.fieldid = '46' AND r.dataid = 4 ORDER BY CAST(`c`.`content` AS SIGNED) DESC LIMIT $start, 10");
$recordids = array_keys($desccontent);
$recordids = implode(',', $recordids);

$radicalsData = $DB->get_records_sql("SELECT c.* FROM `mdl_data_records` as r LEFT JOIN `mdl_data_content` as c ON r.id = c.recordid WHERE c.recordid IN ($recordids) ");
$radicalsData = array_chunk($radicalsData, 12);
if(count($radicalsData) > 0){
    echo '<table id="radicalTbl" class="table table-bordered table-striped">'; 
    echo '<thead class="thead-light">';
        echo '<tr>';
            echo '<th>Radical No</th>';
            echo '<th>Simplified</th>';
            echo '<th>Traditional</th>';
            echo '<th>Variants</th>';
            echo '<th>Meaning</th>';
            echo '<th>Pinyin</th>';
            echo '<th>Strock count</th>';
            echo '<th>Comment</th>';
            echo '<th>Colloquial name</th>';
            echo '<th>Character count</th>';
        echo '</tr>';
    echo '</thead>';
    echo '</table>';
}
 