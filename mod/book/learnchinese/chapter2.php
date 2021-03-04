<?php
defined('MOODLE_INTERNAL') || die;
global $DB;
$totalradicals = $DB->get_record_sql('SELECT count(r.id) AS id FROM `{data_records}` as r LEFT JOIN `{data_content}` as c ON r.id = c.recordid WHERE `r`.`dataid` = 4 ORDER BY recordid ASC');
$totalradicals = $totalradicals->id / 12;
$lastPage = ceil($totalradicals / 10);
$pageNum = intval(filter_input(INPUT_GET, 'page'));
if($pageNum == 0 || $pageNum == 1){
    $previous = "javascript:void(0)";
    $limitStart = 0;
}else{
    $pagenumber = $pageNum - 1;
    $previous = new moodle_url('/mod/book/view.php', ['id'=>15, 'chapterid'=>5, 'page'=> $pagenumber]);
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
    $next = new moodle_url('/mod/book/view.php', ['id'=>15, 'chapterid'=>5, 'page'=> $pagenumber]);
}

$radicalsData = $DB->get_records_sql("SELECT r.id, c.* FROM `{data_records}` as r LEFT JOIN `{data_content}` as c ON r.id = c.recordid WHERE `r`.`dataid` = 4 ORDER BY recordid ASC LIMIT {$limitStart}, 120");
$radicalsData = array_chunk($radicalsData, 12);
if(count($radicalsData) > 0){
    if(is_siteadmin()){
        $ajaxUrl = $CFG->wwwroot.'/mod/book/ajax.php';
        echo '<div class="align-right">';
        echo '<a href="javascript:void(0);" class="btn btn-success" id="updateradicals" data-page="'.$pageNum.'" data-ajaxurl="'.$ajaxUrl.'" role="button" style="margin-bottom:5px;">Update</a>';
        echo '</div>';
        echo '<div class="clear"></div>';
    }
    echo '<table class="table table-bordered table-striped">'; 
    echo '<thead class="thead-light">';
        echo '<tr>';
            echo '<th>Simplified</th>';
            echo '<th>Meaning</th>';
            echo '<th>Pinyin</th>';
            echo '<th>Examples</th>';
            echo '<th>Colloquial name</th>';
            echo '<th>Character count</th>';
        echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    foreach($radicalsData as $key => $radicals){
        echo '<tr>';
            echo '<td>'.$radicals[1]->content.'</td>';
            echo '<td>'.$radicals[4]->content.'</td>';
            echo '<td>'.$radicals[5]->content.'</td>';
            echo '<td>'.$radicals[8]->content.'</td>';
            echo '<td>'.$radicals[10]->content.'</td>';
            echo '<td><span id="'.$radicals[11]->recordid.'">'.$radicals[11]->content.'</span></td>';
        echo '</tr>';
    }
    echo '<tr>';
    echo '<td><a href="'.$previous.'" class="btn btn-link">Previous</a></td>';
    echo '<td colspan="4"></td>';
    echo '<td><a href="'.$next.'" class="btn btn-link">Next</a></td>';
    echo '</tr>';
    echo '</tbody>';
    echo '</table>';
}
 