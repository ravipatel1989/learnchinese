<?php
defined('MOODLE_INTERNAL') || die;
global $DB, $PAGE;
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/mod/book/js/custom.js') );
if (isset($_GET['hsk']) && $_GET['hsk'] != "") {
    $hsk = filter_input(INPUT_GET, 'hsk');
$flashcardData = $DB->get_records_sql("SELECT c.id, c.content FROM {data_content} AS c LEFT JOIN {data_records} AS r ON c.recordid = r.id WHERE c.recordid IN (select c1.recordid FROM {data_content} AS c1 WHERE c1.fieldid = 29 AND c1.content = '' AND c1.content <= $hsk) AND (c.fieldid = '14' OR c.fieldid = '28') AND r.dataid = 3");

$flashcardData = array_chunk($flashcardData, 2);
shuffle($flashcardData);
$flashcardData = array_slice($flashcardData, 0, 40); 
$goback = new moodle_url('/mod/book/view.php', ['id' => 15, 'chapterid' => 20]);
?>
<div class="align-right">
<a href="<?php echo $goback; ?>" class="btn btn-primary" role="button" style=" margin-bottom:5px;">Go back</a>   
</div>
<div id="flashcard" class="slideshow-container">
    <?php
    foreach ($flashcardData as $key => $value) {
        $mp4File = $CFG->characterdata.'/'.$value[0]->content.'.mp4';
        if(file_exists($mp4File)){
        ?>
        <div class="flip-box form-group mySlides fadeslide">
            <div class="correctanswer"><span>Correct&nbsp;<i class="fa fa-check"></i></span></div>
            <div class="wronganswer"><span>Wrong&nbsp;<i class="fa fa-close"></i></span></div>
            <div id="flip-box-inner-<?php echo $key; ?>" class="flip-box-inner">
                <div class="flip-box-front">
                    <a href="javascript:void(0);" class="playvideoasaudio" data-videoid="<?php echo $key; ?>"><i class="fa fa-play" style="font-size:28px;"></i></a>
                </div>
                <div class="flip-box-back normalback">
                    <video width="260" height="200" id="video_<?php echo $key; ?>" controls="true" playsinline>
                        <source src="<?php echo $CFG->wwwroot; ?>/characterdata/<?php echo $value[0]->content; ?>.mp4" type="video/mp4">
                    </video>
                </div>
            </div>
            <input type="text" name="answer" id="answer_<?php echo $key; ?>" class="form-control flashcardanswer" />
            <input type="button" data-id="<?php echo $key; ?>" data-answer="<?php echo $value[1]->content; ?>" data-rotate="no" data-next="no" class="flashcardsubmit btn btn-primary" name="Submit" value="Submit" />
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
    <?php
} else {
    $hsklist = $DB->get_records_sql("SELECT DISTINCT(c.content) AS hsk FROM mdl_data_content AS c LEFT JOIN mdl_data_records AS r ON c.recordid = r.id WHERE c.fieldid = 25 AND r.dataid = 3");
    ?>
    <div class="container">
        <form action="" method="get">
            <input type="hidden" name="id" value="15" />
            <input type="hidden" name="chapterid" value="20" />
            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group">
                        <select name="hsk" class="form-control" required="required">
                            <option value="">- Select HSK level -</option>
                            <?php foreach ($hsklist as $value) : ?>
                                <option value="<?php echo $value->hsk; ?>"><?php echo $value->hsk; ?></option>
    <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <input type="submit" value="Submit" class="btn btn-success" />
                    </div>
                </div>
            </div>
        </form>
    </div>
    <?php
}
?>
