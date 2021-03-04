<?php
defined('MOODLE_INTERNAL') || die;
global $DB;

if(!empty($_POST) && $_POST['updateClass'] == 'Update'){
    $sql = "SELECT c.recordid, c.content as class, (SELECT c1.content FROM {data_content} as c1 WHERE c1.fieldid = 84 AND c1.recordid = c.recordid) as keyword, (SELECT c2.content FROM {data_content} as c2 WHERE c2.fieldid = 90 AND c2.recordid = c.recordid) as hsk FROM {data_content} as c WHERE fieldid = 91";
    $getKeywords = $DB->get_records_sql($sql);
    unset($_POST);

}else{
    $sql = "SELECT c.recordid, c.content as class, (SELECT c1.content FROM {data_content} as c1 WHERE c1.fieldid = 84 AND c1.recordid = c.recordid) as keyword, (SELECT c2.content FROM {data_content} as c2 WHERE c2.fieldid = 90 AND c2.recordid = c.recordid) as hsk FROM {data_content} as c WHERE fieldid = 91 AND content = ''";
    $getKeywords = $DB->get_records_sql($sql);
}
foreach($getKeywords as $recordid => $character){
    
    $sql = "SELECT c.recordid, (SELECT c1.content FROM {data_content} as c1 WHERE c1.fieldid = 74 AND c1.recordid = c.recordid) as class FROM {data_content} as c WHERE c.fieldid = 47 AND c.content LIKE '{$character->keyword}'";
    $getClass = $DB->get_records_sql($sql);
    if(!empty($getClass)){
        $result = '';
        $class = array();
        foreach($getClass as $value){
            $class[] = $value->class;
        }
        $class = array_unique($class);
        $result = implode(',', $class);
        if(empty($class)){
            $result = '***';
        }
        if($character->hsk != '9'){
            $hsksql = "UPDATE `{data_content}` SET `content` = '$result' WHERE `fieldid` = 91 AND `recordid` = '$recordid'";
            $updateClass = $DB->execute($hsksql);
        }
    }
}

    $sql = "SELECT c.* FROM {data_content} as c RIGHT JOIN {data_records} as r ON c.recordid = r.id WHERE r.dataid = 13 ORDER BY c.recordid ASC";
    $keywordRecords = $DB->get_records_sql($sql);
    $keywordRecords = array_chunk($keywordRecords, 8);
?>
<form name="updateClassFrm" method="post" action="">
    <input type="submit" class="btn btn-primary" style="float: right;" name="updateClass" value="Update" />
</form>
<table id="keywordTable" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Keyword</th>
            <th>Pinyin</th>
            <th>Meaning</th>
            <th>CH-HSK</th>
            <th>HSK</th>
            <th>Class</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($keywordRecords as $key => $record){ ?>
        <tr>
            <td><?php echo $record[0]->content;?></td>
            <td><?php echo $record[1]->content;?></td>
            <td><?php echo $record[4]->content;?></td>
            <td><?php echo $record[5]->content;?></td>
            <td><?php echo $record[6]->content;?></td>
            <td><?php echo $record[7]->content;?></td>
        </tr>
        <?php }?>
    </tbody>
</table>
