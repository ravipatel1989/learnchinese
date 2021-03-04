<?php
defined('MOODLE_INTERNAL') || die;
global $DB, $SESSION;
$recordid = filter_input(INPUT_GET, 'recordid');

if (intval($recordid) > 0) {
    $sql = "SELECT c.* FROM {data_content} AS c WHERE c.recordid = $recordid";
    $getData = $DB->get_records_sql($sql);
    $getData = array_values($getData);
    $sentence = str_replace(' ', '', $getData[2]->content);
    $englishchars = preg_replace('/[^0-9\-]/', '', $getData[3]->content);
    $pinyin = preg_replace('/[^A-Za-z0-9\-]/', '', $sentence);
    $pinyin = str_replace($englishchars, '', $pinyin);
    $sentenceWithoutPinyin = str_replace($pinyin, '', $sentence);

    $completeSentence = str_replace($pinyin, $getData[1]->content, $sentence);

    preg_match_all('/./u', $sentenceWithoutPinyin, $matches);

    $sentencewithpinyin = str_replace($getData[1]->content, $getData[1]->content . ' (' . $pinyin . ') ', $completeSentence);
    $formAction = new moodle_url('/mod/book/view.php', ['id' => 15, 'chapterid' => 16]);
    ?>
    <div class="align-right">
        <form name="" method="post" action="<?php echo $formAction; ?>">
            <input type="hidden" name="keyword" value="<?php echo $SESSION->keyword; ?>" />
            <input type="hidden" name="hsk" value="<?php echo $SESSION->hsk; ?>" />
            <input type="submit" class="btn btn-primary" value="Go back" style="margin-bottom:5px;" />
        </form>
    </div>
    <div class="dragword">
        <span class="hide" id="completesentence" data-sentence="<?php echo htmlentities($completeSentence); ?>"></span>
        <label><strong>Drag the word and place in between below characters.</strong></label>
        <ul id="sortable">
            <li id="draggable" class="ui-state-highlight"><?php echo $getData[1]->content; ?></li>
            <?php foreach ($matches[0] as $value) { ?>
                <li class="ui-state-default"><?php echo $value; ?></li>
            <?php } ?>
            <li></li>
        </ul>
        <div class="hidden" id="sentencewithpinyin">
            <p><?php echo $sentencewithpinyin; ?></p>
        </div>
    </div>
    <?php
}