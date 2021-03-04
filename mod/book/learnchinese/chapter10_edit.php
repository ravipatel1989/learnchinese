<?php
defined('MOODLE_INTERNAL') || die;
global $DB, $CFG;
$ajaxUrl = $CFG->wwwroot.'/mod/book/ajax.php';
$content = filter_input(INPUT_GET, 'content');
$sql = "SELECT c.recordid, c.content as pinyin, (SELECT c1.content FROM {data_content} AS c1 WHERE c1.fieldid = 50 AND c1.recordid = c.recordid) AS meaning,(SELECT c2.content FROM {data_content} AS c2 WHERE c2.fieldid = 74 AND c2.recordid = c.recordid) AS classname FROM {data_content} AS c WHERE c.fieldid = 49 AND c.recordid IN (SELECT recordid FROM {data_content} WHERE fieldid = 47 AND content = '$content')";

$wordList = $DB->get_records_sql($sql);
?>
<a href="javascript:window.history.back();" class="btn btn-primary">Go back</a><br><br>
<table id="datatable" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Character</th>
            <th>Pinyin</th>
            <th width="40%">Meaning</th>
            <th>Class</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php
    foreach($wordList as $key => $word){
        echo '<tr>';
        echo '<td>'.$content.'</td>';
        echo '<td id="pinyin_'.$key.'">'.$word->pinyin.'</td>';
        echo '<td id="meaning_'.$key.'">'.$word->meaning.'</td>';
        echo '<td id="class_'.$key.'">'.$word->classname.'</td>';
        echo '<td><a href="javascript:void(0);" id="editword_'.$key.'" data-recordid="'.$key.'" data-ajaxurl="'.$ajaxUrl.'" class="btn btn-primary editword">Edit</a>&nbsp;<a href="javascript:void(0);" id="deletekeyword_'.$key.'"  data-recordid="'.$key.'" data-ajaxurl="'.$ajaxUrl.'" class="btn btn-primary deleteword">Delete</a></td>';
        echo '</tr>';
    }
    ?>
    </tbody>
</table>
