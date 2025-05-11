<?php

class UserLoginTest extends PHPUnit\Framework\TestCase
{
    protected static $connection;

    public static function setUpBeforeClass(): void
    {
        require_once('.\php\connect.php'); 
        self::$connection = $connection; 
    }

    public function testSuccessfulLogin()
    {
        $username = 'fff';
        $password = 'fff'; 
        $query = "SELECT authenticate_user($1, $2) AS auth_result";
        $result = pg_query_params(self::$connection, $query, array($username, $password));
        $this->assertNotFalse($result);
        
        $row = pg_fetch_assoc($result);
        $auth_result = json_decode($row['auth_result'], true);
        
        $this->assertEquals('success', $auth_result['status'], "Login should be successful for user: $username");
    }

    public function testFailedLogin()
    {
        $username = 'invaliduser';
        $password = 'invalidpassword';
        $query = "SELECT authenticate_user($1, $2) AS auth_result";
        $result = pg_query_params(self::$connection, $query, array($username, $password));
        $this->assertNotFalse($result);
        
        $row = pg_fetch_assoc($result);
        $auth_result = json_decode($row['auth_result'], true);
        
        $this->assertEquals('error', $auth_result['status'], "Login should fail for user: $username");
    }

    public static function tearDownAfterClass(): void
    {
        pg_close(self::$connection);
    }
}
?>
