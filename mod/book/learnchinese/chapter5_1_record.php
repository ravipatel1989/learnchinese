<?php
defined('MOODLE_INTERNAL') || die;
global $DB, $PAGE;
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/mod/book/js/custom.js'));
$chinesechar = filter_input(INPUT_GET, 'chinesechar');
$pinyin = filter_input(INPUT_GET, 'pinyin');
$recordid = filter_input(INPUT_GET, 'recordid');
$goback = new moodle_url('/mod/book/view.php', ['id' => 15, 'chapterid' => 24, 'search' => $pinyin]);
$sql = "SELECT c.id,c.content FROM {data_content} AS c LEFT JOIN {data_records} AS r ON c.recordid = r.id WHERE c.recordid IN (SELECT c1.recordid FROM {data_content} AS c1 WHERE c1.fieldid = 47 AND c1.content LIKE '%{$chinesechar}%') AND r.dataid = 5";
$HSKdata = $DB->get_records_sql($sql);
$HSKdatachunk = $HSKdata = array_chunk($HSKdata, 4);

$tone1 = $tone2 = $tone3 = $tone4 = $tone5 = array();
foreach ($HSKdata as $hskvalues) {
    if (strpos($hskvalues[2]->content, $pinyin . '1') !== false) {
        $tone1[] = $hskvalues;
    }
    if (strpos($hskvalues[2]->content, $pinyin . '2') !== false) {
        $tone2[] = $hskvalues;
    }
    if (strpos($hskvalues[2]->content, $pinyin . '3') !== false) {
        $tone3[] = $hskvalues;
    }
    if (strpos($hskvalues[2]->content, $pinyin . '4') !== false) {
        $tone4[] = $hskvalues;
    }
    if (strpos($hskvalues[2]->content, $pinyin . '5') !== false) {
        $tone5[] = $hskvalues;
    }
}
$HSKdata = array_merge($tone1, $tone2, $tone3, $tone4, $tone5);
$singleCharData = $DB->get_records_sql("SELECT content FROM {data_content} WHERE recordid = '$recordid' AND (fieldid = 22 OR fieldid = 25)");
$singleCharData = array_values($singleCharData);
$pinyinChars = filter_input(INPUT_GET,'searchpinyin');
$pinyinCharsArr = explode('_', $pinyinChars);
?>
<div class="align-right">
    <a href="<?php echo $goback; ?>" class="btn btn-info" role="button" style=" margin-bottom:5px;">Go back</a>   
</div>
<div class="clear"></div>
<div class="container wordrecord">
    <div class="row">
        <div class="col-sm-12">
            <div class="chinesecharacter">
                <div class="innerdiv">
                    <h1><?php echo $chinesechar; ?></h1>
                </div>
            </div>
        </div>
    </div>
    <table class="table table-bordered table-striped">
        <tr>
            <th>HSK Level :</th>
            <td><?php echo $singleCharData[1]->content;?></td>
        </tr>
        <tr>
            <th>Defination :</th>
            <td><?php echo $singleCharData[0]->content;?></td>
        </tr>
    </table>
    <?php 
    if (!empty($HSKdatachunk)) { 
        ?>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr align="center">
                        <th>Words</th>
                        <th>HSK</th>
                        <th>Pinyin</th>
                        <th>Meaning</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    
                    foreach($HSKdatachunk as $hkey => $value): 
                        $hskArr = explode(' ', $value[2]->content);
                        $intersect = array_intersect($hskArr, $pinyinCharsArr);
                        $pinyin = array_values($intersect);
                    if(!empty($intersect)){    
?>
                        <tr align="center"><th colspan="4"><?php echo $pinyin[0]; ?></th></tr>
                        <tr>
                            <td><?php echo $value[0]->content; ?></td>
                            <td><?php echo $value[1]->content; ?></td>
                            <td><?php echo $value[2]->content; ?></td>
                            <td><?php echo $value[3]->content; ?></td>
                        </tr>
                    <?php } ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php } else { ?>
        <br>
        <h3><center>No record found.</center></h3>
    <?php } ?>
</div>