<?php
/**
 * Related Link
 *
 * @copyright 2019 LazyDev
 * @version   1.1.0
 * @link      https://lazydev.pro
 */

defined('DATALIFEENGINE') || die('Hacking attempt!');

$cleanIntArray = function($a) {
	$tempArray = [];
	foreach ($a as $value) {
		if (($value = intval($value)) > 0) {
			$tempArray[] = $value;
		}
	}
	
	return $tempArray;
};

$newsId = is_numeric($newsId) ? intval($newsId) : false;
$limit = is_numeric($limit) ? intval($limit) : 5;
if ($idExclude) {
	$idExclude = explode(',', $idExclude);
	$idExclude = $cleanIntArray($idExclude);
}

$notAllowCats = explode(',', $user_group[$member_id['user_group']]['not_allow_cats']);

if ($catExclude) {
	$catExclude = explode(',', $catExclude);
	$catExclude = $cleanIntArray($catExclude);
	
	if ($catExclude) {
		$catExclude = $catExclude;
		$notAllowCats = $notAllowCats[0] != '' ? $notAllowCats + $catExclude : $catExclude;
		$notAllowCats = array_unique($notAllowCats);
	} else {
		$catExclude = false;
	}
}

$configRelatedLink = [
	'id' => $newsId,
	'limit' => $limit,
	'id-exclude' => $idExclude ?: false,
	'cat-exclude' => $catExclude ?: false,
];
$cacheHash = implode('_', $configRelatedLink);

if (!$configRelatedLink['id']) {
	return;
}

$content = dle_cache('news_related_link', $config['skin'] . $cacheHash, true);

