<?php
// Turn error reporting on during testing (not production)
error_reporting(1);

require('./settings.php');
require __DIR__.'/vendor/autoload.php';

// $request_parts = explode('/', $_SERVER['REQUEST_URI']); 
// $route = array_pop($request_parts);

$params = parseUrl($_SERVER['REQUEST_URI']); 
$route = $params['route'];
unset($params['route']); //remove route key from array



$db = new mysqli("localhost", $settings['username'], $settings['password'], $settings['dbname']);
// If we have an error connecting to the db, then exit page
if ($db->connect_errno) {
    print_response(['success'=>false,"error"=>"Connect failed: ".$db->connect_error]);
}

// not all items are legit
$routes = ['routes'=>
    [
        ['type'=>'post','name'=>'register','params'=>[]],
        ['type'=>'post','name'=>'login','params'=>['email','']],
        ['type'=>'get','name'=>'categories','params'=>[]],    
        ['type'=>'get','name'=>'search','params'=>[]], 
        ['type'=>'get','name'=>'browse','params'=>[]]           
    ]
];

$response = false;

// JavaScript Injections

$default_js_scripts = [
    // "<script src=\"./js/jquery.min.js?v=".rand()."\"></script>",
    // "<script src=\"https://samuelweems.com/candy_shop/js/bootstrap.bundle.min.js\"></script>",
    "<script src=\"https://samuelweems.com/candy_shop/js/cookie.js\"></script>",
    "<script src=\"https://samuelweems.com/candy_shop/js/menu.js\"></script>"
   // "<script src=\"https://samuelweems.com/candy_shop/js/documentReady.js\"></script>"
    
];

$scripts = [];

