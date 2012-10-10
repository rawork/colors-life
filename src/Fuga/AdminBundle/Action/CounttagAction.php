<?php

namespace Fuga\AdminBundle\Action;

class CounttagAction extends Action {

	function __construct(&$adminController) {
		parent::__construct($adminController);
	}

	function getText() {
		
//		$this->fixImages();
//		$this->fixFiles();
//		$this->fixNews();
//		$this->fixTables(); 
		
		$this->calculateCatalog();
		$this->buildSitemapXML();
		$this->buildShopYML('shop.yml');
		
		//$this->correctImages();
		//$this->_clearImages();

		$this->messageAction(false ? 'Ошибка расчета тегов' : 'Расчет тегов завершен');
	}
	
	function calculateCatalog() {
		$this->get('connection')->execQuery('truncate_tags', "TRUNCATE TABLE article_tag");
		$this->get('connection')->execQuery('truncate_tags', "TRUNCATE TABLE article_tags_articles");
		$this->get('connection')->execQuery('truncate_tags', "TRUNCATE TABLE article_products_articles");
		$articles = $this->get('connection')->getItems('get_art', "SELECT id,tag FROM article_article WHERE publish=1");
		$tags_full = array();
		foreach ($articles as $article) {

			$tags = $article['tag'];
			$tags_array = explode(',', $tags);
			foreach ($tags_array as $tag) {
				$tag = trim($tag);
				if (!isset($tags_full[$tag])) {
					$tags_full[$tag] = array('q' => 1, 'articles' => array($article['id']));
				} else {
					$tags_full[$tag]['q']++;
					$tags_full[$tag]['articles'][] = $article['id'];
				}
			}
		}
		foreach ($tags_full as $tag => $tag_info) {
			$this->get('connection')->execQuery('add_tag', "INSERT INTO article_tag(name, quantity) VALUES('$tag', ".$tag_info['q'].")");
			$last_id = $this->get('connection')->getInsertID();
			foreach ($tag_info['articles'] as $article_id) {
				$this->get('connection')->execQuery('add_tag_links', "INSERT INTO article_tags_articles(tag_id, article_id) VALUES($last_id, $article_id)");
			}
		}
		$tag_max_min = $this->get('connection')->getItems('get_tags', "SELECT max(quantity) as max, min(quantity) as min FROM article_tag");
		$min = intval($tag_max_min[0]['min']);
		$max = intval($tag_max_min[0]['max']);

		$minsize = 1;
		$maxsize = 10;
		$tags = $this->get('connection')->getItems('get_tags', "SELECT id, name, quantity FROM article_tag");
		foreach ($tags as $tag) {

			if ($min == $max) {
				$num = ($maxsize - $minsize)/2 + $minsize;
				$fontSize = round($num);
			} else {
				$num = ($tag['quantity'] - $min)/($max - $min)*($maxsize - $minsize) + $minsize;
				$fontSize = round($num);
			}
			$this->get('connection')->execQuery('update_tag_info', "UPDATE article_tag SET weight='".$fontSize."' WHERE id=".$tag['id']);
		}


		$brands = $this->get('connection')->getItems('get_brands', "SELECT cp.id, cp.name, count(cs.id) as quantity FROM catalog_producer cp JOIN catalog_product cs ON cp.id=cs.producer_id WHERE cs.publish=1 GROUP BY cp.id");
		$min = $brands[0]['quantity'];
		$max = $brands[0]['quantity'];
		foreach ($brands as $brand) {
			if ($brand['quantity'] > $max) {
				$max = $brand['quantity'];
			}
			if ($brand['quantity'] < $min) {
				$min = $brand['quantity'];
			}
			$this->get('connection')->execQuery('update_tag_info', "UPDATE catalog_producer SET quantity='".$brand['quantity']."' WHERE id=".$brand['id']);
		}

		$brands = $this->get('connection')->getItems('get_brands', "SELECT id, name, quantity FROM catalog_producer");
		foreach ($brands as $brand) {

			if ($min == $max) {
				$num = ($maxsize - $minsize)/2 + $minsize;
				$fontSize = round($num);
			} else {
				$num = ($brand['quantity'] - $min)/($max - $min)*($maxsize - $minsize) + $minsize;
				$fontSize = round($num);
			}
			$this->get('connection')->execQuery('update_tag_info', "UPDATE catalog_producer SET weight='".$fontSize."' WHERE id=".$brand['id']);
		}
		//die();

		foreach ($tags as $tag) {
			$items = $this->get('connection')->getItems('get_products', "SELECT id,name FROM catalog_product WHERE tag LIKE '%".$tag['name']."%'");
			if (count($items)) {
				$articles = $this->get('connection')->getItems('get_articles', "SELECT article_id FROM article_tags_articles WHERE tag_id=".$tag['id']);
				foreach ($items as $item) {
					foreach ($articles as $article) {
						$this->get('connection')->execQuery('add_products_links', "INSERT INTO article_products_articles(product_id, article_id) VALUES(".$item['id'].", ".$article['article_id'].")");
					}
				}
			}
		}

		$categories = $this->get('connection')->getItems('get_cats', "SELECT id,parent_id,title FROM catalog_category WHERE parent_id=0");
		foreach ($categories as $category) {
			$categories2 = $this->get('connection')->getItems('get_cats', "SELECT id,parent_id,title FROM catalog_category WHERE parent_id=".$category['id']);
			foreach ($categories2 as $category2) {
				$this->get('connection')->execQuery('add_products_links', "UPDATE catalog_category SET root_id=".$category['id']." WHERE id=".$category['id']." OR id=".$category2['id']." OR parent_id=".$category2['id']);
			}
		}
	}
	
