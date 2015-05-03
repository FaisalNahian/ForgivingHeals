<?php
    global $forgivingheals_comment,$current_user;
?>
<li data-id="<?php echo $forgivingheals_comment->comment_ID ?>">
    <div class="row">
        <div class="col-md-3 col-xs-3">
            <a href="javascript:void(0)" class="avatar-cmt-in-cmt">
                <?php echo $forgivingheals_comment->avatar; ?>
            </a>
            <p class="cmt-author">
                <a href="<?php echo $forgivingheals_comment->author_url ?>"  title="<?php echo $forgivingheals_comment->author ?>">
                    <?php echo $forgivingheals_comment->author ?>                
                </a>
            </p>
        </div>
        <div class="col-md-9 col-xs-9">
            <div class="content-cmt-in-cmt">
            <?php echo  $forgivingheals_comment->content_filter ?>
            <span class="time-cmt-in-cmt"><?php echo $forgivingheals_comment->human_date ?>. <!-- <a href="javascript:void(0)">Edit&nbsp;&nbsp;<i class="fa fa-pencil"></i></a> --></span>
            </div>
        </div>
        <div class="col-md-12">
            <div class="clearfix"></div>
            <div class="line" style="width:90%;"></div>
        </div>
    </div>
</li>