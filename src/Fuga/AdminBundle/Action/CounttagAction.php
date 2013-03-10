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
		
		$this->fixNested();
		$this->calculateCatalog();
		$this->buildSitemapXML();
		$this->buildShopYML('shop.yml');
		
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
	
	function getEntities($table) {
		$items = $this->get('connection')->getItems('eee', "SELECT id FROM $table WHERE publish=1");
		return $items;
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
		$date		= date('Y-m-d');
		$period	= 'weekly'; //     always, hourly, daily, weekly, monthly, yearly, never

		$link = <<<EOD
<url>
	<loc>http://www.colors-life.ru/</loc>
	<lastmod>$date</lastmod>
	<changefreq>$period</changefreq>
	<priority>0.8</priority>
</url>
EOD;
		fwrite($fh, $link."\n");
		$nodes = $this->getTreeEntities('page_page', 0);
		$period = 'weekly';
		foreach ($nodes as $node) {
			$url = $this->get('container')->href($node['name']);
			$link = <<<EOD
<url>
	<loc>http://www.colors-life.ru$url</loc>
	<lastmod>$date</lastmod>
	<changefreq>$period</changefreq>
</url>
EOD;
			fwrite($fh, $link."\n");
		}
		$categories = $this->getTreeEntities('catalog_category', 0);
		foreach ($categories as $category) {
			$url = $this->get('container')->href('catalog', 'index', array($category['id']));
			$link = <<<EOD
<url>
	<loc>http://www.colors-life.ru$url</loc>
	<lastmod>$date</lastmod>
	<changefreq>$period</changefreq>
</url>
EOD;
			fwrite($fh, $link."\n");
		}

		$items = $this->getEntities('article_article');
		foreach ($items as $item) {
			$url = $this->get('container')->href('articles', 'read', array($item['id']));
			$link = <<<EOD
<url>
	<loc>http://www.colors-life.ru$url</loc>
	<lastmod>$date</lastmod>
	<changefreq>$period</changefreq>
</url>
EOD;
			fwrite($fh, $link."\n");
		}

		$items = $this->getEntities('catalog_product');
		foreach ($items as $item) {
			$url = $this->get('container')->href('catalog', 'stuff', array($item['id']));
			$link = <<<EOD
<url>
	<loc>http://www.colors-life.ru$url</loc>
	<lastmod>$date</lastmod>
	<changefreq>$period</changefreq>
</url>
EOD;
			fwrite($fh, $link."\n");
		}

		$items = $this->getEntities('news_news');
		foreach ($items as $item) {
			$url = $this->get('container')->href('news', 'read', array($item['id']));
			$link = <<<EOD
<url>
	<loc>http://www.colors-life.ru$url</loc>
	<lastmod>$date</lastmod>
	<changefreq>$period</changefreq>
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
		$content = ""; // контент yml файла
		$f = fopen($filepath.$filename, "w") or die("Error opening file"); // открываем файл на запись

		$categories = $this->getTreeEntities('catalog_category');
		// блок создания контента
		$content = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";   // файл формата XML 1.0
		$content .= "<!DOCTYPE yml_catalog SYSTEM \"shops.dtd\">\n";   //  тип файла - файл Yandex Маркета
		$content .= "<yml_catalog date=\"".date("Y-m-d H:i")."\">\n";   // дата создания файла
		$content .= "<shop>\n";    // начинаем описывать структуру. Основа структуры файла - элемент shop
		$content .= "<name>Цвета жизни</name>\n";  //  название магазина
		$content .= "<company>Цвета жизни</company>\n";  // title  - заголовок вашего магазина
		$content .= "<url>http://colors-life.ru/</url>\n"; // url адрес магазина
		$content .= "<currencies><currency id=\"RUR\" rate=\"1\"/></currencies>\n";   // список валют, в нашем случае только рубли
		$content .= "<categories>\n";  // описываем категории продукции, у каждой категории свой уникальный ID
		foreach ($categories as $category) {
			$id		= $category['id'];
			$parentId	= $category['parent_id'];
			$name		= htmlspecialchars(strip_tags($category['title']));
			$content .= "<category id=\"$id\" parentId=\"$parentId\">$name</category>\n";  // у нас всего одна категория
		}
		$content .= "</categories>\n";
		$content .= "<offers>\n";

		$products = $this->get('container')->getItems('catalog_product', "publish=1 AND price<>'0.00'"); // выбираем все товары
		foreach ($products as $product) // в цикле обрабатываем каждый товар
		{
			$name			= htmlspecialchars(strip_tags($product['name']));
			$producer		= htmlspecialchars(strip_tags((isset($product['producer_id_name']) ? $product['producer_id_name'] : '')));
			$description	= str_replace('&laquo;', '&quot;', htmlspecialchars(strip_tags($product['description'])));
			$description	= str_replace('&raquo;', '&quot;', $description);
			$url			= 'http://colors-life.ru'.$this->get('container')->href('catalog', 'stuff', array($product['id']));
			
			$is_exist		= $product['is_exist'] ? 'true' : 'false';

			$content .= "<offer id=\"".$product['id']."\" available=\"".$is_exist."\">\n";  // id товара
			$content .= "<url>$url</url>\n";  // ссылка на страницу товара ( полностью )
			$content .= "<price>".$product['price']."</price>\n";  // стоимость продукта
			$content .= "<currencyId>RUR</currencyId>\n"; // валюта
			$content .= "<categoryId>".$product['category_id']."</categoryId>\n"; // ID категории
			$content .= "<picture>http://colors-life.ru".$product['image']."</picture>\n";  // ссылка на картинку ( полностью )
			$content .= "<delivery>true</delivery>\n";
			$content .= "<name>".$name."</name>\n";  // название товара
			$content .= "<vendor>".$producer."</vendor>\n";
			$content .= "<vendorCode>".$product['articul']."</vendorCode>\n";
			$content .= "<description>$description</description>\n"; // описание продукта
			$content .= "<country_of_origin>".(isset($product['producer_id_country']) ? $product['producer_id_country'] : '')."</country_of_origin>\n";
			$content .= "</offer>\n";
		}
		$content .= "</offers>\n";  // дописываем закрывающие тэги
		$content .= "</shop>\n";
		$content .= "</yml_catalog>";


		fputs($f, $content);  // записываем наш контент в файл
		fclose($f);
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
	
	private function updateNestedSets($tableName = 'catalog_category' ,$parentId = 0, $level = 1, $leftKey = 1) {
		$items = $this->get('connection')->getItems('items', 'SELECT id,title FROM '.$tableName.' WHERE parent_id='.$parentId.' ORDER BY sort');
		if (count($items)) {
			foreach ($items as $item) {
				$right_key = $this->updateNestedSets($tableName, $item['id'], $level+1, $leftKey+1);
				$name = 'catalog_category' == $tableName ? ',name=\''.strtolower($this->get('util')->translitStr(trim($item['title']))).'\'' : '';
				$this->get('connection')->execQuery('upd', 
					'UPDATE '.$tableName.' SET left_key='.$leftKey.
					',right_key='.$right_key.',level='.$level.
					$name
					.' WHERE id='.$item['id']
				);
				$leftKey = $right_key+1;
			}
			$right_key++;
		} else {
			$right_key = $leftKey;
		}
		return $right_key;
	}
	
	private function checkNestedSets($tableName = 'catalog_category') {
		$items = $this->get('connection')->getItems('items', 'SELECT id FROM '.$tableName.' WHERE left_key >= right_key');
		if (count($items)) {
			$this->get('log')->write($tableName.': ошибка nestedsets 1');
		}
		$item = $this->get('connection')->getItem('items', 'SELECT COUNT(id) as quantity, MIN(left_key) as min_key, MAX(right_key) as max_key FROM '.$tableName);
		if (1 != $item['min_key']) {
			$this->get('log')->write($tableName.': ошибка nestedsets 2');
		}
		if ($item['quantity']*2 != $item['max_key']) {
			$this->get('log')->write($tableName.': ошибка nestedsets 3');
		}
		$items = $this->get('connection')->getItems('items', 'SELECT id, MOD((right_key - left_key) / 2) AS ostatok FROM '.$tableName.' WHERE ostatok = 0');
		if (count($items)) {
			$this->get('log')->write($tableName.': ошибка nestedsets 4');
		}
		$items = $this->get('connection')->getItems('items', 'SELECT id, MOD((left_key – level + 2) / 2) AS ostatok FROM '.$tableName.' WHERE ostatok = 1');
		if (count($items)) {
			$this->get('log')->write($tableName.': ошибка nestedsets 5');
		}
		$items = $this->get('connection')->getItems('items', 
			'SELECT t1.id, COUNT(t1.id) AS rep, MAX(t3.right_key) AS max_right 
FROM '.$tableName.' AS t1, '.$tableName.' AS t2, '.$tableName.' AS t3 
WHERE t1.left_key <> t2.left_key AND t1.left_key <> t2.right_key AND t1.right_key <> t2.left_key AND t1.right_key <> t2.right_key 
GROUP BY t1.id HAVING max_right <> SQRT(4 * rep + 1) + 1');
		if (count($items)) {
			$this->get('log')->write($tableName.': ошибка nestedsets 6');
		}
	}
	
	private function updateLinkTables() {
		$items = $this->get('connection')->getItems('items', 
				'SELECT category_id, producer_id FROM `catalog_product` 
				WHERE producer_id <> 0 AND category_id <> 0 
				GROUP BY category_id, producer_id 
				ORDER BY `catalog_product`.`producer_id` ASC');
		$this->get('connection')->execQuery('truncate', "TRUNCATE TABLE catalog_categories_producers");
		foreach ($items as $item) {
			$this->get('connection')->execQuery('add', 'INSERT INTO catalog_categories_producers VALUES('.$item['category_id'].','.$item['producer_id'].')');
		}
	}
	
	private function fixNested() {
		$this->updateNestedSets('catalog_category');
		$this->checkNestedSets('catalog_category');
		$this->updateNestedSets('page_page');
		$this->checkNestedSets('page_page');
		$this->updateLinkTables();
	}
	
}
