<?php
defined('MOODLE_INTERNAL') || die;
global $DB;
$addnew = new moodle_url('/mod/data/edit.php', ['d' => 13]);
$sesskey = sesskey();
$ajaxUrl = $CFG->wwwroot.'/mod/book/ajax.php';
$sql = "SELECT recordid, content as keyword, (SELECT c1.content FROM {data_content} as c1 WHERE c1.fieldid = 91 AND c1.recordid = c.recordid) as class, (SELECT c1.content FROM {data_content} as c1 WHERE c1.fieldid = 86 AND c1.recordid = c.recordid) as pinyin, (SELECT c2.content FROM {data_content} as c2 WHERE c2.fieldid = 88 AND c2.recordid = c.recordid) as meaning, (SELECT c3.content FROM {data_content} as c3 WHERE c3.fieldid = 90 AND c3.recordid = c.recordid) as hsk FROM {data_content} as c WHERE c.fieldid = 84 AND c.content IN (SELECT content as keyword FROM {data_content} WHERE fieldid = 84 GROUP BY content HAVING COUNT(content) > 1) ORDER BY content";

$getKeywords = $DB->get_records_sql($sql);
// check if all hsk is 9 for duplicate keyword then remove all from list
$unsetkeywords = array();
foreach($getKeywords as $key => $record){
    $unsetkeywords[$record->keyword][$record->recordid] = $record->hsk;
}
foreach($unsetkeywords as $key => $value){
    $checkAllhsk = array_unique($value);
    $hskvals = array_count_values($checkAllhsk);
    $countvals = count($hskvals);
    if($hskvals[9]==1 && $countvals==1){
        foreach($value as $recordid=>$hsk){
            unset($getKeywords[$recordid]);
        }
    }
}
?>
<table id="keywordTable" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Keyword</th>
            <th>Pinyin</th>
            <th>Meaning</th>
            <th>HSK</th>
            <th>Class</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <div class="align-right" style="display:block">
        <a href="<?php echo $addnew; ?>" class="btn btn-primary" target="_blank">Add new</a>
    </div>
<?php
foreach($getKeywords as $key => $record){
    echo '<tr id="delete_'.$record->recordid.'">';
        echo '<td>'.$record->keyword.'</td>';
        echo '<td>'.$record->pinyin.'</td>';
        echo '<td id="meaning_'.$record->recordid.'">'.$record->meaning.'</td>';
        echo '<td id="hsk_'.$record->recordid.'">'.$record->hsk.'</td>';
        echo '<td id="class_'.$record->recordid.'">'.$record->class.'</td>';
        echo '<td><a href="javascript:void(0);" id="editkeyword_'.$record->recordid.'" data-recordid="'.$record->recordid.'"  data-ajaxurl="'.$ajaxUrl.'" class="btn btn-primary editkeyword">Edit</a>&nbsp;<a href="javascript:void(0);" id="deletekeyword_'.$record->recordid.'" data-recordid="'.$record->recordid.'"  data-ajaxurl="'.$ajaxUrl.'" class="btn btn-danger deletekeyword">Delete</a></td>';
    echo '</tr>';
}
?>
    </tbody>
</table>