	function getEntities($sTableName) {
		$aEntities = $this->get('connection')->getItems('eee', "SELECT id FROM $sTableName WHERE publish=1");
		return $aEntities;
	}

	function getTreeEntities($sTableName, $iParentId = 0) {
		$aEntities = array();
		$aTempEntities = $this->get('connection')->getItems('eee', "SELECT * FROM $sTableName WHERE publish=1 AND parent_id = $iParentId");
		foreach ($aTempEntities as $aTempEntity) {
			$aSubEntities = $this->getTreeEntities($sTableName, $aTempEntity['id']);
			$aEntities[] = $aTempEntity;
			$aEntities = array_merge($aEntities, $aSubEntities);
		}
		//$aEntities = array_merge($aEntities, $aTempEntities);
		return $aEntities;
	}

	function buildSitemapXML() {
		global $PRJ_DIR;

		$fh = fopen($PRJ_DIR.'/sitemap.xml', 'w+');
		fwrite($fh, '<?xml version="1.0" encoding="UTF-8"?>'."\n");
		fwrite($fh, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n");
		$sDate		= date('Y-m-d');
		$sChange	= 'daily'; //     always, hourly, daily, weekly, monthly, yearly, never

		$link = <<<EOD
<url>
	<loc>http://www.colors-life.ru/</loc>
	<lastmod>$sDate</lastmod>
	<changefreq>$sChange</changefreq>
	<priority>0.8</priority>
</url>
EOD;
		fwrite($fh, $link."\n");
		$aSections = $this->getTreeEntities('page_page', 0);
		$sChange = 'weekly';
		foreach ($aSections as $aSection) {
			$sURL = $aSection['module_id'] ? $aSection['name'].'/' : $aSection['name'].'.htm';
			$link = <<<EOD
<url>
	<loc>http://www.colors-life.ru/$sURL</loc>
	<lastmod>$sDate</lastmod>
	<changefreq>$sChange</changefreq>
</url>
EOD;
			fwrite($fh, $link."\n");
		}
		$categories = $this->getTreeEntities('catalog_category', 0);
		foreach ($categories as $category) {
			$sURL = 'catalog/index.'.$category['id'].'.htm';
			$link = <<<EOD
<url>
	<loc>http://www.colors-life.ru/$sURL</loc>
	<lastmod>$sDate</lastmod>
	<changefreq>$sChange</changefreq>
</url>
EOD;
			fwrite($fh, $link."\n");
		}

		$aEntities = $this->getEntities('article_article');
		foreach ($aEntities as $aEntity) {
			$sURL = 'articles/read.'.$aEntity['id'].'.htm';
			$link = <<<EOD
<url>
	<loc>http://www.colors-life.ru/$sURL</loc>
	<lastmod>$sDate</lastmod>
	<changefreq>$sChange</changefreq>
</url>
EOD;
			fwrite($fh, $link."\n");
		}

		$aEntities = $this->getEntities('catalog_product');
		foreach ($aEntities as $aEntity) {
			$sURL = 'catalog/stuff.'.$aEntity['id'].'.htm';
			$link = <<<EOD
<url>
	<loc>http://www.colors-life.ru/$sURL</loc>
	<lastmod>$sDate</lastmod>
	<changefreq>$sChange</changefreq>
</url>
EOD;
			fwrite($fh, $link."\n");
		}

		$aEntities = $this->getEntities('news_news');
		foreach ($aEntities as $aEntity) {
			$sURL = 'news/read.'.$aEntity['id'].'.htm';
			$link = <<<EOD
<url>
	<loc>http://www.colors-life.ru/$sURL</loc>
	<lastmod>$sDate</lastmod>
	<changefreq>$sChange</changefreq>
</url>
EOD;
			fwrite($fh, $link."\n");
		}

		fwrite($fh, '</urlset>'."\n");
		fclose($fh);
	}

	function buildShopYML($filename) {
		global $PRJ_DIR;
		$filepath = $PRJ_DIR."/yml/"; // Путь к файлу
		$ymlcontent = ""; // контент yml файла
		$f = fopen($filepath.$filename, "w") or die("Error opening file"); // открываем файл на запись

		$aCategories = $this->getTreeEntities('catalog_category');
		// блок создания контента
		$ymlcontent = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";   // файл формата XML 1.0
		$ymlcontent .= "<!DOCTYPE yml_catalog SYSTEM \"shops.dtd\">\n";   //  тип файла - файл Yandex Маркета
		$ymlcontent .= "<yml_catalog date=\"".date("Y-m-d H:i")."\">\n";   // дата создания файла
		$ymlcontent .= "<shop>\n";    // начинаем описывать структуру. Основа структуры файла - элемент shop
		$ymlcontent .= "<name>Цвета жизни</name>\n";  //  название магазина
		$ymlcontent .= "<company>Цвета жизни</company>\n";  // title  - заголовок вашего магазина
		$ymlcontent .= "<url>http://colors-life.ru/</url>\n"; // url адрес магазина
		$ymlcontent .= "<currencies><currency id=\"RUR\" rate=\"1\"/></currencies>\n";   // список валют, в нашем случае только рубли
		$ymlcontent .= "<categories>\n";  // описываем категории продукции, у каждой категории свой уникальный ID
		foreach ($aCategories as $aCategory) {
			$iId		= $aCategory['id'];
			$iParentId	= $aCategory['parent_id'];
			$sName		= htmlspecialchars(strip_tags($aCategory['title']));
			$ymlcontent .= "<category id=\"$iId\" parentId=\"$iParentId\">$sName</category>\n";  // у нас всего одна категория
		}
		$ymlcontent .= "</categories>\n";
		$ymlcontent .= "<offers>\n";

		$aStuff = $this->get('container')->getItems('catalog_product', "publish=1 AND price<>'0.00'"); // выбираем все товары
		foreach ($aStuff as $aRow) // в цикле обрабатываем каждый товар
		{
			$sName			= htmlspecialchars(strip_tags($aRow['name']));
			$sProducer		= htmlspecialchars(strip_tags((isset($aRow['producer_id_name']) ? $aRow['producer_id_name'] : '')));
			$sDescription	= str_replace('&laquo;', '&quot;', htmlspecialchars(strip_tags($aRow['description'])));
			$sDescription	= str_replace('&raquo;', '&quot;', $sDescription);
			$sAvailable		= $aRow['is_exist'] ? 'true' : 'false';

			$ymlcontent .= "<offer id=\"".$aRow['id']."\" available=\"".$sAvailable."\">\n";  // id товара
			$ymlcontent .= "<url>http://colors-life.ru/catalog/stuff.".$aRow['id'].".htm</url>\n";  // ссылка на страницу товара ( полностью )
			$ymlcontent .= "<price>".$aRow['price']."</price>\n";  // стоимость продукта
			$ymlcontent .= "<currencyId>RUR</currencyId>\n"; // валюта
			$ymlcontent .= "<categoryId>".$aRow['category_id']."</categoryId>\n"; // ID категории
			$ymlcontent .= "<picture>http://colors-life.ru".$aRow['image']."</picture>\n";  // ссылка на картинку ( полностью )
			$ymlcontent .= "<delivery>true</delivery>\n";
			//$ymlcontent .= "<local_delivery_cost>375</local_delivery_cost>\n";
			$ymlcontent .= "<name>".$sName."</name>\n";  // название товара
			$ymlcontent .= "<vendor>".$sProducer."</vendor>\n";
			$ymlcontent .= "<vendorCode>".$aRow['articul']."</vendorCode>\n";
			$ymlcontent .= "<description>$sDescription</description>\n"; // описание продукта
			$ymlcontent .= "<country_of_origin>".(isset($aRow['producer_id_country']) ? $aRow['producer_id_country'] : '')."</country_of_origin>\n";
			$ymlcontent .= "</offer>\n";
		}
		$ymlcontent .= "</offers>\n";  // дописываем закрывающие тэги
		$ymlcontent .= "</shop>\n";
		$ymlcontent .= "</yml_catalog>";


		fputs($f, $ymlcontent);  // записываем наш контент в файл
		fclose($f);
	}

	function correctImages() {
		global $PRJ_DIR;
		$items = $this->get('connection')->getItems('items', "SELECT id, image, small_image, big_image FROM catalog_product");
		foreach ($items as $item) {
			$basename = 'stuff_';
			$sNewPath1 = '';
			$sNewPath2 = '';
			$sNewPath3 = '';
			if ($item['image']) {
				$path_parts = pathinfo($item['image']);
				$sNewPath1 = $path_parts['dirname'].'/'.$basename.$item['id'].".".$path_parts['extension'];
				rename($PRJ_DIR.$item['image'], $PRJ_DIR.$sNewPath1);
			}
			if ($item['small_image']) {
				$path_parts = pathinfo($item['small_image']);
				$sNewPath2 = $path_parts['dirname'].'/'.$basename.$item['id']."_small.".$path_parts['extension'];
				rename($PRJ_DIR.$item['small_image'], $PRJ_DIR.$sNewPath2);
			}
			if ($item['big_image']) {
				$path_parts = pathinfo($item['big_image']);
				$sNewPath3 = $path_parts['dirname'].'/'.$basename.$item['id']."_big.".$path_parts['extension'];
				rename($PRJ_DIR.$item['big_image'], $PRJ_DIR.$sNewPath3);
			}
			$this->get('connection')->execQuery('upd', "UPDATE catalog_product SET image='$sNewPath1', small_image='$sNewPath2', big_image='$sNewPath3' WHERE id=".$item['id']);
		}
	}

	private function _clearImages() {
		$items = $this->get('connection')->getItems('items', "SELECT id, image, small_image, big_image FROM catalog_product");
		foreach ($items as $item) {
			if ($item['big_image'] && $item['big_image'] != '/upload/stuff_'.$item['id'].'_big.jpg' ) {
				$this->get('connection')->execQuery('upd', "UPDATE catalog_product SET big_image='' WHERE id=".$item['id']);
			}

		}
	}
	
	private function fixImages() {
		global $UPLOAD_REF, $PRJ_DIR;
		$date = new \Datetime('2012-01-01');
		$step = 50;
		$i = 50;
		$items = $this->get('connection')->getItems('items', "SELECT id, image, small_image, big_image FROM catalog_product");
		foreach ($items as $item) {
			if ($i >= $step) {
				$date->add(new \DateInterval('P1D'));
				$path = $UPLOAD_REF.$date->format('/Y/m/d/');
				@mkdir($PRJ_DIR.$path, 0755, true);
				$i = 0;
			}
			if ($item['small_image']) {
				$pathParts = pathinfo($item['small_image']);
				$smallImage = $path. $pathParts['basename'];
				@rename($PRJ_DIR.$item['small_image'], $PRJ_DIR.$smallImage);
			} else {
				$smallImage = $item['small_image'];
			}
			if ($item['image']) {
				$pathParts = pathinfo($item['image']);
				$image = $path. $pathParts['basename'];
				@rename($PRJ_DIR.$item['image'], $PRJ_DIR.$image);
			} else {
				$image = $item['image'];
			}
			if ($item['big_image']) {
				$pathParts = pathinfo($item['big_image']);
				$bigImage = $path. $pathParts['basename'];
				@rename($PRJ_DIR.$item['big_image'], $PRJ_DIR.$bigImage);
			} else {
				$bigImage = $item['big_image'];
			}
			if ($smallImage == $item['small_image']) {
				continue;
			}
			
			$this->get('connection')->execQuery('upd', "UPDATE catalog_product SET small_image='".$smallImage."',image='".$image."',big_image='".$bigImage."' WHERE id=".$item['id']);
			$i++;
		}
	}
	
	private function fixNews() {
		global $UPLOAD_REF, $PRJ_DIR;
		$date = new \Datetime('2012-05-01');
		$step = 50;
		$i = 50;
		$items = $this->get('connection')->getItems('items', "SELECT id, image FROM news_news");
		foreach ($items as $item) {
			if ($i >= $step) {
				$date->add(new \DateInterval('P1D'));
				$path = $UPLOAD_REF.$date->format('/Y/m/d/');
				@mkdir($PRJ_DIR.$path, 0755, true);
				$i = 0;
			}
			if ($item['image']) {
				$pathParts = pathinfo($item['image']);
				$image = $path. $pathParts['basename'];
				@rename($PRJ_DIR.$item['image'], $PRJ_DIR.$image);
			} else {
				$image = $item['image'];
			}
			
			if ($image == $item['image']) {
				continue;
			}
			
			$this->get('connection')->execQuery('upd', "UPDATE news_news SET image='".$image."' WHERE id=".$item['id']);
			$i++;
		}
	}
	
	private function fixFiles() {
		global $UPLOAD_REF, $PRJ_DIR;
		$date = new \Datetime('2012-04-01');
		$step = 50;
		$i = 50;
		$items = $this->get('connection')->getItems('items', "SELECT id, file FROM system_files");
		foreach ($items as $item) {
			if ($i >= $step) {
				$date->add(new \DateInterval('P1D'));
				$path = $UPLOAD_REF.$date->format('/Y/m/d/');
				@mkdir($PRJ_DIR.$path, 0755, true);
				$i = 0;
			}
			if ($item['file']) {
				$pathParts = pathinfo($item['file']);
				$newPath = $path. $pathParts['basename'];
				@rename($PRJ_DIR.$item['file'], $PRJ_DIR.$newPath);
			} else {
				$newPath = $item['file'];
			}
			
			if ($newPath == $item['file']) {
				continue;
			}
			
			$this->get('connection')->execQuery('upd', "UPDATE system_files SET file='".$newPath."' WHERE id=".$item['id']);
			$i++;
		}
	}
	
	private function fixTables() {
		$items = $this->get('connection')->getItems('items', "SHOW TABLES");
		$items = array_values($items);
		foreach ($items as $table) {
			$tableName = array_values($table);
			$this->get('connection')->execQuery('alter', 'ALTER TABLE `'.$tableName[0].'` DROP COLUMN classid, DROP COLUMN seniorid');
		}
	}
	
}