switch($route){
    case 'mainPage':
         $view = file_get_contents('views/content.html');
         echo render_view($view,$scripts);
         break;
    case 'browsePage':
         $view = file_get_contents('views/products.html');
      
         $scripts[] = 'https://samuelweems.com/candy_shop/js/categories.js';
         $scripts[] = 'https://samuelweems.com/candy_shop/js/products.js';
        
         if(!array_key_exists('offset',$params)){
            $offset = 0;
        }else{
            $offset = $params['offset'];
        }
        if(!array_key_exists('size',$params)){
            $size = 18;
        }else{
            $size = $params['size'];
        }
        if(!array_key_exists('category',$params)){
            $category = "all";
        }else{
            $category = $params['category'];
        }
        if(!array_key_exists('search',$params)){
            $function = '<script type ="text/javascript"> grabProducts('.$offset.','.$size.',$("#candy-content"),"'.$category.'", ""); </script>';
        }else{
            $search_term = $params['search'];
            $function = '<script type ="text/javascript"> grabProducts('.$offset.','.$size.',$("#candy-content"),"", "'.$search_term.'"); </script>';
        }

         echo render_view($view,$scripts,$function);
         break;
    case 'aboutPage'://not sure
         $view = file_get_contents('views/about.html');
         echo render_view($view,$scripts);
         break;
    case 'contactPage':
         $view = file_get_contents('views/contact.html');
         echo render_view($view,$scripts);
         break;
    case 'cartPage':
         $view = file_get_contents('views/cart.html');
         $scripts[] = 'https://samuelweems.com/candy_shop/js/getCart.js';
         echo render_view($view,$scripts);
         break;
    case 'checkoutPage':
         $view = file_get_contents('views/checkout.html');
         echo render_view($view,$scripts);
         break;
    case 'loginPage':
         $view = file_get_contents('views/register.html');
         echo render_view($view,$scripts);
         break;
    case 'productDetailPage':
        $view = file_get_contents('views/product_detail.html');
        $productID = $params['productID'];
        $scripts[] = 'https://samuelweems.com/candy_shop/js/productDetail.js';
        $scripts[] = 'https://samuelweems.com/candy_shop/js/addCart.js';
        $function = '<script type ="text/javascript"> productDetail('.$productID.'); </script>';
        echo render_view($view, $scripts, $function);
        break;
    case 'getProductDetail':
        $productID = $params['productID'];
        $response['data'] = getProductDetail($productID);
        break;
    case 'navigation'://legacy
         $response['data'] = getMenuItems($menu_id);
         break;
    case 'categories':
        if(!array_key_exists('offset',$params)){
            $offset = 0;
        }else{
            $offset = $params['offset'];
        }
        if(!array_key_exists('size',$params)){
            $size = 10;
        }else{
            $size = $params['size'];
        }
    
        $response['data'] = getCategories($offset,$size);
        break;     
    case 'menu':
        $response = getMenuItems();
        break;
    case 'topMenu':
        $response = getMenuItems(2);
        break;
    case 'search':
        if(sizeof($params) > 0){
            $response['data'] = searchCandy($params);
        }else{
            $response['data'] = ['error'=>'No search params'];
        }
        break;
    case 'browse':
        if(!array_key_exists('offset',$params)){
            $offset = 0;
        }else{
            $offset = $params['offset'];
        }
        if(!array_key_exists('size',$params)){
            $size = 10;
        }else{
            $size = $params['size'];
        }
        if(!array_key_exists('category',$params)){
            $category = 'all';
        }else{
            $category = $params['category'];
        }

        $response['data'] = browse($offset,$size, $category);
        break;  

    case 'addCart':
        global $db;
       
        $item_id = $params['pid'];
        $uid = $params['uid'];
        $sql = "SELECT * FROM `products` WHERE id = '{$item_id}'";

        $result = $db->query($sql);
        
        while ($row = $result->fetch_assoc()) {
            $item = $row;
        } 

        $price = $item['price'];
        $name = $item['name'];
        $image = $item['image_path'] . '_small' . '.' . $item['img_type'];


       //  $ip =getRealIpAddr();

        $sql = "INSERT INTO `shopping_cart` (`uid`, `item_id`, `date_created`, `price`, `count`, `coupon_code`, `ip_address`, `guest`, `product_name`, `image_path`) VALUES ('{$uid}', {$item_id}, now(), '{$price}', '1', '', '$ip', '1', '$name', '$image');";
        $result = $db->query($sql);

        

        $sql = "INSERT INTO `user_total` (`uid`, `total_cost`) VALUES ('{$uid}', '0');";
        
        $result = $db->query($sql);

        return $result;
        break;
    
    case 'updateCart':
        global $db;
        $item_id = $params['pid'];
        $user_id = $params['uid'];
        $count = $params['count'];

        if ($count != 0)
            $sql = "UPDATE `shopping_cart` SET `count` = {$count} WHERE `uid` = '{$user_id}' AND `item_id` = {$item_id}";
        else
            $sql = "DELETE FROM `shopping_cart` WHERE uid = '{$user_id}' AND `item_id` = {$item_id}";

    $db->query($sql);

        break;
    case 'updateTotal' :
        global $db;
        $total_cost = $params['totalAmount'];
        $user_id=$params['uid'];
    
    $sql = "UPDATE `user_total` SET `total_cost` = '{$total_cost}' WHERE `uid` = '{$user_id}'";
   
    $db->query($sql);


    
    break;

   case "register":
 
    global $db;
    
    $username= $_POST['username'];
    $email = $_POST['email'];
    $pass = password_hash(trim($_POST['password']),PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO `users` (`username`, `password`, `email`) VALUES ('{$username}', '{$pass}', '{$email}')";
    $result = $db->query($sql);

   break;

    case "loginPHP":
   
    global $db;
    
    $name= $_POST['username'];
    
    $query = "SELECT * FROM `users` WHERE `username` = '{$name}'";
    
    $result = $db->query($query);
    $result = $result->fetch_assoc();
    
      

    $pass = $_POST['password'];
   
    if (password_verify($pass, $result['password']))
        $response = "TRUE";
    
     else $response = "FALSE";
break;

    case 'payment':
  $cost = $params['cost'];
  $cost = $cost * 100;
     
   \Stripe\Stripe::setApiKey("sk_test_BQokikJOvBiI2HlWgH4olfQ2");

// Token is created using Checkout or Elements!
// Get the payment token ID submitted by the form:
$token = $_POST['stripeToken'];


$charge = \Stripe\Charge::create([
    'amount' => $cost,
    'currency' => 'usd',
    'description' => 'Example charge',
    'source' => $token,
]);


$view = file_get_contents('views/cart.html');
$scripts[] = 'https://samuelweems.com/candy_shop/js/emptyCart.js';
$scripts[] = 'https://samuelweems.com/candy_shop/js/getCart.js';

echo render_view($view,$scripts);
        break;


    case 'getCart':
        global $db;
        //print_r("HERE");
        $items = [];

        $uid = $params['uid'];

        $sql = "SELECT * FROM `shopping_cart` WHERE uid = '{$uid}'";

        $result = $db->query($sql);

         while ($row = $result->fetch_assoc()) {
             $items[] = $row;
             }
        $response= $items;
        break;

        case 'emptyCart':
        global $db;
      
        $items = [];
        $uid = $params['uid'];

        $sql = "DELETE FROM `shopping_cart` WHERE uid = '{$uid}'";

        $db->query($sql);

        break;

    default:
        $urls = [];
        foreach($routes['routes'] as $route){
            $urls[] = ['type'=>$route['type'],'url'=>'https://samuelweems.com'."/".$route['name']];
        }
        $response = $urls;
        $response['request_parts'] = $request_parts;
}

if($response !== false){
    $response['success']=true;
    print_response($response);
}
////////////////////////////////////////////////////////////////////////


function browse($offset=0,$size=10, $category='all'){
    global $db;
    $category = str_replace('%20', ' ', $category);
    if ($category == "all"){
        $category = "%";}

    

    $sql = "SELECT * FROM `products` WHERE category LIKE '$category' LIMIT {$offset}, {$size}";
    
    $result = $db->query($sql);
    
    while($row = $result->fetch_assoc()){
        $items[] = array_map('utf8_encode',$row);
    }
    
  //  print_r($items);
    return $items;
}

function getProductDetail($productID = 0){
    global $db;
    $sql = "SELECT * FROM `products` WHERE id= {$productID}";
    $result = $db->query($sql);
    $result = $result->fetch_assoc();
    return $result;
}

function getCategories($offset = 0, $size = 20){
    global $db;

    $sql = "SELECT count(category) as num,category FROM `products` group by category order by num desc LIMIT {$offset}, {$size}";

    $result = $db->query($sql);

    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }

    return $items; 
}
function searchCandy($params){
    
    $size = 18;
    $offset =0;
    // $offset = $params['offset'];
 
    global $db;
    $items = [];
   
    if(sizeof($params)==0){
        return $items;
    }

    // Start empty where clause
    $where = "WHERE";

    // Loop through array adding "key like value"
    // along with an "and" in case there are more than one filter pairs
    foreach($params as $k => $v){
        $where = $where." ".$k." LIKE '%".$v."%' AND" ;
    }

  

    // Add "1" for last and to make it work :) 
    $where .= " 1";
    
    $sql = "SELECT * FROM `products` {$where} LIMIT {$offset}, {$size}";

    $result = $db->query($sql);
    
    while($row = $result->fetch_assoc()){
        $items[] = array_map('utf8_encode',$row);
    }
    
    //$items['sql'] = $sql;

    //print_r($sql);

    return $items;
    
}

