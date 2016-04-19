<?php

class Database
{
    // Store the single instance of Database
    private static $m_pInstance;

    public static function Instance()
    {
        if(!self::$m_pInstance)
        {
            try
            {
                self::$m_pInstance = new PDO(sprintf("mysql:host=%s;port=%d;dbname=%s", DB_HOST, DB_PORT, DB_NAME),
                    DB_USER, DB_PASS,
                    array(PDO::ATTR_PERSISTENT => true));
                //Set the PDO error mode to exception
                self::$m_pInstance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            catch(PDOException $e)
            {
                //include_once(ROOT.'lib/Log.php');
                //Log::Instance()->addNotice("PDO Connenction Error.", getallheaders());
                echo 'mysql connection failed on '.DB_HOST;
            }
        }
        return self::$m_pInstance;
    }
}