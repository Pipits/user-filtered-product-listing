<html>
<body>
<?php

// array for filters
$filters = array();

// default options
$opts = $default_opts = [
    'paginate' => true,
    'count' => 12,
    'template' => 'products/list_multi_column',
];


/* ========== GETTING THE VALUES ========== */

// ---------- BRANDS
// only read the values if parameter exists and is an array
if(isset($_GET['brand'])) {

    if(is_array($_GET['brand'])) {
        // ?brand%5B%5D=
        // glue together the slugs as 'brand1,brand2,brand3'
        $brand_slugs = implode(",", $_GET['brand']);
    } else {
        // ?brand=
        $brand_slugs = $_GET['brand'];
    }

    // add a filter
    $filters[] = [
        'filter' => 'brand.slug',
        'match' => 'in',
        'value' => $brand_slugs,
    ];

    PerchSystem::set_var('selected_brands', $brand_slugs);
}



// ---------- CATEGORIES
if(isset($_GET['category'])) {

    $cat_paths = $_GET['category'];

    // add the category option to $opts
    $opts['category'] = $cat_paths;
    $opts['category-match'] = 'any';

    if(is_array($_GET['category'])) {
        // ?category%5B%5D=
        $selected_cats = implode(",", $cat_paths);
    } else {
        // ?category=
        $selected_cats = $cat_paths;
    }

    PerchSystem::set_var('selected_cats', $selected_cats);
}



// ---------- PRICES
if(perch_get('price')) {
    $price = perch_get('price');

    // if numeric, we have one number (?price=minPrice)
    if(is_numeric($price)) {
        // filter for greater or equal to
        $filters[] = [
            'filter' => 'price',
            'match' => 'gte',
            'value' => $price,
        ];

        PerchSystem::set_var('selected_price', $price);
    } elseif(substr_count($price, '-') == 1) {
        // we have a signle hyphen (hopefully ?price=minPrice-maxPrice)

        // split
        $prices = explode('-', $price);

        // check both are numeric
        if(is_numeric($prices[0]) && is_numeric($prices[1])) {
            $min_price = $prices[0];
            $max_price = $prices[1];

            // filter for items between min and max inclusively
            $filters[] = [
                'filter' => 'price',
                'match' => 'eqbetween',
                'value' => $min_price . ',' . $max_price,
            ];

            PerchSystem::set_var('selected_price', $min_price . '-' . $max_price);
        }
    }
}




/* ========== SETTING UP THE FORM ========== */

$cats_fieldset = perch_categories([
    'set' => 'products',
    'template' => '_filters_fieldset',
], true);


$brands_fieldset = perch_shop_brands([
    'template' => 'brands/_filters_fieldset',
], true);


PerchSystem::set_var('cats_fieldset', $cats_fieldset);
PerchSystem::set_var('brands_fieldset', $brands_fieldset);

perch_form('product_filters.html');




/* ---------- LISTING ---------- */

// add the filter option if we have some
if(count($filters) > 0) {
    $opts['filter'] = $filters;
}

// add filter-mode option if we have multiple filters
if(count($filters) > 1) {
    $opts['filter-mode'] = 'ungrouped';
}

// get filtered listing, but don't output it
$products = perch_shop_products($opts, true);

// output filtered listing if the result isn't empty
// otherwise display unfiltered listing
if($products) {
    echo $products;
} else {
    echo "<div>Your search didn't match any items</div>";
    perch_shop_products($default_opts);
}
?>
</body>
</html>