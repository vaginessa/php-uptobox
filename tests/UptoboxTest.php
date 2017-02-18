<?php

//
// Oussama Elgoumri
// contact@sec4ar.com
//
// Sat Feb 18 12:45:05 WET 2017
//

namespace OussamaElgoumri;

use OussamaElgoumri\Exceptions\UptoboxInfoExpiredException;
use OussamaElgoumri\Exceptions\UptoboxLinkDeadException;
use OussamaElgoumri\Exceptions\UptoboxLinkIsNotValidException;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\DomCrawler\Crawler;


class LinkTest extends TestCommon
{
    public function test_workflow()
    {
        $link = new Uptobox($this->link_not_in_my_account);
        $info = $link->getInfo();
        $this->assertTrue(is_array($info));
        $this->assertEquals(count($info), 9);
    }

    /**
     * @test
     */
    public function setDirectAndStream()
    {
        list($obj, $m2) = $this->getMethod('setDirectAndStream');
        $m2->invoke($obj, $this->link);

        $this->assertTrue(is_string($obj->getDirect()));
        $this->assertRegExp('/https?:\/\/uptostream.com\/iframe\/.*/', $obj->getStream());
    }

    /**
     * @test
     */
    public function setFields()
    {
        list($obj, $m) = $this->getMethod('setFields');
        $data = $m->invoke($obj, $this->link);
        $this->assertGreaterThanOrEqual(8, count($data));

        $crawler = Request::get($this->link_not_in_my_account);
        $data = $m->invoke($obj, $crawler);
        $this->assertGreaterThanOrEqual(8, count($data));
    }

    /**
     * @test
     */
    public function rename()
    {
        $crawler = Request::get($this->link_not_in_my_account);
        list($obj, $m2) = $this->getMethod('setInfo');
        $m2->invoke($obj, $crawler);

        list($obj1, $m1) = $this->getMethod('addToMyAccount');
        $link = $m1->invoke($obj, $this->link_not_in_my_account);

        list($obj2, $m) = $this->getMethod('rename');
        $m->invoke($obj, $link);

        $crawler = Request::get('https://uptobox.com/?op=my_files');
        $first_link = $crawler->filter('.cell_files td:nth-child(2) a')->first()->attr('href');

        $crawler = Request::get($first_link);
        $title = $crawler->filter('.para_title')->text();
        preg_match('/(.*) +\(.*\)$/i', $title, $m);
        $this->assertEquals(strlen($m[1]), 44);
    }

    /**
     * @test
     */
    public function addToMyAccount()
    {
        list($obj, $m) = $this->getMethod('setInfo');
        $m->invoke($obj, new Crawler(curl_get($this->link_not_in_my_account)));

        $crawler = Request::get('https://uptobox.com/?op=my_files');

        try {
            $first_link = $crawler->filter('.cell_files td:nth-child(2) a')->first()->attr('href');

            list($obj1, $m) = $this->getMethod('addToMyAccount');
            $new_link = $m->invoke($obj, $this->link_not_in_my_account);
            $this->assertNotEquals($first_link, $new_link);

            $crawler = Request::get('https://uptobox.com/?op=my_files');
            $first_link = $crawler->filter('.cell_files td:nth-child(2) a')->first()->attr('href');
            $this->assertEquals($first_link, $new_link);
        } catch (\InvalidArgumentException $e) {
            //
        }
    }

    /**
     * @test
     */
    public function setInfo()
    {
        list($obj, $m) = $this->getMethod('setInfo');
        $crawler = Request::get($this->link);

        $data = $m->invoke($obj, $crawler);
        $this->assertEquals(array_keys($data), [
            'para_title', 'name', 'size', 'add_to_my_account',
        ]);

        $crawler = Request::get($this->link_not_in_my_account);
        $data = $m->invoke($obj, $crawler);
        $this->assertEquals(array_keys($data), [
            'para_title', 'name', 'size', 'add_to_my_account',
        ]);
    }

    /**
     * @test
     */
    public function isDead()
    {
        list($obj, $m) = $this->getMethod('isDead');
        $crawler = Request::get($this->link);

        $this->assertFalse($m->invoke($obj, $crawler, $this->link));

        try {
            $crawler = Request::get($this->dead_link);
            $m->invoke($obj, $crawler, $this->dead_link); 
        } catch (UptoboxLinkDeadException $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * @test
     */
    public function validate()
    {
        list($obj, $m) = $this->getMethod('validate');
        
        $this->assertTrue($m->invoke($obj, $this->link));

        try {
            $m->invoke($obj, $this->invalid_link);
        } catch (UptoboxLinkIsNotValidException $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * Set the test environment.
     */
    public function setUp()
    {
        parent::setUp();

        $this->dead_link              = 'https://uptobox.com/934zvu9srwpc';
        $this->invalid_link           = 'invalid';
        $this->link                   = 'https://uptobox.com/934zvu9srwic';
        $this->link_not_in_my_account = 'http://uptobox.com/7kq5grk35g0t';
    }  
}
