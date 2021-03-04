<?php
defined('MOODLE_INTERNAL') || die;
global $DB, $PAGE;
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/mod/book/js/custom.js') );
$flashcardData = $DB->get_records_sql("SELECT c.id, c.content FROM {data_content} AS c LEFT JOIN {data_records} AS r ON c.recordid = r.id WHERE (c.fieldid = '1' OR c.fieldid = '9' OR c.fieldid = '60') AND r.dataid = 1");
$flashcardData = array_chunk($flashcardData, 3);
shuffle($flashcardData);
?>

<div id="flashcard" class="slideshow-container">
    <?php
    foreach ($flashcardData as $key => $value) {
        if($value[1]->content!=""){
        ?>
        <div class="flip-box form-group mySlides fadeslide">
            <div class="correctanswer"><span>Correct&nbsp;<i class="fa fa-check"></i></span></div>
            <div class="wronganswer"><span>Wrong&nbsp;<i class="fa fa-close"></i></span></div>
            <div id="flip-box-inner-<?php echo $key; ?>" class="flip-box-inner">
                <div class="flip-box-front">
                    <audio controls="">
                        <source src="<?php echo $CFG->wwwroot; ?>/pluginfile.php/40/mod_data/content/<?php echo $value[1]->id; ?>/<?php echo $value[1]->content; ?>" type="audio/mp3">
                    </audio>
                </div>
                <div class="flip-box-back normalback">
                    <p class="chinesechar"><?php echo $value[0]->content; ?></p>
                    <?php if($value[2]->content!=""){ ?>
                    <p><?php echo $value[2]->content; ?></p>
                    <?php } ?>
                </div>
            </div>
            <input type="text" name="answer" id="answer_<?php echo $key; ?>" class="form-control flashcardanswer" />
            <input type="button" data-id="<?php echo $key; ?>" data-answer="<?php echo $value[0]->content; ?>" class="flashcardsubmit btn btn-primary" name="Submit" value="Submit" />
        </div>

        <?php 
        } 
    }?>
    <!-- Next and previous buttons -->
    <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
    <a class="next" onclick="plusSlides(1)">&#10095;</a>
</div>
<div class="totalscore">
    <h3>Score <span class="score">0</span> out of <span class="scoreoutof">0</span></h3>
</div>