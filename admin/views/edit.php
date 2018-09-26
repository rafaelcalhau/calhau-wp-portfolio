<div class="wrap calhau-portfolio-wrapper">

    <div class="ui grid">
        <div class="ten wide column">
            <h2 class="ui header">
                <span class="big dashicons dashicons-laptop"></span>
                <div class="content">
                    <?php echo esc_html( get_admin_page_title() ); ?>
                    <div class="sub header">Editing your portfolio's item "<?=$item->project_name?>"</div>
                </div>
            </h2>
        </div>

        <div class="six wide column">
            <div class="ui tiny breadcrumb pull-right">
                <a href="?page=<?=$page?>" class="section">
                    <i class="ui fitted home icon"></i>
                    Manage Portfolio
                </a>
                <i class="right angle icon divider"></i>
                <div class="active section">Item: <?=$item->project_name?></div>
            </div>
        </div>
    </div>

    <div class="ui divider"></div>

    <?php 
       if( isset( $formProcessed ) and $formProcessed == 'ok' ) {
            echo '<div class="ui positive message">';
            echo '  <i class="close icon is-dismissable"></i>';
            echo '  <div class="header">';
            echo '      Item added';
            echo '  </div>';
            echo '  <p>Your portfolio item is already available from your website.</p>';
            echo '</div>';
        }
    ?>

    <form id="formPortfolio" action="admin-post.php" method="post" class="ui large calhau form">

        <div class="ui grid">
            <div class="six wide column">
                <div class="sixteen wide column portfolio-upload">
                    <?php if(isset($item->filename) and is_file('../uploads/portfolio/large/'. $item->filename)): ?>
                        <img id="portfolioImage" src="../uploads/portfolio/large/<?=$item->filename?>" class="ui top aligned fluid image">
                    <?php else: ?>
                        <img src="<?=$pluginPath?>admin/images/picture.png" class="ui top aligned fluid image">
                    <?php endif; ?>
                </div>
                <div class="sixteen wide column">
                    <label for="file" class="ui icon big  fluid button">
                        <i class="upload icon"></i> Select the image
                        <input type="file" name="file" id="file" style="display: none">
                        <input type="hidden" name="file_data" id="file_data">
                        <input type="hidden" name="file_info" id="file_info">
                        <input type="hidden" name="file_current" id="file_current" value="<?=$item->filename?>">
                    </label>
                </div>
            </div>
            <div class="ten wide column">
                <div class="ui grid">
                    <label class="four wide styled column">Project's Name</label>
                    <div class="twelve wide column">
                        <div class="ui fluid input">
                            <input type="text" name="project_name" id="project_name" maxlength="45" value="<?=$item->project_name?>">
                        </div>
                    </div>

                    <label class="four wide styled column">Project's URL</label>
                    <div class="twelve wide column">
                        <div class="ui fluid left icon input">
                            <i class="world icon"></i>
                            <input type="text" name="project_url" id="project_url" maxlength="45" value="<?=$item->project_url?>">
                        </div>
                    </div>

                    <label class="four wide styled column">Resume</label>
                    <div class="twelve wide column">
                        <div class="field">
                            <textarea name="short_description" id="short_description" rows="4"><?=$item->resume?></textarea>
                        </div>
                    </div>

                    <label class="four wide styled column">
                        Position
                    </label>
                    <div class="four wide column">
                        <div class="ui fluid input">
                            <input type="number" min="0" max="9999999999" name="ordering" id="ordering" maxlength="10" value="0">
                        </div>
                    </div>

                    <label class="three wide styled column">
                        Publish Date
                    </label>
                    <div class="five wide column">
                        <div class="ui fluid input">
                            <input type="date" name="published_at" id="published_at" maxlength="10" value="<?=$item->published_at?>">
                        </div>
                    </div>
                </div>
            </div>

            <label>Description</label>
            <div class="sixteen wide column">
                <div class="field">
                    <textarea name="description" id="description" rows="10" class="tinymce"><?=stripslashes($item->description)?></textarea>
                </div>
            </div>

            <input type="hidden" name="action" value="calhau_portfolio_update_item">
            <input type="hidden" name="calhau_add_meta_form_nonce" value="<?=$nonce?>">
            <input type="hidden" name="item_id" value="<?=$item->id?>">

            <div class="sixteen wide column">
                <button type="submit" class="ui big black labeled icon button pull-right">
                    <i class="save icon"></i> Save
                </button>
            </div>
        </div>

    </form>
    
</div>

<!-- jCrop -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-jcrop/2.0.4/css/Jcrop.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-jcrop/2.0.4/js/Jcrop.min.js"></script>

<!-- TinyMCE -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/4.8.3/tinymce.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/4.8.3/themes/modern/theme.min.js"></script>

<script>
   $(function(){
        var file = $('#file'),
            fileData = $('#file_data'),
            fileInfo = $('#file_info'),
            form = $('#formPortfolio'),
            hasJcropInitiated = false,
            image = $('#portfolioImage'),
            jcrop_api,
            projectName = $('#project_name'),
            projectUrl = $('#project_url'),
            projectShortDescription = $('#short_description'),
            publishedAt = $('#published_at');
        
        $('.menu .item').tab();

        $('.is-dismissable').on('click', function(e) {
            $(this).parent().fadeOut()
        })

        tinymce.init({
            selector: 'textarea.tinymce',
            height: 300,
            menubar: false,
            plugins: [
                'advlist autolink lists link image charmap print preview anchor textcolor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table contextmenu paste code help wordcount'
            ],
            toolbar: 'insert | undo redo |  formatselect | bold italic backcolor  | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
            content_css: ['//fonts.googleapis.com/css?family=Lato:300,300i,400,400i']
        });


        file.on('change', function() {
            readFile(this);
        });

        form.on('submit', function(e) {
            if(file.val() === "") {
                alertify.alert("Please select an image in your device.");
                return false;
            }
            else if(fileData.val() !== "" && fileInfo.val() === "") {
                alertify.alert("Please select the cropping area for your image.");
                return false;
            }
            else if(projectName.val().length <= 1) {
                alertify.alert("Please enter your project's name.");
                return false;
            }
            else if(projectUrl.val().length <= 1) {
                alertify.alert("Please enter your project's url.");
                return false;
            }
            else if(projectShortDescription.val().length <= 1) {
                alertify.alert("Please enter a short description for your project.");
                return false;
            }
            else if(publishedAt.val().length != 10) {
                alertify.alert("Please enter a date about when your project was published.");
                return false;
            }
        });
        
        function readFile(input)
        {
            if(input.files && input.files[0]) {
                var reader = new FileReader();

                reader.readAsDataURL(input.files[0]);
                reader.onload = function(e) {

                    if(hasJcropInitiated) {
                        jcrop_api.destroy();
                    }

                    image
                        .attr('src', e.target.result)
                        .Jcrop({
                            aspectRatio: 16 / 10,
                            onSelect: showCoords
                        }, function(){

                            hasJcropInitiated = true;
                            jcrop_api = this;
                            
                        });
                    
                    fileData.val(e.target.result);

                }
            }
        }

        function showCoords(c)
        {
            console.log("data: "+ JSON.stringify(c));
            fileInfo.val(JSON.stringify(c));
        };
    })
</script>