if ($content) {
	echo $content;
} else {
	$where = [];
	if ($config['allow_multi_category']) {
		$category = intval($category_id);
		$where[] = "(category LIKE '{$category},%' OR category = '{$category}')";
	} else {
		$where[] = "category IN ('" . $category_id . "')";
	}
	
	if ($configRelatedLink['id-exclude']) {
		$where[] = "id NOT IN('" . implode("','", $configRelatedLink['id-exclude']) . "')";
	}
	
	$oldMySQL = version_compare($db->mysql_version, '5.5.3', '<') == 1 ? false : true;
	if ($notAllowCats[0] != '') {
		if ($config['allow_multi_category']) {
			if ($oldMySQL) {
				$where[] = "category NOT REGEXP '\\\\b(" . implode('|', $notAllowCats) . ")\\\\b'";
			} else {
				$where[] = "category NOT REGEXP '([[:punct:]]|^)(" . implode('|', $notAllowCats) . ")([[:punct:]]|$)'";
			}
		} else {
			$where[] = "category NOT IN ('" . implode("','", $notAllowCats) . "')";
		}
	}

	if ($config['no_date'] && !$config['news_future']) {
		$where[] = "date < '" . date('Y-m-d H:i:s', time()) . "'";
	}
	
	$where = implode(' AND ', $where);
	
	$tpl->load_template('lazydev/related_link/news.tpl');
	$sqlCalc = $db->super_query("SELECT COUNT(*) as count, MAX(id) as max_id, MIN(id) as min_id FROM " . PREFIX . "_post WHERE {$where} AND id!='{$configRelatedLink['id']}' AND approve='1'");

	if ($sqlCalc['count'] > 0) {
		if ($sqlCalc['min_id'] == $configRelatedLink['id']) {
			$configRelatedLink['limit'] -= 1;
			$sql_result = $db->query("(SELECT * FROM " . PREFIX . "_post LEFT JOIN " . PREFIX . "_post_extras ON (" . PREFIX . "_post.id=" . PREFIX . "_post_extras.news_id) WHERE approve='1' AND {$where} AND id>'{$configRelatedLink['id']}' ORDER BY id ASC LIMIT {$configRelatedLink['limit']}) UNION (SELECT * FROM " . PREFIX . "_post LEFT JOIN " . PREFIX . "_post_extras ON (" . PREFIX . "_post.id=" . PREFIX . "_post_extras.news_id) WHERE id='{$sqlCalc['max_id']}')");
		} elseif ($sqlCalc['max_id'] == $configRelatedLink['id']) {
			$configRelatedLink['limit'] -= 1;
			$sql_result = $db->query("SELECT * FROM ((SELECT * FROM " . PREFIX . "_post LEFT JOIN " . PREFIX . "_post_extras ON (" . PREFIX . "_post.id=" . PREFIX . "_post_extras.news_id) WHERE approve='1' AND {$where} AND id<'{$configRelatedLink['id']}' ORDER BY id DESC LIMIT {$configRelatedLink['limit']}) UNION (SELECT * FROM " . PREFIX . "_post LEFT JOIN " . PREFIX . "_post_extras ON (" . PREFIX . "_post.id=" . PREFIX . "_post_extras.news_id) WHERE id='{$sqlCalc['min_id']}') ) as r ORDER BY r.id ASC");
		} else {
			$countBackNews = $db->super_query("(SELECT COUNT(*) as count FROM " . PREFIX . "_post WHERE approve='1' AND {$where} AND id>'{$configRelatedLink['id']}') UNION ALL (SELECT COUNT(*) as count FROM " . PREFIX . "_post WHERE approve='1' AND {$where} AND id<'{$configRelatedLink['id']}')", true);
			
			if ($countBackNews[0]['count'] > 2 && $countBackNews[1]['count'] >= 2) {
				$limitBackNews = 2;
				$configRelatedLink['limit'] -= 2;
			} elseif ($countBackNews[0]['count'] == 2 || $countBackNews[0]['count'] == 1) {
				$limitBackNews = $configRelatedLink['limit'] - $countBackNews[0]['count'];
				$configRelatedLink['limit'] = $countBackNews[0]['count'];
			} else {
				$limitBackNews = 1;
				$configRelatedLink['limit'] -= 1;
			}
			
			$sql_result = $db->query("SELECT * FROM ((SELECT * FROM " . PREFIX . "_post LEFT JOIN " . PREFIX . "_post_extras ON (" . PREFIX . "_post.id=" . PREFIX . "_post_extras.news_id) WHERE approve='1' AND {$where} AND id<'{$configRelatedLink['id']}' ORDER BY id DESC LIMIT {$limitBackNews}) UNION (SELECT * FROM " . PREFIX . "_post LEFT JOIN " . PREFIX . "_post_extras ON (" . PREFIX . "_post.id=" . PREFIX . "_post_extras.news_id) WHERE approve='1' AND {$where} AND id>'{$configRelatedLink['id']}' ORDER BY id ASC LIMIT {$configRelatedLink['limit']})) as r ORDER BY r.id ASC");
		}

		if (file_exists(ENGINE_DIR . '/classes/plugins.class.php')) {
			include (DLEPlugins::Check(ENGINE_DIR . '/modules/show.custom.php'));
		} else {
			include ENGINE_DIR . '/modules/show.custom.php';
		}
		
		if ($config['files_allow'] && strpos($tpl->result['content'], '[attachment=') !== false) {
			$tpl->result['content'] = show_attach($tpl->result['content'], $attachments);
		}
	}
	
	$tpl->load_template('lazydev/related_link/block.tpl');

	if (trim($tpl->result['content']) != '') {
		$tpl->set('{related-link}', $tpl->result['content']);
		$tpl->set_block("'\\[related\\](.*?)\\[/related\\]'si", '\\1');
		$tpl->set_block("'\\[not-related\\](.*?)\\[/not-related\\]'si", '');
	} else {
		$tpl->set('{related-link}', '');
		$tpl->set_block("'\\[related\\](.*?)\\[/related\\]'si", '');
		$tpl->set_block("'\\[not-related\\](.*?)\\[/not-related\\]'si", '\\1');
	}

	$tpl->compile('related_block');
	$tpl->clear();
	create_cache('news_related_link', $tpl->result['related_block'], $config['skin'] . $cacheHash, true);

	echo $tpl->result['related_block'];
}
?>