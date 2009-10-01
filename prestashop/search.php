<?php

include(dirname(__FILE__).'/debug.php');
include(dirname(__FILE__).'/config/config.inc.php');
include(dirname(__FILE__).'/init.php');

if (Tools::getValue('ajaxSearch') AND $query = urldecode(Tools::getValue('q')) AND !is_array($query))
{
	include(dirname(__FILE__).'/init.php');
	$link = new Link();
	$search = Search::find(intval(Tools::getValue('id_lang')), $query, 1, 10, 'position', 'desc', true);
	foreach ($search as $product)
		echo $product['id_product'].'|'.$product['pname'].'|'.$product['cname'].'|'.$link->getProductLink($product['id_product'], $product['prewrite'], $product['crewrite'])."\n";
	die;
}

include(dirname(__FILE__).'/header.php');
include(dirname(__FILE__).'/product-sort.php');

if ($query = Tools::getValue('search_query', Tools::getValue('tag', Tools::getValue('ref'))) AND !is_array($query))
{
	$n = abs(intval(Tools::getValue('n', Configuration::get('PS_PRODUCTS_PER_PAGE'))));
	$p = abs(intval(Tools::getValue('p', 1)));
	$search = Search::find(intval($cookie->id_lang), $query, $p, $n, $orderBy, $orderWay);
	$nbProducts = $search['total'];
	include(dirname(__FILE__).'/pagination.php');
	$smarty->assign(array('products' => $search['result'], 'nbProducts' => $search['total'], 'query' => $query));
}
else
{
	$smarty->assign(array(
	'products' => array(),
	'pages_nb' => 1,
	'nbProducts' => 0));
}

function HOOK_PRODUCT_LIST_ACTIONS($params) {
 global $product;

 $product = $params['product'];
 return Module::hookExec('productListActions');
}

if (!isset($_GET['id_category'])) $_GET['id_category'] = 0;
if (!isset($_GET['id_manufacturer'])) $_GET['id_manufacturer'] = 0;
if (!isset($_GET['id_supplier'])) $_GET['id_supplier'] = 0;

$smarty->register_function("HOOK_PRODUCT_LIST_ACTIONS", "HOOK_PRODUCT_LIST_ACTIONS");

$smarty->display(_PS_THEME_DIR_.'search.tpl');

include(dirname(__FILE__).'/footer.php');

?>