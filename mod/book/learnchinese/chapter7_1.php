<?php
defined('MOODLE_INTERNAL') || die;
global $DB;

$sentences = $DB->get_records_sql("SELECT c.recordid, c.content, (select c1.content FROM {data_content} AS c1 WHERE c1.recordid = c.recordid AND c1.fieldid = 67) AS sentence FROM {data_content} AS c WHERE c.fieldid = 68 AND content LIKE '%5'");
?>
<table id="mismatchpinyin" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Character</th><th>Pinyin</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($sentences as $key => $value){?>
        <tr>
            <td><?php echo preg_replace("/[^\x{4e00}-\x{9fa5}]+/u", '', $value->sentence);?></td>
            <td><?php echo $value->content;?></td>
        </tr>
        <?php }?>
    </tbody>
</table>