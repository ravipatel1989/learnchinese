<?php
defined('MOODLE_INTERNAL') || die;
global $DB, $PAGE, $SESSION, $CFG;
$addnew = new moodle_url('/mod/data/edit.php', ['d' => 5]);
$goback = new moodle_url('/mod/book/view.php', ['id'=>15,'chapterid'=>71]);
$emptyclass = new moodle_url('/mod/book/view.php', ['id'=>15,'chapterid'=>71,'action' => 'undefinedclass']);
$sesskey = sesskey();
$ajaxUrl = $CFG->wwwroot.'/mod/book/ajax.php';
if(isset($_GET['action']) && $_GET['action']=='removeduplicate'){
    if(isset($_GET['sesskey']) && $_GET['sesskey'] == $sesskey){
        $recordid = $_GET['recordid'];
        $sql = "DELETE FROM `{data_content}` WHERE `recordid` = '$recordid'";
        $delete = $DB->execute($sql);
        $sql = "DELETE FROM `{data_records}` WHERE `id` = '$recordid'";
        $delete = $DB->execute($sql);
        $_GET['action'] = 'deletesingle';
        $_GET['recordid'] = $recordid;
    }
}
if(isset($_GET['action']) && $_GET['action']=='deleteemptyclass'){
    if(isset($_GET['sesskey']) && $_GET['sesskey'] == $sesskey){
        $recordid = $_GET['recordid'];
        $sql = "DELETE FROM `{data_content}` WHERE `recordid` = '$recordid'";
        $delete = $DB->execute($sql);
        
        $sql = "DELETE FROM `{data_records}` WHERE `id` = '$recordid'";
        $delete = $DB->execute($sql);
    }
}
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
        $sql = "SELECT dc.recordid FROM {data_content} as dc LEFT JOIN {data_content} as dc1 on dc.recordid = dc1.recordid WHERE (dc.fieldid = 47 AND dc.content = (SELECT c1.content FROM {data_content} AS c1 WHERE fieldid = 47 AND recordid = $sentence)) AND (dc.fieldid = 74 AND dc.content != '') AND (dc1.fieldid = 48 AND dc1.content != '9')";
        
        $recordids = $DB->get_records_sql($sql);
        $recordids = array_keys($recordids);
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
    $sql = "SELECT recordid FROM {data_content} WHERE fieldid = 47 AND content = (SELECT c1.content FROM {data_content} AS c1 WHERE fieldid = 47 AND recordid = $recordid) order by recordid DESC";
    $recordids = $DB->get_records_sql($sql);
    $recordids = array_keys($recordids);
    $deleteduplicates = new moodle_url('/mod/book/view.php', ['id' => 15, 'chapterid' => 71, 'recordid' => $recordid]);
    ?>
        <form action="<?php echo $deleteduplicates; ?>" method="post">
        <input type="hidden" name="action" value="removeduplicate" />
        <input type="submit" name="submit" class="btn btn-primary" value="Delete" />
        <a href="<?php echo $goback; ?>" class="btn btn-primary">Go back</a>
        <a href="<?php echo $addnew; ?>" target="_blank" class="btn btn-primary">Add New</a>
        <br><br>
        <table id="datatable" class="table table-bordered table-striped">    
        <thead>
            <tr><th><input type="checkbox" name="allcheckboxes" id="allcheckboxes" class="allcheckboxes" checked="checked" /></th><th>Character</th><th>HSK</th><th>PinYin</th><th>Meaning</th><th>Class</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php 
            foreach($recordids as $recordid){ 
                $sql = "SELECT id, recordid, content FROM {data_content} WHERE recordid = $recordid ORDER BY fieldid";
                $hskrecord = $DB->get_records_sql($sql);
                $hskrecord = array_values($hskrecord);
                $editrecord = new moodle_url('/mod/data/edit.php', ['d' => 5, 'rid' => $hskrecord[0]->recordid, 'sesskey'=>$sesskey]);
                $deleterecord = new moodle_url('/mod/book/view.php', ['id' => 15, 'chapterid' => 71, 'action'=>'removeduplicate','recordid'=>$hskrecord[0]->recordid, 'sesskey'=>$sesskey]);
            ?>
            <tr>
                <td><input type="checkbox" name="recordids[]" class="sentencechk" checked="checked" value="<?php echo $hskrecord[0]->recordid; ?>" /></td>
                <td><?php echo $hskrecord[0]->content; ?></td>
                <td><?php echo $hskrecord[1]->content; ?></td>
                <td><?php echo $hskrecord[2]->content; ?></td>
                <td><?php echo $hskrecord[3]->content; ?></td>
                <td><?php echo $hskrecord[6]->content; ?></td>
                <td><a href="<?php echo $editrecord; ?>" target="_blank" class="btn btn-primary">Edit</a>&nbsp;<a href="<?php echo $deleterecord; ?>" class="btn btn-primary">Delete</a></td>
            </tr>
            <?php } ?>
        </tbody>
        </table>
        </form>
    <?php
}else if(isset($_GET['action']) && $_GET['action']=="undefinedclass"){
    $sql = "SELECT dc.recordid,dc.content,(SELECT dc1.content from `{data_content}` as dc1 where dc.recordid = dc1.recordid and dc1.fieldid = 48) AS HSK, (SELECT dc2.content from `{data_content}` as dc2 where dc.recordid = dc2.recordid and dc2.fieldid = 49) AS Pinyin, (SELECT dc3.content from `{data_content}` as dc3 where dc.recordid = dc3.recordid and dc3.fieldid = 50) AS meaning, (SELECT dc4.content from `{data_content}` as dc4 where dc.recordid = dc4.recordid and dc4.fieldid = 47) AS charater FROM `{data_fields}` as `df` left join `{data_content}` as `dc` on df.id = dc.fieldid where df.dataid = 5 AND dc.fieldid = 74 AND dc.content = 'undefined' ORDER BY `dc`.`recordid` ASC";
    
    $records = $DB->get_records_sql($sql);
    ?>
    <a href="<?php echo $goback; ?>" class="btn btn-primary">Go back</a>
    <br><br>
    <table id="undefinedword" class="table table-bordered table-striped">    
    <thead>
        <tr>
            <th><input type="checkbox" name="allcheckboxes" id="allcheckboxes" class="allcheckboxes" checked="checked" /></th>
            <th>Character</th>
            <th>HSK</th>
            <th>Pinyin</th>
            <th>Meaning</th>
            <th>Class</th>
            <th>Action</th>
        </tr>
    </thead>
    </table>    
    <?php
}else{
//    $sql = "SELECT dc.recordid, dc.fieldid, dc.content, COUNT(dc.content) AS countnum FROM {data_content} as dc LEFT JOIN {data_content} AS dc1 on dc.recordid = dc1.recordid WHERE (dc1.fieldid = 74 AND dc1.content = '') AND dc.fieldid = 47 GROUP BY dc.content HAVING COUNT(dc.content) > 1 ORDER BY CHAR_LENGTH(dc.content) ASC";
//    $sql = "SELECT dc.recordid, dc.fieldid, dc.content,(SELECT COUNT(dc2.fieldid) FROM {data_content} as dc2 where dc2.fieldid = 47 AND dc2.content = dc.content) AS countnum FROM {data_content} as dc LEFT JOIN {data_content} AS dc1 on dc.recordid = dc1.recordid WHERE (dc1.fieldid = 74 AND dc1.content = '') AND dc.fieldid = 47 ORDER BY CHAR_LENGTH(dc.content) ASC";
//    $emptyclassData = $DB->get_records_sql($sql);
//    $emptyclasstotalrecord = count($emptyclassData);
    $sql = "SELECT dc.recordid, dc.fieldid, dc.content FROM mdl_data_content as dc LEFT JOIN mdl_data_content AS dc1 on dc.recordid = dc1.recordid WHERE dc1.fieldid = 74 AND dc.fieldid = 47 GROUP BY dc.content, dc1.content HAVING count(dc.content) > 1";
    $sentenceData = $DB->get_records_sql($sql);
    $totalrecord = count($sentenceData);
//    $totalrecords = $emptyclasstotalrecord + $totalrecord;
    echo '<h6>There are '.$totalrecord.' duplicate records</h6>';
    $deletebulk = new moodle_url('/mod/book/view.php', ['id' => 15, 'chapterid' => 71, 'action'=>'deletebulk']);
    ?>
    <form action="<?php echo $deletebulk; ?>" method="post">
    <input type="hidden" name="action" value="deletebulk" />
    <input type="submit" name="submit" class="btn btn-primary" value="Bulk Delete" />
    <a href="<?php echo $addnew; ?>" target="_blank" class="btn btn-primary">Add New</a>
    <a href="<?php echo $emptyclass; ?>" class="btn btn-primary">Undefined Class</a>
    <br><br>
    <table id="datatable" class="table table-bordered table-striped">    
    <thead>
        <tr><th><input type="checkbox" name="allcheckboxes" id="allcheckboxes" class="allcheckboxes" checked="checked" /></th><th>Character</th><th>Total Records</th><th>Action</th></tr>
    </thead>
    <tbody>
        <?php foreach($sentenceData as $key => $sentence){ 
            $totalchar = $DB->get_record_sql("SELECT count(recordid) as totalchar FROM {data_content} WHERE fieldid = 47 AND content = '{$sentence->content}'");
            $deletesingle = new moodle_url('/mod/book/view.php', ['id' => 15, 'chapterid' => 71, 'action'=>'deletesingle', 'recordid'=>$key]);
            $editrecord = new moodle_url('/mod/book/view.php', ['id' => 15, 'chapterid' => 71, 'action'=>'edit', 'content'=>$sentence->content]);
            ?>
        <tr>
            <td><input type="checkbox" name="sentenceArr[]" id="sentence_<?php echo $key;?>" class="sentencechk" checked="checked" value="<?php echo $key;?>" /></td>
            <td><?php echo $sentence->content; ?></td>
            <td><?php echo $totalchar->totalchar; ?></td>
            <td><a href="<?php echo $editrecord; ?>" class="btn btn-primary">Edit</a>&nbsp;<a href="<?php echo $deletesingle; ?>" class="btn btn-primary">Delete</a></td>
        </tr>
        <?php } ?>
    </tbody>
    </table>
    </form>
    
    <?php
}    
?>
