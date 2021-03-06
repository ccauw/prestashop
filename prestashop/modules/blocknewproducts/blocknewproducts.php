<?php

class BlockNewProducts extends Module
{
    private $_html = '';
    private $_postErrors = array();

    function __construct()
    {
        $this->name = 'blocknewproducts';
        $this->tab = 'Blocks';
        $this->version = 0.9;

        parent::__construct();

        $this->displayName = $this->l('New products block');
        $this->description = $this->l('Displays a block featuring newly added products');
    }

    function install()
    {
        if (parent::install() == false 
				OR $this->registerHook('rightColumn') == false
				OR Configuration::updateValue('NEW_PRODUCTS_NBR', 5) == false)
			return false;
		return true;
    }

	public function getContent()
	{
		$output = '<h2>'.$this->displayName.'</h2>';
		if (Tools::isSubmit('submitBlockNewProducts'))
		{
			if (!$productNbr = Tools::getValue('productNbr') OR empty($productNbr))
				$output .= '<div class="alert error">'.$this->l('You should fill the "products displayed" field').'</div>';
			elseif (intval($productNbr) == 0)
				$output .= '<div class="alert error">'.$this->l('Invalid number.').'</div>';
			else
			{
				Configuration::updateValue('NEW_PRODUCTS_NBR', intval($productNbr));
				$output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />'.$this->l('Settings updated').'</div>';
			}
		}
		return $output.$this->displayForm();
	}

	public function displayForm()
	{
		$output = '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset><legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Settings').'</legend>
				<label>'.$this->l('Products displayed').'</label>
				<div class="margin-form">
					<input type="text" name="productNbr" value="'.intval(Configuration::get('NEW_PRODUCTS_NBR')).'" />
					<p class="clear">'.$this->l('Set the number of products to be displayed in this block').'</p>
				</div>
				<center><input type="submit" name="submitBlockNewProducts" value="'.$this->l('Save').'" class="button" /></center>
			</fieldset>
		</form>';
		return $output;
	}

    function hookRightColumn($params)
    {
		global $smarty, $category_path;

		$category_path_ids = array();
		foreach ($category_path as $cat)
			$category_path_ids[] = $cat['id_category'];

		$smarty->caching = 1;
		$cache_id = $category_path_ids[count($category_path_ids) - 1] . '-' . $params['cookie']->id_lang . '-' . $params['cookie']->id_currency;
		if (!$smarty->is_cached($this->find_template(__FILE__, 'blocknewproducts.tpl'), $cache_id)) {

		$currency = new Currency(intval($params['cookie']->id_currency));
		$newProducts = Product::getNewProducts(intval($params['cookie']->id_lang), 0, 5*Configuration::get('NEW_PRODUCTS_NBR'));
		$new_products = array();
		if ($newProducts) {
		        $nr = 0;
			foreach ($newProducts AS $newProduct) {
			        if ($nr >= Configuration::get('NEW_PRODUCTS_NBR'))
				        break;
				$display = false;
				foreach(Product::getIndexedCategories($newProduct['id_product']) as $row) {
					if (in_array($row['id_category'], $category_path_ids)) {
						$display = true;
						break;
					}
				}
				if ($display) {
				        $new_products[] = $newProduct;
					$nr += 1;
				}
			}
		}
		$smarty->assign(array(
			'new_products' => $new_products,
			'mediumSize' => Image::getSize('medium')));
		
		} // End if not cached

		return $this->display(__FILE__, 'blocknewproducts.tpl', $cache_id);
	}
	
	function hookLeftColumn($params)
	{
		return $this->hookRightColumn($params);
	}
}


?>
