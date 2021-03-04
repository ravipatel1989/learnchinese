<?php
defined('MOODLE_INTERNAL') || die;
global $DB, $PAGE, $SESSION, $CFG;
$addnew = new moodle_url('/mod/data/edit.php', ['d' => 9]);
$sesskey = sesskey();

$sql = "SELECT recordid, content FROM {data_content} WHERE fieldid = 67 ORDER BY recordid ASC";
$sentenceData = $DB->get_records_sql($sql);
$countcontent = array();
foreach($sentenceData as $key => $sentences){
    $sentenceData[$key]->contentnew = preg_replace("/[^\x{4e00}-\x{9fa5}]+/u", '', $sentenceData[$key]->content);
}

$sentenceData = json_decode(json_encode($sentenceData), true);

if(isset($_POST['action']) && $_POST['action']=='removeduplicate'){
    $recordids = $_POST['recordids'];
    $recordids = implode(',', $recordids);
    $sql = "DELETE FROM `{data_content}` WHERE `recordid` IN ($recordids)";
    $delete = $DB->execute($sql);
    $sql = "DELETE FROM `{data_records}` WHERE `id` IN ($recordids)";
    $delete = $DB->execute($sql);
}
if(isset($_POST['action']) && $_POST['action']=="deletebulk"){
    $sentenceArr = $_POST['sentenceArr'];
    foreach($sentenceArr as $sentence){
        $sql = "SELECT content FROM {data_content} WHERE fieldid = 67 AND content = (SELECT c1.content FROM {data_content} AS c1 WHERE fieldid = 67 AND recordid = $sentence)";
        $sentenceval = $DB->get_record_sql($sql);
        $recordids = array();
        $sentencenopunc = preg_replace("/[^\x{4e00}-\x{9fa5}]+/u", '', $sentenceval->content);
        
        foreach($sentenceData as $record){
            if($record['contentnew'] == $sentencenopunc){
                $recordids[] = $record['recordid'];
            }
        }
        unset($recordids[0]);
        $recordIds = implode(',', $recordids);
        $sql = "DELETE FROM `{data_content}` WHERE `recordid` IN ($recordIds)";
        $delete = $DB->execute($sql);
        $sql = "DELETE FROM `{data_records}` WHERE `id` IN ($recordIds)";
        $delete = $DB->execute($sql);
    }
}
if(isset($_GET['action']) && $_GET['action']=="deletesingle"){
    
    $recordid = $_GET['recordid'];
    $sql = "SELECT content FROM {data_content} WHERE fieldid = 67 AND content = (SELECT c1.content FROM {data_content} AS c1 WHERE fieldid = 67 AND recordid = $recordid)";
    $sentence = $DB->get_record_sql($sql);
    $recordids = array();
    $sentencenopunc = preg_replace("/[^\x{4e00}-\x{9fa5}]+/u", '', $sentence->content);
    foreach($sentenceData as $sentence){
        if($sentence['contentnew'] == $sentencenopunc){
            $recordids[] = $sentence['recordid'];
        }
    }
        
    $deleteduplicates = new moodle_url('/mod/book/view.php', ['id' => 15, 'chapterid' => 77, 'recordid' => $recordid]);
    ?>
        <form action="<?php echo $deleteduplicates; ?>" method="post">
        <input type="hidden" name="action" value="removeduplicate" />
        <input type="submit" name="submit" class="btn btn-primary" value="Delete" />
        <a href="<?php echo $addnew; ?>" target="_blank" class="btn btn-primary">Add New</a>
        <br><br>
        <table id="datatable" class="table table-bordered table-striped">    
        <thead>
            <tr><th><input type="checkbox" name="allcheckboxes" id="allcheckboxes" class="allcheckboxes" checked="checked" /></th><th>Character</th><th>HSK</th><th>PinYin</th><th>Meaning</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php 
            foreach($recordids as $recordid){ 
                $sql = "SELECT id, recordid, content FROM {data_content} WHERE recordid = $recordid ORDER BY fieldid";
                $sentence = $DB->get_records_sql($sql);
                $sentence = array_values($sentence);
                $editrecord = new moodle_url('/mod/data/edit.php', ['d' => 9, 'rid' => $sentence[0]->recordid, 'sesskey'=>$sesskey]);
            ?>
            <tr>
                <td><input type="checkbox" name="recordids[]" class="sentencechk" checked="checked" value="<?php echo $sentence[0]->recordid; ?>" /></td>
                <td><?php echo $sentence[0]->content; ?></td>
                <td><?php echo $sentence[1]->content; ?></td>
                <td><?php echo $sentence[2]->content; ?></td>
                <td><?php echo $sentence[3]->content; ?></td>
                <td><a href="<?php echo $editrecord; ?>" target="_blank" class="btn btn-primary">Edit</a></td>
            </tr>
            <?php } ?>
        </tbody>
        </table>
        </form>
    <?php
}else{
 

    $duplicateContent = array_count_values(array_column($sentenceData, "contentnew"));

    foreach (array_keys($duplicateContent, 1) as $key) {
        unset($duplicateContent[$key]);
    }

    $totalrecord = count($duplicateContent);
    
    echo '<h6>There are '.$totalrecord.' duplicate records</h6>';
    $deletebulk = new moodle_url('/mod/book/view.php', ['id' => 15, 'chapterid' => 77, 'action'=>'deletebulk']);
    ?>
    <form action="<?php echo $deletebulk; ?>" method="post">
    <input type="hidden" name="action" value="deletebulk" />
    <input type="submit" name="submit" class="btn btn-primary" value="Bulk Delete" />
    <a href="<?php echo $addnew; ?>" target="_blank" class="btn btn-primary">Add New</a>
    <br><br>
    <table id="datatable" class="table table-bordered table-striped">    
    <thead>
        <tr><th><input type="checkbox" name="allcheckboxes" id="allcheckboxes" class="allcheckboxes" checked="checked" /></th><th>Sentence</th><th>Total Records</th><th>Delete Record</th></tr>
    </thead>
    <tbody>
        <?php 
        foreach($sentenceData as $key => $sentence){
            if (array_key_exists($sentence['contentnew'],$duplicateContent)){
            $deletesingle = new moodle_url('/mod/book/view.php', ['id' => 15, 'chapterid' => 77, 'action'=>'deletesingle', 'recordid'=>$key]);
            $editrecord = new moodle_url('/mod/data/edit.php', ['d' => 9, 'rid' => $key, 'sesskey'=>$sesskey]);
            ?>
        <tr>
            <td><input type="checkbox" name="sentenceArr[]" id="sentence_<?php echo $key;?>" class="sentencechk" checked="checked" value="<?php echo $key;?>" /></td>
            <td><?php echo $sentence['content']; ?></td>
            <td><?php echo $duplicateContent[$sentence['contentnew']]; ?></td>
            <td><a href="<?php echo $editrecord; ?>" target="_blank" class="btn btn-primary">Edit</a>&nbsp;<a href="<?php echo $deletesingle; ?>" class="btn btn-primary">Delete</a></td>
        </tr>
        <?php 
            unset($duplicateContent[$sentence['contentnew']]);
            } 
            } ?>
    </tbody>
    </table>
    </form>
    
    <?php
}    
?>
<script>
    jQuery(document).ready(function($){
        $('.allcheckboxes').change(function(){ //".checkbox" change 
            if($('.allcheckboxes:checked').length == $('.checkbox').length){
                   $('.sentencechk').prop('checked',false);
            }else{
                   $('.sentencechk').prop('checked',true);
            }
        });
    });
</script>