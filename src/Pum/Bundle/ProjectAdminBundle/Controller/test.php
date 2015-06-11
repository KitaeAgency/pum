<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class import extends CI_Controller {

    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     *      http://example.com/index.php/welcome
     *  - or -
     *      http://example.com/index.php/welcome/index
     *  - or -
     * Since this controller is set as the default controller in
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see http://codeigniter.com/user_guide/general/urls.html
     */
    function __construct()
    {
        parent::__construct();
        $this->load->model('import_model', '', TRUE);//true pour activer la base de données
        session_start();

        /* Secure Call */
        $_POST = $this->input->post(NULL, TRUE); // returns all POST items with XSS filter
        $_GET = $this->input->get(NULL, TRUE); // returns all GET items with XSS filter
    }

    function xml_import()
    {
        $path = $_SERVER['DOCUMENT_ROOT'].'/media/';
        $path_done = $_SERVER['DOCUMENT_ROOT'].'/media/import_done/';
        if(file_exists($path))
        {
            $fd = opendir($path);
            while(($file = readdir($fd)) !== false)
            {
                if ($file != '.' && $file != '..' && $file != 'import_done')
                {
                    $articles = array();
                    $xml = simplexml_load_file($path.$file);
                    $total_articles = count($xml->item);
                    $add_articles = 0;
                    foreach($xml->item as $el)
                    {
                        $item = array();
                        foreach($el as $k => $field)
                        {
                            if ($k != 'images')
                            {
                                $item[$k] = (string)$field;
                            }
                            else
                            {
                                $item['images'] = array();
                                foreach($field as $img)
                                {
                                    $item['images'][] = array(
                                        (string)$img->small, (string)$img->big
                                    );
                                }
                            }
                        }
                        $articles[] = $item;
                    }

                    $authorizedFields = $this->db->list_fields('item');
                    $articles = array_reverse($articles);
                    foreach($articles as $params)
                    {
                        if ($this->import_model->add_item($params, $authorizedFields, false))
                        {
                            $add_articles++;
                        }
                    }
                    echo $add_articles.'/'.$total_articles.' items added';
                    @rename($path.$file, $path_done.$file);
                    //@unlink($path.$file);
                    break;
                }
            }
        }
    }

    function xmlentities($string) {
        return str_replace(array("&", "<", ">", "\"", "'"),
            array("&amp;", "&lt;", "&gt;", "&quot;", "&apos;"), $string);
    }

    function array2XML($datas, $encodage='utf-8')
    {
        if (isset($_SESSION['directSave']) && $_SESSION['directSave']) {
            return;
        }

        if($datas)
        {
            //$trans = array_map('utf8_encode', array_flip(array_diff(get_html_translation_table(HTML_ENTITIES), get_html_translation_table(HTML_SPECIALCHARS))));
            $datas_chunk = array_chunk($datas, 25);
            foreach($datas_chunk as $items)
            {
                $xml = new DOMDocument('1.0', 'utf-8');
                $root = $xml->createElement('items');
                $xml->appendChild($root);
                foreach($items as $item)
                {
                    $el = $xml->createElement('item');
                    foreach($item as $k => $field)
                    {
                        if ($k != 'images')
                        {
                            if($encodage != 'utf-8') $field = utf8_encode($field);
                            $el->appendChild($xml->createElement($k, $this->xmlentities($field)));
                        }
                        else
                        {
                            $images = $xml->createElement('images');
                            foreach($field as $img)
                            {
                                $moreimg = $xml->createElement('picto');
                                foreach($img as $key => $value)
                                {
                                    $format = ($key == 0) ? 'small' : 'big';
                                    if($encodage != 'utf-8') $value = utf8_encode($value);
                                    $moreimg->appendChild($xml->createElement($format, $this->xmlentities($value)));
                                }
                                $images->appendChild($moreimg);
                            }
                            $el->appendChild($images);
                        }
                    }
                    $root->appendChild($el);
                }
                //$xml->formatOutput = true;
                $folder = $_SERVER['DOCUMENT_ROOT'].'/media/';
                $filename = microtime(true).'.xml';
                $file_src = $folder.$filename;
                $dest_folder = '/chaussurespascheres/media/';
                $xml->save($file_src);
                //$this->sendFileToServeur($filename, $file_src, $dest_folder);
            }
        }
    }

    function sendFileToServeur($filename, $file_src, $dest_folder)
    {
        $ftp_server = "ftp.garage-sales.fr";
        $login = "garagesa";
        $password = base64_decode("UGh1YzAzMTI4NQ==");
        $connect = ftp_connect($ftp_server);
        if (ftp_login($connect, $login, $password))
        {
            $destination_file = $dest_folder.$filename;
            $source_file = $file_src;
            $upload = ftp_put($connect, $destination_file, $source_file, FTP_ASCII);
            if (!$upload)
            {
                echo "Le transfert Ftp a échoué!<br/>";
            }
            else
            {
                echo "Upload réussi de ". $filename."<br/>";
                if (!rename($file_src, $_SERVER['DOCUMENT_ROOT'].'/media/uploaded_'.$filename))
                {
                    echo "Rename raté de $file_src...<br/>";
                }
            }
        }
        else
        {
            echo "Connexion impossible en tant que ".$login."<br/>";
        }
        ftp_close($connect);
    }

    function get_sendAllFilesToServeur()
    {
        $path = $_SERVER['DOCUMENT_ROOT'].'/media/';
        $dest_folder = '/chaussurespascheres/media/';
        $path_done = $_SERVER['DOCUMENT_ROOT'].'/media/import_done/';
        if(file_exists($path))
        {
            $ftp_server = "ftp.garage-sales.fr";
            $login = "garagesa";
            $password = base64_decode("UGh1YzAzMTI4NQ==");
            $connect = ftp_connect($ftp_server);
            if (ftp_login($connect, $login, $password))
            {
                $fd = opendir($path);
                while(($file = readdir($fd)) !== false)
                {
                    if ($file != '.' && $file != '..' && $file != 'import_done')
                    {
                        $destination_file = $dest_folder.$file;
                        $source_file = $path.$file;
                        $upload = ftp_put($connect, $destination_file, $source_file, FTP_ASCII);
                        if (!$upload)
                        {
                            echo "Le transfert Ftp a échoué pour ". $file."<br/>";
                        }
                        else
                        {
                            echo "Upload réussi de ". $file."<br/>";
                            @rename($path.$file, $path_done.$file);
                        }
                    }
                }
                ftp_close($connect);
            }
            else
            {
                echo "Connexion impossible en tant que ".$login."<br/>";
            }

        }
    }

    function robot_import()
    {die('ok1');
        $_SESSION['yo'] = 1;
        if (!isset($_SESSION['yo']) && !strpos($_SERVER['HTTP_HOST'], 'argosit'))
        {
            if ($_POST)
            {
                if ( isset($_POST['code']) && $_POST['code']=='seto' )
                {
                    $_SESSION['yo'] = 1;
                }
                header('location: /import/');
            }
            else
            {
                echo '<form action="" method="POST">';
                echo 'Mot de passe ? <input style="padding:0 10px;margin:0 0 0 10px;background:#eee;height:30px;width:200px;border:2px solid #ccc;" type="text" name="code" />';
                echo '</form>';
            }
        }
        else
        {
            if ($_GET && isset($_GET['yo']) && $_GET['yo']) {
                $_SESSION['directSave'] = 1;
                set_time_limit(1800);
                $imports = array(
                    'sarenza_new', 'sarenza_promo', 'sarenza_top', 'sarenza_luxe',
                    'zalando_new', 'zalando_promo', 'zalando_top',
                    'zalando_new', 'zalando_promo', 'zalando_top',
                    'zalando_new', 'zalando_promo', 'zalando_top',
                    'zalando_new', 'zalando_promo', 'zalando_top',
                    'zalando_new', 'zalando_promo', 'zalando_top',
                    'spartoo_new', 'spartoo_promo', 'spartoo_luxe',
                    'redoute_new', 'redoute_promo', 'redoute_sport', 'aubaine_promo',
                    'newlook_new', 'newlook_top',
                );
                shuffle($imports);
                reset($imports);
                $function = 'get_'.current($imports);
                var_dump($function);die;
                $this->$function();

                exit;
            }

            if($_POST && isset($_POST['import_name']))
            {
                if(strpos($_POST['import_name'], 'pixmania') !== false || strpos($_POST['import_name'], 'kiabi') !== false)
                {
                    echo '<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />';
                }
                else
                {
                    echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
                }
                echo 'IMPORT<br/><hr/>';
                set_time_limit(15000);
                include_once($_SERVER['DOCUMENT_ROOT']."/php/simple_html_dom.php");
                echo 'R&eacute;sultats de l\'import de <b>'.ucfirst($_POST['import_name']).'</b><br/>';

                $function = 'get_'.$_POST['import_name'];
                $this->$function();

                echo '<br/><a href="/import/"><b>Retour</b></a>';
            }
            else
            {
                echo 'IMPORT<br/><hr/>';
                $imports = array(
                    'sarenza_new', 'sarenza_promo', 'sarenza_top', 'sarenza_luxe', 'all_sarenza',
                    'br',
                    'zalando_new', 'zalando_promo', 'zalando_top', 'all_zalando',
                    'br',
                    'spartoo_new', 'spartoo_promo', 'spartoo_luxe', 'all_spartoo',
                    'br',
                    //'desmazieres_new', 'kiabi_new', 'all_desmazieres_kiabi',
                    'br',
                    'redoute_new', 'redoute_promo', 'redoute_sport', 'aubaine_promo', 'all_redoute',
                    'br',
                    'newlook_new', 'newlook_top', 'all_newlook',
                    'br',
                    //'pixmania_homme_new', 'pixmania_femme_new', 'pixmania_homme_top', 'pixmania_femme_top', 'all_pixmania',
                    'br',
                    'all',
                    'br',
                    'sendAllFilesToServeur',
                );
                foreach($imports as $import)
                {
                    if($import == 'br')
                    {
                        echo '<div style="clear:both"></div>';
                    }
                    else
                    {
                        echo '<form method="post" action="" style="float:left; padding:5px 10px;margin:5px 0"><input type="hidden" name="import_name" value="'.$import.'" /><input type="submit" value="'.ucfirst($import).'" /></form>';
                    }
                }
            }
        }
    }

    function get_all_sarenza()
    {
        $this->get_sarenza_new();$this->get_sarenza_promo();$this->get_sarenza_top();$this->get_sarenza_luxe();
    }

    function get_all_zalando()
    {
        $this->get_zalando_new();$this->get_zalando_promo();$this->get_zalando_top();
    }

    function get_all_spartoo()
    {
        $this->get_spartoo_new();$this->get_spartoo_promo();$this->get_spartoo_luxe();
    }

    function get_all_desmazieres_kiabi()
    {
        //$this->get_desmazieres_new();$this->get_kiabi_new();
    }

    function get_all_redoute()
    {
        $this->get_redoute_new();$this->get_redoute_promo();$this->get_redoute_sport();$this->get_aubaine_promo();
    }

    function get_all_newlook()
    {
        $this->get_newlook_new();
    }

    function get_all_pixmania()
    {
        //$this->get_pixmania_homme_new();$this->get_pixmania_femme_new();$this->get_pixmania_homme_top();$this->get_pixmania_femme_top();
    }

    function get_all()
    {
        $this->get_sarenza_new();$this->get_sarenza_promo();$this->get_sarenza_top();$this->get_sarenza_luxe();
        $this->get_spartoo_new();$this->get_spartoo_promo();$this->get_spartoo_luxe();
        //$this->get_desmazieres_new();$this->get_kiabi_new();
        $this->get_redoute_new();$this->get_redoute_promo();$this->get_redoute_sport();$this->get_aubaine_promo();
        $this->get_newlook_new();
        //$this->get_pixmania_homme_new();$this->get_pixmania_femme_new();$this->get_pixmania_homme_top();$this->get_pixmania_femme_top();
        $this->get_zalando_new();$this->get_zalando_promo();$this->get_zalando_top();

        $this->get_sendAllFilesToServeur();
    }


    /*************************************** Sarenza *************************************/
    /*                                                                                   */
    /*                                                                                   */
    /*                                                                                   */
    /*************************************************************************************/
    function sarenza_basic($urls=array(), $forceFields=array(), $debug=false)
    {
        $time = time();
        $articles = array();
        $add_articles = 0;
        $field_img = 'data-href';

        foreach($urls as $url)
        {
            $html = file_get_html($url);
            if(!$html)
            {
                continue;
            }

            foreach($html->find('section[id=primary-content] div.vignette a') as $e)
            {
                $query = $this->db->get_where('item', array('main_link' => $e->href));
                $query = $query->result();
                if (count($query) > 0)
                {
                    continue;
                }

                $articleDatas = array();
                $page = file_get_html($e->href);
                if(!$page)
                {
                    continue;
                }

                /* Basic Information */
                $articleDatas['main_link'] = $e->href;
                $articleDatas['from'] = 'Sarenza';
                $articleDatas['create_date'] = $time - $add_articles;
                foreach($forceFields as $k => $forceField)
                {
                    $articleDatas[$k] = $forceField;
                }
                /* Genre */
                if(strpos($url, 'femme') !== false)
                {
                    $articleDatas['genre'] = 'F';
                }
                elseif(strpos($url, 'homme') !== false)
                {
                    $articleDatas['genre'] = 'H';
                }
                else
                {
                    $articleDatas['genre'] = 'E';
                }
                /* Marque */
                foreach($page->find('div[id=FP] a[itemprop=brand]') as $marque)
                {
                    $articleDatas['marque'] = trim(strip_tags($marque));
                }
                /* Title */
                foreach($page->find('div[id=FP] span[itemprop=name]') as $title)
                {
                    $articleDatas['title'] = ucfirst(trim(strip_tags($title)));
                }
                /* Base Price */
                foreach($page->find('div[id=FP] span[itemprop=highPrice]') as $price)
                {
                    $articleDatas['base_price'] = floatval(str_replace(',', '.', strip_tags($price)));
                }
                /* Price */
                foreach($page->find('div[id=FP] strong[itemprop=price] span.product-price') as $price)
                {
                    $articleDatas['total_price'] = floatval(str_replace(',', '.', strip_tags($price)));
                }
                /* Couleur */
                foreach($page->find('div[id=FP] h4.color-placeholder') as $color)
                {
                    $articleDatas['color'] = ucfirst(trim(strip_tags($color)));
                }
                /* Content */
                foreach($page->find('div[id=FP] div.detail-product') as $content)
                {
                    $articleDatas['content'] = str_replace('  ', '',$content);
                }
                /* Description */
                foreach($page->find('div[id=FP] p[itemprop=description]') as $description)
                {
                    $articleDatas['description'] = str_replace('  ', '',$description);
                }
                /* Categorie */
                foreach($page->find('div[id=FP] div.detail-product ul li') as $categorie)
                {
                    $articleDatas['categorie'] = trim(str_replace('Type', '', strip_tags($categorie)));
                    break;
                }
                /* Main image */
                foreach($e->find('div.img-wrapper img') as $image)
                {
                    $articleDatas['main_image'] = $image->src;
                }
                /* More Images */
                foreach($page->find('div[id=FP] ul.slider img') as $image)
                {
                    $articleDatas['images'][] = array($image->src, str_replace('PI_', '', $image->src));
                }
                if(isset($articleDatas['main_image']) && $articleDatas['main_image'] == 'http://azure.sarenza.net/static/V5/global/images/ajax-loader.gif?2')
                {
                    if(isset($articleDatas['images'][0]) && $articleDatas['images'][0])
                    {
                        $articleDatas['main_image'] = $articleDatas['images'][0][1];
                    }
                }
                /* Add Unique Id to the Item base on Reference - Marque - Title - Couleur - Enseigne*/
                if (isset($articleDatas['marque']) && isset($articleDatas['title']) && isset($articleDatas['color']) && isset($articleDatas['main_image']) && isset($articleDatas['total_price']))
                {
                    if(!isset($articleDatas['categorie']))
                    {
                        $articleDatas['categorie'] = '';
                    }
                    /* Add item */

                    if (isset($_SESSION['directSave']) && $_SESSION['directSave']) {
                        if (!isset($this->fields)) {
                            $this->fields = $this->db->list_fields('item');
                        }

                        $this->import_model->add_item($articleDatas, $this->fields, false)
                    } else {
                        $articles[] = $articleDatas;
                        $add_articles++;
                    }
                }
                if ($debug)
                {
                    printr($articles);die;
                }

                /* Clean up memory */
                $page->clear();
                unset($page);
            }

            /* Clean up memory */
            $html->clear();
            unset($html);
        }

        $authorizedFields = $this->db->list_fields('item');
        $articles = array_reverse($articles);
        foreach($articles as $params)
        {
            if (!$this->import_model->add_item($params, $authorizedFields, false))
            {
                $add_articles--;
            }
        }
        $this->array2XML($articles);

        echo 'Nombre de chaussures ajout&eacute; : '.$add_articles;
        $duration = time() - $time;
        echo '<br/>Duration : '.$duration.' seconds<br/>';
    }

    function get_sarenza_new()
    {
        $urls = array(
            "http://www.sarenza.com/chaussure-nouvelle-collection-femme",
            "http://www.sarenza.com/chaussure-nouvelle-collection-homme",
            "http://www.sarenza.com/chaussure-nouvelle-collection-enfant",
        );
        $forceFields['is_new'] = '1';
        $this->sarenza_basic($urls, $forceFields);
    }

    function get_sarenza_promo()
    {
        $urls = array(
            "http://www.sarenza.com/chaussure-pas-cher-femme",
            "http://www.sarenza.com/chaussure-pas-cher-homme",
            "http://www.sarenza.com/chaussure-pas-cher-enfant",
        );
        $forceFields['is_promo'] = '1';
        $this->sarenza_basic($urls, $forceFields);
    }

    function get_sarenza_top()
    {
        $urls = array(
            "http://www.sarenza.com/chaussure-femme-top-ventes",
            "http://www.sarenza.com/chaussure-homme-top-ventes",
            "http://www.sarenza.com/chaussure-enfant-top-ventes",
        );
        $forceFields['is_top'] = '1';
        $this->sarenza_basic($urls, $forceFields);
    }

    function get_sarenza_luxe()
    {
        $urls = array(
            "http://www.sarenza.com/tout-chaussure-luxe-femme",
            "http://www.sarenza.com/tout-chaussure-luxe-homme",
            "http://www.sarenza.com/tout-chaussure-luxe-enfant",
        );
        if(mt_rand(1, 10) > 5)
        {
            $forceFields['is_new'] = 1;
        }
        else
        {
            $forceFields['is_top'] = 1;
        }
        $this->sarenza_basic($urls, $forceFields);
    }

    /*************************************** Zalando *************************************/
    /*                                                                                   */
    /*                                                                                   */
    /*                                                                                   */
    /*************************************************************************************/
    function zalando_basic($urls=array(), $forceFields=array(), $debug=false, $itemShape='')
    {
        $time = time();
        $articles = array();
        $add_articles = 0;
        if (!$itemShape)
        {
            $itemShape = 'div[id=content] div.mainCol ul.productsGridList li.gItem a.productBox';
        }

        foreach($urls as $url)
        {
            $html = file_get_html($url);
            if(!$html)
            {
                continue;
            }

            foreach($html->find($itemShape) as $e)
            {
                $query = $this->db->get_where('item', array('main_link' => 'http://www.zalando.fr/'.$e->href));
                $query = $query->result();
                if (count($query) > 0)
                {
                    continue;
                }

                $articleDatas = array();
                $page = file_get_html('http://www.zalando.fr/'.$e->href);
                if(!$page)
                {
                    continue;
                }

                /* Basic Information */
                $articleDatas['main_link'] = 'http://www.zalando.fr/'.$e->href;
                $articleDatas['from'] = 'Zalando';
                $articleDatas['create_date'] = $time - $add_articles;
                foreach($forceFields as $k => $forceField)
                {
                    $articleDatas[$k] = $forceField;
                }
                /* Genre */
                if(strpos($url, 'femme') !== false)
                {
                    $articleDatas['genre'] = 'F';
                }
                elseif(strpos($url, 'homme') !== false)
                {
                    $articleDatas['genre'] = 'H';
                }
                else
                {
                    $articleDatas['genre'] = 'E';
                }
                /* Marque */
                foreach($page->find('span[itemprop=brand]') as $marque)
                {
                    $articleDatas['marque'] = trim(strip_tags($marque));
                }
                /* Title & Color */
                foreach($page->find('span[itemprop=name]') as $title)
                {
                    $infos = explode('-', strip_tags($title));
                    $color = ucfirst(trim($infos[count($infos)-1]));
                    unset($infos[count($infos)-1]);
                    $title = trim(implode('-', $infos));

                    $articleDatas['title'] = $title;
                    $articleDatas['color'] = $color;

                    if ($articleDatas['title']) break;
                }
                /* Base Price */
                foreach($page->find('span[id=articleOldPrice]') as $price)
                {
                    $price = explode(' ', trim(strip_tags(str_replace('1 ', '1', $price))));
                    $articleDatas['base_price'] = str_replace(array('&nbsp;', ','), array('', '.'), $price[0]);
                }
                /* Price */
                foreach($page->find('span[id=articlePrice]') as $price)
                {
                    $price = explode(' ', trim(strip_tags(str_replace('1 ', '1', $price))));
                    $articleDatas['total_price'] = str_replace(array('&nbsp;', ','), array('', '.'), $price[0]);
                }
                if (!isset($articleDatas['total_price']))
                {
                    foreach($page->find('div[id=productSidebar] div.boxPrice span[id=articlePrice] span[itemprop=price]') as $price)
                    {
                        $price = explode(' ', trim(strip_tags(str_replace('1 ', '1', $price))));
                        $articleDatas['total_price'] = str_replace(array('&nbsp;', ','), array('', '.'), $price[0]);
                    }
                }
                /* Description */
                foreach($page->find('div[id=boxDescription] div[id=productDetails] ul') as $description)
                {
                    $articleDatas['description'] = str_replace('  ', '',$description);
                }
                /* Categorie */
                foreach($page->find('div[id=content] div.breadcrumbs ul a[name=header.breadcrumb.4]') as $categorie)
                {
                    $articleDatas['categorie'] = strip_tags($categorie);
                }
                /* Main image */
                foreach($e->find('img') as $image)
                {
                    $articleDatas['main_image'] = $image->src;
                }
                /* More Images */
                foreach($page->find('div[id=moreImages] ul a') as $image)
                {
                    foreach($image->find('img') as $thumb)
                    {
                        $articleDatas['images'][] = array($thumb->src, $image->rev);
                    }
                }
                /* Add Unique Id to the Item base on Reference - Marque - Title - Couleur - Enseigne*/
                if (isset($articleDatas['marque']) && isset($articleDatas['title']) && isset($articleDatas['color']) && isset($articleDatas['main_image']) && isset($articleDatas['total_price']))
                {
                    if(isset($articleDatas['base_price']) && $articleDatas['base_price'] && $articleDatas['base_price'] != $articleDatas['total_price'])
                    {
                        $articleDatas['is_promo'] = 1;
                        $articleDatas['is_new'] = 0;
                        $articleDatas['is_top'] = 0;
                    }
                    if(!isset($articleDatas['categorie']))
                    {
                        $articleDatas['categorie'] = '';
                    }

                    /* Add item */
                    if (isset($_SESSION['directSave']) && $_SESSION['directSave']) {
                        if (!isset($this->fields)) {
                            $this->fields = $this->db->list_fields('item');
                        }

                        $this->import_model->add_item($articleDatas, $this->fields, false)
                    } else {
                        $articles[] = $articleDatas;
                        $add_articles++;
                    }
                }
                if ($debug)
                {
                    printr($articles);die;
                }

                /* Clean up memory */
                $page->clear();
                unset($page);
            }

            /* Clean up memory */
            $html->clear();
            unset($html);
        }

        $authorizedFields = $this->db->list_fields('item');
        $articles = array_reverse($articles);
        foreach($articles as $params)
        {
            if (!$this->import_model->add_item($params, $authorizedFields, false))
            {
                $add_articles--;
            }
        }
        $this->array2XML($articles);

        echo 'Nombre de chaussures ajout&eacute; : '.$add_articles;
        $duration = time() - $time;
        echo '<br/>Duration : '.$duration.' seconds<br/>';
    }

    function get_zalando_new()
    {
        $urls = array(
            "http://www.zalando.fr/nouvelle-collection/chaussures-femme/",
            "http://www.zalando.fr/nouvelle-collection/chaussures-homme/",
            "http://www.zalando.fr/nouvelle-collection/chaussures-enfant/",
            "http://www.zalando.fr/chaussures-femme-luxe/?order=activation_date",
            "http://www.zalando.fr/chaussures-homme-luxe/?order=activation_date",
        );
        $forceFields['is_new'] = '1';
        $this->zalando_basic($urls, $forceFields);
    }

    function get_zalando_promo()
    {
        $urls = array(
            "http://www.zalando.fr/promo-chaussures-femme/",
            "http://www.zalando.fr/promo-chaussures-homme/",
            "http://www.zalando.fr/promo-chaussures-enfant/",
            "http://www.zalando.fr/chaussures-femme-luxe/?order=sale",
            "http://www.zalando.fr/chaussures-homme-luxe/",
        );
        $forceFields['is_promo'] = '1';
        $this->zalando_basic($urls, $forceFields);
    }

    function get_zalando_top()
    {
        $urls = array(
            "http://www.zalando.fr/chaussures-femme/",
            "http://www.zalando.fr/chaussures-homme/",
            "http://www.zalando.fr/chaussures-enfant/",
            "http://www.zalando.fr/chaussures-femme-luxe/",
            "http://www.zalando.fr/chaussures-homme-luxe/",
        );
        $forceFields['is_top'] = '1';
        $this->zalando_basic($urls, $forceFields);
    }

    /*************************************** Spartoo *************************************/
    /*                                                                                   */
    /*                                                                                   */
    /*                                                                                   */
    /*************************************************************************************/
    function spartoo_basic($urls=array(), $forceFields=array(), $debug=false, $itemShape='')
    {
        $time = time();
        $articles = array();
        $add_articles = 0;
        if (!$itemShape)
        {
            $itemShape = 'div[id=droite] div.dis_content_img div.test';
        }

        foreach($urls as $url)
        {
            $html = file_get_html($url);
            if(!$html)
            {
                continue;
            }

            foreach($html->find($itemShape) as $e)
            {
                $stop = false;
                $articleDatas = array();
                foreach($e->find('a') as $lien)
                {
                    $query = $this->db->get_where('item', array('main_link' => 'http://www.spartoo.com/'.$lien->href));
                    $query = $query->result();
                    if (count($query) > 0)
                    {
                        $stop = true;
                        continue;
                    }
                    $page = file_get_html('http://www.spartoo.com/'.$lien->href);
                    if(!$page)
                    {
                        $stop = true;
                        continue;
                    }
                    break;
                }
                if ($stop)
                {
                    continue;
                }

                /* Basic Information */
                $articleDatas['main_link'] = 'http://www.spartoo.com/'.$lien->href;
                $articleDatas['from'] = 'Spartoo';
                $articleDatas['create_date'] = $time - $add_articles;
                foreach($forceFields as $k => $forceField)
                {
                    $articleDatas[$k] = $forceField;
                }
                /* Genre */
                if(strpos($url, 'femme') !== false)
                {
                    $articleDatas['genre'] = 'F';
                }
                elseif(strpos($url, 'homme') !== false)
                {
                    $articleDatas['genre'] = 'H';
                }
                else
                {
                    $articleDatas['genre'] = 'E';
                }
                /* Marque */
                foreach($e->find('span.productlist_marque') as $marque)
                {
                    $articleDatas['marque'] = trim(strip_tags($marque));
                }
                /* Title */
                foreach($e->find('span.productlist_name') as $title)
                {
                    $articleDatas['title'] = ucfirst(trim(strip_tags($title)));
                }
                /* Base Price */
                foreach($page->find('div[id=droite] div.prodcardInfos2 div.dis_market_price s') as $price)
                {
                    $articleDatas['base_price'] = floatval(str_replace(',', '.', strip_tags($price)));
                }
                /* Price */
                foreach($page->find('div[id=droite] div.prodcardInfos2 div.dis_market_price b') as $price)
                {
                    $articleDatas['total_price'] = floatval(str_replace(',', '.', strip_tags($price)));
                }
                /* Price */
                if(!isset($articleDatas['total_price']))
                {
                    foreach($page->find('div[id=droite] div.prodcardInfos2 div.dis_market_price') as $price)
                    {
                        $articleDatas['total_price'] = floatval(str_replace(',', '.', strip_tags($price)));
                    }
                }
                /* Couleur */
                foreach($page->find('div[id=droite] div.prodcardInfos2 span.fn') as $color)
                {
                    $articleDatas['color'] = $color->plaintext;
                    if (preg_match_all('~\b[[:upper:]]+\b~', $articleDatas['color'], $m)) {
                        foreach($m[0] as $maj)
                        {
                            $articleDatas['color'] = str_replace($maj, '', $articleDatas['color']);
                        }
                    }
                    $articleDatas['color'] = ucfirst(trim($articleDatas['color']));
                }
                /* Content */
                foreach($page->find('div[id=infoCompo]') as $content)
                {
                    $articleDatas['content'] = str_replace('  ', '',$content);
                }
                /* Description */
                foreach($page->find('div[id=infoDescription]') as $description)
                {
                    $articleDatas['description'] = str_replace('  ', '',$description);
                }
                /* Main image */
                foreach($e->find('img') as $image)
                {
                    $articleDatas['main_image'] = $image->src;
                    $articleDatas['categorie'] = strtolower($image->alt);
                }
                /* Categorie */
                foreach(array(strtolower($articleDatas['marque']),strtolower($articleDatas['title'])) as $tmp)
                {
                    $articleDatas['categorie'] = str_replace($tmp, '', $articleDatas['categorie']);
                }
                $articleDatas['categorie'] = ucfirst(trim($articleDatas['categorie']));
                /* More Images */
                foreach($page->find('div[id=droite] div.vignetteBas2 a[href=javascript:;]') as $image)
                {
                    foreach($image->find('img') as $thumb)
                    {
                        preg_match('!http://.+\.(?:jpe?g|png|gif)!Ui' ,  $image->onmouseover , $matches);
                        if (isset($matches[0]))
                        {
                            $articleDatas['images'][] = array($thumb->src, $matches[0]);
                        }
                    }
                }
                /* Add Unique Id to the Item base on Reference - Marque - Title - Couleur - Enseigne*/
                if (isset($articleDatas['marque']) && isset($articleDatas['title']) && isset($articleDatas['color']) && isset($articleDatas['main_image']) && isset($articleDatas['total_price']))
                {
                    /* Add item */
                    if (isset($_SESSION['directSave']) && $_SESSION['directSave']) {
                        if (!isset($this->fields)) {
                            $this->fields = $this->db->list_fields('item');
                        }

                        $this->import_model->add_item($articleDatas, $this->fields, false)
                    } else {
                        $articles[] = $articleDatas;
                        $add_articles++;
                    }
                }
                if ($debug)
                {
                    printr($articles);die;
                }

                /* Clean up memory */
                $page->clear();
                unset($page);
            }

            /* Clean up memory */
            $html->clear();
            unset($html);
        }

        $authorizedFields = $this->db->list_fields('item');
        $articles = array_reverse($articles);
        foreach($articles as $params)
        {
            if (!$this->import_model->add_item($params, $authorizedFields, false))
            {
                $add_articles--;
            }
        }
        $this->array2XML($articles);

        echo 'Nombre de chaussures ajout&eacute; : '.$add_articles;
        $duration = time() - $time;
        echo '<br/>Duration : '.$duration.' seconds<br/>';
    }

    function get_spartoo_new()
    {
        $urls = array(
            "http://www.spartoo.com/chaussures-nouvelle-collection-femme.php",
            "http://www.spartoo.com/chaussures-nouvelle-collection-homme.php",
            "http://www.spartoo.com/chaussures-enfants.php",
        );
        if(mt_rand(1, 10) > 5)
        {
            $forceFields['is_new'] = 1;
        }
        else
        {
            $forceFields['is_top'] = 1;
        }
        $this->spartoo_basic($urls, $forceFields);
    }

    function get_spartoo_promo()
    {
        $urls = array(
            "http://www.spartoo.com/chaussures-pas-cher-femme.php",
            "http://www.spartoo.com/chaussures-pas-cher-homme.php",
        );
        $forceFields['is_promo'] = '1';
        $this->spartoo_basic($urls, $forceFields);
    }

    function get_spartoo_luxe()
    {
        $urls = array(
            "http://www.spartoo.com/univers-luxe-femme-chaussures.php",
            "http://www.spartoo.com/univers-luxe-homme-chaussures.php",
        );
        if(mt_rand(1, 10) > 5)
        {
            $forceFields['is_new'] = 1;
        }
        else
        {
            $forceFields['is_top'] = 1;
        }
        $this->spartoo_basic($urls, $forceFields);
    }

    /************************************* Desmazieres ***********************************/
    /*                                                                                   */
    /*                                                                                   */
    /*                                                                                   */
    /*************************************************************************************/
    function desmazieres_basic($urls=array(), $forceFields=array(), $debug=false, $itemShape='')
    {
        $time = time();
        $articles = array();
        $add_articles = 0;
        if (!$itemShape)
        {
            $itemShape = 'div[id=middleRight] div.listingPdts a';
        }

        foreach($urls as $url)
        {
            $html = file_get_html($url);
            if(!$html)
            {
                continue;
            }

            foreach($html->find($itemShape) as $e)
            {
                $query = $this->db->get_where('item', array('main_link' => 'http://www.chaussures-desmazieres.fr'.$e->href));
                $query = $query->result();
                if (count($query) > 0)
                {
                    continue;
                }

                $articleDatas = array();
                $page = file_get_html('http://www.chaussures-desmazieres.fr'.$e->href);
                if(!$page)
                {
                    continue;
                }

                /* Basic Information */
                $articleDatas['main_link'] = 'http://www.chaussures-desmazieres.fr'.$e->href;
                $articleDatas['from'] = 'Desmazieres';
                $articleDatas['create_date'] = $time - $add_articles;
                foreach($forceFields as $k => $forceField)
                {
                    $articleDatas[$k] = $forceField;
                }
                /* Genre */
                if(strpos($url, 'femme') !== false)
                {
                    $articleDatas['genre'] = 'F';
                }
                elseif(strpos($url, 'homme') !== false)
                {
                    $articleDatas['genre'] = 'H';
                }
                else
                {
                    $articleDatas['genre'] = 'E';
                }
                /* Marque */
                foreach($page->find('div[id=ctl00_Middle_p_desc_produit] div.blocR00 img.imgMarq') as $marque)
                {
                    $articleDatas['marque'] = ucfirst(strtolower($marque->alt));
                }
                /* Title & Color */
                foreach($page->find('div[id=ctl00_Middle_p_desc_produit] span[id=ctl00_Middle_LB_article_designation]') as $title)
                {
                    $articleDatas['title'] = strip_tags($title);
                }
                /* Price */
                foreach($page->find('div[id=ctl00_Middle_p_desc_produit] span[id=ctl00_Middle_lab_prix]') as $price)
                {
                    $articleDatas['total_price'] = floatval(str_replace('&euro;', '.', strip_tags($price)));
                }
                if (!isset($articleDatas['total_price']))
                {
                    foreach($page->find('div[id=ctl00_Middle_p_desc_produit] span[id=ctl00_Middle_lab_prix_barre]') as $price)
                    {
                        $articleDatas['total_price'] = floatval(str_replace('&euro;', '.', strip_tags($price)));
                    }
                }
                /* Description */
                foreach($page->find('div[id=ctl00_Middle_p_desc_produit] span[id=ctl00_Middle_lab_texte_article]') as $description)
                {
                    $articleDatas['description'] = strip_tags($description);
                }
                /* Categorie */
                foreach($page->find('div[id=ctl00_Middle_p_desc_produit] span[id=ctl00_Middle_lbl_sscat_fiche]') as $categorie)
                {
                    $articleDatas['categorie'] = strip_tags($categorie);
                }
                /* Main image */
                foreach($e->find('img') as $image)
                {
                    $articleDatas['main_image'] = 'http://www.chaussures-desmazieres.fr'.$image->src;
                }
                /* More Images */
                foreach($page->find('div[id=myCaroussel] ul li img') as $image)
                {
                    $filename = explode('/', $image->src);
                    $filename = $filename[3];
                    $articleDatas['images'][] = array('http://www.chaussures-desmazieres.fr/Visuels/load38x50/'.$filename, 'http://www.chaussures-desmazieres.fr/visuels/load228x300/'.$filename);
                }
                /* Add Unique Id to the Item base on Reference - Marque - Title - Enseigne*/
                if (isset($articleDatas['marque']) && isset($articleDatas['title']) && isset($articleDatas['main_image']) && isset($articleDatas['total_price']))
                {
                    if(mt_rand(1, 10) > 5)
                    {
                        $articleDatas['is_new'] = 1;
                    }
                    else
                    {
                        $articleDatas['is_top'] = 1;
                    }
                    if(isset($articleDatas['base_price']) && $articleDatas['base_price'] && $articleDatas['base_price'] != $articleDatas['total_price'])
                    {
                        $articleDatas['is_promo'] = 1;
                        $articleDatas['is_new'] = 0;
                        $articleDatas['is_top'] = 0;
                    }
                    if(!isset($articleDatas['categorie']))
                    {
                        $articleDatas['categorie'] = '';
                    }

                    /* Add item */
                    if (isset($_SESSION['directSave']) && $_SESSION['directSave']) {
                        if (!isset($this->fields)) {
                            $this->fields = $this->db->list_fields('item');
                        }

                        $this->import_model->add_item($articleDatas, $this->fields, false)
                    } else {
                        $articles[] = $articleDatas;
                        $add_articles++;
                    }
                }
                if ($debug)
                {
                    printr($articles);die;
                }

                /* Clean up memory */
                $page->clear();
                unset($page);
            }

            /* Clean up memory */
            $html->clear();
            unset($html);
        }

        $authorizedFields = $this->db->list_fields('item');
        $articles = array_reverse($articles);
        foreach($articles as $params)
        {
            if (!$this->import_model->add_item($params, $authorizedFields))
            {
                $add_articles--;
            }
        }
        $this->array2XML($articles);

        echo 'Nombre de chaussures ajout&eacute; : '.$add_articles;
        $duration = time() - $time;
        echo '<br/>Duration : '.$duration.' seconds<br/>';
    }

    function get_desmazieres_new()
    {
        $urls = array();
        $pages = array(0);
        $binds = array(
            'produits-01' => 'chaussures-femme',
            'produits-05' => 'chaussures-homme',
            'produits-03' => 'chaussures-enfant',
            'produits-04' => 'chaussures-enfant',
        );
        foreach($pages as $page)
        {
            foreach($binds as $key => $bind)
            {
                $urls[] = 'http://www.chaussures-desmazieres.fr/'.$bind.'/'.$key.'/page'.$page.'.html';
            }
        }

        $forceFields = array();
        $this->desmazieres_basic($urls, $forceFields);
    }

    /**************************************** Kiabi **************************************/
    /*                                                                                   */
    /*                                                                                   */
    /*                                                                                   */
    /*************************************************************************************/
    function kiabi_basic($urls=array(), $forceFields=array(), $debug=false, $itemShape='')
    {
        $time = time();
        $articles = array();
        $add_articles = 0;
        if (!$itemShape)
        {
            $itemShape = 'div[id=pagelist] ul.listarticles li.article';
        }

        foreach($urls as $url)
        {
            $html = file_get_html($url);
            if(!$html)
            {
                continue;
            }

            foreach($html->find($itemShape) as $produit)
            {
                $stop = false;
                $articleDatas = array();
                foreach($produit->find('span.desc a.h2') as $e)
                {
                    $query = $this->db->get_where('item', array('main_link' => 'http://www.kiabi.com'.$e->href));
                    $query = $query->result();
                    if (count($query) > 0)
                    {
                        $stop = true;
                        continue;
                    }
                    $page = file_get_html('http://www.kiabi.com'.$e->href);
                    if(!$page)
                    {
                        continue;
                    }

                    /* Basic Information */
                    $articleDatas['main_link'] = 'http://www.kiabi.com'.$e->href;
                    $articleDatas['from'] = 'Kiabi';
                    $articleDatas['create_date'] = $time - $add_articles;
                    foreach($forceFields as $k => $forceField)
                    {
                        $articleDatas[$k] = $forceField;
                    }
                }
                if ($stop)
                {
                    continue;
                }

                /* Genre */
                if(strpos($url, 'femme') !== false)
                {
                    $articleDatas['genre'] = 'F';
                }
                elseif(strpos($url, 'homme') !== false)
                {
                    $articleDatas['genre'] = 'H';
                }
                else
                {
                    $articleDatas['genre'] = 'E';
                }
                /* Marque & Title */
                foreach($page->find('div[id=center_part_2] h1[itemprop=name]') as $name)
                {
                    $infos = explode("'", strip_tags($name));
                    if (count($infos)>1)
                    {
                        $articleDatas['marque'] = str_replace("'", "", $infos[1]);
                    }
                    else
                    {
                        $articleDatas['marque'] = 'Kiabi';
                    }
                    $articleDatas['title'] = $infos[0];
                }
                /* Color */
                foreach($page->find('div[id=center_part_2] div[id=txtCouleur_1]') as $color)
                {
                    $articleDatas['color'] = trim(str_replace(array('-', '&nbsp;'), array('', ''), strip_tags($color)));
                }
                /* Base Price */
                foreach($page->find('div[id=center_part_2] div[id=prixbarreFP1]') as $price)
                {
                    $articleDatas['base_price'] = floatval(str_replace('&euro;', '.', strip_tags($price)));
                }
                /* Price */
                foreach($page->find('div[id=center_part_2] div[id=DivInfoPrix1]') as $price)
                {
                    $articleDatas['total_price'] = floatval(str_replace('&euro;', '.', strip_tags($price)));
                }
                /* Description */
                foreach($page->find('div[id=center_part_2] span[itemprop=description]') as $description)
                {
                    $articleDatas['description'] = trim(strip_tags($description));
                }
                /* Categorie */
                foreach($page->find('div[id=center_part_2] h1[itemprop=name]') as $categorie)
                {
                    $infos = explode(' ', strip_tags($categorie));
                    $articleDatas['categorie'] = $infos[0];
                }
                /* Main image */
                foreach($produit->find('div.imagecentre img') as $image)
                {
                    $articleDatas['main_image'] = $image->src;
                }
                /* More Images */
                foreach($page->find('div[id=center_part_2] div[id=gallerievcom] img') as $image)
                {
                    $articleDatas['images'][] = array($image->src, str_replace('vc', 'fc', $image->src));
                }
                /* Add Unique Id to the Item base on Reference - Marque - Title - Couleur - Enseigne*/
                if (isset($articleDatas['marque']) && isset($articleDatas['title']) && isset($articleDatas['color']) && isset($articleDatas['main_image']) && isset($articleDatas['total_price']))
                {
                    if(mt_rand(1, 10) > 5)
                    {
                        $articleDatas['is_new'] = 1;
                    }
                    else
                    {
                        $articleDatas['is_top'] = 1;
                    }
                    if(isset($articleDatas['base_price']) && $articleDatas['base_price'] && $articleDatas['base_price'] != $articleDatas['total_price'])
                    {
                        $articleDatas['is_promo'] = 1;
                        $articleDatas['is_new'] = 0;
                        $articleDatas['is_top'] = 0;
                    }
                    if(!isset($articleDatas['categorie']))
                    {
                        $articleDatas['categorie'] = '';
                    }

                    /* Add item */
                    if (isset($_SESSION['directSave']) && $_SESSION['directSave']) {
                        if (!isset($this->fields)) {
                            $this->fields = $this->db->list_fields('item');
                        }

                        $this->import_model->add_item($articleDatas, $this->fields, false)
                    } else {
                        $articles[] = $articleDatas;
                        $add_articles++;
                    }
                }
                if ($debug)
                {
                    printr($articles);die;
                }

                /* Clean up memory */
                $page->clear();
                unset($page);
            }

            /* Clean up memory */
            $html->clear();
            unset($html);
        }

        $authorizedFields = $this->db->list_fields('item');
        $articles = array_reverse($articles);
        foreach($articles as $params)
        {
            if (!$this->import_model->add_item($params, $authorizedFields))
            {
                $add_articles--;
            }
        }
        $this->array2XML($articles, 'iso-8859-1');

        echo 'Nombre de chaussures ajout&eacute; : '.$add_articles;
        $duration = time() - $time;
        echo '<br/>Duration : '.$duration.' seconds<br/>';
    }

    function get_kiabi_new()
    {
        $urls = array(
            "http://www.kiabi.com/femme/chaussures-chaussons/235?p=1&nb=48",
            "http://www.kiabi.com/homme/chaussures-chaussons/332?p=1&nb=48",
        );
        $forceFields = array();
        $this->kiabi_basic($urls, $forceFields);
    }

    /*************************************** Redoute *************************************/
    /*                                                                                   */
    /*                                                                                   */
    /*                                                                                   */
    /*************************************************************************************/
    function redoute_basic($urls=array(), $forceFields=array(), $debug=false, $itemShape='')
    {
        $time = time();
        $articles = array();
        $add_articles = 0;
        if (!$itemShape)
        {
            $itemShape = 'div[id=nav-list] li.product';
        }

        foreach($urls as $url)
        {
            $html = file_get_html($url);
            if(!$html)
            {
                continue;
            }

            foreach($html->find($itemShape) as $product)
            {
                foreach($product->find('a.visu') as $e){}
                $e->href = explode('&categoryid', $e->href);
                $e->href = $e->href[0];
                $query = $this->db->get_where('item', array('main_link' => 'http://www.laredoute.fr'.$e->href));
                $query = $query->result();
                if (count($query) > 0)
                {
                    continue;
                }

                $articleDatas = array();
                $page = file_get_html('http://www.laredoute.fr'.$e->href);
                if(!$page)
                {
                    continue;
                }

                /* Basic Information */
                $articleDatas['main_link'] = 'http://www.laredoute.fr'.$e->href;
                $articleDatas['from'] = 'La Redoute';
                $articleDatas['create_date'] = $time - $add_articles;
                /* Genre */
                if(strpos($url, 'femme') !== false)
                {
                    $articleDatas['genre'] = 'F';
                }
                elseif(strpos($url, 'homme') !== false)
                {
                    $articleDatas['genre'] = 'H';
                }
                else
                {
                    $articleDatas['genre'] = 'E';
                }
                /* Marque */
                foreach($product->find('p.brand a') as $marque)
                {
                    $articleDatas['marque'] = trim(strip_tags($marque));
                }
                if(!isset($articleDatas['marque']))
                {
                    foreach($page->find('img[id=ctl00_ctl00_MainContentAreaPlaceHolder_MainContentAreaPlaceHolder_ProductInfo_rptrProductDetails_ctl00_imgBrandLogo]') as $marque)
                    {
                        $articleDatas['marque'] = $marque->alt;
                    }
                }
                if(!isset($articleDatas['marque']))
                {
                    $articleDatas['marque'] = 'La Redoute Création';
                }
                /* Title & Categorie */
                foreach($page->find('div[id=evo-content] div.product_infos h1[itemprop=name]') as $title)
                {
                    $title = trim(strip_tags($title));
                    $categorie = explode(' ', $title);
                    $articleDatas['title'] = $title;
                    $articleDatas['categorie'] = $categorie[0];
                }
                /* Color */
                foreach($page->find('select[id=ctl00_ctl00_MainContentAreaPlaceHolder_MainContentAreaPlaceHolder_ProductInfo_rptrProductDetails_ctl00_drpColors] option[selected=selected]') as $color)
                {
                    $articleDatas['color'] = ucfirst(strip_tags($color));
                }
                /* Base Price */
                foreach($page->find('span[id=ctl00_ctl00_MainContentAreaPlaceHolder_MainContentAreaPlaceHolder_ProductInfo_rptrProductDetails_ctl00_PreviousListPriceMinimum]') as $price)
                {
                    $articleDatas['base_price'] = floatval(str_replace(',', '.', strip_tags($price)));
                }
                /* Price */
                foreach($page->find('p[id=ctl00_ctl00_MainContentAreaPlaceHolder_MainContentAreaPlaceHolder_ProductInfo_rptrProductDetails_ctl00_ListPriceMinimum] strong[itemprop=price]') as $price)
                {
                    $articleDatas['total_price'] = floatval(str_replace(',', '.', strip_tags($price)));
                }
                /* Description */
                foreach($page->find('div[id=ctl00_ctl00_MainContentAreaPlaceHolder_MainContentAreaPlaceHolder_DetailedDescription_divDescriptionTab] div.product_infos') as $description)
                {
                    $articleDatas['description'] = str_replace('  ', '',$description);
                }
                /* Main image */
                foreach($e->find('img.thumb') as $image)
                {
                    $articleDatas['main_image'] = $image->src;
                }
                if(!isset($articleDatas['main_image']) || !$articleDatas['main_image'])
                {
                    foreach($page->find('meta[property=og:image]') as $image)
                    {
                        $articleDatas['main_image'] = $image->content;
                    }
                }
                /* More Images */
                foreach($page->find('div[id=evo-content] div.views_wrapper ul li') as $image)
                {
                    foreach($image->find('img') as $thumb)
                    {
                        $articleDatas['images'][] = array($thumb->src, $image->main);
                    }
                }
                /* Force Fields */
                foreach($forceFields as $k => $forceField)
                {
                    $articleDatas[$k] = $forceField;
                }
                /* Add Unique Id to the Item base on Reference - Marque - Title - Couleur - Enseigne*/
                if (isset($articleDatas['marque']) && isset($articleDatas['title']) && isset($articleDatas['color']) && isset($articleDatas['main_image']) && isset($articleDatas['total_price']))
                {
                    if(mt_rand(1, 10) > 5)
                    {
                        $articleDatas['is_new'] = 1;
                    }
                    else
                    {
                        $articleDatas['is_top'] = 1;
                    }
                    if(isset($articleDatas['base_price']) && $articleDatas['base_price'] && $articleDatas['base_price'] != $articleDatas['total_price'])
                    {
                        $articleDatas['is_promo'] = 1;
                        $articleDatas['is_new'] = 0;
                        $articleDatas['is_top'] = 0;
                    }
                    if(!isset($articleDatas['categorie']))
                    {
                        $articleDatas['categorie'] = '';
                    }

                    /* Add item */
                    if (isset($_SESSION['directSave']) && $_SESSION['directSave']) {
                        if (!isset($this->fields)) {
                            $this->fields = $this->db->list_fields('item');
                        }

                        $this->import_model->add_item($articleDatas, $this->fields, false)
                    } else {
                        $articles[] = $articleDatas;
                        $add_articles++;
                    }
                }
                if ($debug)
                {
                    printr($articleDatas);die;
                }

                /* Clean up memory */
                $page->clear();
                unset($page);
            }

            /* Clean up memory */
            $html->clear();
            unset($html);
        }

        $authorizedFields = $this->db->list_fields('item');
        $articles = array_reverse($articles);
        foreach($articles as $params)
        {
            if (!$this->import_model->add_item($params, $authorizedFields, false))
            {
                $add_articles--;
            }
        }
        $this->array2XML($articles);

        echo 'Nombre de chaussures ajout&eacute; : '.$add_articles;
        $duration = time() - $time;
        echo '<br/>Duration : '.$duration.' seconds<br/>';
    }

    function get_redoute_new()
    {
        $urls = array(
            "http://www.laredoute.fr/achat-chaussures-chaussures-femme.aspx?categoryid=30342981",
            "http://www.laredoute.fr/achat-chaussures-chaussures-homme.aspx?categoryid=30343336",
            "http://www.laredoute.fr/achat-chaussures-chaussures-fille.aspx?categoryid=111142170",
            "http://www.laredoute.fr/achat-chaussures-chaussures-bebe.aspx?categoryid=111142180",
            "http://www.laredoute.fr/achat-chaussures-chaussures-garcon.aspx?categoryid=111142175",
        );
        $forceFields = array();
        $this->redoute_basic($urls, $forceFields);
    }

    function get_redoute_sport()
    {
        $urls = array(
            "http://www.laredoute.fr/achat-chaussures-chaussures-sport-femme.aspx?categoryid=120002769",
            "http://www.laredoute.fr/achat-chaussures-chaussures-sport-homme.aspx?categoryid=120002782",
            "http://www.laredoute.fr/achat-chaussures-chaussures-sport-fille.aspx?categoryid=120002789",
            "http://www.laredoute.fr/achat-chaussures-chaussures-sport-garcon.aspx?categoryid=120002791",
        );
        $forceFields = array();
        $forceFields['categorie'] = 'Chaussures sport';
        $this->redoute_basic($urls, $forceFields);
    }

    function get_redoute_promo()
    {
        $urls = array(
            "http://www.laredoute.fr/achat-soldes-flottants-pe12-chaussures-femme.aspx?categoryid=120004267",
            "http://www.laredoute.fr/achat-soldes-flottants-pe12-chaussures-homme.aspx?categoryid=120004268",
            "http://www.laredoute.fr/achat-soldes-flottants-pe12-chaussures-fille.aspx?categoryid=120004269",
            "http://www.laredoute.fr/achat-soldes-flottants-pe12-chaussures-garcon.aspx?categoryid=120004270",
        );
        $forceFields['is_promo'] = '1';
        $this->redoute_basic($urls, $forceFields);
    }

    function get_aubaine_promo()
    {
        $urls = array(
            "http://www.laredoute.fr/achat-les-aubaines-aubaines-femme-chaussures.aspx?categoryid=32411212",
            "http://www.laredoute.fr/achat-les-aubaines-aubaines-femme-chaussures-bottes.aspx?categoryid=120004425",
            "http://www.laredoute.fr/achat-les-aubaines-aubaines-femme-chaussures-boots-bottines.aspx?categoryid=32411661",
            "http://www.laredoute.fr/achat-les-aubaines-aubaines-femme-chaussures-ballerines.aspx?categoryid=32411507",
            "http://www.laredoute.fr/achat-les-aubaines-aubaines-femme-chaussures-sandales-tongs.aspx?categoryid=32411289",
            "http://www.laredoute.fr/achat-les-aubaines-aubaines-femme-chaussures-escarpins.aspx?categoryid=32411354",
            "http://www.laredoute.fr/achat-les-aubaines-aubaines-femme-chaussures-baskets-tennis.aspx?categoryid=32411428",
            "http://www.laredoute.fr/achat-les-aubaines-aubaines-femme-chaussures-derbies-mocassins.aspx?categoryid=32411564",
            "http://www.laredoute.fr/achat-les-aubaines-aubaines-homme-chaussures.aspx?categoryid=23111674",
            "http://www.laredoute.fr/achat-les-aubaines-aubaines-enfant-garcon-chaussures.aspx?categoryid=111137179",
            "http://www.laredoute.fr/achat-les-aubaines-aubaines-enfant-fille-chaussures.aspx?categoryid=111137169",
        );
        $forceFields['is_promo'] = '1';
        $forceFields['from'] = 'Les Aubaines';
        $this->redoute_basic($urls, $forceFields);
    }

    /*************************************** NewLook *************************************/
    /*                                                                                   */
    /*                                                                                   */
    /*                                                                                   */
    /*************************************************************************************/
    function newlook_basic($urls=array(), $forceFields=array(), $debug=false, $itemShape='')
    {
        $time = time();
        $articles = array();
        $add_articles = 0;
        if (!$itemShape)
        {
            $itemShape = 'div.ProductListBox li.product a[class=]';
        }

        foreach($urls as $url)
        {
            $html = file_get_html($url);
            if(!$html)
            {
                continue;
            }


            foreach($html->find($itemShape) as $e)
            {
                $query = $this->db->get_where('item', array('main_link' => 'http://www.newlook.com'.$e->href));
                $query = $query->result();
                if (count($query) > 0)
                {
                    continue;
                }

                $articleDatas = array();
                $page = file_get_html('http://www.newlook.com'.$e->href);
                if(!$page)
                {
                    continue;
                }

                /* Basic Information */
                $articleDatas['main_link'] = 'http://www.newlook.com'.$e->href;
                $articleDatas['from'] = 'NewLook';
                $articleDatas['create_date'] = $time - $add_articles;
                /* Genre */
                if(strpos($url, 'womens') !== false)
                {
                    $articleDatas['genre'] = 'F';
                }
                elseif(strpos($url, 'mens') !== false)
                {
                    $articleDatas['genre'] = 'H';
                }
                else
                {
                    $articleDatas['genre'] = 'E';
                }
                /* Marque */
                $articleDatas['marque'] = 'New Look';
                /* Title */
                foreach($page->find('form[id=productDetailForm] div.prod_info div.title_container h1') as $title)
                {
                    $articleDatas['title'] = strip_tags($title);
                }
                /* Base Price */
                foreach($page->find('form[id=productDetailForm] div.prod_info div.title_container div.promo_offer span.was') as $price)
                {
                    $articleDatas['base_price'] = floatval(str_replace(',', '.', strip_tags($price)));
                }
                if(!isset($articleDatas['base_price']) || !$articleDatas['base_price'])
                {
                    foreach($page->find('form[id=productDetailForm] div.prod_info div.title_container div.promo_offer span.was span.promovalue') as $price)
                    {
                        $articleDatas['base_price'] = floatval(str_replace(',', '.', strip_tags($price)));
                    }
                }
                /* Price */
                foreach($page->find('form[id=productDetailForm] div.prod_info div.title_container div.promo_offer span.now') as $price)
                {
                    $articleDatas['total_price'] = floatval(str_replace(',', '.', strip_tags($price)));
                }
                if(!isset($articleDatas['total_price']) || !$articleDatas['total_price'])
                {
                    /* Price */
                    foreach($page->find('form[id=productDetailForm] div.prod_info div.title_container div.promo_offer span.now span.promovalue') as $price)
                    {
                        $articleDatas['total_price'] = floatval(str_replace(',', '.', strip_tags($price)));
                    }
                }
                if(!isset($articleDatas['total_price']) || !$articleDatas['total_price'])
                {
                    /* Price */
                    foreach($page->find('form[id=productDetailForm] div.prod_info div.title_container span.price span') as $price)
                    {
                        $articleDatas['total_price'] = floatval(str_replace(',', '.', strip_tags($price)));
                    }
                }
                /* Description */
                foreach($page->find('div[id=product-info]') as $description)
                {
                    $articleDatas['description'] = preg_replace('!<[^>]+>!', ' ', str_replace('  ', '',$description));
                    $articleDatas['description'] = str_replace('  ', '',$description);
                }
                /* Categorie */
                foreach($page->find('div[id=duk_content] div.breadcrumb ul li.current a') as $categorie)
                {
                    $articleDatas['categorie'] = strip_tags($categorie);
                }
                /* Main image */
                foreach($e->find('img') as $image)
                {
                    $articleDatas['main_image'] = $image->src;
                }
                /* More Images */
                foreach($page->find('div[id=thumbwrap] img.imageThumb') as $image)
                {
                    $articleDatas['images'][] = array($image->src, str_replace('?wid=75&amp;hei=98', '?wid=300&amp;hei=392', $image->src));
                }
                foreach($forceFields as $k => $forceField)
                {
                    $articleDatas[$k] = $forceField;
                }
                /* Add Unique Id to the Item base on Reference - Marque - Title - Couleur - Enseigne*/
                if (isset($articleDatas['marque']) && isset($articleDatas['title']) && isset($articleDatas['main_image']) && isset($articleDatas['total_price']))
                {
                    if(mt_rand(1, 10) > 5)
                    {
                        $articleDatas['is_new'] = '1';
                    }
                    else
                    {
                        $articleDatas['is_top'] = '1';
                    }
                    if(isset($articleDatas['base_price']) && $articleDatas['base_price'] && $articleDatas['base_price'] != $articleDatas['total_price'])
                    {
                        $articleDatas['is_promo'] = 1;
                        $articleDatas['is_new'] = 0;
                        $articleDatas['is_top'] = 0;
                    }
                    if(!isset($articleDatas['categorie']))
                    {
                        $articleDatas['categorie'] = '';
                    }

                    /* Add item */
                    if (isset($_SESSION['directSave']) && $_SESSION['directSave']) {
                        if (!isset($this->fields)) {
                            $this->fields = $this->db->list_fields('item');
                        }

                        $this->import_model->add_item($articleDatas, $this->fields, false)
                    } else {
                        $articles[] = $articleDatas;
                        $add_articles++;
                    }
                }
                if ($debug)
                {
                    printr($articles);die;
                }

                /* Clean up memory */
                $page->clear();
                unset($page);
            }

            /* Clean up memory */
            $html->clear();
            unset($html);
        }

        $authorizedFields = $this->db->list_fields('item');
        $articles = array_reverse($articles);
        foreach($articles as $params)
        {
            if (!$this->import_model->add_item($params, $authorizedFields))
            {
                $add_articles--;
            }
        }
        $this->array2XML($articles);

        echo 'Nombre de chaussures ajout&eacute; : '.$add_articles;
        $duration = time() - $time;
        echo '<br/>Duration : '.$duration.' seconds<br/>';
    }

    function get_newlook_new()
    {
        $urls = array(
            "http://www.newlook.com/eu/shop/shoe-gallery/view-all-shoes_1610001?pageSize=100&icSort=",
            "http://www.newlook.com/eu/shop/mens/shoes-and-boots_30153?pageSize=100&icSort=",
            "http://www.newlook.com/eu/shop/teens/shoes-and-boots_30187?pageSize=100&icSort=",
        );
        $forceFields = array();
        $this->newlook_basic($urls, $forceFields);
    }


    /*************************************** Pixmania ************************************/
    /*                                                                                   */
    /*                                                                                   */
    /*                                                                                   */
    /*************************************************************************************/
    function pixmania_basic($urls=array(), $forceFields=array(), $debug=false, $itemShape='')
    {
        $time = time();
        $articles = array();
        $add_articles = 0;
        if (!$itemShape)
        {
            $itemShape = 'table.results h2 a';
        }

        foreach($urls as $url)
        {
            $html = file_get_html($url);
            if(!$html)
            {
                continue;
            }

            foreach($html->find($itemShape) as $e)
            {
                $query = $this->db->get_where('item', array('main_link' => $e->href));
                $query = $query->result();
                if (count($query) > 0)
                {
                    continue;
                }

                $articleDatas = array();
                $page = file_get_html($e->href);
                if(!$page)
                {
                    continue;
                }

                /* Basic Information */
                $articleDatas['main_link'] = $e->href;
                $articleDatas['from'] = 'Pixmania';
                $articleDatas['create_date'] = $time - $add_articles;
                /* Genre */
                /* if(strpos($url, 'womens') !== false)
                {
                    $articleDatas['genre'] = 'F';
                }
                elseif(strpos($url, 'mens') !== false)
                {
                    $articleDatas['genre'] = 'H';
                }
                else
                {
                    $articleDatas['genre'] = 'E';
                } */
                /* Marque */
                foreach($page->find('span.builder-name a.brand') as $marque)
                {
                    $articleDatas['marque'] = strip_tags($marque);
                }
                /* Title */
                foreach($page->find('div.prd-name span[itemprop=name]') as $title)
                {
                    $articleDatas['title'] = str_replace('  ', '', trim(strip_tags($title)));
                }
                /* Base Price */
                foreach($page->find('p.prd-old-amount span[itemprop=price]') as $price)
                {
                    $articleDatas['base_price'] = floatval(str_replace(',', '.', strip_tags($price)));
                }
                /* Price */
                foreach($page->find('p.prd-amount span[itemprop=price]') as $price)
                {
                    $articleDatas['total_price'] = floatval(str_replace(',', '.', strip_tags($price)));
                }
                if(!isset($articleDatas['total_price']))
                {
                    /* Price */
                    foreach($page->find('form[id=productDetailForm] div.prod_info div.title_container span.price span') as $price)
                    {
                        $articleDatas['total_price'] = floatval(str_replace(',', '.', strip_tags($price)));
                    }
                }
                /* color */
                foreach($page->find('select[id=prd-color] option[selected=selected]') as $color)
                {
                    $articleDatas['color'] = strip_tags($color);
                }
                /* Description */
                foreach($page->find('div[id=pix-review] div.box-content p') as $description)
                {
                    $articleDatas['description'] = str_replace('  ', '',$description);
                }
                /* Categorie */
                foreach($page->find('p.breadcrumb a') as $categorie)
                {
                    $articleDatas['categorie'] = strip_tags($categorie);
                }
                /* Main image & More Images */
                foreach($page->find('img.prd-image') as $image)
                {
                    if(!isset($articleDatas['main_image']))
                    {
                        $articleDatas['main_image'] = str_replace('/m_', '/g_', $image->src);
                    }
                    $articleDatas['images'][] = array(str_replace('/g_', '/m_', str_replace('/m_', '/m_', $image->src)), str_replace('/g_', '/l_', str_replace('/m_', '/l_', $image->src)));
                }
                if(!isset($articleDatas['main_image']))
                {
                    foreach($page->find('div[id=area-2] a.prd-image img') as $image)
                    {
                        $articleDatas['main_image'] = $image->src;
                        $articleDatas['images'][] = array(str_replace('/g_', '/m_', $image->src), str_replace('/g_', '/l_', $image->src));
                    }
                }
                /* Force Fields */
                foreach($forceFields as $k => $forceField)
                {
                    $articleDatas[$k] = $forceField;
                }
                /* Add Unique Id to the Item base on Reference - Marque - Title - Couleur - Enseigne*/
                if (isset($articleDatas['marque']) && isset($articleDatas['title']) && isset($articleDatas['main_image']) && isset($articleDatas['total_price']))
                {
                    if(mt_rand(1, 10) > 5)
                    {
                        $articleDatas['is_new'] = 1;
                    }
                    else
                    {
                        $articleDatas['is_top'] = 1;
                    }
                    if(isset($articleDatas['base_price']) && $articleDatas['base_price'] && $articleDatas['base_price'] != $articleDatas['total_price'])
                    {
                        $articleDatas['is_promo'] = 1;
                        $articleDatas['is_new'] = 0;
                        $articleDatas['is_top'] = 0;
                    }
                    if(!isset($articleDatas['categorie']))
                    {
                        $articleDatas['categorie'] = '';
                    }

                    /* Add item */
                    if (isset($_SESSION['directSave']) && $_SESSION['directSave']) {
                        if (!isset($this->fields)) {
                            $this->fields = $this->db->list_fields('item');
                        }

                        $this->import_model->add_item($articleDatas, $this->fields, false)
                    } else {
                        $articles[] = $articleDatas;
                        $add_articles++;
                    }
                }
                if ($debug)
                {
                    printr($articleDatas);die;
                }

                /* Clean up memory */
                $page->clear();
                unset($page);
            }

            /* Clean up memory */
            $html->clear();
            unset($html);
        }

        $authorizedFields = $this->db->list_fields('item');
        $articles = array_reverse($articles);
        foreach($articles as $params)
        {
            if (!$this->import_model->add_item($params, $authorizedFields))
            {
                $add_articles--;
            }
        }
        $this->array2XML($articles, 'iso-8859-1');

        echo 'Nombre de jeans ajout&eacute; : '.$add_articles;
        $duration = time() - $time;
        echo '<br/>Duration : '.$duration.' seconds<br/>';
    }

    function get_pixmania_homme_new()
    {
        $urls = array(
            'http://www.pixmania.com/fr/fr/11616/xx/xx/2081/51/criteresn.html?sPageInfo=1_12',
            'http://www.pixmania.com/fr/fr/11617/xx/xx/2081/51/criteresn.html?sPageInfo=1_12',
            'http://www.pixmania.com/fr/fr/11619/xx/xx/2081/51/criteresn.html?sPageInfo=1_12',
            'http://www.pixmania.com/fr/fr/11621/xx/xx/2081/51/criteresn.html?sPageInfo=1_12',
            'http://www.pixmania.com/fr/fr/11622/xx/xx/2081/51/criteresn.html?sPageInfo=1_12',
            'http://www.pixmania.com/fr/fr/11623/xx/xx/2081/51/criteresn.html?sPageInfo=1_12',
        );
        $forceFields['is_new'] = '1';
        $forceFields['genre'] = 'H';
        $this->pixmania_basic($urls, $forceFields);
    }

    function get_pixmania_femme_new()
    {
        $urls = array(
            'http://www.pixmania.com/fr/fr/11635/xx/xx/2082/51/criteresn.html?sPageInfo=1_12',
            'http://www.pixmania.com/fr/fr/11625/xx/xx/2082/51/criteresn.html?sPageInfo=1_12',
            'http://www.pixmania.com/fr/fr/11626/xx/xx/2082/51/criteresn.html?sPageInfo=1_12',
            'http://www.pixmania.com/fr/fr/11627/xx/xx/2082/51/criteresn.html?sPageInfo=1_12',
            'http://www.pixmania.com/fr/fr/11628/xx/xx/2082/51/criteresn.html?sPageInfo=1_12',
            'http://www.pixmania.com/fr/fr/11629/xx/xx/2082/51/criteresn.html?sPageInfo=1_12',
            'http://www.pixmania.com/fr/fr/11630/xx/xx/2082/51/criteresn.html?sPageInfo=1_12',
            'http://www.pixmania.com/fr/fr/11631/xx/xx/2082/51/criteresn.html?sPageInfo=1_12',
            'http://www.pixmania.com/fr/fr/11632/xx/xx/2082/51/criteresn.html?sPageInfo=1_12',
            'http://www.pixmania.com/fr/fr/11633/xx/xx/2082/51/criteresn.html?sPageInfo=1_12',
            'http://www.pixmania.com/fr/fr/11634/xx/xx/2082/51/criteresn.html?sPageInfo=1_12',
        );
        $forceFields['is_new'] = '1';
        $forceFields['genre'] = 'F';
        $this->pixmania_basic($urls, $forceFields);
    }

    function get_pixmania_homme_top()
    {
        $urls = array(
            /* 'http://www.pixmania.com/fr/fr/11616/xx/xx/2081/51/criteresn.html?sPageInfo=1_12&sSortInfo=AvisConso-DESC',
            'http://www.pixmania.com/fr/fr/11617/xx/xx/2081/51/criteresn.html?sPageInfo=1_12&sSortInfo=AvisConso-DESC',
            'http://www.pixmania.com/fr/fr/11619/xx/xx/2081/51/criteresn.html?sPageInfo=1_12&sSortInfo=AvisConso-DESC',
            'http://www.pixmania.com/fr/fr/11621/xx/xx/2081/51/criteresn.html?sPageInfo=1_12&sSortInfo=AvisConso-DESC',
            'http://www.pixmania.com/fr/fr/11622/xx/xx/2081/51/criteresn.html?sPageInfo=1_12&sSortInfo=AvisConso-DESC',
            'http://www.pixmania.com/fr/fr/11623/xx/xx/2081/51/criteresn.html?sPageInfo=1_12&sSortInfo=AvisConso-DESC', */
        );
        $forceFields['is_top'] = '1';
        $forceFields['genre'] = 'H';
        $this->pixmania_basic($urls, $forceFields);
    }

    function get_pixmania_femme_top()
    {
        $urls = array(
            /* 'http://www.pixmania.com/fr/fr/11635/xx/xx/2082/51/criteresn.html?sPageInfo=1_12&sSortInfo=AvisConso-DESC',
            'http://www.pixmania.com/fr/fr/11625/xx/xx/2082/51/criteresn.html?sPageInfo=1_12&sSortInfo=AvisConso-DESC',
            'http://www.pixmania.com/fr/fr/11626/xx/xx/2082/51/criteresn.html?sPageInfo=1_12&sSortInfo=AvisConso-DESC',
            'http://www.pixmania.com/fr/fr/11627/xx/xx/2082/51/criteresn.html?sPageInfo=1_12&sSortInfo=AvisConso-DESC',
            'http://www.pixmania.com/fr/fr/11628/xx/xx/2082/51/criteresn.html?sPageInfo=1_12&sSortInfo=AvisConso-DESC',
            'http://www.pixmania.com/fr/fr/11629/xx/xx/2082/51/criteresn.html?sPageInfo=1_12&sSortInfo=AvisConso-DESC',
            'http://www.pixmania.com/fr/fr/11630/xx/xx/2082/51/criteresn.html?sPageInfo=1_12&sSortInfo=AvisConso-DESC',
            'http://www.pixmania.com/fr/fr/11631/xx/xx/2082/51/criteresn.html?sPageInfo=1_12&sSortInfo=AvisConso-DESC',
            'http://www.pixmania.com/fr/fr/11632/xx/xx/2082/51/criteresn.html?sPageInfo=1_12&sSortInfo=AvisConso-DESC',
            'http://www.pixmania.com/fr/fr/11633/xx/xx/2082/51/criteresn.html?sPageInfo=1_12&sSortInfo=AvisConso-DESC',
            'http://www.pixmania.com/fr/fr/11634/xx/xx/2082/51/criteresn.html?sPageInfo=1_12&sSortInfo=AvisConso-DESC', */
        );
        $forceFields['is_top'] = '1';
        $forceFields['genre'] = 'F';
        $this->pixmania_basic($urls, $forceFields);
    }
}
