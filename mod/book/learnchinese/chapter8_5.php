<?php
defined('MOODLE_INTERNAL') || die;
global $DB;
// get sentence from sentence table
$sql = "SELECT id, content FROM {data_content} WHERE fieldid = 106 and content != ''";
$sentenceList = $DB->get_records_sql($sql);
$gidStr = '';
foreach($sentenceList as $key => $sentence){
    $gidStr .= $sentence->content.',';
}
$gidStr = trim($gidStr,',');
$gidArr = explode(',', $gidStr);
$gidArr = array_unique($gidArr);
//GID and Keyword of the grammar record without any sentence matched
$sql = "SELECT c.recordid, c.content AS gid, (SELECT c1.content FROM {data_content} AS c1 WHERE c1.fieldid = 99 AND c1.recordid = c.recordid) AS keyword, (SELECT c2.content FROM {data_content} AS c2 WHERE c2.fieldid = 95 AND c2.recordid = c.recordid) AS format FROM {data_content} AS c WHERE c.fieldid = 92";
$grammerList = $DB->get_records_sql($sql);
//$grammerList = $DB->get_records_sql($sql,array($gidStr));
?>
<table id="datatable" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>GID</th><th>Format</th><th>Keyword</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($grammerList as $key => $grammer){
        if(!in_array($grammer->gid, $gidArr)){    
        ?>
        <tr>
            <td><?php echo $grammer->gid;?></td>
            <td><?php echo $grammer->format;?></td>
            <td><?php echo $grammer->keyword;?></td>
        </tr>
        <?php }
        }?>
    </tbody>
</table>