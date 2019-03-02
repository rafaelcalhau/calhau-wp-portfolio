<div class="wrap calhau-portfolio-wrapper">

    <div class="ui grid">
        <div class="ten wide column">
            <h2 class="ui header">
                <span class="big dashicons dashicons-laptop"></span>
                <div class="content">
                    <?php echo esc_html( get_admin_page_title() ); ?>
                    <div class="sub header">Manage your portfolio items with ease.</div>
                </div>
            </h2>
        </div>

        <div class="six wide column">
            <div class="ui tiny breadcrumb pull-right">
                <a href="?page=<?=$page?>" class="section">
                    <i class="ui fitted home icon"></i>
                    Manage Portfolio
                </a>
            </div>
        </div>
    </div>

    <div class="ui divider"></div>

    <?php 
        if( isset( $del ) and $del === true ) {
            echo '<div class="ui positive message">';
            echo '  <i class="close icon is-dismissable"></i>';
            echo '  <div class="header">';
            echo '      Item added';
            echo '  </div>';
            echo '  <p>Your portfolio item is already available from your website.</p>';
            echo '</div>';
        }
    ?>

    <div class="ui top attached tabular menu">
        <a class="item active" data-tab="first">My items</a>
        <a class="item" data-tab="second">Add item</a>
    </div>
    <div class="ui bottom attached tab segment active" data-tab="first">

        <div class="ui three column grid">
        
            <?php if(isset($items) and is_array($items) and count($items) > 0): ?>
            <?php foreach($items as $item): ?>
        
            <div class="column">
                <div class="ui fluid portfolio card">
                    <div class="image">
                        <?php if(isset($item->filename) and is_file('../uploads/portfolio/normal/'. $item->filename)): ?>
                        <img src="../uploads/portfolio/normal/<?=$item->filename?>" style="width: 100%">
                        <?php endif; ?>
                    </div>
                    <div class="content">
                        <small class="meta">
                            <span class="date"><?= date('Y, d/m', strtotime($item->published_at)) ?></span>
                            <span class="date pull-right">Added in <?= date('Y, d/m', strtotime($item->created_at)) ?>
                        </small>

                        <a class="header">
                            <?= isset($item->project_name) ? $item->project_name : "Project not loaded..." ?>
                        </a>
                        
                        <div class="description">
                            <?= $item->short_description ?>
                        </div>
                    </div>
                    <div class="extra content">
                        <a href="?page=<?=$page?>&action=edit&item_id=<?=$item->id?>" class="ui basic left floated button">
                            <i class="edit icon"></i> Edit
                        </a>
                        <a href="?page=<?=$page?>&action=manage&item_id=<?=$item->id?>&del=true" class="ui basic left floated bt-delete button">
                            <i class="trash icon"></i> Delete
                        </a>
                        <div class="right floated author">
                            <i class="world icon"></i>
                            <?= $item->visits ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php endforeach; ?>
            <?php else: ?>

            <div class="ui warning message">
                <div class="header">
                    <i class="info icon"></i>
                    There is no itens in your portfolio.
                </div>
            </div>

            <?php endif; ?>
            
        </div>

    </div>
    <div class="ui bottom attached tab segment" data-tab="second">
        
        <form id="formPortfolio" action="admin-post.php" method="post" class="ui large calhau form">

            <div class="ui grid">
                <div class="six wide column">
                    <div class="sixteen wide column portfolio-upload">
                        <img id="portfolioImage" src="<?=$pluginPath?>admin/images/picture.png" 
                            class="ui top aligned fluid image">
                    </div>
                    <div class="sixteen wide column">
                        <label for="file" class="ui icon big  fluid button">
                            <i class="upload icon"></i> Select the image
                            <input type="file" name="file" id="file" style="display: none">
                            <input type="hidden" name="file_data" id="file_data">
                            <input type="hidden" name="file_info" id="file_info">
                        </label>
                    </div>
                </div>
                <div class="ten wide column">
                    <div class="ui grid">
                        <label class="four wide styled column">Project's Name</label>
                        <div class="twelve wide column">
                            <div class="ui fluid input">
                                <input type="text" name="project_name" id="project_name" maxlength="45">
                            </div>
                        </div>

                        <label class="four wide styled column">Project's URL</label>
                        <div class="twelve wide column">
                            <div class="ui fluid left icon input">
                                <i class="world icon"></i>
                                <input type="text" name="project_url" id="project_url" maxlength="45">
                            </div>
                        </div>

                        <label class="four wide styled column">Short description</label>
                        <div class="twelve wide column">
                            <div class="field">
                                <textarea name="short_description" id="short_description" rows="4"></textarea>
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
                                <input type="date" name="published_at" id="published_at" maxlength="10">
                            </div>
                        </div>
                    </div>
                </div>

                <label>Description</label>
                <div class="sixteen wide column">
                    <div class="field">
                        <textarea name="description" id="description" rows="10" class="tinymce"></textarea>
                    </div>
                </div>

                <input type="hidden" name="action" value="calhau_portfolio_new_item">
                <input type="hidden" name="calhau_add_meta_form_nonce" value="<?=$nonce?>">

                <div class="sixteen wide column">
                    <button type="submit" class="ui big black labeled icon button pull-right">
                        <i class="save icon"></i> Save
                    </button>
                </div>
            </div>

        </form>

    </div>

    
</div>

<!-- Alertify -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/AlertifyJS/1.11.1/css/alertify.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/AlertifyJS/1.11.1/css/themes/semantic.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/AlertifyJS/1.11.1/alertify.min.js"></script>

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

        $('.bt-delete').on('click', function(e) {
            e.preventDefault();

            var url = $(this).attr('href');

            alertify.confirm(
                'Confirmation', 'Are you sure about delete this item from your Portfolio?',
                function() {
                    location.href = url;
                },
                function() {}
            );
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
            fileInfo.val(JSON.stringify(c))
        }
    })
</script>