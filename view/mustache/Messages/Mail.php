<?php global $email, $subject, $message;
$quote = array_rand($quotes = [
    'C.S. Lewis' => 'You are never too old to set another goal or to dream a new dream.',
    'James Dean' => 'Dream as if you’ll live forever, live as if you’ll die today.',
    'Lupita Nyong’o' => 'No matter where you’re from, your dreams are valid.',
    'Johann Wolfgang von Goethe' => 'What is not started today is never finished tomorrow.',
]);
?>
<!-- Main content -->
<section class="content">

    <div class="row" style="margin-top: 30px">
        <!-- /.col -->
        <div class="col-md-offset-1 col-md-10">
            <!-- Alert -->
            <div id="alert"></div>

            <div class="box box-danger">
                <form data-pjax action="<?= SITE ?>Mail" method="post">
                    <div class="box-header with-border bg-red-active">
                        <h3 class="box-title text-white"><b>"<?= $quotes[$quote] ?>"</b>
                            <small class="text-black">- <?= $quote ?></small>
                        </h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <div class="form-group">
                            <input class="form-control" placeholder="To:" value="Richard@Miles.Systems" disabled>
                        </div>
                        <div class="form-group">
                            <input class="form-control" placeholder="Your Email:" value="<?= $email ?>" name="email">
                        </div>
                        <div class="form-group">
                            <input class="form-control" placeholder="Subject:" value="<?= $subject ?>" name="subject">
                        </div>
                        <div class="form-group">
                    <textarea id="compose-textarea" class="form-control" style="height: 300px" name="message">
                        <?= $message ?>
                    </textarea>
                        </div>
                        <div class="form-group">
                            <div class="btn btn-default btn-file">
                                <i class="fa fa-paperclip"></i> Attachment
                                <input type="file" name="attachment">
                            </div>
                            <p class="help-block">Max. 32MB</p>
                        </div>
                    </div>
                    <!-- /.box-body -->
                    <div class="box-footer">
                        <div class="pull-right">
                            <button type="button" class="btn btn-default"><i class="fa fa-pencil"></i> Draft</button>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-envelope-o"></i> Send</button>
                        </div>
                        <button type="reset" class="btn btn-default"><i class="fa fa-times"></i> Discard</button>
                    </div>
                    <!-- /.box-footer -->
                </form>
            </div>
            <!-- /. box -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
</section>
<!-- /.content -->
<script>
    Carbon(() => $.fn.load_wysihtml5("#compose-textarea"));
</script>