function getMenuItems($menu_id=1){
    
    if(!$menu_id){
        $menu_id = 1;
    }
  
    global $db;
    $items = [];

    $sql = "SELECT * from menu_items where mid = {$menu_id};";

    $result = $db->query($sql);

    while($row = $result->fetch_assoc()){   
        $items[] = $row;
    }
    
    return $items;
}



/**
 * Function print_response($respoonse)
 * 
 * This function builds a response object for requests that need a json 
 * data object. 
 */
function print_response($response){
    
  
    if($response['data']){
        $response['data_size'] = sizeof($response['data']);
    }
    header('Content-Type: application/json');
   // print_r($response);
    echo json_encode($response);
    exit;
}




/**
 * Function render_view($content)
 * Params: 
 *    $content: html content to be included in a built page
 */
function render_view($html, $scripts=[], $function){
    global $default_js_scripts;


    $page = file_get_contents('views/header.html');
    $page .= file_get_contents('views/navigation.html');
    $page .= $html;
    $page .= file_get_contents('views/footer.html');
    

    //add additional scripts to default array
    foreach($scripts as $s){
        $default_js_scripts[] = "<script src=\"{$s}\"></script>\n";
    }
    //$default_js_scripts[] = "\n</body>\n";
  //  $default_js_scripts[] = "</html>\n";

	//"build" the page by concatenating all parts
    $page .= "\n".implode("\n",$default_js_scripts);
    
    $page .= $function;
    echo $page;
    exit;
}

/**
 * This method turns a url of the format: 
 *     https://domain.com/routename/k1/v1/k2/v2/kn/vn 
 *     into an Associative Array: 
 *     $kvp = [
 *        'route' => 'routename',
 *        'k1' => 'v1',
 *        'k2' => 'v2',s
 *        'kn' => 'vn'
 *     ];
 */
function parseUrl($url){
    $parts = explode('/', $_SERVER['REQUEST_URI']);

    $kvp = [];

    // find the index of "app.php" (this filename)
    $i = array_search('candy_shop',$parts);

    // The route name should be located right after.
    $kvp["route"] = $parts[$i+1];

    // remove all unnecessary entries (up till now)
    for($j = 0;$j<=$i+1;$j++){
        array_shift($parts);
    }

    //check to see if last item is empty
    if(trim($parts[sizeof($parts)-1]) == ""){
        array_pop($parts);
    }

    if(sizeof($parts) % 2 == 1){
        $kvp["error"] = "Key value pairs do not match up!";
        return $kvp;
    }
    for($j=0;$j<sizeof($parts);$j+=2){
        $kvp[$parts[$j]] = $parts[$j+1];
    }
    
    return $kvp;
    
}




