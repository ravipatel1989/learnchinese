<?php
defined('MOODLE_INTERNAL') || die;
global $DB;

$sql = $DB->get_records_sql("SELECT c.content FROM {data_content} AS c LEFT JOIN {data_records} AS r ON c.recordid = r.id WHERE c.fieldid = '14' AND r.dataid = 3");
// All chinese charcters list
$chineseCharArr = array_keys($sql);
$dir    = $CFG->characterdata;
$filesList = scandir($dir, 1);
$filesListArr = array_map(function($a){
    if(strpos($a, '.mp4') === false){
        return '';
    }else{
        $a = str_replace('-多音字', '', $a);
        return str_replace('.mp4', '', $a);
    }
},$filesList);
// All mp4 files list in directory
$filesListArr = array_filter($filesListArr);

// Remove array elements from mp4 file list which have chinese charcters
$result = array_diff($filesListArr,$chineseCharArr);
$nocharFiles = array_intersect_key($filesList,$result);

//list to list character that does not have MP4 files
$nomp4files = array_diff($chineseCharArr, $filesListArr);

// ************************** code for delete files ****************************
// 
//$nomp4filesChunks = array_chunk($nomp4files, 50);
//foreach ($nomp4filesChunks as $nomp4Arr){
//    $implodeArr = implode("','",$nomp4Arr);
//    $sql = "SELECT c.recordid FROM {data_content} AS c LEFT JOIN {data_records} AS r ON c.recordid = r.id WHERE r.dataid = 3 AND ((c.fieldid = 14 AND c.content IN('$implodeArr') )) AND c.recordid IN (SELECT c1.recordid FROM {data_content} AS c1 WHERE c1.fieldid = 25 AND c1.content = 9)";
//    $contentRecords = $DB->get_records_sql($sql);
//    $dataRecords = implode("','",array_keys($contentRecords));
//    $deleteSql = $DB->execute("DELETE FROM {data_content} WHERE recordid IN ('$dataRecords')");
//}

?>
<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <table id="matchfiles1" class="table table-bordered table-striped">
                <thead>
                    <tr class="center"><th>Files with no character in chinese character table</th></tr>
                </thead>
                <tbody>
                    <?php foreach($nocharFiles as $file){ 
                        $mp4file = $CFG->wwwroot.'/characterdata/'.$file;
                    ?>
                    <tr class="center"><td><a href="<?php echo $mp4file; ?>" target="_blank"><?php echo $file; ?></a></td></tr>
                    <?php } ?>
                </tbody>
            </table>
            <hr>
        </div>
        <div class="col-sm-12">
            <table id="matchfiles2" class="table table-bordered table-striped">
                <thead>
                    <tr class="center"><th>List of character which has no mp4 files</th></tr>
                </thead>
                <tbody>
                    <?php foreach($nomp4files as $file){ ?>
                    <tr class="center"><td><?php echo $file; ?></td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>