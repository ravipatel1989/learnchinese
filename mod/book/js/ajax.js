jQuery(document).ready(function($){
   $(document).on('click','.updatepinyin',function(){
        var ele = $(this);
        var pinyin = ele.closest('tr').find('input').val();
        var rid = ele.data('id');
        var url = ele.data('ajaxurl');
        $.ajax({
            type: "POST",
            url: url,
            data: { action:'update_pinyin', rid: rid, pinyin: pinyin },
            beforeSend: function(){
                ele.html('Update&nbsp;<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
            },
            success: function(data){
                var obj = JSON.parse(data);
                if(obj.response == "success"){
                    ele.closest('tr').find('td.pinyinval').text(obj.content);
                }
            },
            complete: function(){
                ele.text('Edit').removeClass('btn-success updatepinyin').addClass('btn-primary editpinyin');
            }
        });
    }); 
    $("#updateradicals").click(function(){
        var pagenum = $(this).data('page');
        var ajaxurl = $(this).data('ajaxurl');
        $.ajax({
            type:"POST",
            url: ajaxurl,
            data:{ action:'update_radicals', page:pagenum},
            beforeSend: function(){
                $("#updateradicals").html('Update&nbsp;<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
            },
            success: function(data){
                var obj = JSON.parse(data);
                $.each(obj, function(key, value){
                    $('span#'+key).text(value);
                });
                $(".box.alert.alert-success.msgdiv").remove();
                $('<div data-rel="success" class="box alert alert-success msgdiv"><p style="margin-bottom:0;">Character counts updated successfull.</p></div>').insertBefore("#updateradicals");
                setTimeout(function(){
                    $('.msgdiv').fadeOut();
                },3000);
            },
            complete: function(){
                $("#updateradicals").text('Update');
            }
        });
    });
    $(".pinyinchar").click(function(){
        var pinyin = $(this).data('pinyin');
        var ajaxurl = $(this).data('ajaxurl');
        var action = $(this).data('action');
        $.ajax({
            type:"POST",
            url: ajaxurl,
            data:{ action:action, pinyin:pinyin},
            beforeSend: function(){
                $(".modal-body").html('');
                $(".modal-title").text(pinyin);
            },
            success: function(data){
                $(".modal-body").html(data);
            },
            complete: function(){
                
            }
        });
    });
    $(document).on('click','.deletekeyword, .deleteword',function(){
        if(confirm('Are you sure to delete this record?')){
            var recordid = $(this).data('recordid');
            var ajaxurl = $('#deletekeyword_'+recordid).attr('data-ajaxurl');
            $.ajax({
                type:"POST",
                url: ajaxurl,
                data:{ action:'deletekeyword', recordid:recordid},
                success: function(data){
                    $('tr#delete_'+recordid).remove();
                },
                complete: function(){

                }
            });
        }
    });
    $(document).on('click','.editphrasecount',function(){
        var recordid = $(this).data('recordid');
        var phrase = $('#phrase_'+recordid).text();
        $(this).hide();
        $(this).after('<a href="javascript:void(0);" class="btn btn-primary updatephrase" data-recordid="'+recordid+'">Update</a>');
        $('#phrase_'+recordid).html('<input type="text" name="phrase_'+recordid+'" value="'+phrase+'" />');
    });
    $(document).on('click','.updatephrase',function(){
        var updatelink = $(this);
        var recordid = $(this).attr('data-recordid');
        var phrase = $('input[name=phrase_'+recordid+']').val();
        var ajaxurl = $('#editphrasecount_'+recordid).attr('data-ajaxurl');
        $.ajax({
            type:"POST",
            url: ajaxurl,
            data:{ action:'updatephrase', recordid:recordid, phrase:phrase},
            success: function(data){
                $('input[name=phrase_'+recordid+']').remove();
                $('td#phrase_'+recordid).text(phrase);
            },
            complete: function(){
                updatelink.remove();
                $('a#editphrasecount_'+recordid).show();
            }
        });
    });
    $(document).on('click','.editword',function(){
        var recordid = $(this).data('recordid');
        var pinyin = $('#pinyin_'+recordid).text();
        var meaning = $('#meaning_'+recordid).text();
        var classVal = $('#class_'+recordid).text();
        $(this).hide();
        $(this).after('<a href="javascript:void(0);" class="btn btn-primary updateword" data-recordid="'+recordid+'">Update</a>');
        $('#pinyin_'+recordid).html('<input type="text" name="pinyin_'+recordid+'" value="'+pinyin+'" />');
        $('#meaning_'+recordid).html('<input type="text" name="meaning_'+recordid+'" style="max-width:340px;" value="'+meaning+'" />');
        $('#class_'+recordid).html('<input type="text" name="class_'+recordid+'" value="'+classVal+'" />');
    });
    $(document).on('click','.updateword',function(){
        var updatelink = $(this);
        var recordid = $(this).attr('data-recordid');
        var pinyin = $('input[name=pinyin_'+recordid+']').val();
        var meaning = $('input[name=meaning_'+recordid+']').val();
        var classVal = $('input[name=class_'+recordid+']').val();
        var ajaxurl = $('#editword_'+recordid).attr('data-ajaxurl');
        $.ajax({
            type:"POST",
            url: ajaxurl,
            data:{ action:'updateword', recordid:recordid, pinyin:pinyin, meaning:meaning, classVal:classVal},
            success: function(res){
                $('input[name=pinyin_'+recordid+']').remove();
                $('input[name=meaning_'+recordid+']').remove();
                $('input[name=class_'+recordid+']').remove();
                $('td#pinyin_'+recordid).text(pinyin);
                $('td#meaning_'+recordid).text(meaning);
                $('td#class_'+recordid).text(classVal);
            },
            complete: function(){
                updatelink.remove();
                $('a#editword_'+recordid).show();
            }
        });
    });
    $(document).on('click','.editkeyword',function(){
        var recordid = $(this).data('recordid');
        var meaning = $('#meaning_'+recordid).text();
        var hsk = $('#hsk_'+recordid).text();
        var classVal = $('#class_'+recordid).text();
        $(this).hide();
        $(this).after('<a href="javascript:void(0);" class="btn btn-primary updatekeyword" data-recordid="'+recordid+'">Update</a>');
        $('#meaning_'+recordid).html('<input type="text" name="meaning_'+recordid+'" value="'+meaning+'" />');
        $('#hsk_'+recordid).html('<input type="text" name="hsk_'+recordid+'" value="'+hsk+'" />');
        $('#class_'+recordid).html('<input type="text" name="class_'+recordid+'" value="'+classVal+'" />');
    });
    $(document).on('click','.deletegid',function(){
        var recordid = $(this).attr('data-recordid');
        var gid = $(this).attr('data-gid');
        var ajaxurl = $(this).attr('data-ajaxurl');
        $.ajax({
            type:'POST',
            url:ajaxurl,
            data:{action:'deletegid', recordid: recordid, gid: gid},
            success: function(res){
                $('#gid_'+recordid).text(res);
            }
        });
    });
    $(document).on('click','.deleteSentenceGid',function(){
        var recordid = $(this).attr('data-recordid');
        var gid = $('#gid_'+recordid).text();
        var ajaxurl = $(this).attr('data-ajaxurl');
        if(gid.indexOf('*') !== -1){
            alert('This record can not delete.');
            return false;
        }
        if(confirm('Are you sure to delete GID?')){
            $.ajax({
                type:'POST',
                url:ajaxurl,
                data:{action:'deleteSentenceGid', recordid: recordid, gid: gid},
                success: function(res){
                    $('#gid_'+recordid).text('');
                }
            });
        }
    });

    $(document).on('click','.editgid',function(){
        var recordid = $(this).attr('data-recordid');
        var gid = $('#gid_'+recordid).text();
        if(gid.indexOf('*') !== -1){
            alert('This record can not update.');
            return false;
        }
        $(this).hide();
        $(this).after('<a href="javascript:void(0);" class="btn btn-primary updategid" data-gid="'+gid+'" data-recordid="'+recordid+'">Update</a>');
        $('#gid_'+recordid).html('<input type="text" name="gid_'+recordid+'" value="'+gid+'" />');
    });
    $(document).on('click','.updategid',function(){
        var updategid = $(this);
        var recordid = $(this).attr('data-recordid');
        var gid = $('input[name=gid_'+recordid+']').val();
        var ajaxurl = $('#editGID_'+recordid).attr('data-ajaxurl');
        $.ajax({
            type:"POST",
            url: ajaxurl,
            data:{ action:'updategid', recordid:recordid, gid:gid},
            success: function(data){
                $('input[name=gid_'+recordid+']').remove();
                $('span#gid_'+recordid).text(gid);
            },
            complete: function(){
                updategid.remove();
                $('a#editGID_'+recordid).show();
            }
        });
    });
    $(document).on('click','.updatekeyword',function(){
        var updatelink = $(this);
        var recordid = $(this).attr('data-recordid');
        var meaning = $('input[name=meaning_'+recordid+']').val();
        var hsk = $('input[name=hsk_'+recordid+']').val();
        var classVal = $('input[name=class_'+recordid+']').val();
        var ajaxurl = $('#editkeyword_'+recordid).attr('data-ajaxurl');
        $.ajax({
            type:"POST",
            url: ajaxurl,
            data:{ action:'updatekeywords', recordid:recordid, meaning:meaning, hsk:hsk, classVal:classVal},
            success: function(data){
                $('input[name=meaning_'+recordid+']').remove();
                $('input[name=hsk_'+recordid+']').remove();
                $('input[name=class_'+recordid+']').remove();
                $('td#meaning_'+recordid).text(meaning);
                $('td#hsk_'+recordid).text(hsk);
                $('td#class_'+recordid).text(classVal);
            },
            complete: function(){
                updatelink.remove();
                $('a#editkeyword_'+recordid).show();
            }
        });
    });
});