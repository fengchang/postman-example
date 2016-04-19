<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once('config.php');
require_once(ROOT.'lib/authentication.php');
require_once(ROOT.'lib/logic.php');

$logic = new Logic();

$app = new \Slim\Slim();

$app->post('/users', function () use ($app, $logic) {

    $response = $app->response();
    $response['Content-Type'] = 'application/json';

    $username = $app->request()->post('username');
    $password = $app->request()->post('password');
    $nickname = $app->request()->post('nickname');

    if(empty($username) or empty($password) or empty($nickname))
    {
        $response->status(400);
    }
    else
    {
        $ret = $logic->register($username, $password, $nickname);

        $response->status($ret['code']);

        if($ret['result'])
        {
            $response->body(json_encode(['msg'=>$ret['msg']]));
        }
        else
        {
            $response->body(json_encode(['error'=>$ret['msg']]));
        }
    }
});


//登录
$app->post('/authentication', function () use ($app, $logic) {

    $response = $app->response();
    $response['Content-Type'] = 'application/json';

    $username = $app->request()->post('username');
    $password = $app->request()->post('password');

    if(empty($username) or empty($password))
    {
        $response->status(400);
    }
    else
    {
        $ret = $logic->login($username, $password);
        $response->status($ret['code']);

        if($ret['result'])
        {
            $response->body(json_encode($ret['data']));
        }
        else
        {
            $response->body(json_encode(['error'=>$ret['msg']]));
        }
    }
});


$app->post('/cards', function () use ($app, $logic) {

    $response = $app->response();
    $response['Content-Type'] = 'application/json';

    $cardno = $app->request()->post('cardno');
    $desc = $app->request()->post('desc');

    if(empty($cardno) or empty($desc))
    {
        $response->status(400);
    }
    else
    {
        $ret = $logic->regist_card($cardno, $desc);
        $response->status($ret['code']);

        if($ret['result'])
        {
            $response->body(json_encode($ret));
        }
        else
        {
            $response->body(json_encode(['error'=>$ret['msg']]));
        }
    }
});

$app->get('/cards/:carno', function ($cardno) use ($app, $logic) {

    $response = $app->response();
    $response['Content-Type'] = 'application/json';

    if(empty($cardno))
    {
        $response->status(400);
    }
    else
    {
        $ret = $logic->get_card_info($cardno);
        $response->status($ret['code']);

        if($ret['result'])
        {
            $response->body(json_encode($ret['data']));
        }
        else
        {
            $response->body(json_encode(['error'=>$ret['msg']]));
        }
    }
});


$app->post('/deposit', function () use ($app, $logic) {

    $response = $app->response();
    $response['Content-Type'] = 'application/json';

    $cardid = $app->request()->post('cardid');
    $amount = $app->request()->post('amount');

    if(empty($cardid) or empty($amount))
    {
        $response->status(400);
    }
    else
    {
        $ret = $logic->add_value_to_card($cardid, intval($amount));
        $response->status($ret['code']);

        if($ret['result'])
        {
            $response->body(json_encode($ret));
        }
        else
        {
            $response->body(json_encode(['error'=>$ret['msg']]));
        }
    }
});

$app->run();
