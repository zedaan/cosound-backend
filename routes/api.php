<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group.
|
*/

use Dingo\Api\Routing\Router;

/**
 * Group with Throttle
 * @var Associative-Array $group
 */
$group = [
    'middleware' => 'api.throttle',
    'limit' => 60,
    'expires' => 1,
    'namespace' => 'App\Http\Controllers'
];

/** 
 * @var Router $api 
 */
$api = app(Router::class);

$api->version('v1', $group, function (Router $api) {

    // Public APIs starts from here...
    // $api->resource('countries', 'CountryController');
    $api->resource('genres', 'GenreController');

    $api->post('register', 'Auth\RegisterController@register')->name('register');
    $api->post('password/forgot', 'Auth\PasswordController@forgot')->name('password.forgot');
    $api->get('password/reset/{code}', 'Auth\PasswordController@reset')->name('password.reset.get');
    $api->post('password/reset', 'Auth\PasswordController@postReset')->name('password.reset.post');

    // Login...
    $api->group([
        'namespace' => 'Auth',
        'prefix' => 'login'
    ], function (Router $api) {
        
        // Local 
        $api->post('/', 'LoginController@login')->name('login');

        // Google
        $api->get('google', 'LoginController@googleRedirect')->name('login.google');
        $api->get('google/callback', 'LoginController@googleCallback')->name('login.google.callback');
        
        // Facebook
        $api->get('facebook', 'LoginController@facebookRedirect')->name('login.facebook');
        $api->get('facebook/callback', 'LoginController@facebookCallback')->name('login.facebook.callback');
    });    
    
    // Public View
    $api->group([
        'prefix' => 'users'
    ], function (Router $api) {

        $api->get('{id}', 'Auth\UserController@publicProfile')->name('user.profile.public');
        $api->get('{id}/uploads/{type?}', 'Auth\UserController@publicUploads')->name('user.uploads.public');
        $api->get('{id}/posts', 'Post\PostController@publicPosts')->name('user.posts.public');
    });
    
    $api->group([
        'prefix' => 'posts',
        'namespace' => 'Post'
    ], function (Router $api) {

        // Get Post
        $api->get('{id}', 'PostController@fetchById')->name('post.fetchById');
        // Get Comments of Post
        $api->get('{id}/comments', 'PostController@getComments')->name('post.comment.get');
    });

    // refresh token
    $api->post('refresh', 'Auth\TokenController@refresh')->name('refresh');
    
    // Protected APIs starts from here...
    $api->group(['middleware' => 'jwt.auth'], function(Router $api) {
        
        // Handle user token...
        
        $api->post('logout', 'Auth\TokenController@logout')->name('logout');
        $api->get('login/via_token', 'Auth\LoginController@loginViaToken')->name('login.viaToken');
        $api->get('me', 'Auth\UserController@me')->name('user.show');

        // Protect APIs with Confirmed Email address starts from here...
        $api->group(['middleware' => 'isConfirmed'], function (Router $api) {
            
            // Handle user...
            $api->put('password/change', 'Auth\UserController@changePassword')->name('password.change');
            $api->put('update', 'Auth\UserController@update')->name('user.update');
            $api->post('update/avatar', 'Auth\UserController@updateAvatar')->name('user.update.avatar');
        });
        
        // SignUp Suggesstions...
        $api->get('suggestions', 'Auth\UserController@suggestions')->name('suggestions');
        
        // Follow/Unfollow users...
        $api->post('follow', 'Auth\UserController@follow')->name('follow');

        // Getting Follow Status By User
        $api->get('user/{id}/follow', 'Auth\UserController@getFollowStatusByUser')->name('user.follow.get');
        
        // User posts...
        $api->group([
            'prefix' => 'posts',
            'namespace' => 'Post'
        ], function (Router $api) {

            $api->post('/', 'PostController@save')->name('post.save');
            $api->get('/', 'PostController@fetch')->name('post.fetch');
            $api->delete('{id}', 'PostController@deletePost')->name('post.delete');
            
            $api->post('{id}/repost', 'PostController@repost')->name('post.repost');
            
            $api->get('{id}/status', 'PostController@processStatus')->name('post.processStatus');
            
            // Like/Unlike user post...
            $api->post('{id}/like', 'PostController@like')->name('post.like');
            
            // Comment on user post...
            $api->post('{id}/comments', 'PostController@postComment')->name('post.comment.post');
            $api->delete('{post_id}/comments/{comment_id}', 'PostController@deleteComment')->name('post.comment.delete');

            // Music Artist and Title update
            $api->put('music/{id}/edit', 'PostController@editMusicInfo')->name('post.music.edit');
        });
        
        // User uploads
        $api->get('uploads/{type?}', 'Auth\UserController@fetchUploads')->name('upload.fetch');
        
        // User feeds
        $api->get('feeds', 'Post\FeedController@feeds')->name('feed.get');
        $api->post('feeds/enrich', 'Post\FeedController@enrich')->name('feed.enrich');

        $api->get('notifications', 'Notification\NotificationController@notifications')->name('notifications.get');
        $api->get('notifications/count', 'Notification\NotificationController@count')->name('notifications.count');
        $api->put('notifications/action/{type}', 'Notification\NotificationController@action')->name('notifications.action');

        $api->post('payment_methods/card', 'PaymentMethodController@saveCard')->name('paymentMethods.card.save');
        $api->get('payment_methods/card', 'PaymentMethodController@getCard')->name('paymentMethods.card.get');
        $api->delete('payment_methods/card', 'PaymentMethodController@removeCard')->name('paymentMethods.card.remove');

        // Marketplace
        $api->group([
            'prefix' => 'marketplace',
            'namespace' => 'Marketplace'
        ], function (Router $api) {
            
            $api->get('categories', 'ServiceCategoryController@serviceCategories')->name('marketplace.serviceCategories');
            $api->get('categories/{category_id}/{subcategory_id?}', 'ServiceController@getServicesByCategory')->name('marketplace.services.getByCategory');

            $api->post('services', 'ServiceController@createService')->name('marketplace.services.create');
            $api->get('services/featured', 'ServiceController@getFeaturedServices')->name('marketplace.services.featured');
            $api->get('services/offered', 'ServiceController@getOfferedServices')->name('marketplace.services.offered');
            $api->get('services/purchased', 'ServiceController@getPurchasedServices')->name('marketplace.services.purchased');
            $api->get('services/{id}', 'ServiceController@getServiceById')->name('marketplace.services.getById');

            $api->get('services/{id}/reviews', 'ReviewController@index')->name('marketplace.services.reviews.index');
            $api->post('services/{id}/reviews', 'ReviewController@store')->name('marketplace.services.reviews.store');
            $api->delete('services/{id}/reviews/{reviewId}', 'ReviewController@destroy')->name('marketplace.services.reviews.destroy');

            $api->get('cart', 'CartController@getCartItems')->name('marketplace.cart.getItems');
            $api->get('cart/count', 'CartController@count')->name('marketplace.cart.count');
            $api->post('cart', 'CartController@addToCart')->name('marketplace.cart.add');
            $api->delete('cart/{id}', 'CartController@removeFromCart')->name('marketplace.cart.remove');
            
            $api->post('place_order', 'OrderController@placeOrder')->name('marketplace.order.place');
        });

        // Admin
        $api->group([
        'middleware' => 'isAdmin',
        'prefix' => 'admin',
        'namespace' => 'Admin'], function (Router $api) {
            
            // Marketplace Management...
            $api->group([
                'prefix' => 'marketplace',
                'namespace' => 'Marketplace'], function (Router $api) {
                    
                $api->get('services/active', 'ServiceController@activeServices')->name('admin.marketplace.services.active');
                $api->get('services/pending', 'ServiceController@pendingServices')->name('admin.marketplace.services.pending');

                $api->get('services/{id}', 'ServiceController@getServiceById')->name('admin.marketplace.services.getById');
                $api->put('services/{id}/approve', 'ServiceController@approveService')->name('admin.marketplace.services.approve');
                $api->delete('services/{id}', 'ServiceController@deleteService')->name('admin.marketplace.services.delete');
            });

            // User Management
            $api->group([
                'prefix' => 'users',
                'namespace' => 'User'], function (Router $api) {
                    
                $api->get('/', 'UserController@fetchUsers')->name('admin.users.fetch');
                $api->get('/{id}', 'UserController@fetchUserById')->name('admin.users.fetchById');
                $api->put('/{id}/admin', 'UserController@toggleAdminStatus')->name('admin.users.toggleAdminStatus');
            });
        });
        // Search
        $api->group([
            'prefix' => 'search'
        ], function (Router $api) {
            $api->get('users', 'Auth\UserController@search')->name('search.users');
            // $api->get('users', 'SearchController@searchUsers')->name('search.users');
        });

    });
});
