<?php

namespace Fuga\AdminBundle\Action;

class CounttagAction extends Action {

	function __construct(&$adminController) {
		parent::__construct($adminController);
	}

	function getText() {
		
		$this->fixNested();
		$this->calculateCatalog();
		$this->buildSitemapXML();
		$this->buildShopYML('shop.yml');
		
		$this->messageAction(false ? 'Ошибка расчета тегов' : 'Расчет тегов завершен');
	}
	
	function calculateCatalog() {
		$this->get('connection1')->query("TRUNCATE TABLE article_tag");
		$this->get('connection1')->query("TRUNCATE TABLE article_tags_articles");
		$this->get('connection1')->query("TRUNCATE TABLE article_products_articles");
		$sql = "SELECT id,tag FROM article_article WHERE publish=1";
		$stmt = $this->get('connection1')->prepare($sql);
		$stmt->execute();
		$articles = $stmt->fetchAll();
		$tags_full = array();
		foreach ($articles as $article) {
			$tags = $article['tag'];
			$tags_array = explode(',', $tags);
			foreach ($tags_array as $tag) {
				$tag = strtolower(trim($tag));
				if (!isset($tags_full[$tag])) {
					$tags_full[$tag] = array('q' => 1, 'articles' => array($article['id']));
				} else {
					$tags_full[$tag]['q']++;
					$tags_full[$tag]['articles'][] = $article['id'];
				}
			}
		}
//		print_r($tags_full);
//		exit;
//		$this->get('log')->write(json_encode($tags_full));
		foreach ($tags_full as $tag => $tag_info) {
			$lastId = $this->get('container')->addItem('article_tag', array(
				'name' => $tag, 
				'quantity' =>  $tag_info['q']
			));
			foreach ($tag_info['articles'] as $articleId) {
				$this->get('connection1')->insert('article_tags_articles', array(
					'tag_id' => $lastId, 
					'article_id' => $articleId
				));
			}
		}
		$sql = "SELECT max(quantity) as max, min(quantity) as min FROM article_tag";
		$stmt = $this->get('connection1')->prepare($sql);
		$stmt->execute();
		$tag_max_min = $stmt->fetchAll();
		$min = intval($tag_max_min[0]['min']);
		$max = intval($tag_max_min[0]['max']);

		$minsize = 1;
		$maxsize = 10;
		$sql = "SELECT id,name,quantity FROM article_tag";
		$stmt = $this->get('connection1')->prepare($sql);
		$stmt->execute();
		$tags = $stmt->fetchAll();
		foreach ($tags as $tag) {
			if ($min == $max) {
				$num = ($maxsize - $minsize)/2 + $minsize;
				$weight = round($num);
			} else {
				$num = ($tag['quantity'] - $min)/($max - $min)*($maxsize - $minsize) + $minsize;
				$weight = round($num);
			}
			$this->get('connection1')->update('article_tag', 
				array('weight' => $weight),
				array('id' => $tag['id'])
			);
		}
		
		$sql = "SELECT p.id, p.name, count(pr.id) as quantity 
			FROM catalog_producer p 
			JOIN catalog_product pr ON p.id=pr.producer_id 
			WHERE pr.publish=1 GROUP BY p.id";
		$stmt = $this->get('connection1')->prepare($sql);
		$stmt->execute();
		$brands = $stmt->fetchAll();
		$min = $brands[0]['quantity'];
		$max = $brands[0]['quantity'];
		foreach ($brands as $brand) {
			if ($brand['quantity'] > $max) {
				$max = $brand['quantity'];
			}
			if ($brand['quantity'] < $min) {
				$min = $brand['quantity'];
			}
			$this->get('connection1')->update('catalog_producer',
				array('quantity' => $brand['quantity']),
				array('id' => $brand['id'])
			);
		}
		
		$sql = "SELECT id, name, quantity FROM catalog_producer";
		$stmt = $this->get('connection1')->prepare($sql);
		$stmt->execute();
		$brands = $stmt->fetchAll();
		foreach ($brands as $brand) {
			if ($min == $max) {
				$num = ($maxsize - $minsize)/2 + $minsize;
				$weight = round($num);
			} else {
				$num = ($brand['quantity'] - $min)/($max - $min)*($maxsize - $minsize) + $minsize;
				$weight = round($num);
			}
			$this->get('connection1')->update('catalog_producer',
				array('weight' => $weight),
				array('id' => $brand['id'])
			);
		}

		foreach ($tags as $tag) {
			$sql = "SELECT id,name FROM catalog_product WHERE tag LIKE '%".$tag['name']."%'";
			$stmt = $this->get('connection1')->prepare($sql);
			$stmt->execute();
			$items = $stmt->fetchAll();
			if (count($items)) {
				$sql = "SELECT article_id FROM article_tags_articles WHERE tag_id= :id ";
				$stmt = $this->get('connection1')->prepare($sql);
				$stmt->bindValue('id', $tag['id']);
				$stmt->execute();
				$articles = $stmt->fetchAll();
				foreach ($items as $item) {
					foreach ($articles as $article) {
						try {
							$this->get('connection1')->insert('article_products_articles', array(
								'product_id' => $item['id'],
								'article_id' => $article['article_id']
							));
						} catch (\Exception $e){
							
						}
					}
				}
			}
		}
		
		$sql = "SELECT id,parent_id,title FROM catalog_category WHERE parent_id=0";
		$stmt = $this->get('connection1')->prepare($sql);
		$stmt->execute();
		$categories = $stmt->fetchAll();
		foreach ($categories as $category) {
			$sql = "SELECT id,parent_id,title FROM catalog_category WHERE parent_id= :id ";
			$stmt = $this->get('connection1')->prepare($sql);
			$stmt->bindValue('id', $category['id']);
			$stmt->execute();
			$categories2 = $stmt->fetchAll();
			foreach ($categories2 as $category2) {
				$this->get('connection1')->update('catalog_category',
					array('root_id' => $category['id']),
					array('id' => $category['id'])
				);
				$this->get('connection1')->update('catalog_category',
					array('root_id' => $category['id']),
					array('id' => $category2['id'])
				);
				$this->get('connection1')->update('catalog_category',
					array('root_id' => $category['id']),
					array('parent_id' => $category2['id'])
				);
			}
		}
	}
	
