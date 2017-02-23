<?php

//
// Oussama Elgoumri
// contact@sec4ar.com
//
// Sat Feb 18 12:45:29 WET 2017
//

namespace OussamaElgoumri;

use OussamaElgoumri\Exceptions\UptoboxCouldNotGetNewLinkFromYourAccountException;
use OussamaElgoumri\Exceptions\UptoboxDirectLinkException;
use OussamaElgoumri\Exceptions\UptoboxInfoExpiredException;
use OussamaElgoumri\Exceptions\UptoboxLinkDeadException;
use OussamaElgoumri\Exceptions\UptoboxLinkFieldsException;
use OussamaElgoumri\Exceptions\UptoboxLinkIsNotValidException;
use OussamaElgoumri\Exceptions\UptoboxNotAddedToYourAccountException;
use OussamaElgoumri\Exceptions\UptoboxStreamException;
use Symfony\Component\DomCrawler\Crawler;

class Uptobox
{
    protected $attempt_add_to_my_account = 0;
    protected $attempt_get_direct_link = 0;
    protected $attempt_get_fields = 0;
    protected $attempt_get_new_link = 0;
    protected $attempt_set_stream = 0;
    protected $attempt_set_info = 0;
    protected $fields;
    protected $info = [];
    protected $link;


    /**
     * Link constructor.
     *
     * @param $link
     * @throws UptoboxLinkDeadException
     * @throws \HttpUrlException
     */
    public function __construct($link)
    {
        $this->info['source_link'] = $this->link = $link;
        $this->info['new_link']    = $this->link = $link;

        $this->validate($link);
        $crawler = Request::get($link);

        $this->isDead($crawler, $link);
        $this->setInfo($crawler);

        if ($this->info['add_to_my_account']) {
            $this->info['new_link'] =
                $this->link         =
                $link               =
                $this->addToMyAccount($link);
        }

        $this->rename($link);

        if ($this->info['add_to_my_account']) {
            $this->setFields($link);
        } else {
            $this->setFields($crawler);
        }

        $this->setDirectAndStream($link);
    }

    /**
     * Get uptobox link information.
     *
     * @return array
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * Check if direct link still active.
     *
     * @param  string    $direct
     * @return bool
     */
    private function isDirectOk($direct)
    {
        if (get_headers($direct)[3] === 'Content-Type: application/octet-stream') {
            return true;
        }

        return false;
    }

    /**
     * Set direct link.
     *
     * @param  string    $link
     * @return string
     */
    private function setDirectAndStream($link, $set_stream = true)
    {
        if (!$this->fields) {
            $this->setFields($link);
        }

        $crawler = Request::post($link, $this->fields, [ CURLOPT_REFERER => $link ]);
        $direct = $crawler->filter('.tos + div a');

        if ($direct && $direct->count()) {
            $direct = $direct->first()->attr('href');

            if ($set_stream) {
                $this->setStream($crawler, $link);
            }

            return $this->info['direct'] = $direct;
        }

        $direct = $crawler->filter('div[align="center"] > a:first-child');

        if ($direct && $direct->count()) {
            $direct = $direct->first()->attr('href');

            if ($set_stream) {
                $this->setStream($crawler, $link);
            }

            return $this->info['direct'] = $direct;
        }

        if ($this->attempt_get_direct_link < 3) {
            sleep($this->attempt_get_direct_link + 1);
            $this->attempt_get_direct_link += 1;
            return $this->setDirectAndStream($link, $set_stream);
        }

        if ($this->attempt_get_direct_link === 3) {
            $this->attempt_get_direct_link += 1;
            sleep(60);
            ftruncate(fopen(getCookieFile(), 'r+'), 0);
            return $this->setDirectAndStream($link, $set_stream);
        }

        $direct = $crawler->filter('td[align="center"] > a:first-child');

        if ($direct && $direct->count()) {
            $direct = $direct->first()->attr('href');

            if ($set_stream) {
                $this->setStream($crawler, $link);
            }

            return $this->info['direct'] = $direct;
        }

        throw new UptoboxDirectLinkException($link);
    }

    /**
     * Rename file in an uptobox account.
     *
     * @param string    $link
     * @return string
     */
    private function rename($link)
    {
        preg_match('/uptobox\.com\/(.*)/i', $link, $m);
        $file = new \SplFileinfo($this->info['name']);
        $this->info['slug'] 
            = $filename 
            = sha1($this->info['name']) . '.' . $file->getExtension();

        Request::post(
            'http://uptobox.com',
            [
                'file_code'     => $m[1],
                'file_descr'    => '',
                'file_name'     => $filename,
                'file_password' => '',
                'file_public'   => 1,
                'op'            => 'file_edit',
                'save'          => 'Submit',
            ],
            [
                CURLOPT_REFERER    => "https://uptobox.com/?op=file_edit&file_code={$m[1]}",
            ]
        );
    }

