<?php
defined('MOODLE_INTERNAL') || die;
global $DB, $PAGE, $SESSION, $CFG;
$ajaxUrl = $CFG->wwwroot.'/mod/book/ajax.php';
if(isset($_POST) && $_POST['GID']!=""){
    $gid = filter_input(INPUT_POST, 'GID');
    $sql = "SELECT fieldid, content FROM {data_content} WHERE recordid = (SELECT recordid FROM {data_content} WHERE fieldid = ? AND content = ?) AND (fieldid = ? OR fieldid = ? OR fieldid = ?)";
    $gidRecords = $DB->get_records_sql($sql, array(92,$gid,92,95,96));
    echo '<a href="javascript:window.history.back();" class="align-right btn btn-primary">Go back</a><br><br>';
    echo '<h5>GID: '.$gidRecords[92]->content.'</h5>';
    echo '<h5>Format: '.$gidRecords[95]->content.'</h5>';
    echo '<h5>Example: '.$gidRecords[96]->content.'</h5>';
    
    $sql = "SELECT c.recordid, c.content AS gid, (SELECT c1.content FROM {data_content} AS c1 WHERE c1.fieldid = ? AND c1.recordid = c.recordid) AS sentence FROM {data_content} AS c WHERE c.recordid IN (SELECT recordid FROM {data_content} WHERE fieldid = ? AND CONCAT(',', content, ',') like '%,{$gidRecords[92]->content},%')";
    
    $sentenceList = $DB->get_records_sql($sql, array(67,106));
    echo '<table id="datatable" class="table table-bordered table-striped">';
        echo '<thead>';
            echo '<tr>';
            echo '<th>Sentence</th>';
            echo '<th>GID</th>';
            echo '<th>Action</th>';
            echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
    foreach($sentenceList as $record){
        echo '<tr>';
        echo '<td>'.$record->sentence.'</td>';
        echo '<td id="gid_'.$record->recordid.'">'.$record->gid.'</td>';
        echo '<td><a href="javascript:void(0);" id="deletegid_'.$record->recordid.'" data-gid="'.$gidRecords[92]->content.'"  data-recordid="'.$record->recordid.'" data-ajaxurl="'.$ajaxUrl.'" class="btn btn-primary deletegid">Remove</a></td>';
        echo '</tr>';
    }
        echo '</tbody>';
    echo '</table>';
}else{
?>
<form method="POST" action="">
    <div class="align-center">
        <input type="text" name="GID" placeholder="Enter the GID" />&nbsp;
        <input type="submit" value="Submit" class="btn btn-primary" />
    </div>
</form>
<?php
}
?>


