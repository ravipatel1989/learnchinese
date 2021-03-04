<?php
defined('MOODLE_INTERNAL') || die;
global $DB, $PAGE, $SESSION, $CFG;
$sesskey = sesskey();
$ajaxUrl = $CFG->wwwroot.'/mod/book/ajax.php';
$pinyinData = $DB->get_records_sql("SELECT c.recordid, c.content as phrase_count, (SELECT REPLACE(c1.content, '。', '.') FROM {data_content} as c1 WHERE c1.fieldid = 67 AND c1.recordid = c.recordid) as sentence FROM {data_content} as c WHERE c.fieldid = 105 order by c.recordid");

?>
<form action="" method="post">
    <div class="align-right" style="display:block">
        <input type="submit" name="update" value="Update" class="btn btn-primary" onclick="return confirm('Are you sure to update? It will take some time.')" />
    </div>
</form>
<table id="phraseCntTable" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Sentence</th>
            <th>Phrase count</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>    
<?php
foreach($pinyinData as $key => $value){
        
        if($value->phrase_count == '' || (isset($_POST) && $_POST['update'] == "Update")){
            $regex = "/[.，*？!！＇’\"@#$&-_ ]+$/";
            $value->sentence = preg_replace($regex, "", $value->sentence);
            $cnt = 1;
            $cnt += substr_count($value->sentence,".");
            $cnt += substr_count($value->sentence,",");
            $cnt += substr_count($value->sentence,"，");
            $cnt += substr_count($value->sentence,"!");
            $cnt += substr_count($value->sentence,"！");
            $cnt += substr_count($value->sentence,"?");
            $cnt += substr_count($value->sentence,"？");
            $selectQry = "SELECT count(id) AS totalid FROM {data_content} WHERE fieldid = 105 AND recordid = '$value->recordid'";
            $recordExists = $DB->get_record_sql($selectQry);
//            if($recordExists->totalid == 0){
//                $insertPhraseSql = "INSERT INTO `{data_content}`(`fieldid`, `recordid`, `content`) VALUES ('105','$value->recordid',$cnt)";
//                $DB->execute($insertPhraseSql);
//            }else{
                $updatePhraseSql = "UPDATE `{data_content}` SET `content`='$cnt' WHERE fieldid = 105 AND recordid = '$key'";
                $DB->execute($updatePhraseSql);
                $value->phrase_count = $cnt;
//            }
        }
        echo '<tr>';
        echo '<td>'.str_replace('.', '°', $value->sentence).'</td>';
        echo '<td id="phrase_'.$value->recordid.'">'.$value->phrase_count.'</td>';
        echo '<td><a href="javascript:void(0);" id="editphrasecount_'.$value->recordid.'" data-recordid="'.$value->recordid.'"  data-ajaxurl="'.$ajaxUrl.'" class="btn btn-primary editphrasecount">Edit</a></td>';
        echo '</tr>';
}
?>
</tbody>
</table>
