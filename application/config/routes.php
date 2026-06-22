<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller']    = 'login';
$route['404_override']          = 'NotFound';
$route['translate_uri_dashes']  = FALSE;

$route['items/(:num)']              = 'items/index/$1';

// Routes barang masuk (in)
$route['items/in/(:num)']                   = 'items/in/$1';
$route['items/in/unit/(:any)/(:num)']       = 'items/in_unit/$1/$2';
$route['items/in/unit/(:any)']              = 'items/in_unit/$1';
$route['items/in/availability/(:any)/(:num)'] = 'items/in_availability/$1/$2';
$route['items/in/availability/(:any)']      = 'items/in_availability/$1';
$route['items/in/search/(:num)']            = 'items/in_search/$1';
$route['items/in/search']                   = 'items/in_search';
$route['items/in']                          = 'items/in';

// Routes barang keluar (out)
$route['items/out/(:num)']                  = 'items/out/$1';
$route['items/out/unit/(:any)/(:num)']      = 'items/out_unit/$1/$2';
$route['items/out/unit/(:any)']             = 'items/out_unit/$1';
$route['items/out/availability/(:any)/(:num)'] = 'items/out_availability/$1/$2';
$route['items/out/availability/(:any)']     = 'items/out_availability/$1';
$route['items/out/search/(:num)']           = 'items/out_search/$1';
$route['items/out/search']                  = 'items/out_search';
$route['items/out']                         = 'items/out';
$route['units/(:num)']      = 'units/index/$1';
$route['suppliers/(:num)']  = 'suppliers/index/$1';
$route['recipients/(:num)'] = 'recipients/index/$1';
$route['users/(:num)']      = 'users/index/$1';
$route['inputs/(:num)']     = 'inputs/index/$1';
$route['outputs/(:num)']    = 'outputs/index/$1';

// Routes konfirmasi checkout via email
$route['cartin/confirm/(:any)/(:any)']  = 'cartin/confirm/$1/$2';
$route['cartout/confirm/(:any)/(:any)'] = 'cartout/confirm/$1/$2';
