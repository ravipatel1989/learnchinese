<?php
defined('MOODLE_INTERNAL') || die;
global $DB;
$update = filter_input(INPUT_GET, 'update');
if($update == 'hsk'){
    $start = filter_input(INPUT_GET, 'start');
    $end = filter_input(INPUT_GET, 'end');
    $whereAnd = "";
    if($start!="" && $end!=""){
        $whereAnd = " AND (c.id >= $start AND c.id <= $end)";
    }
//    $sql = "SELECT c.recordid, c.content,(SELECT c1.content FROM `{data_content}` AS c1 WHERE c1.fieldid = 56 AND c1.recordid = c.recordid) AS chineseword FROM `{data_records}` as r LEFT JOIN `{data_content}` as c ON r.id = c.recordid WHERE c.fieldid = 59 AND c.recordid IN (SELECT recordid FROM {data_content} WHERE fieldid = 59 AND content = '') $whereAnd";
    $sql = "SELECT c.recordid, c.content,(SELECT c1.content FROM `{data_content}` AS c1 WHERE c1.fieldid = 56 AND c1.recordid = c.recordid) AS chineseword FROM `{data_records}` as r LEFT JOIN `{data_content}` as c ON r.id = c.recordid WHERE c.fieldid = 59 $whereAnd";

    $records = $DB->get_records_sql($sql);
    if(count($records) > 0){
        try {
            $transaction = $DB->start_delegated_transaction();
            foreach($records as $record){
                preg_match_all('/./u', $record->chineseword, $characterArr);
                $hskVal = '';
                foreach($characterArr[0] as $chinesechar){
                    $sql = "SELECT content FROM {data_content} WHERE fieldid = 25 AND recordid = (SELECT recordid FROM {data_content} WHERE fieldid = 14 AND content LIKE '$chinesechar')";
                    $result = $DB->get_record_sql($sql);
                    if(!isset($result->content) || $result->content==""){
                        $result->content = 9;
                    }
                    $hskVal .= $result->content;
                }
                $sql = "UPDATE `{data_content}` SET `content` = $hskVal WHERE `fieldid` = 59 AND `recordid` = '$record->recordid'";
                $update = $DB->execute($sql);
            }  
            $transaction->allow_commit();
        } catch(\Exception $e) {
            $transaction->rollback($e);
        }
    }
}

?>
<br>
<?php if(is_siteadmin()){ ?>
<?php $updatehsk = new moodle_url('/mod/book/view.php', ['id' => 15, 'chapterid' => 9, 'update' => 'hsk']); ?>
<div class="align-right">
    <a href="javascript:void(0);" data-url="<?php echo $updatehsk;?>" id="updatehsk" class="btn btn-success" role="button" style="margin-bottom:5px;">Update HSK</a>
</div>
<?php } ?>
<div class="clear"></div>
<table id="hsklevelwords" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Word<br><hr>HSK</th><th>Sentence</th><th>English</th>
        </tr>
    </thead>
</table>
