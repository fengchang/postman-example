<?php

require_once(ROOT.'lib/database.php');

class Authentication
{
    private static $m_pInstance;
    private $pdo;
    private $user_id;

    public static function Instance()
    {
        if (!self::$m_pInstance)
        {
            self::$m_pInstance = new Authentication();
        }
        return self::$m_pInstance;
    }

    private function __construct()
    {
        $this->pdo = Database::Instance();
        if(isset($_SERVER['HTTP_X_AUTHORIZATION']))
        {
            $token = $_SERVER['HTTP_X_AUTHORIZATION'];
            $this->token_to_userid($token);
        }
        else
        {
            $this->user_id = 0;
        }
    }

    private function token_to_userid($token)
    {
        if(empty($token))
        {
            $this->user_id = 0;
        }
        else
        {
            $stmt = $this->pdo->prepare("SELECT user_id FROM user_device WHERE token=:token");
            $stmt->bindValue(':token', $token, PDO::PARAM_STR);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(empty($results))
            {
                $this->user_id = 0;
            }
            else
            {
                $this->user_id = $results[0]['user_id'];
            }
        }
    }

    function add_device($user_id)
    {
        $token = md5(uniqid());
        $stmt = $this->pdo->prepare("INSERT INTO user_device (user_id, token, login_at, last_login)
        VALUES (:user_id, :token, NOW(), NOW())");
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':token', $token, PDO::PARAM_STR);
        $stmt->execute();
        return $token;
    }

    function user_id()
    {
        return $this->user_id;
    }

    function token()
    {
        if($this->user_id!=0)
        {
            return $_SERVER['HTTP_X_AUTHORIZATION'];
        }
        else
        {
            return NULL;
        }
    }

    function balance()
    {
        if($this->user_id==0)
            return 0;
        else
        {
            $stmt = $this->pdo->prepare("SELECT balance FROM user WHERE id=:uid");
            $stmt->bindValue(':uid', $this->user_id, PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user['balance'];
        }
    }
}
