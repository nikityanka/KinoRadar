<?php

class MovieSearchTest extends PHPUnit\Framework\TestCase
{
    protected static $connection;

    public static function setUpBeforeClass(): void
    {
        self::$connection = pg_connect("host=172.20.7.53 port=5432 dbname=db2991_04 user=st2991 password=pwd2991 connect_timeout=20");
        $schema_name = "movie_search";
        pg_query(self::$connection, "SET search_path TO $schema_name");
    }

    public function testSearchByTitle()
    {
        $searchQuery = 'Начало';
        $result = pg_query_params(self::$connection, "SELECT * FROM get_filtered_movies(p_limit => 50, p_offset => 0) WHERE title ILIKE $1", array('%' . $searchQuery . '%'));
        $this->assertNotFalse($result);
        $this->assertGreaterThan(0, pg_num_rows($result), "Фильмов не найдено с заголовком: $searchQuery");
    }

    public function testSearchByYearRange()
    {
        $yearFrom = 2000;
        $yearTo = 2020;
        $result = pg_query_params(self::$connection, "SELECT * FROM get_filtered_movies(p_year_from => $1, p_year_to => $2, p_limit => 50, p_offset => 0)", array($yearFrom, $yearTo));
        $this->assertNotFalse($result);
        $this->assertGreaterThan(0, pg_num_rows($result), "Фильмов не найдено с интервалом от $yearFrom до $yearTo");
    }

    public static function tearDownAfterClass(): void
    {
        pg_close(self::$connection);
    }
}
?>
