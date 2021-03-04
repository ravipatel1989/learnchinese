<?php
defined('MOODLE_INTERNAL') || die();
global $DB, $CFG;

$word = $DB->get_records_sql("SELECT c.recordid, c.content FROM {data_content} AS c WHERE c.fieldid = 56");
$sentence = $DB->get_records_sql("SELECT c.recordid, c.content FROM {data_content} As c WHERE c.fieldid = 57");

//print_r($word);

$csvArr = array();
foreach ($word as $key => $val){
    $wordval = $val->content;
    $sentenceval = preg_replace('/[^A-Za-z0-9\-]/', '', $sentence[$key]->content);
    $csvArr[] = array('word'=>$wordval, 'pinyin'=>$sentenceval);
}

function multi_unique($src){
     $output = array_map("unserialize",
     array_unique(array_map("serialize", $src)));
   return $output;
}

$csvArr = multi_unique($csvArr);
//print_r($csvArr);
$file = fopen("learnchinese/csv/wordpinyin.csv","w");

foreach ($csvArr as $line) {
  fputcsv($file, $line);
}

fclose($file);
echo '<div style="display:block; text-align:center;font-size: 20px;text-decoration: underline;font-weight: bold;">';
echo '<a target="_blank" href="'.$CFG->wwwroot.'/mod/book/learnchinese/csv/wordpinyin.csv">Open CSV</a>';
echo '</div>';
