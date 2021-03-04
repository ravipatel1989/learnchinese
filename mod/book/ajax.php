<?php
require(__DIR__ . '/../../config.php');
global $DB, $PAGE, $SESSION, $CFG;
$action = filter_input(INPUT_POST, 'action');
switch ($action) {
    case 'update_pinyin':
        $recordid = filter_input(INPUT_POST, 'rid');
        $content = filter_input(INPUT_POST, 'pinyin');
        $multiplepinyin = trim($content);
        $multiplepinyin = count(explode(' ', $multiplepinyin));
        $fieldid = 28;
        $sql = "UPDATE {data_content} SET content = ? WHERE fieldid = ? AND recordid = ?";
        $return = $DB->execute($sql, array($content, $fieldid, $recordid));
        if ($return == 1) {
            $sql = "UPDATE {data_content} SET content = ? WHERE fieldid = ? AND recordid = ?";
            $DB->execute($sql, array($multiplepinyin, 29, $recordid));
            echo json_encode(array('response' => "success", "recordid" => $recordid, "content" => $content));
        } else {
            echo json_encode(array('response' => "fail"));
        }
        die;
        break;
    case 'update_radicals':
        $pagenum = filter_input(INPUT_POST, 'page');
        if (intval($pagenum) == 0 || intval($pagenum) == 1) {
            $pagenum = 1;
        }
        $limitStart = ($pagenum - 1) * 10;
        $radicalsData = $DB->get_records_sql("SELECT r.id, c.* FROM `{data_records}` as r LEFT JOIN `{data_content}` as c ON r.id = c.recordid WHERE `r`.`dataid` = 4 AND c.fieldid = 34 ORDER BY recordid ASC LIMIT {$limitStart}, 10");
        try {
            try {
                $transaction = $DB->start_delegated_transaction();
                $returnArr = array();
                foreach ($radicalsData as $key => $value) {
                    $radicalCount = $DB->get_record_sql("SELECT count(r.id) AS totalradicals FROM {data_content} AS c LEFT JOIN {data_records} AS r ON c.recordid = r.id WHERE c.fieldid = '23' AND c.content LIKE '{$value->content}%' AND r.dataid = 3");
                    $sql = "UPDATE {data_content} SET content = ? WHERE fieldid = ? AND recordid = ?";
                    $DB->execute($sql, array($radicalCount->totalradicals, 46, $value->recordid));
                    $returnArr[$value->recordid] = $radicalCount->totalradicals;
                }
                $transaction->allow_commit();
                echo json_encode($returnArr);
            } catch (Exception $e) {
                // Make sure transaction is valid.
                if (!empty($transaction) && !$transaction->is_disposed()) {
                    $transaction->rollback($e);
                }
            }
        } catch (Exception $e) {
            // Silence the rollback exception or do something else.
        }
        die;
        break;
    case 'get_radicals':
        $totalradicals = $DB->get_record_sql('SELECT count(r.id) AS id FROM `{data_records}` as r LEFT JOIN `{data_content}` as c ON r.id = c.recordid WHERE `r`.`dataid` = 4');
        $limit = " LIMIT {$_POST['length']} OFFSET {$_POST['start']}";
        $descContent = $DB->get_records_sql("SELECT c.recordid FROM {data_content} AS c LEFT JOIN {data_records} AS r ON c.recordid = r.id WHERE c.fieldid = '46' AND r.dataid = 4 ORDER BY CAST(`c`.`content` AS SIGNED) DESC $limit");
        $recordids = array_keys($descContent);
        $recordids = implode(',', $recordids);
        $sql = "SELECT c.* FROM `mdl_data_records` as r LEFT JOIN `mdl_data_content` as c ON r.id = c.recordid WHERE c.recordid IN ($recordids)";
        $radicalsData = $DB->get_records_sql($sql);
        $radicalsData = array_chunk($radicalsData, 12);
        usort($radicalsData, function($a, $b) {
            return $b[11]->content <=> $a[11]->content;
        });
        header("Content-Type: application/json");
        $data = array();
        foreach ($radicalsData as $key => $radicals) {
            $searchLink = new moodle_url('/mod/book/view.php', ['id' => 15, 'chapterid' => 6, 'search' => $radicals[1]->content]);
            $records = array();
            $records[] = $radicals[0]->content;
            $records[] = $radicals[1]->content;
            $records[] = $radicals[2]->content;
            $records[] = $radicals[3]->content;
            $records[] = $radicals[4]->content;
            $records[] = $radicals[5]->content;
            $records[] = $radicals[7]->content;
            $records[] = $radicals[9]->content;
            $records[] = $radicals[10]->content;
            $records[] = "<a href='$searchLink'>" . $radicals[11]->content . "</a>";
            $data[] = $records;
        }
        $data = array_values($data);
        $recordsTotal = $recordsFiltered = ($totalradicals->id) / 12;
        $json_data = array(
            "draw" => intval($_REQUEST['draw']),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $data
        );
        echo json_encode($json_data);
        die;
        break;
    case 'hsklevelwords':
        $totalhsklevelwords = $DB->get_record_sql('SELECT count(r.id) AS id FROM `{data_records}` as r LEFT JOIN `{data_content}` as c ON r.id = c.recordid WHERE c.fieldid = 59 AND `r`.`dataid` = 7');
        $length = filter_input(INPUT_POST, 'length');
        $length = $length * 5;
        $offset = filter_input(INPUT_POST, 'start');
        $offset = $offset * 5;
        $limit = " LIMIT {$length} OFFSET {$offset}";
        $hskwordlist = $DB->get_records_sql("SELECT c.* FROM {data_content} AS c LEFT JOIN {data_records} AS r ON c.recordid = r.id WHERE r.dataid = 7 $limit");
        $firstElement = array_key_first($hskwordlist);
        $lastElement = array_key_last($hskwordlist);
        $hskwordlist = array_chunk($hskwordlist, 5);
        $hskwordlist = array_values($hskwordlist);

        header("Content-Type: application/json");
        $data = array();
        foreach ($hskwordlist as $key => $word) {
            $records = array();
            $records[] = $word[1]->content . '<br><hr>' . $word[4]->content;
            $records[] = $word[2]->content;
            $records[] = $word[3]->content;
            $data[] = $records;
        }
        $recordsTotal = $recordsFiltered = $totalhsklevelwords->id;
        $json_data = array(
            "draw" => intval($_REQUEST['draw']),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "firstElement" => $firstElement,
            "lastElement" => $lastElement,
            "data" => $data
        );
        echo json_encode($json_data);
        die;
        break;
    
    case 'keywordhsklevelwords':
        $totalhsklevelwords = $DB->get_record_sql('SELECT count(r.id) AS id FROM `{data_records}` as r LEFT JOIN `{data_content}` as c ON r.id = c.recordid WHERE c.fieldid = 90 AND `r`.`dataid` = 13');
        $length = filter_input(INPUT_POST, 'length');
        $length = $length * 8;
        $offset = filter_input(INPUT_POST, 'start');
        $offset = $offset * 8;
        $limit = " LIMIT {$length} OFFSET {$offset}";
        $hskwordlist = $DB->get_records_sql("SELECT c.* FROM {data_content} AS c LEFT JOIN {data_records} AS r ON c.recordid = r.id WHERE r.dataid = 13 $limit");
        $firstElement = array_key_first($hskwordlist);
        $lastElement = array_key_last($hskwordlist);
        $hskwordlist = array_chunk($hskwordlist, 8);
        $hskwordlist = array_values($hskwordlist);
        header("Content-Type: application/json");
        $data = array();
        foreach ($hskwordlist as $key => $word) {
            $records = array();
            $records[] = $word[5]->content . '<br><hr>' . $word[6]->content;
            $records[] = $word[0]->content;
            $records[] = $word[4]->content;
            $data[] = $records;
        }
        $recordsTotal = $recordsFiltered = $totalhsklevelwords->id;
        $json_data = array(
            "draw" => intval($_REQUEST['draw']),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "firstElement" => $firstElement,
            "lastElement" => $lastElement,
            "data" => $data
        );
        echo json_encode($json_data);
        die;
        break;
    case 'undefinedwordFn':
        $totalundefined = $DB->get_record_sql('SELECT count(c.id) AS id FROM `{data_content}` as c WHERE c.fieldid = 74');
        $length = filter_input(INPUT_POST, 'length');
        $offset = filter_input(INPUT_POST, 'start');
        $limit = " LIMIT {$length} OFFSET {$offset}";
        
        $sql = "SELECT dc.recordid,dc.content,(SELECT dc1.content from `{data_content}` as dc1 where dc.recordid = dc1.recordid and dc1.fieldid = 48) AS HSK, (SELECT dc2.content from `{data_content}` as dc2 where dc.recordid = dc2.recordid and dc2.fieldid = 49) AS Pinyin, (SELECT dc3.content from `{data_content}` as dc3 where dc.recordid = dc3.recordid and dc3.fieldid = 50) AS meaning, (SELECT dc4.content from `{data_content}` as dc4 where dc.recordid = dc4.recordid and dc4.fieldid = 47) AS charater FROM `{data_fields}` as `df` left join `{data_content}` as `dc` on df.id = dc.fieldid where df.dataid = 5 AND dc.fieldid = 74 AND dc.content = 'undefined' ORDER BY `dc`.`recordid` ASC $limit";
        $wordrecords = $DB->get_records_sql($sql);
        
        header("Content-Type: application/json");
        $data = [];
        
        foreach($wordrecords as $key => $record){
            $editrecord = new moodle_url('/mod/book/view.php', ['id' => 15, 'chapterid' => 71, 'action'=>'edit', 'content'=>$record->charater]);
            $records = array();
            $records[] = '<input type="checkbox" name="sentenceArr[]" id="sentence_'.$key.'" class="sentencechk" checked="checked" value="'.$key.'" />';
            $records[] = $record->charater;
            $records[] = $record->hsk;
            $records[] = $record->pinyin;
            $records[] = $record->meaning;
            $records[] = $record->content;
            $records[] = '<a href="'.$editrecord.'" class="btn btn-primary">Edit</a>&nbsp;<a href="'.$deletesingle.'" class="btn btn-primary">Delete</a>';
            $data[] = $records;
        }
        $recordsTotal = $recordsFiltered = $totalundefined->id;
        $json_data = array(
            "draw" => intval($_REQUEST['draw']),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "firstElement" => $firstElement,
            "lastElement" => $lastElement,
            "data" => $data
        );
        echo json_encode($json_data);
        die;
        break;
    case 'similarkeywordFn':
        // get keywords from grammer table
        $sql = "SELECT c1.recordid, REPLACE(c1.content, ' ', '') as content,(SELECT c2.content FROM {data_content} as c2 WHERE c2.fieldid = 92 AND c2.recordid = c1.recordid) as id FROM {data_content} as c1 WHERE c1.fieldid = 99";
        $keywords = $DB->get_records_sql($sql);
        
        $totalSimilar = $DB->get_record_sql("SELECT count(c.id) total FROM {data_content} AS c WHERE c.fieldid = 116");
        
        $length = filter_input(INPUT_POST, 'length');
        $offset = filter_input(INPUT_POST, 'start');
        $limit = " LIMIT {$length} OFFSET {$offset}";
        
        $sql = "SELECT c.recordid, c.content as keyword, (SELECT c2.content FROM {data_content} AS c2 WHERE c2.fieldid = 124 AND c2.recordid = c.recordid) AS GID FROM {data_content} AS c WHERE c.fieldid = 116 $limit";
        $similarList = $DB->get_records_sql($sql);

        $counter = 0;
        header("Content-Type: application/json");
        $data = array();
        foreach($similarList as $similar){
            $GID = array();
            foreach($keywords as $keyword){
                $regex = "/[.，*？!！＇’\"@#$&-_ ]+$/";
                $keyword->content = mb_ereg_replace($regex, "", $keyword->content);
                $boolchar = preg_match("/\p{Han}+/u", $keyword->content);
                if($boolchar == 1){
                    $counter++;
                    $keyword->content = preg_replace('/\[.*\]/', '', $keyword->content);
                    $keyword->content = preg_replace('/\[.*/', '', $keyword->content);
                    $keyword->content = str_replace('(', '', $keyword->content);
                    $keyword->content = str_replace(')', '', $keyword->content);
                    $keyword->content = str_replace('、', ',', $keyword->content);
                    $keyword->content = str_replace('，', ',', $keyword->content);
                    $keyword->content = str_replace('++', '+', $keyword->content);
                    $keyword->content = trim($keyword->content,'+');
                    $pluskeyword = array($keyword->content);
                    if(strpos($keyword->content, '+') !== false){
                        $pluskeyword = explode('+', $keyword->content);
                    }
                    $finalArr = array();
                    $commacount = 0;
                    foreach($pluskeyword as $key => $commakeywords){
                        $commakeywordsArr = preg_split('/(,|\/)/', $commakeywords,-1, PREG_SPLIT_NO_EMPTY);
                        $pluskeyword[$key] = $commakeywordsArr;
                    }

                    $countpluskeyword = count($pluskeyword);
                    $finalkeywordArr = [];
                    if($countpluskeyword==1){
                        $finalkeywordArr = $pluskeyword[0];
                    }
                    else{
                        for($i=0; $i<$countpluskeyword; $i++){
                            $countj = count($pluskeyword[$i]);
                            for($j=0;$j<$countj;$j++){
                                $countk = count($pluskeyword[$i+1]);
                                for($k=0;$k<$countk;$k++){
                                    $finalkeywordArr[$i][] = $pluskeyword[$i][$j].'-'.$pluskeyword[$i+1][$k];
                                }
                            }
                            $pluskeyword[$i+1] = $finalkeywordArr[$i];
                        }
                        $finalkeywordArr = end($finalkeywordArr);
                    }
                    $similarkeywords = explode(',', $similar->keyword);
                    foreach ($similarkeywords as $similarval){
                        foreach($finalkeywordArr as $fkeyword){
                            $fkeyword = explode('-', $fkeyword);
                            $totalchar = count($fkeyword);
                            $cnt = 0;
                            for($i=0; $i<$totalchar; $i++){
                                if(mb_strpos($similarval, $fkeyword[$i]) !== false){
                                    $cnt++;
                                    $similarval = mb_substr($similarval, mb_strpos($similarval, $fkeyword[$i]) + 1);
                                }
                            }
                            if($cnt==$totalchar){
                                $GID[] = $keyword->id;
                                break;
                            }
                        }
                    }
                }
            }
            if(!empty($GID)){
                $selectQry = "SELECT content FROM {data_content} WHERE fieldid = 124 AND recordid = '$similar->recordid'";
                $existingGid = $DB->get_record_sql($selectQry);
                $existingGid = $existingGid->content;
                $GID = implode(',', $GID);
                if(intval($existingGid) > 0){
                    $GID = $GID.','.$existingGid;
                }
                $GID = explode(',', $GID);
                $GID = array_unique($GID);
                $GID = implode(',', $GID);
                $sql = "UPDATE `{data_content}` SET `content` = '$GID' WHERE `fieldid` = 124 AND `recordid` = '$similar->recordid'";
                $updateClass = $DB->execute($sql);
            }else{
                $selectQry = "SELECT content FROM {data_content} WHERE fieldid = 106 AND recordid = '$similar->recordid'";
                $GID = $DB->get_record_sql($selectQry);
                $GID = $existingGid->content;
            }
            
            $records = array();
            $records[] = $similar->keyword;
            $records[] = $GID;
            $data[] = $records;
        }
        $recordsTotal = $recordsFiltered = $totalSimilar->total;
        $json_data = array(
            "draw" => intval($_REQUEST['draw']),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $data
        );
        echo json_encode($json_data);
        die;
        break;
    case 'senteceGID':
        $ajaxUrl = $CFG->wwwroot.'/mod/book/ajax.php';
        $length = filter_input(INPUT_POST, 'length');
        $offset = filter_input(INPUT_POST, 'start');
        $limit = " LIMIT {$length} OFFSET {$offset}";
        
        // get keywords from grammer table
        $sql = "SELECT c1.recordid, REPLACE(c1.content, ' ', '') as content,(SELECT c2.content FROM {data_content} as c2 WHERE c2.fieldid = 92 AND c2.recordid = c1.recordid) as id FROM {data_content} as c1 WHERE c1.fieldid = 99";
        
        $keywords = $DB->get_records_sql($sql);
        
        $totalSentence = $DB->get_record_sql("SELECT count(c.id) as totalsentence FROM {data_content} AS c WHERE c.fieldid = 105 AND c.content = 1");
        
        $sql = "SELECT c.recordid, (SELECT c1.content FROM {data_content} AS c1 WHERE c1.fieldid = 67 AND c1.recordid = c.recordid) AS sentence, (SELECT c2.content FROM {data_content} AS c2 WHERE c2.fieldid = 106 AND c2.recordid = c.recordid) AS GID FROM {data_content} AS c WHERE c.fieldid = 105 AND c.content = 1 $limit";
        $sentenceList = $DB->get_records_sql($sql);
        header("Content-Type: application/json");
        $data = array();
        foreach($sentenceList as $sentence){
            $GID = array();
            foreach($keywords as $keyword){
                $regex = "/[.，*＇’\"@#$&-_ ]+$/";
                $keyword->content = mb_ereg_replace($regex, "", $keyword->content);
                $boolchar = preg_match("/\p{Han}+/u", $keyword->content);
                if($boolchar == 1){
                    $keyword->content = preg_replace('/\[.*\]/', '', $keyword->content);
                    $keyword->content = preg_replace('/\[.*/', '', $keyword->content);
                    $keyword->content = str_replace('(', '', $keyword->content);
                    $keyword->content = str_replace(')', '', $keyword->content);
                    $keyword->content = str_replace('、', ',', $keyword->content);
                    $keyword->content = str_replace('，', ',', $keyword->content);
                    $keyword->content = str_replace('++', '+', $keyword->content);
                    $keyword->content = trim($keyword->content,'+');
                    $pluskeyword = array($keyword->content);
                    
                    if(strpos($keyword->content, '+') !== false){
                        $pluskeyword = explode('+', $keyword->content);
                    }
                    $finalArr = array();
                    $commacount = 0;
                    foreach($pluskeyword as $key => $commakeywords){
                        $commakeywordsArr = preg_split('/(,|\/)/', $commakeywords,-1, PREG_SPLIT_NO_EMPTY);
                        $pluskeyword[$key] = $commakeywordsArr;
                    }

                    $countpluskeyword = count($pluskeyword);
                    
                    $finalkeywordArr = [];
                    if($countpluskeyword==1){
                        $finalkeywordArr = $pluskeyword[0];
                    }
                    else{
                        for($i=0; $i<$countpluskeyword; $i++){
                            $countj = count($pluskeyword[$i]);
                            for($j=0;$j<$countj;$j++){
                                $countk = count($pluskeyword[$i+1]);
                                for($k=0;$k<$countk;$k++){
                                    $finalkeywordArr[$i][] = $pluskeyword[$i][$j].'-'.$pluskeyword[$i+1][$k];
                                }
                            }
                            $pluskeyword[$i+1] = $finalkeywordArr[$i];
                        }
                        $finalkeywordArr = end($finalkeywordArr);
                    }
                    foreach($finalkeywordArr as $fkeyword){
                        $fkeyword = explode('-', $fkeyword);
                        $totalchar = count($fkeyword);
                        $sentenceval = $sentence->sentence;
                        $cnt = 0;
                        for($i=0; $i<$totalchar; $i++){
                            if(mb_strpos($sentenceval, $fkeyword[$i]) !== false){
                                $cnt++;
                                $sentenceval = mb_substr($sentenceval, mb_strpos($sentenceval, $fkeyword[$i]) + 1);
                            }
                        }
                        if($cnt==$totalchar){
                            $GID[] = $keyword->id;
                            break;
                        }
                    }
                }
            }
            if(!empty($GID)){
                $selectQry = "SELECT content FROM {data_content} WHERE fieldid = 106 AND recordid = '$sentence->recordid'";
                $existingGid = $DB->get_record_sql($selectQry);
                $existingGid = $existingGid->content;
                
                if(strpos($existingGid,'*') === false){
                    $GID = implode(',', $GID);
                    if(intval($existingGid) > 0){
                        $GID = $GID.','.$existingGid;
                    }
                    $GID = explode(',', $GID);
                    $GID = array_unique($GID);
                    $GID = implode(',', $GID);
                    $sql = "UPDATE `{data_content}` SET `content` = ? WHERE `fieldid` = ? AND `recordid` = ?";
                    $updateClass = $DB->execute($sql, array($GID, 106, $sentence->recordid));
                }
            }else{
                $sql = "SELECT content FROM {data_content} WHERE fieldid = ? AND recordid = ?";
                $gidSql = $DB->get_record_sql($sql, array(106, $sentence->recordid));
                $GID = $gidSql->content;
            }
            $records = array();
            $records[] = $sentence->sentence;
            $records[] = '<span id="gid_'.$sentence->recordid.'">'.$GID.'</span>';
            if(strpos($GID, '*') !== false){
                $records[] = '';
            }else{
                $records[] = '<a href="javascript:void(0);" class="btn btn-primary editgid" id="editGID_'.$sentence->recordid.'" data-recordid="'.$sentence->recordid.'" data-ajaxUrl="'.$ajaxUrl.'">Edit</a>&nbsp;<a href="javascript:void(0);" class="btn btn-danger deleteSentenceGid" id="deleteSentenceGid_'.$sentence->recordid.'" data-recordid="'.$sentence->recordid.'" data-ajaxUrl="'.$ajaxUrl.'">Delete</a>';
            }
            
            $data[] = $records;
        }
        $recordsTotal = $recordsFiltered = $totalSentence->totalsentence;
        $json_data = array(
            "draw" => intval($_REQUEST['draw']),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $data
        );
        echo json_encode($json_data);
        die;
        break;
    case 'unmatchedSenteceFn':
        $totalKeywords = $DB->get_record_sql("SELECT count(c1.id) as total FROM {data_content} as c1 WHERE c1.fieldid = 99");
        // get sentence from sentence table
        $sql = "SELECT id, content FROM {data_content} WHERE fieldid = 106 and content != ''";
        $sentenceList = $DB->get_records_sql($sql);
        $gidStr = '';
        foreach($sentenceList as $key => $sentence){
            $gidStr .= $sentence->content.',';
        }
        $gidStr = trim($gidStr,',');
        $gidArr = explode(',', $gidStr);
        $gidArr = array_unique($gidArr);
        $gidStr = implode(',', $gidArr);
        
        break;
    case 'chhsklevelwords':
        $totalhsklevelwords = $DB->get_record_sql('SELECT count(r.id) AS id FROM `{data_records}` as r LEFT JOIN `{data_content}` as c ON r.id = c.recordid WHERE c.fieldid = 48 AND `r`.`dataid` = 5');
        $length = filter_input(INPUT_POST, 'length');
        $length = $length * 5;
        $offset = filter_input(INPUT_POST, 'start');
        $offset = $offset * 5;
        $limit = " LIMIT {$length} OFFSET {$offset}";
   
        $hskwordlist = $DB->get_records_sql("SELECT c.* FROM {data_content} AS c LEFT JOIN {data_records} AS r ON c.recordid = r.id WHERE r.dataid = 5 order by r.id $limit");
        
        $firstElement = array_key_first($hskwordlist);
        $lastElement = array_key_last($hskwordlist);
        $hskwordlist = array_chunk($hskwordlist, 5);
        $hskwordlist = array_values($hskwordlist);
        header("Content-Type: application/json");
        $data = array();
        foreach ($hskwordlist as $key => $word) {
            $records = array();
            $records[] = $word[0]->content . '<br><hr>' . $word[4]->content;
            $records[] = $word[2]->content;
            $records[] = $word[3]->content;
            $data[] = $records;
        }
        $recordsTotal = $recordsFiltered = $totalhsklevelwords->id;
        $json_data = array(
            "draw" => intval($_REQUEST['draw']),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "firstElement" => $firstElement,
            "lastElement" => $lastElement,
            "data" => $data
        );
        echo json_encode($json_data);
        die;
        break;

    case 'get_pinyin_with_tone':
        
        $searchVal = filter_input(INPUT_POST, 'pinyin');
        $pinyinData = $DB->get_records_sql("SELECT r.id, content, (select content FROM {data_content} as cc WHERE cc.fieldid = 14 AND cc.recordid = c.recordid) AS charcontent, (select RIGHT(content, 1) FROM {data_content} AS tn WHERE tn.fieldid = 28 AND tn.recordid = c.recordid) AS tonenumber FROM mdl_data_content AS c LEFT JOIN mdl_data_records AS r ON c.recordid = r.id WHERE (c.content LIKE '" . $searchVal . "1%' OR c.content LIKE '" . $searchVal . "2%' OR c.content LIKE '" . $searchVal . "3%' OR c.content LIKE '" . $searchVal . "4%' OR c.content LIKE '" . $searchVal . "5%' OR c.content LIKE '% " . $searchVal . "1%' OR c.content LIKE '% " . $searchVal . "2%' OR c.content LIKE '% " . $searchVal . "3%' OR c.content LIKE '% " . $searchVal . "4%' OR c.content LIKE '% " . $searchVal . "5%') AND c.fieldid = 28 AND r.dataid = 3");
        $pinyinArr = [];
        $i = 0;
        foreach ($pinyinData as $key => $value) {
            if (strpos($value->content, $searchVal) !== false) {
                $content = explode(' ', $value->content);
                if (!empty($content) && count($content) > 1) {
                    foreach ($content as $k => $v) {
                        $pinyinchar = preg_replace('/[0-9]+/', '', $v);
                        if ($pinyinchar == $searchVal) {
                            $tonenumber = substr($v, -1);
                            $pinyinArr[$tonenumber][]['charcontent'] = $value->charcontent;
                            $i++;
                        }
                    }
                } else {
                    $pinyinArr[$value->tonenumber][]['charcontent'] = $value->charcontent;
                    $i++;
                }
            }
        }
        ksort($pinyinArr);
        
        $html = <<<HTML
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Tone 1</th>
                    <th>Tone 2</th>
                    <th>Tone 3</th>
                    <th>Tone 4</th>
                    <th>Tone 5</th>
                </tr>
            </thead>
            <tbody>
HTML;
                if (!array_key_exists('1', $pinyinArr)) {
                    $pinyinArr['1'][] = array('charcontent' => 'X');
                }
                if (!array_key_exists('2', $pinyinArr)) {
                    $pinyinArr['2'][] = array('charcontent' => 'X');
                }
                if (!array_key_exists('3', $pinyinArr)) {
                    $pinyinArr['3'][] = array('charcontent' => 'X');
                }
                if (!array_key_exists('4', $pinyinArr)) {
                    $pinyinArr['4'][] = array('charcontent' => 'X');
                }
                if (!array_key_exists('5', $pinyinArr)) {
                    $pinyinArr['5'][] = array('charcontent' => 'X');
                }

                $maxlen = count(max($pinyinArr));
                for ($i = 0; $i < $maxlen; $i++) {
                    $html .= '<tr>';
                    for ($j = 1; $j <= 5; $j++) {
                        if (isset($pinyinArr[$j][$i]['charcontent'])) {
                            $chineseChar = $pinyinArr[$j][$i]['charcontent'];
                            $mp4file = $CFG->wwwroot . '/characterdata/' . $chineseChar . '.mp4';
                            if (!file_exists($CFG->characterdata . '/' . $chineseChar . '.mp4')) {
                                $mp4file = $CFG->wwwroot . '/characterdata/' . $chineseChar . '-多音字.mp4';
                            }
                            $html .= '<td><a href="' . $mp4file . '" target="_blank">' . $chineseChar . '</a></td>';
                        } else {
                            $html .= '<td></td>';
                        }
                    }
                    $html .= '</tr>';
                }
        $html .= <<<HTML
            </tbody>
        </table>
HTML;
        echo $html;
        die;
        break;
        
    case 'get_character_multiplepinyin':
        
        $searchVal = filter_input(INPUT_POST, 'pinyin');
        $sql = "SELECT r.id, content, (select content FROM {data_content} as cc WHERE cc.fieldid = 14 AND cc.recordid = c.recordid) AS charcontent, (select content FROM {data_content} AS tn WHERE tn.fieldid = 28 AND tn.recordid = c.recordid) AS pinyin FROM {data_content} AS c LEFT JOIN {data_records} AS r ON c.recordid = r.id WHERE (c.content LIKE '".$searchVal."1%' OR c.content LIKE '".$searchVal."2%' OR c.content LIKE '".$searchVal."3%' OR c.content LIKE '".$searchVal."4%' OR c.content LIKE '".$searchVal."5%' OR c.content LIKE '% ".$searchVal."1%' OR c.content LIKE '% ".$searchVal."2%' OR c.content LIKE '% ".$searchVal."3%' OR c.content LIKE '% ".$searchVal."4%' OR c.content LIKE '% ".$searchVal."5%') AND c.recordid IN (SELECT r1.id FROM {data_content} AS c1 LEFT JOIN {data_records} AS r1 ON c1.recordid = r1.id WHERE c1.fieldid = '29' AND c1.content > 0 AND r1.dataid = 3) AND c.fieldid = 28 AND r.dataid = 3";    
        $pinyinData = $DB->get_records_sql($sql);
        echo '<table class="table table-bordered table-striped">';
        echo '<thead>';
            echo '<tr align="center">';
            echo '<th width="20%">#</th>';
            echo '<th width="40%">Pinyin</th>';
            echo '<th width="40%">Chinese Character</th>';

            echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        $cnt = 0;
        foreach($pinyinData as $key => $value){
            if(strpos($value->pinyin,$searchVal) !== false){
                $cnt++;
                echo '<tr align="center">';
                echo '<td>'.$cnt.'</td>';
                echo '<td class="pinyinval">'.$value->pinyin.'</td>';
                $mp4file = $CFG->wwwroot.'/characterdata/'.$value->charcontent.'.mp4';
                if(!file_exists($CFG->characterdata.'/'.$value->charcontent.'.mp4')){
                    $mp4file = $CFG->wwwroot.'/characterdata/'.$value->charcontent.'-'.'多音字'.'.mp4';
                }
                echo '<td><a href="'.$mp4file.'" target="_blank">'.$value->charcontent.'</a></td>';
                echo '</tr>';
            }
        }
        echo '</tbody>';
        echo '</table>';
        die;
        break;
    case 'updateword':
        $recordid = filter_input(INPUT_POST, 'recordid');
        $pinyin = filter_input(INPUT_POST, 'pinyin');
        $meaning = filter_input(INPUT_POST, 'meaning');
        $classVal = filter_input(INPUT_POST, 'classVal');
        
        $pinyinsql = "UPDATE {data_content} SET `content` = \"{$pinyin}\" WHERE `fieldid` = 49 AND `recordid` = $recordid";
        $DB->execute($pinyinsql);
        $meaningsql = 'UPDATE {data_content} SET `content` = ? WHERE `fieldid` = ? AND `recordid` = ?';
        try {
            $DB->execute($meaningsql, array($meaning, 50, $recordid));
        } catch (Exception $exc) {
            echo $exc->getMessage();
        }
        $classSql = "UPDATE {data_content} SET `content` = ? WHERE `fieldid` = ? AND `recordid` = ?";
        
        try {
            $DB->execute($classSql, array($classVal, 74, $recordid));
        } catch (Exception $exc) {
            echo $exc->getMessage();
        }
        die;
        break;
    case 'updatekeywords':
        $recordid = filter_input(INPUT_POST, 'recordid');
        $meaning = filter_input(INPUT_POST, 'meaning');
        $hsk = filter_input(INPUT_POST, 'hsk');
        $classVal = filter_input(INPUT_POST, 'classVal');
        $meaningsql = "UPDATE {data_content} SET `content` = ? WHERE `fieldid` = ? AND `recordid` = ?";
        try {
            $DB->execute($meaningsql, array($meaning, 88, $recordid));
        } catch (Exception $exc) {
            echo $exc->getMessage();
        }
        $hsksql = "UPDATE {data_content} SET `content` = '$hsk' WHERE `fieldid` = 90 AND `recordid` = $recordid";
        $updateHsk = $DB->execute($hsksql);
        $classSql = "UPDATE {data_content} SET `content` = '$classVal' WHERE `fieldid` = 91 AND `recordid` = $recordid";
        $updateHsk = $DB->execute($classSql);
        die;
        break;
    case 'updatephrase':
        $recordid = filter_input(INPUT_POST, 'recordid');
        $phrase = filter_input(INPUT_POST, 'phrase');
        $phraseSql = "UPDATE {data_content} SET `content` = '$phrase' WHERE `fieldid` = 105 AND `recordid` = $recordid";
        $DB->execute($phraseSql);
        die;
        break;
    case 'updategid':
        $recordid = filter_input(INPUT_POST, 'recordid');
        $gid = filter_input(INPUT_POST, 'gid');
        $gidSql = "UPDATE {data_content} SET `content` = ? WHERE `fieldid` = ? AND `recordid` = ?";
        $DB->execute($gidSql,array($gid,106,$recordid));
        echo $gidSql;
        die;
        break;
    case 'deletekeyword':
        $recordid = filter_input(INPUT_POST, 'recordid');
        $contentDelete = "DELETE FROM `{data_content}` WHERE `recordid` = $recordid";
        $DB->execute($contentDelete);
        $recordsDelete = "DELETE FROM `{data_records}` WHERE `id` = $recordid";
        $DB->execute($recordsDelete);
        die;
        break;
    case 'deletegid':
        $recordid = filter_input(INPUT_POST, 'recordid');
        $gid = filter_input(INPUT_POST, 'gid');
        $sql = "SELECT content FROM {data_content} WHERE fieldid = ? AND recordid = ?";
        $gidVal = $DB->get_record_sql($sql, array(106, $recordid));
        $gidArr = explode(',', $gidVal->content);
        if (($key = array_search($gid, $gidArr)) !== false) {
//            unset($gidArr[$key]);
        }
        $gids = implode(',', $gidArr);
        $gids = '*'.$gids;
        $gidUpdate = "UPDATE {data_content} SET `content` = ? WHERE `fieldid` = ? AND `recordid` = ?";
        $DB->execute($gidUpdate,array($gids,106,$recordid));
        echo $gids;
        die;
    case 'deleteSentenceGid':
        $recordid = filter_input(INPUT_POST, 'recordid');
        $gidDelete = "UPDATE {data_content} SET `content` = ? WHERE `fieldid` = ? AND `recordid` = ?";
        $DB->execute($gidDelete,array('',106,$recordid));
        die;
        break;
    default:
        break;
}