	function getEntities($table) {
		$sql = "SELECT id FROM $table WHERE publish=1";
		$stmt = $this->get('connection1')->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	function getTreeEntities($table, $id = 0) {
		$entities = array();
		$sql = "SELECT * FROM $table WHERE publish=1 AND parent_id = $id";
		$stmt = $this->get('connection1')->prepare($sql);
		$stmt->execute();
		$items = $stmt->fetchAll();
		foreach ($items as $item) {
			$children = $this->getTreeEntities($table, $item['id']);
			$entities[] = $item;
			$entities = array_merge($entities, $children);
		}
		return $entities;
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

		$products = $this->get('container')->getItems('catalog_product', "publish=1 AND price<>0"); // выбираем все товары
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
			$content .= "<picture>http://colors-life.ru".$product['middle_imagenew']."</picture>\n";  // ссылка на картинку ( полностью )
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
	
	private function updateNestedSets($table = 'catalog_category' , $parentId = 0, $level = 1, $left_key = 0) {
		$sql = "SELECT id,title,name FROM $table WHERE parent_id= :id ORDER BY sort";
		$stmt = $this->get('connection1')->prepare($sql);
		$stmt->bindValue('id', $parentId);
		$stmt->execute();
		$items = $stmt->fetchAll();
		if ($items) {
			foreach ($items as $item) {
				$left_key++;
				$right_key = $this->updateNestedSets($table, $item['id'], $level+1, $left_key);
				
				$name = 'catalog_category' == $table ? strtolower($this->get('util')->translitStr(trim($item['title']))) : $item['name'];
				$this->get('connection1')->update($table,
					array(
						'left_key' => $left_key, 
						'right_key' => $right_key, 
						'level' => $level,
						'name' => $name
					),	
					array('id' => $item['id'])
				);
				$left_key = $right_key;
			}
		} else {
			$right_key = $left_key;
		}
		return ++$right_key;
	}
	
	private function checkNestedSets($tableName = 'catalog_category') {
		$sql = 'SELECT id FROM '.$tableName.' WHERE left_key >= right_key';
		$stmt = $this->get('connection1')->prepare($sql);
		$stmt->execute();
		$items = $stmt->fetchAll();
		if (count($items)) {
			$this->get('log')->write($tableName.': ошибка 1: left_key >= right_key');
		}
		$sql = 'SELECT COUNT(id) as quantity, MIN(left_key) as min_key, MAX(right_key) as max_key FROM '.$tableName;
		$stmt = $this->get('connection1')->prepare($sql);
		$stmt->execute();
		$item = $stmt->fetch();
		if (1 != $item['min_key']) {
			$this->get('log')->write($tableName.': ошибка 2: min <> 1');
		}
		if ($item['quantity']*2 != $item['max_key']) {
			$this->get('log')->write($tableName.': ошибка 3: max <> quantity*2');
		}
		$sql = 'SELECT id,right_key,left_key FROM '.$tableName.' HAVING MOD((right_key - left_key), 2) = 0';
		$stmt = $this->get('connection1')->prepare($sql);
		$stmt->execute();
		$items = $stmt->fetchAll();
		if (count($items)) {
			$this->get('log')->write($tableName.': ошибка 4: MOD((right_key - left_key) / 2) <> 0');
		}
		$sql = 'SELECT id,level,left_key FROM '.$tableName.' HAVING MOD((left_key - level + 2), 2) = 1';
		$stmt = $this->get('connection1')->prepare($sql);
		$stmt->execute();
		$items = $stmt->fetchAll();
		if (count($items)) {
			$this->get('log')->write($tableName.': ошибка 5: MOD((left_key – level + 2) / 2) = 1');
		}
		$sql = 'SELECT t1.id, COUNT(t1.id) AS rep, MAX(t3.right_key) AS max_right 
			FROM '.$tableName.' AS t1, '.$tableName.' AS t2, '.$tableName.' AS t3 
			WHERE t1.left_key <> t2.left_key AND t1.left_key <> t2.right_key AND 
			t1.right_key <> t2.left_key AND t1.right_key <> t2.right_key 
			GROUP BY t1.id HAVING max_right <> SQRT(4 * rep + 1) + 1';
		$stmt = $this->get('connection1')->prepare($sql);
		$stmt->execute();
		$items = $stmt->fetchAll();
		if (count($items)) {
			$this->get('log')->write($tableName.': ошибка 6:');
		}
	}
	
	private function updateLinkTables() {
		$sql = 'SELECT category_id, producer_id FROM catalog_product 
				WHERE producer_id <> 0 AND category_id <> 0 
				GROUP BY category_id, producer_id 
				ORDER BY producer_id';
		$stmt = $this->get('connection1')->prepare($sql);
		$stmt->execute();
		$items = $stmt->fetchAll();
		$this->get('connection1')->query("TRUNCATE TABLE catalog_categories_producers");
		foreach ($items as $item) {
			$this->get('connection1')->insert('catalog_categories_producers',array(
				'category_id' => $item['category_id'],
				'producer_id' => $item['producer_id']
			));
		}
	}
	
	private function fixNested() {
		$this->updateNestedSets('catalog_category');
		$this->checkNestedSets('catalog_category');
		$this->updateNestedSets('page_page');
		$this->checkNestedSets('page_page');
//		$this->updateLinkTables();
	}
	
}
