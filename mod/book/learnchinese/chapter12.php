<?php
defined('MOODLE_INTERNAL') || die;
global $DB;
//$addnew = new moodle_url('/mod/data/edit.php', ['d' => 13]);
//$sesskey = sesskey();
if(!empty($_POST) && $_POST['updateClass'] == 'Update'){
    $sql = "SELECT c.recordid, (SELECT REPLACE(c1.content, ' ', '') FROM {data_content} as c1 WHERE c1.fieldid = 99 AND c1.recordid = c.recordid) as keyword FROM {data_content} as c WHERE c.recordid IN (SELECT recordid FROM {data_content} WHERE fieldid = 100)";
    
    $getGrammer = $DB->get_records_sql($sql);
    unset($_POST);
}else{
    $sql = "SELECT c.recordid, (SELECT REPLACE(c1.content, ' ', '') FROM {data_content} as c1 WHERE c1.fieldid = 99 AND c1.recordid = c.recordid) as keyword FROM {data_content} as c WHERE c.recordid IN (SELECT recordid FROM {data_content} WHERE fieldid = 100 AND content = '')";
    $getGrammer = $DB->get_records_sql($sql);
}

foreach($getGrammer as $recordid => &$record){
    $record->keyword = preg_replace("/[a-zA-Z0-9]+/", '',$record->keyword);
    $record->keyword = str_replace( array( '[', ']', '(', ')', '.'), '', $record->keyword); 
    $record->keyword = str_replace( array( '+'), ',', $record->keyword); 
}
foreach ($getGrammer as $recordid => $record){
    $keywordArr = explode(',', $record->keyword);
    $keywordArr = array_filter($keywordArr);
    $class = '';
    foreach($keywordArr as $character){
        $classSql = "SELECT c.content as classVal FROM {data_content} as c WHERE c.fieldid = 91 AND c.recordid IN (SELECT recordid FROM {data_content} WHERE fieldid = 84 AND content LIKE '{$character}' GROUP BY content)";

        try {
            $classValue = $DB->get_record_sql($classSql);
            if(isset($classValue)){
                $class .= $classValue->classval.',';
            }
        }catch(Exception $e) {
            echo 'Message: ' .$e->getMessage();
        }
        
    }
    $class = explode(',', $class);
    $class = array_filter(array_unique($class));
    $class = implode(',', $class);
    if($class!=""){
        // update grammer class
        $sql = "UPDATE `{data_content}` SET `content` = '$class' WHERE `fieldid` = 100 AND `recordid` = '$recordid'";
        $updateClass = $DB->execute($sql);
    }
}
$sql = "SELECT id, recordid, content FROM {data_content} WHERE fieldid IN (99,100) ORDER BY recordid";
$grammerList = $DB->get_records_sql($sql);
$grammerChunk = array_chunk($grammerList, 2);
?>
<form name="updateClassFrm" method="post" action="">
    <input type="submit" class="btn btn-primary" style="float: right;" name="updateClass" value="Update" />
</form>
<table id="keywordTable" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Keyword</th>
            <th>Class</th>
        </tr>
    </thead>
    <tbody>
    <?php
    foreach($grammerChunk as $key => $record){
        if($record[1]->content!="" && $record[0]->content!=""){
            echo '<tr>';
                echo '<td>'.$record[0]->content.'</td>';
                echo '<td>'.$record[1]->content.'</td>';
            echo '</tr>';
        }
    }
    ?>
    </tbody>
</table>
