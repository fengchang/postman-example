<?php
require_once(ROOT.'lib/database.php');
require_once(ROOT.'lib/authentication.php');

class Logic
{
    function __construct()
    {
        $this->pdo = Database::Instance();
        $this->auth = Authentication::Instance();
    }

    private function check_username($username)
    {
        $stmt = $this->pdo->prepare('SELECT username FROM user WHERE username=:username');
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $row_count = $stmt->rowCount();
        if($row_count>=1)
        {
            return FALSE;
        }
        else
        {
            return TRUE;
        }
    }

    function register($username, $password, $nickname)
    {
        if($this->check_username($username))
        {
            $encrypted_pass = md5($password);
            $stmt = $this->pdo->prepare("INSERT INTO user (username, password, nickname, citycode, create_time)
            VALUES (:username, :password, :nickname, '010', NOW())");
            $stmt->bindValue(':username', $username, PDO::PARAM_STR);
            $stmt->bindValue(':password', $encrypted_pass, PDO::PARAM_STR);
            $stmt->bindValue(':nickname', $nickname, PDO::PARAM_STR);
            $stmt->execute();
            $user_id = $this->pdo->lastInsertId();

            return ['result'=>TRUE, 'code'=>201, 'msg'=>'注册成功'];
        }
        else
        {
            return['result'=>FALSE, 'code'=>409, 'msg'=>'用户名已经占用'];
        }
    }

    function login($username, $password)
    {
        $encrypted_pass = md5($password);
        $stmt = $this->pdo->prepare('SELECT id, nickname, balance FROM user WHERE username = :username AND password = :password');
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->bindValue(':password', $encrypted_pass, PDO::PARAM_STR);
        $stmt->execute();
        $row_count = $stmt->rowCount();
        if($row_count==1)
        {
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $user_id = $results[0]['id'];

            $token = $this->auth->add_device($user_id);

            return ['result'=>TRUE, 'code'=>200,
                'data'=>['token'=>$token, 'user_id'=>$user_id, 'nickname'=>$results[0]['nickname'], 'balance'=>$results[0]['balance']]];
        }
        else
        {
            return ['result'=>FALSE, 'msg'=>'用户名或密码错误', 'code'=>401];
        }
    }

    function regist_card($card_no, $desc)
    {
        $user_id = $this->auth->user_id();
        if($user_id==0)
            return ['result'=>FALSE, 'msg'=>'没有登录,Token为'.$_SERVER['HTTP_AUTHORIZATION'], 'code'=>401];

        $stmt = $this->pdo->prepare("SELECT * FROM card WHERE card_no=:cardno");
        $stmt->bindValue(':cardno', $card_no, PDO::PARAM_STR);
        $stmt->execute();
        if($stmt->rowCount()>0)
            return ['result'=>FALSE, 'msg'=>'卡已注册', 'code'=>409];

        $stmt = $this->pdo->prepare("INSERT INTO card (card_no, user_id, `desc`) VALUES (:cardno, :userid, :desc)");
        $stmt->bindValue(':cardno', $card_no, PDO::PARAM_STR);
        $stmt->bindValue(':desc', $desc, PDO::PARAM_STR);
        $stmt->bindValue(':userid', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        return ['result'=>TRUE, 'msg'=>'注册成功', 'code'=>200];
    }

    function get_card_info($card_no)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM card WHERE card_no=:cardno");
        $stmt->bindValue(':cardno', $card_no, PDO::PARAM_STR);
        $stmt->execute();
        if($stmt->rowCount()==0)
            return ['result'=>FALSE, 'msg'=>'卡未注册', 'code'=>404];
        else
        {
            $card = $stmt->fetch(PDO::FETCH_ASSOC);
            return ['result'=>TRUE, 'code'=>200, 'data'=>$card];
        }
    }

    function add_value_to_card($card_id, $amount)
    {
        $user_id = $this->auth->user_id();
        if($user_id==0)
            return ['result'=>FALSE, 'msg'=>'没有登录', 'code'=>401];

        if($amount>$this->auth->balance())
            return ['result'=>FALSE, 'msg'=>'余额不足', 'code'=>403];

        $stmt = $this->pdo->prepare("SELECT * FROM card WHERE id=:cardid");
        $stmt->bindValue(':cardid', $card_id, PDO::PARAM_INT);
        $stmt->execute();
        if($stmt->rowCount()==0)
            return ['result'=>FALSE, 'msg'=>'卡ID错误', 'code'=>400];

        $stmt = $this->pdo->prepare("INSERT INTO transcation (user_id, type, relative_id, amount, create_time)
        VALUES (:uid, 'add', :card_id, :amount, NOW())");
        $stmt->bindValue(':uid', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':card_id', $card_id, PDO::PARAM_INT);
        $stmt->bindValue(':amount', $amount, PDO::PARAM_INT);
        $stmt->execute();

        $stmt = $this->pdo->prepare("UPDATE user SET balance=balance-:amount WHERE id=:uid");
        $stmt->bindValue(':uid', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':amount', $amount, PDO::PARAM_INT);
        $stmt->execute();
        return ['result'=>TRUE, 'msg'=>'充值成功', 'code'=>200];
    }
}