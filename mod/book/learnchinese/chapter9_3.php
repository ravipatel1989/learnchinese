<?php
defined('MOODLE_INTERNAL') || die;
global $DB;
$update = filter_input(INPUT_GET, 'update');
if($update == 'chhskTohsk'){
    $sql = "SELECT recordid, content AS chhsk FROM {data_content} WHERE fieldid = 70";
    $records = $DB->get_records_sql($sql);
    if(count($records) > 0){
        try {
            $transaction = $DB->start_delegated_transaction();
            foreach($records as $record){
                $chArray = mb_str_split($record->chhsk);
                $maxnumber = max($chArray);
                $sql = "UPDATE `{data_content}` SET `content` = $maxnumber WHERE `fieldid` = 48 AND `recordid` = '$record->recordid' AND content != '9'";
                $update = $DB->execute($sql);
            }  
            $transaction->allow_commit();
        } catch(\Exception $e) {
            $transaction->rollback($e);
        }
    }
}
if($update == 'hsk'){
    $start = filter_input(INPUT_GET, 'start');
    $end = filter_input(INPUT_GET, 'end');
    $whereAnd = "";
    if($start!="" && $end!=""){
        $whereAnd = " AND (c.id >= $start AND c.id <= $end)";
    }

    $sql = "SELECT c.recordid, c.content FROM `{data_records}` as r LEFT JOIN `{data_content}` as c ON r.id = c.recordid WHERE c.fieldid = 47 AND c.fieldid != 70";

    $records = $DB->get_records_sql($sql);
    if(count($records) > 0){
        try {
            $transaction = $DB->start_delegated_transaction();
            foreach($records as $record){
                $hskVal = '';
                $chArray = mb_str_split($record->content);
                foreach($chArray as $chinesechar){
                    $sql = "SELECT c.content FROM {data_content} as c WHERE c.fieldid = 25 AND c.recordid = (SELECT c1.recordid FROM {data_content} as c1 WHERE c1.fieldid = 14 AND c1.content = '$chinesechar')";
                   
                    $result = $DB->get_record_sql($sql);
                    if(!isset($result->content) || $result->content==""){
                        $result->content = 9;
                    }
                    $hskVal .= $result->content;
                }
                $sql = "SELECT c.recordid FROM {data_content} AS c WHERE c.fieldid = 70 AND c.recordid = '$record->recordid'";
                $checkExist = $DB->get_record_sql($sql);
                if(isset($checkExist->recordid) && $checkExist->recordid > 0){
                    $sql = "UPDATE `{data_content}` SET `content` = $hskVal WHERE `fieldid` = 70 AND `recordid` = '$record->recordid'";
                    $update = $DB->execute($sql);
                }else{
                    $sql = "INSERT INTO `{data_content}` ( `fieldid`, `recordid`, `content`) VALUES ('70', '".$record->recordid."', '$hskVal')";
                    $insert = $DB->execute($sql);
                }
//                $insert = $DB->execute($sql);
            }  
            $transaction->allow_commit();
        } catch(\Exception $e) {
            $transaction->rollback($e);
        }
    }
}
function mb_str_split( $string ) {

    return preg_split('/(?<!^)(?!$)/u', $string );
}
?>
<br>
<?php if(is_siteadmin()){ 
$updatehsk = new moodle_url('/mod/book/view.php', ['id' => 15, 'chapterid' => 68, 'update' => 'hsk']);
$chhsktohsk = new moodle_url('/mod/book/view.php', ['id' => 15, 'chapterid' => 68, 'update' => 'chhskTohsk']);
?>
<div class="align-right">
    <a href="javascript:void(0);" data-url="<?php echo $updatehsk;?>" id="updatechhsk" class="btn btn-success" role="button" style="margin-bottom:5px;">Update HSK</a>
    <a href="<?php echo $chhsktohsk; ?>" class="btn btn-success" role="button" style="margin-bottom:5px;">CH-HSK to HSK</a>
</div>
<?php } ?>
<div class="clear"></div>
<table id="chhsklevelwords" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Word<br><hr>CH-HSK</th><th>Pinyin</th><th>English</th>
        </tr>
    </thead>
</table>