    /**
     * Add to my uptobox account.
     *
     * @param  string    $link
     * @return string
     */
    private function addToMyAccount($link)
    {
        $rand = mt_rand(1, 9);
        preg_match('/uptobox\.com\/(.*)/i', $link, $m);
        $query = "http://uptobox.com?op=my_files&add_my_acc={$m[1]}&rnd=0.{$rand}162496938370168";
        $data = Request::get($query, [], false);

        if ($data !== 'Added to your account') {
            if ($this->attempt_add_to_my_account < 3) {
                sleep($this->attempt_add_to_my_account + 1);
                $this->attempt_add_to_my_account += 1;
                return $this->addToMyAccount($link);                
            } 

            throw new UptoboxNotAddedToYourAccountException($link);
        }

        try {
            $crawler = Request::get('https://uptobox.com/?op=my_files');
            $new_link = array_values(array_filter($crawler->filter('.cell_files td:nth-child(2) a')->each(function($link) {
                $name = substr($this->info['name'], 0, 60);

                if (strpos($link->text(), $name) === 0) {
                    return $link->attr('href');
                }
            })));

            if (!isset($new_link[0])) {
                throw new \InvalidArgumentException();
            }

            return $new_link[0];
        } catch (\InvalidArgumentException $e) {
            if ($this->attempt_get_new_link < 3) {
                sleep($this->attempt_get_new_link + 1);
                $this->attempt_get_new_link += 1;
                return $this->addToMyAccount($link);
            } 

            throw new UptoboxCouldNotGetNewLinkFromYourAccountException();
        }
    }

    /**
     * set uptobox stream link
     *
     * @return bool|null|string
     * @throws UptoboxLinkFieldsException
     * @throws UptoboxStreamException
     */
    public function setStream(Crawler $crawler, $link)
    {
        $key = 'uptostream:';
        $stream = $crawler->filter('.tos + div a');

        if ($stream && $stream->count() === 2) {
            $s = $stream->last()->attr('href');
            return $this->info['stream'] = preg_replace('/(.*)\/(.*)$/', '$1/iframe/$2', $s);
        }

        if ($stream && $stream->count() === 1) {
            return $this->info['stream'] = '';
        }

        $stream = $crawler->filter('a[href*="uptostream"]');

        if ($stream && $stream->count()) {
            $s = $stream->first()->attr('href');
            return $this->info['stream'] = preg_replace('/(.*)\/(.*)$/', '$1/iframe/$2', $s);
        }

        if ($this->attempt_set_stream < 3) {
            sleep($this->attempt_set_stream + 1);
            $this->attempt_set_stream += 1;

            return $this->setStream($crawler, $link);
        }

        $this->info['stream'] = '';
    }

    /**
     * Get stream.
     *
     * @return string
     */
    public function getStream()
    {
        return $this->info['stream'];
    }

    /**
     * Get direct download link.
     *
     * @return string
     */
    public function getDirect()
    {
        return $this->info['direct'];
    }

    /**
     * Get fields to send post request.
     *
     * @param  string   $link
     * @throws UptoboxLinkFieldsException
     * @return array
     */
    private function setFields($data)
    {
        if ($data instanceof Crawler) {
            $crawler = $data;
        } else {
            $link = $data;
            $crawler = Request::get($link);
        }

        $inputs = $crawler->filter('form[name="F1"] input[name]');

        if ($inputs && $inputs->count()) {
            $data_fields = $inputs->each(function ($input, $i) {
                $name = $input->attr('name');
                $contents = $input->attr('value');

                if (!$contents) {
                    $contents = '';
                }

                return [$name => $contents];
            });

            if (count($data_fields)) {
                $fields = [];

                foreach ($data_fields as $field) {
                    foreach ($field as $key => $value) {
                        $fields[$key] = $value;
                    }
                }

                return $this->fields = $fields;
            }
        }

        if ($this->attempt_get_fields < 3) {
            sleep($this->attempt_get_fields + 1);

            $this->attempt_get_fields += 1;
            return $this->setFields(Request::get($this->link));
        }

        throw new UptoboxLinkFieldsException();
    }

    /**
     * Make sure we have a valid uptobox link.
     *
     * @param string    $link
     * @throws \HttpUrlException
     * @throws UptoboxLinkIsNotValid
     */
    public function validate($link)
    {
        if (!filter_var($link, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
            throw new UptoboxLinkIsNotValidException($link);
        }

        if (!preg_match('/https?:\/\/uptobox.com\/.+/i', $link)) {
            throw new UptoboxLinkIsNotValidException($link);
        }

        return true;
    }

    /**
     * Set link info.
     *
     * @param  $crawler
     * @return Crawler
     */
    private function setInfo($crawler)
    {
        try {
            $text = $crawler->filter('.para_title')->text();
            preg_match('/(.+) +\((.+)\)$/', $text, $m);

            $this->info['para_title'] = $text;
            $this->info['name'] = $m[1];
            $this->info['size'] = $m[2];

            $addToMyAccount = $crawler->filter('.tos small a.blue_link');

            if ($addToMyAccount->count()) {
                $this->info['add_to_my_account'] = true;
            } else {
                $this->info['add_to_my_account'] = false;
            }

            return $this->info;
        } catch (\InvalidArgumentException $e) {
            if ($this->attempt_set_info < 3) {
                sleep($this->attempt_set_info + 1);
                $this->attempt_set_info += 1;

                $crawler = Request::get($this->link);
                return $this->setInfo($crawler);
            }
        }

        $this->info['para_title'] = 'Unknown (0 MB)';
        $this->info['name'] = 'Unknown';
        $this->info['size'] = '0 MB';
        $this->info['add_to_my_account'] = false;
    }

    /**
     * Is link dead?
     *
     * @param  Crawler $crawler
     * @return bool
     */
    private function isDead(Crawler $crawler, $link)
    {
        // File not found:
        $c = $crawler->filter('.reseller .para_title');

        if ($c && $c->count()) {
            throw new UptoboxLinkDeadException($link);
        }

        // Page not found:
        $c = $crawler->filter('.faq .faq_list');

        if ($c && $c->count()) {
            throw new UptoboxLinkDeadException($link);
        }

        return false;
    }
}
