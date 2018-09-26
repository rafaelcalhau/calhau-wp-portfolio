<?php

// Including Autoloader
$path = str_replace( ["\includes", "/includes"], "", __DIR__ );
require $path . "/vendor/autoload.php";

use WideImage\WideImage;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class CalhauPortfolio
{

    protected $upload_path;

    public function __construct(){
        $this->upload_path = ABSPATH . "uploads/portfolio/";
    }

    /**
     * Run on plugin activation process
     *
     * @return void
     */
    public function activate()
    {
        $this->createPluginTables();
    }

    /**
     * Run on plugin deactivation process
     *
     * @return void
     */
    public function deactivate()
    {

    }

    /**
     * Run all plugin's actions and filters
     *
     * @return void
     */
    public function run()
    {

        // add a menu item to admin's sidebar
        add_action( "admin_menu", [$this, "addMenu"] );

        // add action for managing data from new portfolio's item
        add_action( "admin_post_calhau_portfolio_new_item", [$this, "item_store"] );

        // add action for editting item's data from portfolio
        add_action( "admin_post_calhau_portfolio_update_item", [$this, "item_update"] );

        // loads the admin styles for this plugin
        self::enqueueAdminStyle();

        // loads the public styles for this plugin
        self::enqueuePublicStyle();

        // handles the portfolio's shortcode for giving life to the portfolio on front-end
        add_filter( "the_content", [$this, "handleShortcode"] );
        
    }

    /**
     * Add a menu item to WordPress's Admin Sidebar
     *
     * @return void
     */
    public function addMenu()
    {
        add_menu_page(
            "My Portfolio",
            "My Portfolio",
            "manage_options",
            "calhau-portfolio",
            "calhau_portfolio_main_page",
            "dashicons-laptop",
            2
        );
    }

    /**
     * Creates all portfolio's tables
     *
     * @return void
     */
    public function createPluginTables()
    {
        global $wpdb;

        $charsetCollate = $wpdb->get_charset_collate();
        $tablePortfolio = CALHAU_TBL_PORTFOLIO;

        // Checking existence
        $tableExists = $wpdb->get_var("SHOW TABLES LIKE '{$tablePortfolio}'");
        if($tableExists == "") {

            try {

                $wpdb->query(
                    "CREATE TABLE IF NOT EXISTS `{$tablePortfolio}` (".
                    "   `id` INT(10) NOT NULL AUTO_INCREMENT,".
                    "   `project_name` VARCHAR(45) NOT NULL,".
                    "   `project_url` VARCHAR(45) NULL,".
                    "   `short_description` VARCHAR(255) NULL,".
                    "   `description` TEXT NULL,".
                    "   `ordering` INT(10) NOT NULL DEFAULT '0',".
                    "   `visits` INT(10) NOT NULL DEFAULT '0',".
                    "   `published_at` DATE NULL,".
                    "   `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),".
                    "   `updated_at` DATETIME NULL,".
                    "   PRIMARY KEY (`id`))".
                    " ENGINE = InnoDB {$charsetCollate};"
                );

            } catch(Exception $e) {

                wp_die( $e->getMessage() );

            }

        }

        $tablePortfolioImages = CALHAU_TBL_PORTFOLIO_IMAGES;

        // Checking existence
        $tableExists = $wpdb->get_var("SHOW TABLES LIKE '{$tablePortfolioImages}'");
        if($tableExists == "") {

            try {

                $wpdb->query(
                    "CREATE TABLE `{$tablePortfolioImages}` (".
                    "   `id` INT(10) NOT NULL AUTO_INCREMENT,".
                    "   `portfolio_item_id` INT(10) NOT NULL,".
                    "   `filename` VARCHAR(45) NOT NULL,".
                    "   `description` VARCHAR(45) NULL,".
                    "   `is_featured` TINYINT(1) NOT NULL DEFAULT 0,".
                    "   `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP(),".
                    "   `updated_at` DATETIME NULL,".
                    "   PRIMARY KEY (`id`),".
                    "   INDEX `fk_{$tablePortfolioImages}_{$tablePortfolio}_idx` (`portfolio_item_id` ASC),".
                    "   CONSTRAINT `fk_{$tablePortfolioImages}_{$tablePortfolio}`".
                    "     FOREIGN KEY (`portfolio_item_id`)".
                    "     REFERENCES `{$tablePortfolio}` (`id`)".
                    "     ON DELETE CASCADE".
                    "     ON UPDATE NO ACTION)".
                    " ENGINE = InnoDB {$charsetCollate};"
                );

            } catch(Exception $e) {

                wp_die( $e->getMessage() );

            }

        }
    }

    /**
     * Drop all portfolio's tables
     *
     * @return void
     */
    public function destroyPluginTables()
    {
        global $wpdb;

        $tablePortfolio = CALHAU_TBL_PORTFOLIO;
        $tablePorfolioImages = CALHAU_TBL_PORTFOLIO_IMAGES;

        $wpdb->query("DROP TABLE IF EXISTS {$tablePortfolio}");
        $wpdb->query("DROP TABLE IF EXISTS {$tablePortfolioImages}");
    }

    /**
     * Loads the css styles to admin area
     *
     * @return void
     */
    public function enqueueAdminStyle()
	{
        $styleName = 'calhau_portfolio_admin_styles';
        $url = plugins_url( '/admin/css/'. $styleName .'.css', __FILE__ );

        self::loadFileStyle($styleName, $url);
    }
    
    /**
     * Loads the css styles to public area
     *
     * @return void
     */
    public function enqueuePublicStyle()
	{
        $styleName = 'calhau_portfolio_public_styles';
        $url = plugins_url( '/public/css/'. $styleName .'.css', __FILE__ );    
        
        self::loadFileStyle($styleName, $url);
    }

    /**
     * Helper function which returns the dimensions of image created from a string
     *
     * @param string $data
     * @return void
     */
    public function getimagesizefromstring($data)
    {
        $uri = 'data://application/octet-stream;base64,' . base64_encode($data);
        return getimagesize($uri);
    }

    /**
     * Replaces the portfolio's shortcode to the portfolio items
     *
     * @return string
     */
    public function handleShortcode($content) : string
    {
        global $wpdb; 
        $items = $wpdb->get_results(
            "SELECT a.id, a.project_name, a.project_url, a.short_description, a.published_at, b.filename ".
            "FROM ". CALHAU_TBL_PORTFOLIO . " a ".
            "INNER JOIN ". CALHAU_TBL_PORTFOLIO_IMAGES ." b ON b.portfolio_item_id = a.id AND b.is_featured = true ".
            "WHERE a.published_at <= NOW() ".
            "ORDER BY a.published_at DESC, a.ordering"
        );

        if(count($items) == 0) {

            $html = 'Sorry, no items are available.';

        } else {

            $html = 
            '<div id="portfolio-gallery">'.
            '   <div class="ui centered grid">';

            foreach ($items as $item) {
                $html .= 
                    '<div class="eight wide tablet five wide computer column portfolio-item">'.
                    '   <div class="ui fluid card">'.
                    '       <div class="image">'.
                    '           <a href="'. $item->project_url .'">'.
                    '               <img src="../uploads/portfolio/normal/'. $item->filename .'" class="ui fluid image">'.
                    '           </a>'.
                    '       </div>'.
                    '       <div class="content">'.
                    '           <small class="meta">'.
                    '               <span class="date">'. date('Y, d/m', strtotime($item->published_at)) .'</span>'.
                    '           </small>'.
                    '           <div class="header">'. $item->project_name .'</div>'.
                    '           <div class="description">'. $item->short_description .'</div>'.
                    '       </div>'.
                    '       <div class="extra content">'.
                    '           <a target="_blank" href="'. $item->project_url .'" class="pull-right">Visit</a>'.
                    '       </div>'.
                    '   </div>'.
                    '</div>';
            }

            $html .= 
                '   </div>
                </div>';

        }

        return str_replace( "[block-portfolio]", $html, $content );
    }

    /**
     * Deletes a item from portfolio
     *
     * @param integer $item_id
     * @return void
     */
    public function item_delete(int $item_id)
    {
        global $wpdb;

        $directories = ['large', 'normal', 'thumbnail'];
        $filepath = $this->upload_path;

		try {
            $wpdb->query("START TRANSACTION");

            // Portfolio's Extra Images
            $images = $wpdb->get_results(
                "SELECT filename FROM ". CALHAU_TBL_PORTFOLIO_IMAGES ." WHERE portfolio_item_id = '{$item_id}' "
            );

            if($images != null) {
                foreach ($images as $image):
                    foreach ($directories as $dir):
                        if( is_file($filepath . $dir  ."/" . $image->filename) ) {
                            unlink($filepath . $dir  ."/" . $image->filename);
                        } else {
                            wp_die("Error: ". $filepath . $dir  ."/" . $image->filename . "does not exists.");
                        }
                    endforeach;
                endforeach;
            }

            // Delete records from database
            $wpdb->query("DELETE FROM ". CALHAU_TBL_PORTFOLIO_IMAGES . " WHERE portfolio_item_id = '{$item_id}'");
            $wpdb->query("DELETE FROM ". CALHAU_TBL_PORTFOLIO . " WHERE id = '{$item_id}'");

            $wpdb->query("COMMIT");
        }
        catch(Exception $e) {
            $wpdb->query("ROLLBACK");
            wp_die( $e->getMessage() );
        }

        if ( wp_get_referer() ) {
            $referer = explode( "&action", wp_get_referer() );
            if(count( $referer  == 2)) {
                $referer = $referer[0] . "&action=manage";
            } else {
                $referer = $referer[0];
            }

            wp_safe_redirect( $referer . "&result=ok" );	
        } else {	
            wp_safe_redirect( get_home_url() );	
        }
        
    }

    /**
     * Checks if the selected item exists.
     *
     * @param integer $item_id
     * @return bool
     */
    public function item_exists(int $item_id) : bool
    {
        global $wpdb;

        $item = $wpdb->get_row(
			"SELECT id FROM ". CALHAU_TBL_PORTFOLIO ." WHERE id = '{$item_id}' "
        );
        
        if($item == null) {
            return false;
        }

        return true;
    }

    /**
     * Add a new item to portfolio
     *
     * @return void
     */
    public function item_store()
    {
        global $wpdb;

        $data = $_POST;

        $file = explode("base64,", $data['file_data']);
        if (count($file) != 2) {
            wp_die( "Image invalid." );
        }

        $extension = explode('/', $file[0]);

        if(is_array($extension) and count($extension) == 2) {
            $extension = str_replace(";", "", $extension[1]);
        } else {
            $extension = "png";
        }

        $decodedFile = base64_decode($file[1]);
        $fileInfo = json_decode(stripslashes($data["file_info"]));
        $imageSize = self::getimagesizefromstring($decodedFile);

        $widthProportion = round($imageSize[0] / $fileInfo->w, 1);
        $normalProportion = round($fileInfo->w / 400, 1);
        $thumbnailProportion = round($fileInfo->w / 100, 1);

        $filepath = $this->upload_path;

        try {

            // Begin transaction
            $wpdb->query('START TRANSACTION');

            $wpdb->query(
                "INSERT INTO ". CALHAU_TBL_PORTFOLIO ." SET ".
                " project_name = '{$data["project_name"]}', ".
                " project_url = '{$data["project_url"]}', ".
                " short_description = '{$data["short_description"]}', ".
                " description = '{$data["description"]}', ".
                " ordering = '{$data["ordering"]}', ".
                " published_at = '{$data["published_at"]}' "
            );
    
            $id = $wpdb->insert_id;
            $filename = "portfolio_item_". $id ."_r_". mt_rand(11,99) ."_". time() .".". $extension;
            
            $wpdb->query(
                "INSERT INTO ". CALHAU_TBL_PORTFOLIO_IMAGES ." SET ".
                " portfolio_item_id = '{$id}', ".
                " filename = '{$filename}', ".
                " is_featured = true"
            );

            WideImage::load($decodedFile)
                ->crop(
                    $fileInfo->x * $widthProportion,
                    $fileInfo->y * $widthProportion,
                    $imageSize[0], 
                    $fileInfo->y2 * $widthProportion
                )
                ->resize($fileInfo->w * 1.5, $fileInfo->h * 1.5)
                ->saveToFile($filepath . "large/". $filename);

            WideImage::load($filepath . "large/". $filename)
                ->resize(400, $fileInfo->h * $thumbnailProportion)
                ->saveToFile($filepath . "normal/". $filename);

            WideImage::load($filepath . "large/". $filename)
                ->resize(100, $fileInfo->h * $thumbnailProportion)
                ->saveToFile($filepath . "thumbnail/". $filename);
    
            add_action( 'admin_notices', [$this, "noticeItemAdded"] );

            $wpdb->query('COMMIT');

        } catch(Exception $e) {
            $wpdb->query('ROLLBACK');
            wp_die($e->getMessage);
        }

        if ( wp_get_referer() ) {

            wp_safe_redirect( wp_get_referer() . "&result=ok" );

        } else {

            wp_safe_redirect( get_home_url() );

        }
    }

    /**
     * Edit an item from the portfolio
     *
     * @return void
     */
    public function item_update()
    {
        global $wpdb;

        $data = $_POST;
        $decodedFile = null;
        $item_id = (int) $data["item_id"];

        if($data["file_data"] != "") {

            $file = explode("base64,", $_POST['file_data']);
            if (count($file) != 2) {
                wp_die( "Image invalid." );
            }

            $extension = explode('/', $file[0]);

            if(is_array($extension) and count($extension) == 2) {
                $extension = str_replace(";", "", $extension[1]);
            } else {
                $extension = "png";
            }

            $decodedFile = base64_decode($file[1]);
            $fileInfo = json_decode(stripslashes($data["file_info"]));
            $imageSize = self::getimagesizefromstring($decodedFile);

            $widthProportion = round($imageSize[0] / $fileInfo->w, 1);
            $normalProportion = round($fileInfo->w / 400, 1);
            $thumbnailProportion = round($fileInfo->w / 100, 1);

            $filepath = $this->upload_path;

        }

        try {

            // Begin transaction
            $wpdb->query('START TRANSACTION');

            $wpdb->query(
                "UPDATE ". CALHAU_TBL_PORTFOLIO ." SET ".
                " project_name = '{$data["project_name"]}', ".
                " project_url = '{$data["project_url"]}', ".
                " resume = '{$data["resume"]}', ".
                " description = '{$data["description"]}', ".
                " ordering = '{$data["ordering"]}', ".
                " published_at = '{$data["published_at"]}' ".
                "WHERE id = '{$item_id}'"
            );

            if($decodedFile != null) {

                $filename = "portfolio_item_". $id ."_r_". mt_rand(11,99) ."_". time() .".". $extension;

                WideImage::load($decodedFile)
                    ->crop(
                        $fileInfo->x * $widthProportion,
                        $fileInfo->y * $widthProportion,
                        $imageSize[0], 
                        $fileInfo->y2 * $widthProportion
                    )
                    ->resize($fileInfo->w * 1.5, $fileInfo->h * 1.5)
                    ->saveToFile($filepath . "large/". $filename);

                WideImage::load($filepath . "large/". $filename)
                    ->resize(400, $fileInfo->h * $thumbnailProportion)
                    ->saveToFile($filepath . "normal/". $filename);

                WideImage::load($filepath . "large/". $filename)
                    ->resize(100, $fileInfo->h * $thumbnailProportion)
                    ->saveToFile($filepath . "thumbnail/". $filename);

                if(is_file($filepath . "thumbnail/". $filename)) {
                    $wpdb->query(
                        "UPDATE ". CALHAU_TBL_PORTFOLIO_IMAGES .
                        " SET filename = '{$filename}'".
                        " WHERE portfolio_item_id = '{$item_id}' AND is_featured = true"
                    );
                }
                    
            }
    
            add_action( 'admin_notices', [$this, "noticeItemAdded"] );

            $wpdb->query('COMMIT');

        } catch(Exception $e) {
            $wpdb->query('ROLLBACK');
            wp_die($e->getMessage);
        }

        if($decodedFile != null) {
            $directories = ['large', 'normal', 'thumbnail'];
            foreach ($directories as $dir):
                if( is_file($filepath . $dir  ."/" . $data["file_current"]) ) {
                    unlink($filepath . $dir  ."/" . $data["file_current"]);
                }
            endforeach;
        }

        if ( wp_get_referer() ) {

            wp_safe_redirect( wp_get_referer() . "&result=ok" );

        } else {

            wp_safe_redirect( get_home_url() );

        }
    }
    
    /**
     * Enqueue the speciefied css file from an url
     *
     * @return void
     */
    public function loadFileStyle(string $styleName, string $url)
    {
        $url = str_replace("includes/", "", $url);

        if ($fp = curl_init($url)) {
            wp_enqueue_style( $styleName, $url );
        } else {
            wp_die(
                "The specified css file does not exist: \n".
                $file
            );
        }
    }

    /**
     * Creates a new notice to admin area
     *
     * @return void
     */
    public function noticeItemAdded(){
        echo '<div class="notice notice-success is-dismissable">';
        echo '  <p>'+ _e('Nice!', 'Item added successfully.', 'calhau-portfolio') +'</p>';
        echo '</div>';
    }

}