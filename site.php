<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;
use \Hcode\Model\User;

$app->get('/', function() {
	$products = Product::listAll();
	$page = new Page();
	$page->setTpl("index", [
		'products'=>Product::checkList($products)
	]);
});

$app->get('/categories/:idcategory', function($idcategory){
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
	$category = new Category();
	$category->get((int)$idcategory);
	$pagination = $category->getProductsPage($page);
	$page = new Page();
	$pages = [];
	for ($i=1; $i <= $pagination['pages'] ; $i++) { 
		array_push($pages, [
			'link'=>'/Projeto-PHP7-Hcode/index.php/categories/'.$category->getidcategory().'?page='.$i,
			'page'=>$i
		]);
	}
	$page->setTpl('category', [
		'category'=>$category->getValues(),
		'products'=>$pagination['data'],
		'pages'=>$pages
	]);
});

$app->get('/products/:desurl', function($desurl){
	$product = new Product();
	$product->getFromURL($desurl);
	$page = new Page();
	$page->setTpl('product-detail', [
		'product'=>$product->getValues(),
		'categories'=>$product->getcategories()
	]);
});

$app->get('/cart', function(){
	$cart = Cart::getFromSession();
	$page = new Page();
	$page->setTpl('cart',[
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts(),
		'error'=>Cart::getMsgError()
	]);
});

$app->get('/cart/:idproduct/add', function($idproduct){
	$product = new Product();
	$product->get((int)$idproduct);
	$cart = Cart::getFromSession();
	$qtd = (isset($_GET['qtd'])) ? (int)$_GET['qtd'] : 1;
	for ($i=0; $i < $qtd; $i++) { 
		$cart->addProduct($product);
	}
	header("Location: /Projeto-PHP7-Hcode/index.php/cart");
	exit;
});

$app->get('/cart/:idproduct/minus', function($idproduct){
	$product = new Product();
	$product->get((int)$idproduct);
	$cart = Cart::getFromSession();
	$cart->removeProduct($product);
	header("Location: /Projeto-PHP7-Hcode/index.php/cart");
	exit;
});

$app->get('/cart/:idproduct/remove', function($idproduct){
	$product = new Product();
	$product->get((int)$idproduct);
	$cart = Cart::getFromSession();
	$cart->removeProduct($product, true);
	header("Location: /Projeto-PHP7-Hcode/index.php/cart");
	exit;
});

$app->post('/cart/freight', function(){
	$cart = Cart::getFromSession();
	$cart->setFreight($_POST['zipcode']);
	header("Location: /Projeto-PHP7-Hcode/index.php/cart");
	exit;
});

$app->get("/checkout", function(){
	User::verifyLogin(false);
	$cart = Cart::getFromSession();
	$address = new Address();
	$page = new Page();
	$page->setTpl('checkout', [
		'cart'=>$cart->getValues(),
		'address'=>$address->getValues()
	]);
});

$app->get("/login", function(){
	$page = new Page();
	$page->setTpl('login', [
		'error'=>User::getError()
	]);
});

$app->post("/login", function(){
	try{
	User::login($_POST['login'], $_POST['password']);
	}catch(Exception $e){
		User::setError($e->getMessage());
	}
	header("Location: /Projeto-PHP7-Hcode/index.php/checkout");
	exit;
});

$app->get("/logout", function(){
	User::logout();
	header("Location: /Projeto-PHP7-Hcode/index.php/login");
	exit;
});

?>