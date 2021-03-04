<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Book view page
 *
 * @package    mod_book
 * @copyright  2004-2011 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once(__DIR__.'/locallib.php');
require_once($CFG->libdir.'/completionlib.php');

//$PAGE->requires->css( new moodle_url($CFG->wwwroot . '/mod/book/css/bootstrap.min.css') ); 
$PAGE->requires->css( new moodle_url($CFG->wwwroot . '/mod/book/css/style.css') );
$PAGE->requires->css( new moodle_url('https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css'),true ); 
$PAGE->requires->css( new moodle_url('https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css'),true );

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/mod/book/js/jquery.ui.touch-punch.min.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/mod/book/js/script.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/mod/book/js/ajax.js') );
$PAGE->requires->js( new moodle_url('https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js'),true );

$id        = optional_param('id', 0, PARAM_INT);        // Course Module ID
$bid       = optional_param('b', 0, PARAM_INT);         // Book id
$chapterid = optional_param('chapterid', 0, PARAM_INT); // Chapter ID
$edit      = optional_param('edit', -1, PARAM_BOOL);    // Edit mode

// =========================================================================
// security checks START - teachers edit; students view
// =========================================================================
if ($id) {
    $cm = get_coursemodule_from_id('book', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
    $book = $DB->get_record('book', array('id'=>$cm->instance), '*', MUST_EXIST);
} else {
    $book = $DB->get_record('book', array('id'=>$bid), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('book', $book->id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
    $id = $cm->id;
}

require_course_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/book:read', $context);

$allowedit  = has_capability('mod/book:edit', $context);
$viewhidden = has_capability('mod/book:viewhiddenchapters', $context);

if ($allowedit) {
    if ($edit != -1 and confirm_sesskey()) {
        $USER->editing = $edit;
    } else {
        if (isset($USER->editing)) {
            $edit = $USER->editing;
        } else {
            $edit = 0;
        }
    }
} else {
    $edit = 0;
}

// read chapters
$chapters = book_preload_chapters($book);

if ($allowedit and !$chapters) {
    redirect('edit.php?cmid='.$cm->id); // No chapters - add new one.
}
// Check chapterid and read chapter data
if ($chapterid == '0') { // Go to first chapter if no given.
    // Trigger course module viewed event.
    book_view($book, null, false, $course, $cm, $context);

    foreach ($chapters as $ch) {
        if ($edit) {
            $chapterid = $ch->id;
            break;
        }
        if (!$ch->hidden) {
            $chapterid = $ch->id;
            break;
        }
    }
}

$courseurl = new moodle_url('/course/view.php', array('id' => $course->id));

// No content in the book.
if (!$chapterid) {
    $PAGE->set_url('/mod/book/view.php', array('id' => $id));
    notice(get_string('nocontent', 'mod_book'), $courseurl->out(false));
}
// Chapter doesnt exist or it is hidden for students
if ((!$chapter = $DB->get_record('book_chapters', array('id' => $chapterid, 'bookid' => $book->id))) or ($chapter->hidden and !$viewhidden)) {
    print_error('errorchapter', 'mod_book', $courseurl);
}

$PAGE->set_url('/mod/book/view.php', array('id'=>$id, 'chapterid'=>$chapterid));


// Unset all page parameters.
unset($id);
unset($bid);
unset($chapterid);

// Read standard strings.
$strbooks = get_string('modulenameplural', 'mod_book');
$strbook  = get_string('modulename', 'mod_book');
$strtoc   = get_string('toc', 'mod_book');

// prepare header
$pagetitle = $book->name . ": " . $chapter->title;
$PAGE->set_title($pagetitle);
$PAGE->set_heading($course->fullname);

book_add_fake_block($chapters, $chapter, $book, $cm, $edit);

// prepare chapter navigation icons
$previd = null;
$prevtitle = null;
$navprevtitle = null;
$nextid = null;
$nexttitle = null;
$navnexttitle = null;
$last = null;
foreach ($chapters as $ch) {
    if (!$edit and $ch->hidden) {
        continue;
    }
    if ($last == $chapter->id) {
        $nextid = $ch->id;
        $nexttitle = book_get_chapter_title($ch->id, $chapters, $book, $context);
        $navnexttitle = get_string('navnexttitle', 'mod_book', $nexttitle);
        break;
    }
    if ($ch->id != $chapter->id) {
        $previd = $ch->id;
        $prevtitle = book_get_chapter_title($ch->id, $chapters, $book, $context);
        $navprevtitle = get_string('navprevtitle', 'mod_book', $prevtitle);
    }
    $last = $ch->id;
}

if ($book->navstyle) {
    $navprevicon = right_to_left() ? 'nav_next' : 'nav_prev';
    $navnexticon = right_to_left() ? 'nav_prev' : 'nav_next';
    $navprevdisicon = right_to_left() ? 'nav_next_dis' : 'nav_prev_dis';

    $chnavigation = '';
    if ($previd) {
        $navprev = get_string('navprev', 'book');
        if ($book->navstyle == 1) {
            $chnavigation .= '<a title="' . $navprevtitle . '" class="bookprev" href="view.php?id=' .
                $cm->id . '&amp;chapterid=' . $previd .  '">' .
                $OUTPUT->pix_icon($navprevicon, $navprevtitle, 'mod_book') . '</a>';
        } else {
            $chnavigation .= '<a title="' . $navprev . '" class="bookprev" href="view.php?id=' .
                $cm->id . '&amp;chapterid=' . $previd . '">' .
                '<span class="chaptername"><span class="arrow">' . $OUTPUT->larrow() . '&nbsp;</span></span>' .
                $navprev . ':&nbsp;<span class="chaptername">' . $prevtitle . '</span></a>';
        }
    } else {
        if ($book->navstyle == 1) {
            $chnavigation .= $OUTPUT->pix_icon($navprevdisicon, '', 'mod_book');
        }
    }
    if ($nextid) {
        $navnext = get_string('navnext', 'book');
        if ($book->navstyle == 1) {
            $chnavigation .= '<a title="' . $navnexttitle . '" class="booknext" href="view.php?id=' .
                $cm->id . '&amp;chapterid='.$nextid.'">' .
                $OUTPUT->pix_icon($navnexticon, $navnexttitle, 'mod_book') . '</a>';
        } else {
            $chnavigation .= ' <a title="' . $navnext . '" class="booknext" href="view.php?id=' .
                $cm->id . '&amp;chapterid='.$nextid.'">' .
                $navnext . ':<span class="chaptername">&nbsp;' . $nexttitle.
                '<span class="arrow">&nbsp;' . $OUTPUT->rarrow() . '</span></span></a>';
        }
    } else {
        $navexit = get_string('navexit', 'book');
        $sec = $DB->get_field('course_sections', 'section', array('id' => $cm->section));
        $returnurl = course_get_url($course, $sec);
        if ($book->navstyle == 1) {
            $chnavigation .= '<a title="' . $navexit . '" class="bookexit"  href="'.$returnurl.'">' .
                $OUTPUT->pix_icon('nav_exit', $navexit, 'mod_book') . '</a>';
        } else {
            $chnavigation .= ' <a title="' . $navexit . '" class="bookexit"  href="'.$returnurl.'">' .
                '<span class="chaptername">' . $navexit . '&nbsp;' . $OUTPUT->uarrow() . '</span></a>';
        }
    }
}

// We need to discover if this is the last chapter to mark activity as completed.
$islastchapter = false;
if (!$nextid) {
    $islastchapter = true;
}

book_view($book, $chapter, $islastchapter, $course, $cm, $context);

// =====================================================
// Book display HTML code
// =====================================================

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($book->name));

$navclasses = book_get_nav_classes();

if ($book->navstyle) {
    // Upper navigation.
    echo '<div class="navtop clearfix ' . $navclasses[$book->navstyle] . '">' . $chnavigation . '</div>';
}

// The chapter itself.
$hidden = $chapter->hidden ? ' dimmed_text' : null;
echo $OUTPUT->box_start('generalbox book_content' . $hidden);

if (!$book->customtitles) {
    if (!$chapter->subchapter) {
        $currtitle = book_get_chapter_title($chapter->id, $chapters, $book, $context);
        echo $OUTPUT->heading($currtitle, 3);
    } else {
        $currtitle = book_get_chapter_title($chapters[$chapter->id]->parent, $chapters, $book, $context);
        $currsubtitle = book_get_chapter_title($chapter->id, $chapters, $book, $context);
        echo $OUTPUT->heading($currtitle, 3);
        echo $OUTPUT->heading($currsubtitle, 4);
    }
}
$chaptertext = file_rewrite_pluginfile_urls($chapter->content, 'pluginfile.php', $context->id, 'mod_book', 'chapter', $chapter->id);
$id        = optional_param('id', 0, PARAM_INT);        // Course Module ID
$chapterid = optional_param('chapterid', 0, PARAM_INT); // Chapter ID

if($id==15){
    switch ($chapterid) {
        case 0:
        case 1:
        case 2:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter1_1.php');
            break;
        case 3:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter1_3.php');
            break;
        case 13:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            if(isset($_GET['search'])){
                require_once('learnchinese/chapter1_3_search.php');
            }else{
                require_once('learnchinese/chapter1_3.php');
            }
            break;
        case 25:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter1_4.php');
            break;
        case 14:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter1_5.php');
            break;
        case 26:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter1_6.php');
            break;
        case 15:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter1_7.php');
            break;
        case 17:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter1_8.php');
            break;
        case 5:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter2.php');
            break;
        case 6:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            if(isset($_GET['search'])){
                require_once('learnchinese/chapter2_1_search.php');
            }else{
                require_once('learnchinese/chapter2_1.php');
            }
            break;
        case 7:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            if(isset($_GET['recordid']) && $_GET['recordid']!=""){
                require_once('learnchinese/chapter3_record.php');
            }else{
                require_once('learnchinese/chapter3.php');
            }
            break;
        case 8:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter3_1.php');
            break;
        case 9:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter3_2.php');
            break;
        case 16:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            if(isset($_GET['recordid']) && $_GET['recordid']!=""){
                require_once('learnchinese/chapter3_3_dragword.php');
            }else{
                require_once('learnchinese/chapter3_3.php');
            }
            break;
        case 12:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter3_4.php');
            break;
        case 10:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter4.php');
            break;
        case 11:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter4_1.php');
            break;
        case 18:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter4_2.php');
            break;
        case 19:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter4_3.php');
            break;
        case 20:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter4_4.php');
            break;
        case 21:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter4_5.php');
            break;
        case 22:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            if(isset($_GET['search'])){
                require_once('learnchinese/chapter4_6_search.php');
            }else{
                require_once('learnchinese/chapter4_6.php');
            }
            break;
        case 23:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter6.php');
            break;
        case 24:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            if(isset($_GET['chinesechar'])){
                require_once('learnchinese/chapter6_1_record.php');
            }else{
                require_once('learnchinese/chapter6_1.php');
            }
            break;
        case 28:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter6_2.php');
            break;
        case 29:
        case 58:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter6_3.php');
            break;
        case 30:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter6_4.php');
            break;
        case 60:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter6_5.php');
            break;
        case 62:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter7_1.php');
            break;
        case 63:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter8.php');
            break;
        case 64:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter8_1.php');
            break;
        case 65:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter8_2.php');
            break;
        case 66:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter9.php');
            break;
        case 67:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter9_1.php');
            break;
        case 69:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter9_2.php');
            break;
        case 68:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter9_3.php');
            break;
        case 70:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter9_4.php');
            break;
        case 71:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            if($_GET['action']=='edit'){
                require_once('learnchinese/chapter10_edit.php');
            }else{
                require_once('learnchinese/chapter10.php');
            }
            break;
        case 75:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter7_2.php');
            break;
        case 76:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter7_3.php');
            break;
        case 77:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter10_1.php');
            break;
        case 77:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter10_1.php');
            break;
        case 78:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter10_2.php');
            break;
        case 79:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter11.php');
            break;
        case 80:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter11_1.php');
            break;
        case 81:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter11_2.php');
            break;
        case 82:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter12.php');
            break;
        case 83:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter8_3.php');
            break;
        case 84:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter8_4.php');
            break;
        case 85:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter13.php');
            break;
        case 86:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter8_5.php');
            break;
        case 87:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));   
            require_once('learnchinese/chapter8_6.php');
            break;

        default:
            echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));
            break;
    }    
}else{
    echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'overflowdiv'=>true, 'context'=>$context));
}

echo $OUTPUT->box_end();

if (core_tag_tag::is_enabled('mod_book', 'book_chapters')) {
    echo $OUTPUT->tag_list(core_tag_tag::get_item_tags('mod_book', 'book_chapters', $chapter->id), null, 'book-tags');
}

if ($book->navstyle) {
    // Lower navigation.
    echo '<div class="navbottom clearfix ' . $navclasses[$book->navstyle] . '">' . $chnavigation . '</div>';
}

echo $OUTPUT->footer();
