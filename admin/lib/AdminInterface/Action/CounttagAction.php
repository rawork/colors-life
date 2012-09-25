<?php

namespace AdminInterface\Action;

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
		$this->get('connection')->execQuery('truncate_tags', "TRUNCATE TABLE articles_tags");
		$this->get('connection')->execQuery('truncate_tags', "TRUNCATE TABLE articles_tags_articles");
		$this->get('connection')->execQuery('truncate_tags', "TRUNCATE TABLE articles_stuff_articles");
		$articles = $this->get('connection')->getItems('get_art', "SELECT id,tags FROM articles_articles WHERE publish='on'");
		$tags_full = array();
		foreach ($articles as $article) {

			$tags = $article['tags'];
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
			$this->get('connection')->execQuery('add_tag', "INSERT INTO articles_tags(name, quantity) VALUES('$tag', ".$tag_info['q'].")");
			$last_id = $this->get('connection')->getInsertID();
			foreach ($tag_info['articles'] as $article_id) {
				$this->get('connection')->execQuery('add_tag_links', "INSERT INTO articles_tags_articles(tag_id, article_id) VALUES($last_id, $article_id)");
			}
		}
		$tag_max_min = $this->get('connection')->getItems('get_tags', "SELECT max(quantity) as max, min(quantity) as min FROM articles_tags");
		$min = intval($tag_max_min[0]['min']);
		$max = intval($tag_max_min[0]['max']);

		$minsize = 1;
		$maxsize = 10;
		$tags = $this->get('connection')->getItems('get_tags', "SELECT id, name, quantity FROM articles_tags");
		foreach ($tags as $tag) {

			if ($min == $max) {
				$num = ($maxsize - $minsize)/2 + $minsize;
				$fontSize = round($num);
			} else {
				$num = ($tag['quantity'] - $min)/($max - $min)*($maxsize - $minsize) + $minsize;
				$fontSize = round($num);
			}
			$this->get('connection')->execQuery('update_tag_info', "UPDATE articles_tags SET position='".$fontSize."' WHERE id=".$tag['id']);
		}


		$brands = $this->get('connection')->getItems('get_brands', "SELECT cp.id, cp.name, count(cs.id) as quantity FROM catalog_producers cp JOIN catalog_stuff cs ON cp.id=cs.producer_id WHERE cs.publish='on' GROUP BY cp.id");
		$min = $brands[0]['quantity'];
		$max = $brands[0]['quantity'];
		foreach ($brands as $brand) {
			if ($brand['quantity'] > $max) {
				$max = $brand['quantity'];
			}
			if ($brand['quantity'] < $min) {
				$min = $brand['quantity'];
			}
			$this->get('connection')->execQuery('update_tag_info', "UPDATE catalog_producers SET quantity='".$brand['quantity']."' WHERE id=".$brand['id']);
		}

		$brands = $this->get('connection')->getItems('get_brands', "SELECT id, name, quantity FROM catalog_producers");
		foreach ($brands as $brand) {

			if ($min == $max) {
				$num = ($maxsize - $minsize)/2 + $minsize;
				$fontSize = round($num);
			} else {
				$num = ($brand['quantity'] - $min)/($max - $min)*($maxsize - $minsize) + $minsize;
				$fontSize = round($num);
			}
			$this->get('connection')->execQuery('update_tag_info', "UPDATE catalog_producers SET position='".$fontSize."' WHERE id=".$brand['id']);
		}
		//die();

		foreach ($tags as $tag) {
			$items = $this->get('connection')->getItems('get_stuff', "SELECT id,name FROM catalog_stuff WHERE tags LIKE '%".$tag['name']."%'");
			if (count($items)) {
				$articles = $this->get('connection')->getItems('get_articles', "SELECT article_id FROM articles_tags_articles WHERE tag_id=".$tag['id']);
				foreach ($items as $item) {
					foreach ($articles as $article) {
						$this->get('connection')->execQuery('add_stuff_links', "INSERT INTO articles_stuff_articles(stuff_id, article_id) VALUES(".$item['id'].", ".$article['article_id'].")");
					}
				}
			}
		}

		$categories = $this->get('connection')->getItems('get_cats', "SELECT id,p_id,name FROM catalog_categories WHERE p_id=0");
		foreach ($categories as $category) {
			$categories2 = $this->get('connection')->getItems('get_cats', "SELECT id,p_id,name FROM catalog_categories WHERE p_id=".$category['id']);
			foreach ($categories2 as $category2) {
				$this->get('connection')->execQuery('add_stuff_links', "UPDATE catalog_categories SET root_c_id=".$category['id']." WHERE id=".$category['id']." OR id=".$category2['id']." OR p_id=".$category2['id']);
			}
		}
	}
	
	function getEntities($sTableName) {
		$aEntities = $this->get('connection')->getItems('eee', "SELECT id FROM $sTableName WHERE publish='on'");
		return $aEntities;
	}

	function getTreeEntities($sTableName, $iParentId = 0) {
		$aEntities = array();
		$aTempEntities = $this->get('connection')->getItems('eee', "SELECT * FROM $sTableName WHERE publish='on' AND p_id = $iParentId");
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
		$aSections = $this->getTreeEntities('tree_tree', 0);
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
		$aCategories = $this->getTreeEntities('catalog_categories', 0);
		foreach ($aCategories as $aCategory) {
			$sURL = 'catalog/index.'.$aCategory['id'].'.htm';
			$link = <<<EOD
<url>
	<loc>http://www.colors-life.ru/$sURL</loc>
	<lastmod>$sDate</lastmod>
	<changefreq>$sChange</changefreq>
</url>
EOD;
			fwrite($fh, $link."\n");
		}

		$aEntities = $this->getEntities('articles_articles');
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

		$aEntities = $this->getEntities('catalog_stuff');
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

		$aCategories = $this->getTreeEntities('catalog_categories');
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
			$iParentId	= $aCategory['p_id'];
			$sName		= htmlspecialchars(strip_tags($aCategory['name']));
			$ymlcontent .= "<category id=\"$iId\" parentId=\"$iParentId\">$sName</category>\n";  // у нас всего одна категория
		}
		$ymlcontent .= "</categories>\n";
		$ymlcontent .= "<offers>\n";

		$aStuff = $this->get('container')->getItems('catalog_stuff', "publish='on' AND price<>'0.00'"); // выбираем все товары
		foreach ($aStuff as $aRow) // в цикле обрабатываем каждый товар
		{
			$sName			= htmlspecialchars(strip_tags($aRow['name']));
			$sProducer		= htmlspecialchars(strip_tags($aRow['producer_id_name']));
			$sDescription	= str_replace('&laquo;', '&quot;', htmlspecialchars(strip_tags($aRow['description'])));
			$sDescription	= str_replace('&raquo;', '&quot;', $sDescription);
			$sAvailable		= $aRow['is_exist'] ? 'true' : 'false';

			$ymlcontent .= "<offer id=\"".$aRow['id']."\" available=\"".$sAvailable."\">\n";  // id товара
			$ymlcontent .= "<url>http://colors-life.ru/catalog/stuff.".$aRow['id'].".htm</url>\n";  // ссылка на страницу товара ( полностью )
			$ymlcontent .= "<price>".$aRow['price']."</price>\n";  // стоимость продукта
			$ymlcontent .= "<currencyId>RUR</currencyId>\n"; // валюта
			$ymlcontent .= "<categoryId>".$aRow['c_id']."</categoryId>\n"; // ID категории
			$ymlcontent .= "<picture>http://colors-life.ru".$aRow['image']."</picture>\n";  // ссылка на картинку ( полностью )
			$ymlcontent .= "<delivery>true</delivery>\n";
			//$ymlcontent .= "<local_delivery_cost>375</local_delivery_cost>\n";
			$ymlcontent .= "<name>".$sName."</name>\n";  // название товара
			$ymlcontent .= "<vendor>".$sProducer."</vendor>\n";
			$ymlcontent .= "<vendorCode>".$aRow['articul']."</vendorCode>\n";
			$ymlcontent .= "<description>$sDescription</description>\n"; // описание продукта
			$ymlcontent .= "<country_of_origin>".$aRow['producer_id_country']."</country_of_origin>\n";
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
		$items = $this->get('connection')->getItems('items', "SELECT id, image, small_image, big_image FROM catalog_stuff");
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
			$this->get('connection')->execQuery('upd', "UPDATE catalog_stuff SET image='$sNewPath1', small_image='$sNewPath2', big_image='$sNewPath3' WHERE id=".$item['id']);
		}
	}

	private function _clearImages() {
		$items = $this->get('connection')->getItems('items', "SELECT id, image, small_image, big_image FROM catalog_stuff");
		foreach ($items as $item) {
			if ($item['big_image'] && $item['big_image'] != '/upload/stuff_'.$item['id'].'_big.jpg' ) {
				$this->get('connection')->execQuery('upd', "UPDATE catalog_stuff SET big_image='' WHERE id=".$item['id']);
			}

		}
	}
	
	private function fixImages() {
		global $UPLOAD_REF, $PRJ_DIR;
		$date = new \Datetime('2012-01-01');
		$step = 50;
		$i = 50;
		$items = $this->get('connection')->getItems('items', "SELECT id, image, small_image, big_image FROM catalog_stuff");
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
			
			$this->get('connection')->execQuery('upd', "UPDATE catalog_stuff SET small_image='".$smallImage."',image='".$image."',big_image='".$bigImage."' WHERE id=".$item['id']);
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
